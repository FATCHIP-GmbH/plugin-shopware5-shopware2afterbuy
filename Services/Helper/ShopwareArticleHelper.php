<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
use Shopware\Models\Article\Repository;
use Shopware\Models\Article\Unit as ShopwareUnit;
use Shopware\Models\Property\Group as FilterGroup;
use Shopware\Models\Property\Option as FilterOption;
use Shopware\Models\Property\Value as FilterValue;
use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Models\Status;
use viaebShopwareAfterbuy\ValueObjects\Article;
use viaebShopwareAfterbuy\ValueObjects\ProductPicture;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use viaebShopwareAfterbuy\ValueObjects\Article as ValueArticle;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Article as ShopwareArticle;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Image as ArticleImage;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Image\Mapping as ImageMapping;
use Shopware\Models\Article\Image\Rule as ImageRule;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Attribute\Article as ArticlesAttribute;
use Shopware\Models\Attribute\ArticlePrice;
use Shopware\Models\Attribute\ArticleSupplier;
use Shopware\Models\Attribute\Category as CategoryAttribute;
use Shopware\Models\Attribute\ConfiguratorOption;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Media\Media;
use Zend_Db_Adapter_Exception;


/**
 * Class ShopwareArticleHelper
 * @package viaebShopwareAfterbuy\Services\Helper
 */
class ShopwareArticleHelper extends AbstractHelper
{
    /** @var */
    protected $suppliers;

    /** @var */
    protected $customerGroup;

    /** @var */
    protected $configuratorOptions;

    /** @var ImageMapping[] */
    private $imageMappings;

    /** @var ConfiguratorGroup[] */
    private $configuratorGroups;

    /**
     * @param ArticleDetail $detail
     */
    public function fixMissingAttribute(ArticleDetail $detail)
    {
        $attr = new ArticlesAttribute();
        $detail->setAttribute($attr);

        try {
            $this->entityManager->persist($detail);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving attribute');
        } catch (ORMException $e) {
            $this->logger->error('Error saving attribute');
        }
    }


    /**
     * @param array $ids
     */
    public function updateExternalIds(array $ids)
    {
        $sql = '';

        foreach ($ids as $internalId => $externalId) {
            $sql .= "INSERT INTO s_articles_attributes (articledetailsID, afterbuy_id)
VALUES ($internalId, $externalId)
ON duplicate key update afterbuy_id = $externalId;";

        }

        if ( ! empty($sql)) {
            try {
                $this->db->query($sql);
            } catch (Zend_Db_Adapter_Exception $e) {
                $this->logger->error('Error storing external ids', array($sql));
            }
        }
    }

    /**
     * @param $id
     *
     * @return object|null
     */
    public function getDefaultCustomerGroup($id)
    {
        $this->customerGroup = $this->entityManager->getRepository(Group::class)->findOneBy(
            array('id' => $id)
        );

        return $this->customerGroup;
    }

    /**
     * @param ShopwareArticle $entity
     * @param ValueArticle    $article
     * @param bool            $netInput
     */
    public function setSimpleArticleValues(ShopwareArticle $entity, ValueArticle &$article, bool $netInput)
    {
        $detail = $entity->getMainDetail();

        if ($detail->getEan()) {
            $article->setEan($detail->getEan());
        }
        $article->setInternalIdentifier($detail->getNumber());
        $article->setStockMin($detail->getStockMin());
        $article->setStock($detail->getInStock());
        $article->setWeight((string) $detail->getWeight());

        if($detail->getPurchaseUnit()) {
            $article->setBasePriceFactor(Helper::convertNumberToABString($detail->getPurchaseUnit()));
        }

        /** @noinspection PhpDeprecationInspection */
        if($entity->getLastStock()) {
            $article->setLastStock(true);
        }

        $prices = $detail->getPrices();
        foreach ($prices AS $detailPrice) {
            $group = $detailPrice->getCustomerGroup();
            if ($group === $this->customerGroup) {
                $price = $detailPrice;
                break;
            }
        }
        // if no price is found for the default customer group, use first found price
        if (empty($price)) {
            $price = $detail->getPrices()->first();
        }

        $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput);
        $article->setPrice($price);
        $article->setBuyingPrice($detail->getPurchasePrice());

