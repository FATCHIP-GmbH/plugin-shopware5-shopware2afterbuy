<?php

namespace viaebShopwareAfterbuy\Services\Helper;

use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Models\Status;
use viaebShopwareAfterbuy\ValueObjects\ProductPicture;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function fixMissingAttribute(ArticleDetail $detail): void
    {
        $attr = new ArticlesAttribute();
        $detail->setAttribute($attr);
        $this->entityManager->persist($detail);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // TODO: handle exception
        }
    }


    /**
     * @param array $ids
     */
    public function updateExternalIds(array $ids): void
    {
        $sql = '';

        foreach ($ids as $internalId => $externalId) {
            $sql .= "INSERT INTO s_articles_attributes (articledetailsID, afterbuy_id, articleID)
VALUES ($internalId, $externalId,NULL)
ON duplicate key update afterbuy_id = $externalId;";

        }

        if ( ! empty($sql)) {
            try {
                $this->db->query($sql);
            } catch (Zend_Db_Adapter_Exception $e) {
                // TODO: handle exception
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

        $price = $detail->getPrices()->filter(function (Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

        $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput);

        $article->setPrice($price);

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

        $price = $detail->getPrices()->filter(function (Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

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
        if ($detail === null) {
            $images = $entity->getImages();
        } else {
            $images = $detail->getImages();
        }

        if ($images->count()) {
            foreach ($images as $index => $image) {

                /** @var Image $image */

                try {
                    if ($detail === null) {
                        $path = $image->getMedia()->getPath();
                    } else {
                        $path = $image->getParent()->getMedia()->getPath();
                    }
                } catch (Exception $e) {
                    continue;
                }

                $url = $this->mediaService->getUrl($path);

                // TODO: check flipping conditions => faster
                if ($detail === null && $image->getMain() === 1) {
                    $article->setMainImageUrl($url);

                    $thumbnails = $image->getMedia()->getThumbnails();

                    if (is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }

                    // TODO: $thumbnailUrl might not have been defined
                    /** @noinspection PhpUndefinedVariableInspection */
                    $article->setMainImageThumbnailUrl($thumbnailUrl);
                    continue;
                }

                if ($index === 0 && $detail !== null) {
                    $thumbnails = $image->getParent()->getMedia()->getThumbnails();

                    if (is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }

                    $article->setMainImageUrl($url);
                    // TODO: $thumbnailUrl might not have been defined
                    /** @noinspection PhpUndefinedVariableInspection */
                    $article->setMainImageThumbnailUrl($thumbnailUrl);
                    continue;
                }

                /** @var Image $image */

                // TODO: check flipping conditions => faster
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
    public function setArticleMainValues(ShopwareArticle $entity, string $targetEntity): ValueArticle
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

        $article->setTax($entity->getTax()->getTax());

        $article->setManufacturer($entity->getSupplier()->getName());

        return $article;
    }

    /**
     * @param ShopwareArticle $article
     * @param ArticleDetail   $detail
     * @param array           $variants
     */
    public function assignVariants(ShopwareArticle &$article, ArticleDetail $detail, array $variants): void
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
    public function getAssignableConfiguratorOptions(array $variants): array
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

                $this->entityManager->persist($option);
                try {
                    $this->entityManager->flush($option);
                } catch (OptimisticLockException $e) {
                    // TODO: handle exception
                }

                $this->getConfiguratorOptions();
                $this->configuratorOptions = array_change_key_case($this->configuratorOptions, CASE_LOWER);
            }

            $options[] = $option;
        }

        return $options;
    }

    /**
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
    public function getConfiguratorOptions(): void
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
    public function getAssignableConfiguratorSet(ShopwareArticle &$article, array $variants): ?Set
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
     * @param ShopwareArticle $article
     * @param ArticleDetail   $detail
     * @param string          $parent
     *
     * @return ArticlesAttribute
     */
    public function getArticleAttributes(
        ShopwareArticle $article,
        ArticleDetail &$detail,
        $parent = ''
    ): ArticlesAttribute {
        if ($detail->getAttribute() === null) {
            $attr = $this->createAttributes($article, $detail, $parent);
            $detail->setAttribute($attr);
        } else {
            return $detail->getAttribute();
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
    public function createPriceAttributes(Price &$price): ArticlePrice
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
     * @param ShopwareArticle $article
     * @param ArticleDetail   $detail
     * @param string          $parent
     *
     * @return ArticlesAttribute()
     */
    public function createAttributes(ShopwareArticle $article, ArticleDetail $detail, $parent = ''): ArticlesAttribute
    {
        $attr = new ArticlesAttribute();

        $attr->setArticle($article);
        $attr->setArticleDetail($detail);

        if ($parent) {
            $attr->setAfterbuyParentId($parent);
        }

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
    public function createSupplier(string $name): Supplier
    {
        $supplier = new Supplier();
        $supplier->setName($name);

        $attribute = new ArticleSupplier();
        $supplier->setAttribute($attribute);

        $this->entityManager->persist($supplier);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // TODO: handle exception
        }

        return $supplier;
    }

    /**
     * @return array
     */
    public function getSuppliers(): array
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
     *
     * @return object|ArticlesAttribute|null
     */
    public function getArticleFromAttribute(string $number)
    {
        $article = $this->entityManager->getRepository(ArticlesAttribute::class)
            ->findOneBy(array('afterbuyParentId' => $number));

        return $article;
    }

    /**
     * returns article. if not available article is needs to be created
     *
     * @param string $number
     * @param string $name
     * @param string $parent
     *
     * @return ShopwareArticle
     */
    public function getMainArticle(string $number, string $name, $parent = ''): ?ShopwareArticle
    {
        $article = null;

        if ($parent) {
            $article = $this->getArticleFromAttribute($parent);
        } else {
            /**
             * @var ArticlesAttribute $article
             */
            $article = $this->getArticleFromAttribute($number);

            if ( ! $article) {
                $article = $this->entityManager
                    ->getRepository(ArticleDetail::class)
                    ->findOneBy(array('number' => $number));
            } else {
                //If Baseproduct we just will set the name
                $article->getArticle()->setName($name);
                $this->entityManager->persist($article);

                return null;
            }
        }

        if ($article !== null) {
            return $article->getArticle();
        }

        return $this->createMainArticle();
    }

    /**
     * returns detail. if not available article is needs to be created
     *
     * @param string          $number
     * @param ShopwareArticle $article
     *
     * @return ArticleDetail
     */
    public function getDetail(string $number, ShopwareArticle &$article): ArticleDetail
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
    public function createMainArticle(): ShopwareArticle
    {
        $article = new ShopwareArticle();

        $article->setName(uniqid('', true));

        $this->entityManager->persist($article);
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // TODO: handle exception
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
    public function createDetail(string $number): ArticleDetail
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
    public function getAssignableConfiguratorGroups(array $variants): array
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
    public function createConfiguratorGroup(string $name): ConfiguratorGroup
    {
        $group = new ConfiguratorGroup();
        $group->setName($name);
        $group->setDescription($name);
        $group->setPosition(1337);

        $this->entityManager->persist($group);
        try {
            $this->entityManager->flush($group);
        } catch (OptimisticLockException $e) {
            // TODO: handle exception
        }

        return $group;
    }

    /**
     *
     */
    public function initializeConfiguratorGroupCache(): void
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
    public function addSetOptions(Set &$set, $options): Set
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
    public function addSetGroups(Set &$set, $groups): Set
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
    public function setArticlesWithoutAnyActiveVariantToInactive(): void
    {
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
     * @param array $valueArticles
     * @param bool  $netInput
     * @param Group $customerGroup
     */
    public function importArticle(
        array $valueArticles,
        bool $netInput,
        Group $customerGroup
    ): void {
        foreach ($valueArticles as $valueArticle) {

            /** @var ShopwareArticle $shopwareArticle */
            $shopwareArticle = $this->getMainArticle(
                $valueArticle->getExternalIdentifier(),
                $valueArticle->getName(),
                $valueArticle->getMainArticleId()
            );

            if ( ! $shopwareArticle) {
                continue;
            }

            /** @var ArticleDetail $articleDetail */
            $articleDetail = $this->getDetail($valueArticle->getExternalIdentifier(), $shopwareArticle);

            //set main values
            $articleDetail->setLastStock($valueArticle->getStockMin());
            $shopwareArticle->setName($valueArticle->getName());
            $shopwareArticle->setDescriptionLong($valueArticle->getDescription());
            $articleDetail->setInStock($valueArticle->getStock());
            $articleDetail->setEan($valueArticle->getEan());

            if ($valueArticle->isActive()) {
                $articleDetail->setActive(1);
                $shopwareArticle->setActive(true);
            }

            $price = Helper::convertPrice($valueArticle->getPrice(), $valueArticle->getTax(), false, $netInput);

            $this->storePrices($articleDetail, $customerGroup, $price);

            $shopwareArticle->setSupplier($this->getSupplier($valueArticle->getManufacturer()));

            $this->getArticleAttributes($shopwareArticle, $articleDetail,
                $valueArticle->getMainArticleId());

            $shopwareArticle->setTax($this->getTax($valueArticle->getTax()));

            $this->assignVariants($shopwareArticle, $articleDetail, $valueArticle->variants);

            $this->entityManager->persist($shopwareArticle);

            //have to flush cuz parent is not getting found otherwise
            try {
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
            }
        }
    }

    /**
     * @param array $valueArticles
     */
    public function associateCategories(array $valueArticles): void
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

                $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier();

                /** @var ArticleDetail $articleDetail */
                $articleDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );

                if ($articleDetail === null) {
                    $articleDetail = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                        ['afterbuyParentId' => $mainArticleId]
                    );
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
    public function associateImages(array $valueArticles): void
    {
        if ( ! $this->configuratorGroups) {
            $this->initializeConfiguratorGroupCache();
        }

        foreach ($valueArticles as $valueArticle) {

            $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier();

            /** @var ArticlesAttribute $attribute */
            $attribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                ['afterbuyParentId' => $mainArticleId]
            );

            if ( ! $attribute) {
                $mainDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );
            } else {
                $mainDetail = $attribute->getArticle()->getMainDetail();
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
    ): void {
        $media = $this->createMediaImage(
            $productPicture->getUrl(),
            'Artikel'
        );

        if ($media === null) {
            return;
        }

        /** @var ArticleDetail $articleDetail */
        $articleDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
            ['number' => $valueArticle->getExternalIdentifier()]
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
    ): void {
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
                    // TODO: handle exception
                }
            }

            if ( ! $rule) {
                $rule = new ImageRule();
                $rule->setMapping($imageMapping);
                $rule->setOption($option);
                $imageMapping->getRules()->add($rule);
            }
        }

        if ( ! $image->getMappings()->count()) {
            $image->getMappings()->add($imageMapping);
        } else {
            $this->entityManager->persist($imageMapping);
        }
    }

    /**
     * @param Media           $media
     * @param ProductPicture  $productPicture
     * @param ShopwareArticle $article
     *
     * @return ArticleImage
     */
    public function createParentImage(
        Media $media,
        ProductPicture $productPicture,
        ShopwareArticle $article
    ): ArticleImage {
        $image = new ArticleImage();

        $image->setArticle($article);
        $image->setPath($media->getName());
        $image->setDescription($media->getDescription());
        $image->setPosition($productPicture->getNr());
        $image->setExtension($media->getExtension());
        $image->setMedia($media);

        $this->entityManager->persist($image);

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
        }

        return $image;
    }

    /**
     * @param ArticleImage  $parent
     * @param ArticleDetail $detail
     *
     * @return ArticleImage
     */
    public function createChildImage(ArticleImage $parent, ArticleDetail $detail): ArticleImage
    {
        $image = new ArticleImage();

        $image->setPosition($parent->getPosition());
        $image->setExtension($parent->getExtension());
        $image->setParent($parent);
        $image->setArticleDetail($detail);

        $this->entityManager->persist($image);

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
        }

        return $image;
    }

    /**
     * @param ArticleImage $image
     *
     * @return ImageMapping
     */
    private function getImageMapping(ArticleImage $image): ImageMapping
    {
        $imageMapping = null;

        if ($image->getId()) {
            // get mapping from cache
            if (is_array($this->imageMappings) && array_key_exists($image->getId(), $this->imageMappings)) {
                $imageMapping = $this->imageMappings[$image->getId()];
            }

            if ( ! $imageMapping) {
                $query = $this->entityManager->createQueryBuilder()
                    ->select(['mapping'])
                    ->from(ImageMapping::class, 'mapping')
                    ->where('mapping.imageId = :image')
                    ->setParameters(array('image' => $image->getId()))
                    ->setMaxResults(1)
                    ->getQuery();

                try {
                    $imageMapping = $query->getOneOrNullResult();
                } catch (NonUniqueResultException $e) {
                    // TODO: handle exception
                }
            }
        }

        if ( ! $imageMapping) {
            $imageMapping = new ImageMapping();
            $imageMapping->setImage($image);
        }

        return $imageMapping;
    }
}