        $article->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());
        $article->setSupplierNumber($detail->getSupplierNumber());

        $article->setVariantId($detail->getId());
        $article->setVariantArticles(null);
    }

    /**
     * @param ShopwareArticle $entity
     * @param ArticleDetail $detail
     * @param string $targetEntity
     * @param bool $netInput
     *
     * @return mixed
     */
    public function setVariantValues(
        ShopwareArticle $entity,
        ArticleDetail $detail,
        string $targetEntity,
        bool $netInput
    ) {
        /** @var ValueArticle $variant */
        $variant = new $targetEntity();

        if ($detail->getEan()) {
            $variant->setEan($detail->getEan());
        }

        $variant->setTax($entity->getTax()->getTax());
        $variant->setInternalIdentifier($detail->getNumber());
        $variant->setStockMin($detail->getStockMin());
        $variant->setStock($detail->getInStock());
        $variant->setSupplierNumber($detail->getSupplierNumber());
        $variant->setVariantId($detail->getId());
        $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());
        $variant->setWeight((string) $detail->getWeight());

        if($detail->getPurchaseUnit()) {
            $variant->setBasePriceFactor(Helper::convertNumberToABString($detail->getPurchaseUnit()));
        }

        // Method getLastSotck() is in sw53 in Article. Since sw54 it is in ArticleDetail.
        $object = method_exists($detail, 'getLastStiock') ? $detail : $entity;
        if($object->getLastStock()) {
            $variant->setLastStock(true);
        }

        $prices = $detail->getPrices();
        foreach ($prices AS $detailPrice) {
            $group = $detailPrice->getCustomerGroup();
            if ($group === $this->customerGroup) {
                $price = $detailPrice;
                break;
            }
        }
        // if no price is found for the default customer group, use first found price
        if (empty($price)) {
            $price = $detail->getPrices()->first();
        }

        $options = [];

        foreach ($detail->getConfiguratorOptions() as $option) {
            /**
             * @var Option $option
             */

            $options[$option->getGroup()->getName()] = $option->getName();
        }
        // we have to take care that the order of variant options stays the same
        ksort($options);

        $variant->setVariants($options);

        $variant->setName($entity->getName() . ' ' . implode(' ', array_values($options)));

        $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput);

        $variant->setPrice($price);
        $variant->setBuyingPrice($detail->getPurchasePrice());

        $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());

        return $variant;
    }

    /**
     * @param ValueArticle    $article
     * @param ShopwareArticle $entity
     */
    public function assignCategories(ValueArticle &$article, ShopwareArticle $entity)
    {
        $categories = [];

        foreach ($entity->getCategories() as $category) {
            /** @var Category $category */
            if ( ! $category->getAttribute() || ! $category->getAttribute()->getAfterbuyCatalogId()) {
                continue;
            }

            $categories[] = $category->getAttribute()->getAfterbuyCatalogId();
        }

        $article->setExternalCategoryIds($categories);
    }

    /**
     * @param ShopwareArticle    $entity
     * @param ValueArticle       $article
     * @param ArticleDetail|null $detail
     */
    public function assignArticleImages(
        ShopwareArticle $entity,
        ValueArticle &$article,
        ArticleDetail $detail = null
    )
    {
        // add pictures from the main article that do not belong to
        // any variant.
        $images = new ArrayCollection();
        if ($detail !== null) {
            $mainImages = $entity->getImages();
            foreach ($mainImages as $mainImage) {
                /** @var  Image $mainImage */
                $mapping = $mainImage->getMappings();
                if (! $mapping->count()) {
                    $images->add($mainImage);
                }
            }
        }

        $mappedImages = ($detail === null) ?$entity->getImages() : $detail->getImages();

        foreach ($mappedImages as $mappedImage) {
            $images->add($mappedImage);
        }

        if ($images->count()) {
            foreach ($images as $index => $image) {

                /** @var Image $image */
                if($image->getMedia() === null && $image->getParent() === null) {
                    continue;
                }

                try {
                    $path = $image->getParent() === null ? $image->getMedia()->getPath() : $image->getParent()->getMedia()->getPath();
                } catch (Exception $e) {
                    continue;
                }

                $url = $this->mediaService->getUrl($path);

                if ($detail === null && $image->getMain() === 1) {
                    $article->setMainImageUrl($url);

                    $thumbnails = $image->getMedia()->getThumbnails();

                    if (is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }

                    if (isset($thumbnailUrl)) {
                        $article->setMainImageThumbnailUrl($thumbnailUrl);
                    }

                    continue;
                }

                if ($detail !== null && $image->getMain() === 1) {
                    $thumbnails = $image->getParent() !== null ? $image->getParent()->getMedia()->getThumbnails() : $image->getMedia()->getThumbnails();

                    if (is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }
                    else {
                        continue;
                    }

                    $article->setMainImageUrl($url);

                    if(!$thumbnailUrl) {
                        continue;
                    }

                    $article->setMainImageThumbnailUrl($thumbnailUrl);
                    continue;
                }

                /** @var Image $image */

                if ($detail !== null || ($image->getChildren() === null || $image->getChildren()->count() === 0)) {

                    $productPicture = new ProductPicture();
                    $productPicture->setAltText($entity->getName() . '_' . ((int)$image->getPosition()));
                    $productPicture->setNr($image->getPosition());
                    $productPicture->setUrl($url);

                    $article->addProductPicture($productPicture);
                }
            }
        }
    }

    /**
     * @param ShopwareArticle $entity
     * @param string $targetEntity
     *
     * @return ValueArticle
     */
    public function setArticleMainValues(ShopwareArticle $entity, string $targetEntity)
    {
        /**
         * article main values
         *
         * @var ValueArticle $article
         */
        $article = new $targetEntity();

        $article->setActive($entity->getActive());
        $article->setName($entity->getName());
        $article->setMainArticleId($entity->getId());


        $article->setDescription($entity->getDescriptionLong());
        $article->setShortDescription($entity->getDescription());
        $article->setKeywords($entity->getKeywords());

        $article->setTax($entity->getTax()->getTax());

        $article->setManufacturer($entity->getSupplier()->getName());

        return $article;
    }

    /**
     * @param ShopwareArticle $article
     * @param ArticleDetail $detail
     * @param array $variants
     */
    public function assignVariants(ShopwareArticle &$article, ArticleDetail $detail, array $variants)
    {
        if ( ! empty($variants)) {
            $groups = $this->getAssignableConfiguratorGroups($variants);
            $options = $this->getAssignableConfiguratorOptions($variants);

            $set = $this->getAssignableConfiguratorSet($article, $variants);

            if ($set) {
                $this->addSetGroups($set, $groups);
                $this->addSetOptions($set, $options);
            }

            $definedOptions = $detail->getConfiguratorOptions();
            foreach ($options as $addOption) {
                if(!$definedOptions->contains($addOption)) {
                    $definedOptions->add($addOption);
                }
            }
        }
    }

    /**
     * @param array $variants
     *
     * @return array
     */
    public function getAssignableConfiguratorOptions(array $variants)
    {
        if ( ! $this->configuratorOptions) {
            $this->getConfiguratorOptions();
        }

        $options = [];
        $this->configuratorOptions = array_change_key_case($this->configuratorOptions, CASE_LOWER);

        foreach ($variants as $variant) {
            if(array_key_exists(strtolower($variant['value']), $this->configuratorOptions)) {
                $option = $this->configuratorOptions[strtolower($variant['value'])];
            } else {
                $option = new Option();
                $option->setName($variant['value']);
                $option->setGroup($this->configuratorGroups[$variant['option']]);
                $option->setPosition(0);

                $attr = new ConfiguratorOption();
                $option->setAttribute($attr);

                try {
                    $this->entityManager->persist($option);
                    $this->entityManager->flush($option);
                } catch (OptimisticLockException $e) {
                    $this->logger->error('Error assigning configurator options', array(json_encode($option)));
                } catch (ORMException $e) {
                    $this->logger->error('Error assigning configurator options', array(json_encode($option)));
                }

                $this->getConfiguratorOptions();
                $this->configuratorOptions = array_change_key_case($this->configuratorOptions, CASE_LOWER);
            }

            $options[] = $option;
        }

        return $options;
    }

    /**
     * @noinspection PhpUnused
     *
     * @return array
     */
    public function getDetailIDsByExternalIdentifier()
    {
        return $this->entityManager->createQueryBuilder()
            ->select(['detail.id', 'detail.articleId', 'detail.number'])
            ->from(ArticleDetail::class, 'detail', 'detail.number')
            ->getQuery()
            ->getResult();
    }

    /**
     *
     */
    public function getConfiguratorOptions()
    {
        $options = $this->entityManager->createQueryBuilder()
            ->select('options')
            ->from(Option::class, 'options', 'options.name')
            ->getQuery()
            ->getResult();

        $this->configuratorOptions = $options;
    }

    /**
     * @param ShopwareArticle $article
     * @param array           $variants
     *
     * @return Set|null
     */
    public function getAssignableConfiguratorSet(ShopwareArticle &$article, array $variants)
    {
        $configuratorSet = null;

        if (empty($variants)) {
            return null;
        }

        $configuratorSet = $article->getConfiguratorSet();

        if ( ! $configuratorSet) {
            $configuratorSet = new Set();

            $article->setConfiguratorSet($configuratorSet);
            $configuratorSet->setName($article->getMainDetail()->getNumber());

        }

        return $configuratorSet;
    }

    /**
     * @param ArticleDetail $detail
     * @param string $parent
     *
     * @return ArticlesAttribute
     */
    public function getArticleAttributes(
        ArticleDetail &$detail,
        $parent = ''
    ) {
        if ($detail->getAttribute() === null) {
            $attr = $this->createAttributes($detail);
            $detail->setAttribute($attr);
        } else {
            $attr = $detail->getAttribute();
        }

        if ($parent) {
            $attr->setAfterbuyParentId($parent);
        }

        return $attr;
    }

    /**
     * create attributes for price if not existing
     *
     * @param Price $price
     *
     * @return ArticlePrice
     */
    public function createPriceAttributes(Price &$price)
    {
        if ($price->getAttribute() === null) {
            $priceAttr = new ArticlePrice();
            $price->setAttribute($priceAttr);
        } else {
            return $price->getAttribute();
        }

        return $priceAttr;
    }

    /**
     * creates article attributes and assign to detail
     *
     * @param ArticleDetail $detail
     * @return ArticlesAttribute()
     */
    public function createAttributes(ArticleDetail $detail)
    {
        $attr = new ArticlesAttribute();
        $attr->setArticleDetail($detail);

        return $attr;
    }


    /**
     * @param ArticleDetail $detail
     * @param Group         $group
     * @param float         $value
     * @param float         $pseudoPrice
     *
     * @return mixed|Price
     */
    public function storePrices(ArticleDetail &$detail, Group $group, float $value, $pseudoPrice = 0.00)
    {
        $this->customerGroup = $group;

        $price = $detail->getPrices()->filter(function (Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

        if ( ! $price) {
            $price = new Price();
            $price->setArticle($detail->getArticle());
            $price->setDetail($detail);
            $price->setCustomerGroup($group);

            $this->createPriceAttributes($price);
        }

        $price->setPrice($value);
        $price->setPseudoPrice($pseudoPrice);


        //assign price to variant
        if ( ! $detail->getPrices()->contains($price)) {
            $detail->getPrices()->add($price);
        }

        return $price;
    }

    /**
     * @param string $supplierName
     *
     * @return Supplier|string
     */
    public function getSupplier(string $supplierName)
    {
        if ( ! $this->suppliers) {
            $this->suppliers = $this->getSuppliers();
        }

        if (array_key_exists($supplierName, $this->suppliers)) {
            return $this->suppliers[$supplierName];
        }

        $supplier = $this->createSupplier($supplierName);
        $this->suppliers = $this->getSuppliers();

        return $supplier;
    }

    /**
     * @param string $name
     *
     * @return Supplier
     */
    public function createSupplier(string $name)
    {
        $supplier = new Supplier();
        $supplier->setName($name);

        $attribute = new ArticleSupplier();
        $supplier->setAttribute($attribute);

        try {
            $this->entityManager->persist($supplier);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving supplier', array($name));
        } catch (ORMException $e) {
            $this->logger->error('Error saving supplier', array($name));
        }

        return $supplier;
    }

    /**
     * @return array
     */
    public function getSuppliers()
    {
        $supplier = $this->entityManager->createQueryBuilder()
            ->select('supplier')
            ->from(Supplier::class, 'supplier', 'supplier.name')
            ->getQuery()
            ->getResult();

        $this->suppliers = $supplier;

        return $supplier;
    }

    /**
     * @param string $number
     * @return object|null
     */
    public function getArticleByNumber(string $number)
    {
        $article = $this->entityManager->getRepository(ArticleDetail::class)
            ->findOneBy(array('number' => $number));

        if($article !== null) {
            return $article->getArticle();
        }

       return null;
    }

    /**
     * @param $externalIdentifier
     * @return ArticleDetail
     */
    public function getArticleByExternalIdentifier($externalIdentifier)
    {
        /** @var Repository $repository */
        $repository = $this->entityManager->getRepository(ArticleDetail::class);

        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $repository->createQueryBuilder('detail');
        $builder->select(['detail'])
            ->leftJoin('detail.attribute', 'a')
            ->where('a.afterbuyId=:externalId')
            ->setParameter('externalId', $externalIdentifier);

        try {
            $article = $builder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            $this->logger->error($e->getMessage());

            return null;
        }

        return $article;
    }

    /**
     * @param string $number
     *
     * @return ShopwareArticle|null
     */
    public function getArticleFromAttribute(string $number)
    {
        $article = $this->entityManager->getRepository(ArticlesAttribute::class)
            ->findOneBy(array('afterbuyParentId' => $number));

        if($article) {
            return $article->getArticleDetail()->getArticle();
        }

        return $article;
    }

    /**
     * returns article. if not available article is needs to be created
     * TODO: we should use one identifier, which IS the external or the internal in respect to config
     *
     * @param string $number
     * @param string $name
     * @param string $parent
     *
     * @param string $externalIdentifyer
     * @return ShopwareArticle
     */
    public function getMainArticle(string $number, string $name, $parent = '', $externalIdentifyer = '')
    {
        $article = null;

        //article variant
        if ($parent) {
            $article = $this->getArticleFromAttribute($parent);

            if(!$article) {
                $article = $this->getArticleByNumber($parent);
            }
        }

        //main article
        if(!$parent) {
            $article = $this->getMainArticleByIdentifyer($number, $externalIdentifyer);

            //update main article name
            if($article) {
                $article->setName($name);

                try {
                    $this->entityManager->persist($article);
                }
                catch(ORMException $e) {
                    $this->logger->error($e->getMessage());
                }
            }

            //fallback get article via number of detail
            if (!$article) {
                $article = $this->getMainArticleFromDetailNumber($number);
            }
        }

        if ($article !== null) {
            return $article;
        }

        $article = $this->createMainArticle();

        return $article;
    }

    /**
     * @param string $number
     * @param string $externalIdentifyer
     * @return ShopwareArticle|null
     */
    public function getMainArticleByIdentifyer(string $number, string $externalIdentifyer) {
        if((int)$this->config['ordernumberMapping'] == 1) {
            $article = $this->getArticleFromAttribute($externalIdentifyer);
        }
        else {
            $article = $this->getArticleFromAttribute($number);
        }

        return $article;
    }

    /**
     * @param string $number
     * @return ShopwareArticle|null
     */
    public function getMainArticleFromDetailNumber(string $number) {
        $article = null;

        $detail = $this->entityManager
            ->getRepository(ArticleDetail::class)
            ->findOneBy(array('number' => $number));

        if(!empty($detail)) {
            $article = $detail->getArticle();
        }

        return $article;
    }

    /**
     * returns detail. if not available article is needs to be created
     *
     * @param string          $number
     * @param ShopwareArticle $article
     *
     * @return ArticleDetail
     */
    public function getDetail(string $number, ShopwareArticle &$article)
    {
        $detail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(array('number' => $number));

        if ($detail === null) {
            $detail = $this->createDetail($number);
        }

        if ( ! $article->getDetails()->contains($detail)) {
            $article->getDetails()->add($detail);
        }

        if ($detail->getArticle() !== $article) {
            $detail->setArticle($article);
        }

        if ( ! $article->getMainDetail()) {
            $article->setMainDetail($detail);
        }

        return $detail;
    }

    /**
     * creates and returns the main article
     *
     * @return ShopwareArticle
     */
    public function createMainArticle()
    {
        $article = new ShopwareArticle();
        $article->setName(uniqid('', true));

        try {
            $this->entityManager->persist($article);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving temporary main article');
        } catch (ORMException $e) {
            $this->logger->error('Error saving temporary main article');
        }

        return $article;
    }

    /**
     * creates and returns the detail
     *
     * @param string $number
     *
     * @return ArticleDetail
     */
    public function createDetail(string $number)
    {
        $detail = new ArticleDetail();
        $detail->setNumber($number);

        return $detail;
    }

    /**
     * @param array $variants
     *
     * @return array
     */
    public function getAssignableConfiguratorGroups(array $variants)
    {
        if ( ! $this->configuratorGroups) {
            $this->initializeConfiguratorGroupCache();
        }

        $groups = [];

        foreach ($variants as $variant) {
            if (array_key_exists($variant['option'], $this->configuratorGroups)) {
                $groups[] = $this->configuratorGroups[$variant['option']];
            } else {
                $groups[] = $this->createConfiguratorGroup($variant['option']);
                $this->initializeConfiguratorGroupCache();
            }

        }

        return $groups;
    }

    /**
     * @param string $name
     *
     * @return ConfiguratorGroup
     */
    public function createConfiguratorGroup(string $name)
    {
        $group = new ConfiguratorGroup();
        $group->setName($name);
        $group->setDescription($name);
        $group->setPosition(1337);

        try {
            $this->entityManager->persist($group);
            $this->entityManager->flush($group);
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving configurator group', array($name));
        } catch (ORMException $e) {
            $this->logger->error('Error saving configurator group', array($name));
        }

        return $group;
    }

    /**
     *
     */
    public function initializeConfiguratorGroupCache()
    {
        $groups = $this->entityManager->createQueryBuilder()
            ->select('groups')
            ->from(ConfiguratorGroup::class, 'groups', 'groups.name')
            ->getQuery()
            ->getResult();

        $this->configuratorGroups = $groups;
    }

    /**
     * add an option to a given set
     *
     * @param Set $set
     * @param     $options
     *
     * @return Set
     */
    public function addSetOptions(Set &$set, $options)
    {
        $setOptions = $set->getOptions();

        foreach ($options as $option) {
            // add missing options
            if ( ! $setOptions->contains($option)) {
                $setOptions->add($option);
            }
        }

        return $set;
    }

    /**
     * add group to a given set
     *
     * @param Set $set
     * @param     $groups
     *
     * @return Set
     */
    public function addSetGroups(Set &$set, $groups)
    {
        $setGroups = $set->getGroups();

        foreach ($groups as $group) {
            // add missing groups
            if ( ! $setGroups->contains($group)) {
                $setGroups->add($group);
            }
        }

        return $set;
    }

    /**
     * @param bool $force
     * @param bool $exportAll
     *
     * @return array|QueryBuilder
     */
    public function getUnexportedArticles($force = false, $exportAll = true)
    {
        $lastExport = $this->entityManager->getRepository(Status::class)->find(1);

        if ($lastExport) {
            $lastExport = $lastExport->getLastProductExport();
        }

        $articles = $this->entityManager->createQueryBuilder()
            ->select(['articles'])
            ->from(ShopwareArticle::class, 'articles', 'articles.id')
            ->leftJoin('articles.details', 'details')
            ->leftJoin('details.attribute', 'attributes');

        if ( ! $exportAll) {
            $articles->where('attributes.afterbuyExportEnabled = 1');
        }

        if ( ! $force) {
            $articles =
                $articles->andWhere("(attributes.afterbuyId IS NULL OR attributes.afterbuyId = '') OR articles.changed >= :lastExport")
                    ->setParameters(array('lastExport' => $lastExport));
        }

        $articles = $articles->getQuery()
            ->setMaxResults(250)
            ->getResult();

        return $articles;
    }

    /**
     */
    public function setArticlesWithoutAnyActiveVariantToInactive()
    {
        /** @noinspection SqlAggregates */
        $sql = 'UPDATE s_articles SET active = 0 WHERE id IN (
                SELECT articleID FROM s_articles_details GROUP BY articleID HAVING BIT_OR(instock) = 0 
                );';

        try {
            Shopware()->Db()->exec($sql);
        } catch (Exception $e) {
            $this->logger->error('Error setting articles without any active variant to inactive');
        }
    }

    /**
     * @param $valueArticle ValueArticle
     * @param $shopwareArticle ShopwareArticle
     */
    public function assignArticleProperties($valueArticle, $shopwareArticle)
    {
        $afterbuyGroup = $this->createFilterGroup('Afterbuy');

        if ($shopwareArticle->getPropertyGroup() === null) {
            $shopwareArticle->setPropertyGroup($afterbuyGroup);
        }

        foreach ($valueArticle->getArticleProperties() as $property) {
            $filterOption = $this->createFilterOption($afterbuyGroup, $property['name']);
            $filterValue = $this->createFilterValue($filterOption, $property['value']);

            if (!$shopwareArticle->getPropertyValues()->contains($filterValue)) {
                $shopwareArticle->getPropertyValues()->add($filterValue);
            }
        }
    }

    /**
     * @param ValueArticle $valueArticle
     * @param ShopwareArticle $shopwareArticle
     */
    public function setMainArticleValues(Article $valueArticle, ShopwareArticle &$shopwareArticle) {
        if(!$valueArticle->getMainArticleId()) {
            $shopwareArticle->setName($valueArticle->getName());
            $shopwareArticle->setDescriptionLong($valueArticle->getDescription());
            $shopwareArticle->setKeywords($valueArticle->getKeywords());
            $shopwareArticle->setDescription($valueArticle->getShortDescription());
        }
    }

    /**
     * @param ValueArticle $valueArticle
     * @param ArticleDetail $articleDetail
     */
    public function setDetailValues(Article $valueArticle, ArticleDetail &$articleDetail) {
        $articleDetail->setLastStock($valueArticle->getStockMin());

        $articleDetail->setInStock($valueArticle->getStock());
        $articleDetail->setEan($valueArticle->getEan());
        $articleDetail->setWeight($valueArticle->getWeight());
        $articleDetail->setPurchasePrice($valueArticle->getBuyingPrice());

        /** @var ShopwareUnit $unit */
        $unit = $this->getUnitFromString($valueArticle->getUnitOfQuantity());
        $articleDetail->setUnit($unit);
        $articleDetail->setPurchaseUnit($valueArticle->getBasePriceFactor());
        $articleDetail->setSupplierNumber($valueArticle->getSupplierNumber());
        $articleDetail->setLastStock((int)$valueArticle->getDiscontinued());
        $articleDetail->setReferenceUnit(1);
    }

    /**
     * @param ValueArticle $valueArticle
     * @param ArticleDetail $articleDetail
     * @param ShopwareArticle $shopwareArticle
     */
    public function setArticleActiveState(Article $valueArticle, ArticleDetail &$articleDetail, ShopwareArticle &$shopwareArticle) {
        /** @noinspection PhpDeprecationInspection */
        $shopwareArticle->setLastStock((int)$valueArticle->getDiscontinued());

        if ($valueArticle->isActive()) {
            $articleDetail->setActive(1);
            $shopwareArticle->setActive(true);
        }
    }

    /**
     * @param array $valueArticles
     * @param bool $netInput
     * @param Group $customerGroup
     */
    public function importArticle(
        array $valueArticles,
        bool $netInput,
        Group $customerGroup
    ) {
        foreach ($valueArticles as $valueArticle) {
            /** @var Article $valueArticle */

            /** @var ShopwareArticle $shopwareArticle */
            $shopwareArticle = $this->getMainArticle(
                $valueArticle->getOrdernunmber(),
                $valueArticle->getName(),
                $valueArticle->getMainArticleId(),
                $valueArticle->getExternalIdentifier()
            );

            $this->setMainArticleValues($valueArticle, $shopwareArticle);

            if ($valueArticle->getBaseProductFlag() === Article::$BASE_PRODUCT_FLAG__VARIATION_SET) {

                try {
                    $this->entityManager->persist($shopwareArticle);
                    $this->entityManager->flush();
                }
                catch(OptimisticLockException $e) {
                    $this->logger->error('Error storing base article values!');
                    continue;
                } catch (ORMException $e) {
                    $this->logger->error('Error storing base article values!');
                    continue;
                }
            }

            $shopwareArticle->setSupplier($this->getSupplier($valueArticle->getManufacturer()));
            $shopwareArticle->setTax($this->getTax($valueArticle->getTax()));

            /** @var ArticleDetail $articleDetail */
            $articleDetail = $this->getDetail($valueArticle->getOrdernunmber(), $shopwareArticle);
            $price = Helper::convertPrice($valueArticle->getPrice(), $valueArticle->getTax(), false, $netInput);

            $this->setDetailValues($valueArticle, $articleDetail);
            $this->setArticleActiveState($valueArticle, $articleDetail, $shopwareArticle);
            $this->storePrices($articleDetail, $customerGroup, $price);
            $this->getArticleAttributes($articleDetail, $valueArticle->getMainArticleId());
            $articleDetail->getAttribute()->setAfterbuyInternalNumber($valueArticle->getAnr());
            $this->storeAfterbuyAttributes($articleDetail, $valueArticle);

            // to make sure we store the 'Afterbuy ProductID' in case the user chooses to use Afterbuy artikelNr as
            // order number
            $articleDetail->getAttribute()->setAfterbuyId($valueArticle->getExternalIdentifier());

            //have to flush cuz parent is not getting found otherwise
            try {
                $this->assignVariants($shopwareArticle, $articleDetail, $valueArticle->variants);
                $this->assignArticleProperties($valueArticle, $shopwareArticle);

                $this->entityManager->persist($shopwareArticle);

                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
                $this->logger->error($e->getMessage());
            } catch (ORMException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param ArticleDetail $articleDetail
     * @param ValueArticle $valueArticle
     */
    public function storeAfterbuyAttributes(ArticleDetail &$articleDetail, ValueArticle $valueArticle) {
        $articleDetail->getAttribute()->setAfterbuyFreeText_1($valueArticle->getFree1());
        $articleDetail->getAttribute()->setAfterbuyFreeText_2($valueArticle->getFree2());
        $articleDetail->getAttribute()->setAfterbuyFreeText_3($valueArticle->getFree3());
        $articleDetail->getAttribute()->setAfterbuyFreeText_4($valueArticle->getFree4());
        $articleDetail->getAttribute()->setAfterbuyFreeText_5($valueArticle->getFree5());
        $articleDetail->getAttribute()->setAfterbuyFreeText_6($valueArticle->getFree6());
        $articleDetail->getAttribute()->setAfterbuyFreeText_7($valueArticle->getFree7());
        $articleDetail->getAttribute()->setAfterbuyFreeText_8($valueArticle->getFree8());
        $articleDetail->getAttribute()->setAfterbuyFreeText_9($valueArticle->getFree9());
        $articleDetail->getAttribute()->setAfterbuyFreeText_10($valueArticle->getFree10());
    }

    /**
     * Returns the ShopwareUnit corresponding to the given unitString. If no unit exists with the given string, a new
     * one will be created
     *
     * @param string $unitString
     * @return ShopwareUnit
     */
    public function getUnitFromString(string $unitString)
    {
        $unit = $this->entityManager->getRepository(ShopwareUnit::class)->findOneBy(array('unit' => $unitString));

        if ($unit === null) {
            $unit = $this->createUnitFromString($unitString);
        }

        return $unit;
    }

    /**
     * @param string $unitString
     * @return ShopwareUnit
     */
    public function createUnitFromString(string $unitString)
    {
        $unit = new ShopwareUnit();
        $unit->setUnit($unitString);
        $unit->setName($unitString);

        try {
            $this->entityManager->persist($unit);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving unit', array($unit));
        } catch (ORMException $e) {
            $this->logger->error('Error saving unit', array($unit));
        }

        return $unit;
    }

    /**
     * @param ValueArticle[] $valueArticles
     */
    public function associateCategories(array $valueArticles)
    {
        foreach ($valueArticles as $valueArticle) {
            if ( ! $valueArticle->isMainProduct()) {
                continue;
            }

            foreach ($valueArticle->getExternalCategoryIds() as $categoryId) {
                /** @var CategoryAttribute $categoryAttribute */
                $categoryAttribute =
                    $this->entityManager->getRepository(CategoryAttribute::class)->findOneBy(
                        ['afterbuyCatalogId' => $categoryId]
                    );

                if ($categoryAttribute === null) {
                    continue;
                }

                $category = $categoryAttribute->getCategory();

                $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getOrdernunmber();

                /** @var ArticleDetail $articleDetail */
                $articleDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );

                if ($articleDetail === null) {
                    $detailAttribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                        ['afterbuyParentId' => $mainArticleId]
                    );

                    if($detailAttribute === null) {
                        $detailAttribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                            ['afterbuyParentId' => $valueArticle->getExternalIdentifier()]
                        );
                    }

                    if($detailAttribute) {
                        $articleDetail = $detailAttribute->getArticleDetail();
                    }
                }

                if ($articleDetail && $article = $articleDetail->getArticle()) {
                    $article->addCategory($category);
                }
            }
        }
    }

    /**
     * @param ValueArticle[] $valueArticles
     */
    public function associateImages(array $valueArticles)
    {
        if ( ! $this->configuratorGroups) {
            $this->initializeConfiguratorGroupCache();
        }

        foreach ($valueArticles as $valueArticle) {

            /** @var ArticlesAttribute $attribute */
            $attribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                ['afterbuyParentId' => $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier()]
            );

            if ( ! $attribute) {
                $mainDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $valueArticle->getOrdernunmber()]
                );
            } else {
                $mainDetail = $attribute->getArticleDetail()->getArticle()->getMainDetail();
            }


            foreach ($valueArticle->getProductPictures() as $productPicture) {
                $this->associateImage($valueArticle, $productPicture, $mainDetail);
            }
        }
    }


    /**
     * @param ValueArticle $valueArticle
     * @param ProductPicture $productPicture
     * @param ArticleDetail $mainDetail
     */
    private function associateImage(
        ValueArticle $valueArticle,
        ProductPicture $productPicture,
        ArticleDetail $mainDetail
    ) {
        $media = $this->createMediaImage(
            $productPicture->getUrl(),
            'Artikel'
        );

        if ($media === null) {
            return;
        }

        /** @var ArticleDetail $articleDetail */
        $articleDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
            ['number' => $valueArticle->getOrdernunmber()]
        );

        /** @var ModelRepository $imageRepo */
        $imageRepo = $this->entityManager->getRepository(ArticleImage::class);

        // all images, assigned to current article with current media
        /** @var ArticleImage[] $images */

        if ($articleDetail && $articleDetail->getArticle()) {
            $images = $imageRepo->findBy([
                'mediaId'   => $media->getId(),
                'articleId' => $articleDetail->getArticle()->getId(),
            ]);
        } elseif ($mainDetail && $mainDetail->getArticleId()) {
            $images = $imageRepo->findBy([
                'mediaId'   => $media->getId(),
                'articleId' => $mainDetail->getArticleId(),
            ]);
        } else {
            $images = array();
        }

        if (count($images) === 0) {
            $image = $this->createParentImage($media, $productPicture, $mainDetail->getArticle());
        } else {
            $image = $images[0];
        }

        $imageMapping = $this->getImageMapping($image);

        //we have to cache the mappings, otherwise we will not be able to find them if not flushed
        if ($image->getId()) {
            $mappings[$image->getId()] = $imageMapping;
        }

        if (is_array($valueArticle->variants) && count($valueArticle->variants) > 0) {
            $this->associateVariantImage($valueArticle, $imageMapping, $image);
        }

        if ( ! $valueArticle->isMainProduct()) {
            $this->createChildImage($image, $articleDetail);
        }

        // reset preview image status
        if ($valueArticle->isMainProduct() && $productPicture->getNr() === '0' && $image->getMain() !== 1) {
            foreach ($mainDetail->getArticle()->getImages() as $_image) {
                /** @var Image $_image */
                $_image->setMain(2);
            }
            $image->setMain(1);
        }
    }

    /**
     * @param ValueArticle $valueArticle
     * @param ImageMapping $imageMapping
     * @param ArticleImage $image
     */
    private function associateVariantImage(
        ValueArticle $valueArticle,
        ImageMapping $imageMapping,
        ArticleImage $image
    ) {
        foreach ($valueArticle->variants as $variantOption) {
            $optionName = $variantOption['value'];
            $optionGroup = $variantOption['option'];

            if (array_key_exists($optionGroup, $this->configuratorGroups)) {
                $group = $this->configuratorGroups[$optionGroup];
            } else {
                $group = $this->entityManager->getRepository(ConfiguratorGroup::class)->findOneBy([
                    'name' => $optionGroup,
                ]);
            }

            /** @var Option $option */
            $option = $this->entityManager->getRepository(Option::class)->findOneBy([
                'name'  => $optionName,
                'group' => $group,
            ]);

            $rule = null;

            if ($imageMapping->getId()) {
                $query = $this->entityManager->createQueryBuilder()
                    ->select(['rule'])
                    ->from(ImageRule::class, 'rule')
                    ->where('rule.mappingId = :mapping')
                    ->andWhere('rule.optionId = :option')
                    ->setParameters(array('mapping' => $imageMapping->getId(), 'option' => $option->getId()))
                    ->setMaxResults(1)
                    ->getQuery();

                try {
                    $rule = $query->getOneOrNullResult();
                } catch (NonUniqueResultException $e) {
                    $this->logger->error(
                        'More than one rule for given mapping and option found.',
                        [$imageMapping->getId(), 'option' => $option->getId()]
                    );
                }
            }

            if ( ! $rule) {
                $rule = new ImageRule();
                $rule->setMapping($imageMapping);
                $rule->setOption($option);
                $imageMapping->getRules()->add($rule);
            }
        }

        try {
            if (!$image->getMappings()->count()) {
                $image->getMappings()->add($imageMapping);
            } else {
                $this->entityManager->persist($imageMapping);
            }
        }
        catch (ORMException $e) {
            $this->logger->error('Error storing variant image association');
        }
    }

    /**
     * @param Media $media
     * @param ProductPicture $productPicture
     * @param ShopwareArticle $article
     *
     * @return ArticleImage
     */
    public function createParentImage(
        Media $media,
        ProductPicture $productPicture,
        ShopwareArticle $article
    ) {
        $image = new ArticleImage();

        $image->setArticle($article);
        $image->setPath($media->getName());
        $image->setDescription($media->getDescription());
        $image->setPosition($productPicture->getNr());
        $image->setExtension($media->getExtension());
        $image->setMedia($media);

        try {
            $this->entityManager->persist($image);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error($e->getMessage());
        } catch (ORMException $e) {
            $this->logger->error($e->getMessage());
        }

        return $image;
    }

    /**
     * @param ArticleImage $parent
     * @param ArticleDetail $detail
     *
     * @return ArticleImage
     */
    public function createChildImage(ArticleImage $parent, ArticleDetail $detail)
    {
        $image = new ArticleImage();

        $image->setPosition($parent->getPosition());
        $image->setExtension($parent->getExtension());
        $image->setParent($parent);
        $image->setArticleDetail($detail);

        try {
            $this->entityManager->persist($image);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error($e->getMessage());
        } catch (ORMException $e) {
            $this->logger->error($e->getMessage());
        }

        return $image;
    }

    /**
     * @param ArticleImage $image
     *
     * @return ImageMapping
     */
    private function getImageMapping(ArticleImage $image)
    {
        $imageMapping = null;

        if ($image->getId()) {
            // get mapping from cache
            if (is_array($this->imageMappings) && array_key_exists($image->getId(), $this->imageMappings)) {
                $imageMapping = $this->imageMappings[$image->getId()];
            }
        }

        if ( ! $imageMapping) {
            $imageMapping = new ImageMapping();
            $imageMapping->setImage($image);
        }

        return $imageMapping;
    }

    /**
     * @param array $columnConfig
     * @param array $config
     * @return array
     */
    public function manipulateArticleList(array $columnConfig, array $config)
    {
        foreach ($columnConfig['data'] as $index => $entity) {
            if ($entity['field'] === 'afterbuyId') {
                $columnConfig['data'][$index]['show'] = true;
            } elseif ($entity['field'] === 'afterbuyExportEnabled' && $config['mainSystem'] != 2 && (int)$config['ExportAllArticles'] == 0) {
                $columnConfig['data'][$index]['show'] = true;
                $columnConfig['data'][$index]['type'] = 'boolean';
            }
        }

        return $columnConfig;
    }

    /**
     * @param string $groupName
     * @return FilterGroup
     */
    public function createFilterGroup(string $groupName)
    {
        /** @var FilterGroup $filterGroup */
        $filterGroup = $this->entityManager->getRepository(FilterGroup::class)->findOneBy(
            ['name' => $groupName]
        );

        if ($filterGroup === null) {
            // create new group
            $filterGroup = new FilterGroup();
            $filterGroup->setName($groupName);
            $filterGroup->setPosition(0);
            $filterGroup->setComparable(0);
            $filterGroup->setSortMode(0);

            try {
                $this->entityManager->persist($filterGroup);
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
                $this->logger->error('Error saving FilterGroup');
            } catch (ORMException $e) {
                $this->logger->error('Error saving FilterGroup');
            }
        }

        return $filterGroup;
    }

    /**
     * @param FilterGroup $filterGroup
     * @param string $optionName
     * @return FilterOption
     */
    public function createFilterOption(FilterGroup $filterGroup, string $optionName)
    {
        /** @var FilterOption[] $options */
//        $option = $this->entityManager->getRepository(FilterOption::class)->findOneBy(['name' => $optionName]);
        $options = $filterGroup->getOptions();

        $option = null;

        $optionIsInGroup = false;

        foreach ($options as $option) {
            if ($option->getName() === $optionName) {
                $optionIsInGroup = true;
                break;
            }
        }

        if (!$optionIsInGroup) {
            // create new option for group
            $option = new FilterOption();
            $option->setName($optionName);
            $option->setFilterable(1);

            try {
                $this->entityManager->persist($option);
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
                $this->logger->error('Error saving FilterOption');
            } catch (ORMException $e) {
                $this->logger->error('Error saving FilterOption');
            }

            $filterGroup->addOption($option);
        }

        return $option;
    }

    /**
     * @param FilterOption $option
     * @param string $valueName
     * @return FilterValue
     */
    public function createFilterValue(FilterOption $option, string $valueName)
    {
        /** @var FilterValue $optionValues */
        $filterValue = $this->entityManager->getRepository(FilterValue::class)->findOneBy([
            'value' => $valueName,
            'optionId' => $option->getId(),
        ]);

        if ($filterValue === null) {
            // create new value for option
            $position = sizeof($option->getValues());
            $filterValue = new FilterValue($option, $valueName);
            $filterValue->setPosition($position);


            try {
                $this->entityManager->persist($filterValue);
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
                $this->logger->error('Error saving FilterValue');
            } catch (ORMException $e) {
                $this->logger->error('Error saving FilterValue');
            }
        }

        return $filterValue;
    }

}
