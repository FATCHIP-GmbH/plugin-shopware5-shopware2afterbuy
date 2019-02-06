<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\ValueObjects\ProductPicture;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Attribute\ArticleSupplier;
use Shopware\Models\Attribute\ConfiguratorOption;
use Shopware\Models\Customer\Group;
use Shopware\Models\Article\Detail;


/**
 * Class ShopwareArticleHelper
 * @package FatchipAfterbuy\Services\Helper
 */
class ShopwareArticleHelper extends AbstractHelper {

    /**
     * @var
     */
    protected $suppliers;

    /**
     * @var
     */
    protected $customerGroup;

    /**
     * @var
     */
    protected $configuratorGroups;

    /**
     * @var
     */
    protected $configuratorOptions;


    /**
     * @param array $ids
     */
    public function updateExternalIds(array $ids) {
        $sql = "";

        foreach ($ids as $internalId=>$externalId) {
            $sql .= "UPDATE s_articles_attributes SET afterbuy_id = $externalId WHERE articledetailsID = $internalId;";

        }

        if(!empty($sql)) {
            $this->db->query($sql);
        }
    }

    /**
     * @param $id
     * @return object|null
     */
    public function getDefaultCustomerGroup($id) {
        $this->customerGroup = $this->entityManager->getRepository(Group::class)->findOneBy(
            array('id' => $id)
        );

        return $this->customerGroup;
    }

    /**
     * @param Article $entity
     * @param \FatchipAfterbuy\ValueObjects\Article $article
     * @param bool $netInput
     */
    public function setSimpleArticleValues(Article $entity, \FatchipAfterbuy\ValueObjects\Article &$article, bool $netInput) {
        $detail = $entity->getMainDetail();

        if($detail->getEan()) {
            $article->setEan($detail->getEan());
        }
        $article->setInternalIdentifier($detail->getNumber());
        $article->setStockMin($detail->getStockMin());
        $article->setStock($detail->getInStock());

        $price = $detail->getPrices()->filter(function(Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

        $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput, false);

        $article->setPrice($price);

        $article->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());
        $article->setSupplierNumber($detail->getSupplierNumber());

        $article->setVariantId($detail->getId());
        $article->setVariantArticles(null);
    }

    /**
     * @param Article $entity
     * @param Detail $detail
     * @param $targetEntity
     * @param $netInput
     * @return mixed
     */
    public function setVariantValues(Article $entity, Detail $detail, $targetEntity, $netInput) {
        $variant = new $targetEntity();

        if($detail->getEan()) {
            $variant->setEan($detail->getEan());
        }

        $variant->setTax($entity->getTax()->getTax());
        $variant->setInternalIdentifier($detail->getNumber());
        $variant->setStockMin($detail->getStockMin());
        $variant->setStock($detail->getInStock());
        $variant->setSupplierNumber($detail->getSupplierNumber());
        $variant->setVariantId($detail->getId());
        $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());

        $price = $detail->getPrices()->filter(function(Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

        $options = [];

        foreach($detail->getConfiguratorOptions() as $option) {
            /**
             * @var Option $option
             */

            $options[$option->getGroup()->getName()] = $option->getName();
        }
        // we have to take care that the order of variant options stays the same
        ksort($options);

        $variant->setVariants($options);

        $variant->setName($entity->getName() . ' ' . implode(" ", array_values($options)));

        $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput, false);

        $variant->setPrice($price);

        $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());

        return $variant;
    }

    /**
     * @param \FatchipAfterbuy\ValueObjects\Article $article
     * @param Article $entity
     */
    public function assignCategories(\FatchipAfterbuy\ValueObjects\Article &$article, Article $entity) {
        $categories = [];

        foreach($entity->getCategories() as $category) {
            /** @var Category $category */
            if(!$category->getAttribute() || !$category->getAttribute()->getAfterbuyCatalogId()) {
                continue;
            }

            $categories[] = $category->getAttribute()->getAfterbuyCatalogId();
        }

        $article->setExternalCategoryIds($categories);
    }

    /**
     * @param Article $entity
     * @param \FatchipAfterbuy\ValueObjects\Article $article
     * @param Detail|null $detail
     */
    public function assignArticleImages(Article $entity, \FatchipAfterbuy\ValueObjects\Article &$article, Detail $detail = null) {
        if(is_null($detail)) {
            $images = $entity->getImages();
        } else {
            $images = $detail->getImages();
        }

        if($images->count()) {
            foreach($images as $index=>$image) {

                if(is_null($detail)) {
                    $path = $image->getMedia()->getPath();
                } else {
                    $path = $image->getParent()->getMedia()->getPath();
                }

                $url = $this->mediaService->getUrl($path);

                if($image->getMain() == 1 && is_null($detail)) {
                    $article->setMainImageUrl($url);

                    $thumbnails = $image->getMedia()->getThumbnails();

                    if(is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }

                    $article->setMainImageThumbnailUrl($thumbnailUrl);
                    continue;
                }

                if($index === 0 && !is_null($detail)) {
                    $thumbnails = $image->getParent()->getMedia()->getThumbnails();

                    if(is_array($thumbnails)) {
                        $thumbnail = reset($thumbnails);
                        $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                    }

                    $article->setMainImageUrl($url);
                    $article->setMainImageThumbnailUrl($thumbnailUrl);
                    continue;
                }


                if((is_null($image->getChildren()) || $image->getChildren()->count() === 0) || !is_null($detail)) {

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
     * @param Article $entity
     * @param $targetEntity
     * @return Article
     */
    public function setArticleMainValues(Article $entity, $targetEntity) {
        /**
         * article main values
         * @var Article $article
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
     * @param Article $article
     * @param Detail $detail
     * @param array $variants
     */
    public function assignVariants(Article &$article, Detail $detail, array $variants) {
        if(!empty($variants)) {
            $groups = $this->getAssignableConfiguratorGroups($variants);
            $options = $this->getAssignableConfiguratorOptions($article, $variants);

            $set = $this->getAssignableConfiguratorSet($article, $variants);

            if($set) {
                $this->addSetGroups($set, $groups);
                $this->addSetOptions($set, $options);
            }

            $detail->setConfiguratorOptions($options);
        }
    }

    /**
     * @param Article $article
     * @param array $variants
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAssignableConfiguratorOptions(Article &$article, array $variants) {
        if(!$this->configuratorOptions) {
            $this->getConfiguratorOptions();
        }

        $options = [];

        foreach($variants as $variant) {
            if(array_key_exists($variant["value"], $this->configuratorOptions)) {
                $option = $this->configuratorOptions[$variant["value"]];
            }
            else {
                $option = new Option();
                $option->setName($variant["value"]);
                $option->setGroup($this->configuratorGroups[$variant["option"]]);
                $option->setPosition(0);

                $attr = new ConfiguratorOption();
                $option->setAttribute($attr);

                $this->entityManager->persist($option);
                $this->entityManager->flush($option);

                $this->getConfiguratorOptions();
            }

            array_push($options, $option);
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getDetailIDsByExternalIdentifier(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select(['detail.id', 'detail.articleId', 'detail.number'])
            ->from(Detail::class, 'detail', 'detail.number')
            ->getQuery()
            ->getResult();
    }

    /**
     *
     */
    public function getConfiguratorOptions() {
        $options = $this->entityManager->createQueryBuilder()
            ->select('options')
            ->from('\Shopware\Models\Article\Configurator\Option', 'options', 'options.name')
            ->getQuery()
            ->getResult();

        $this->configuratorOptions = $options;
    }

    /**
     * @param Article $article
     * @param array $variants
     * @return Set|null
     */
    public function getAssignableConfiguratorSet(Article &$article, array $variants) {
        $configuratorSet = null;

        if(empty($variants)) {
           return null;
        }

        $configuratorSet = $article->getConfiguratorSet();

        if(!$configuratorSet) {
            $configuratorSet = new Set();

            $article->setConfiguratorSet($configuratorSet);
            $configuratorSet->setName($article->getMainDetail()->getNumber());

        }

        return $configuratorSet;
    }

    /**
     * @param Article $article
     * @param Detail $detail
     * @param string $parent
     * @return \Shopware\Models\Attribute\Article
     */
    public function getArticleAttributes(Article $article, Detail &$detail, $parent = '')
    {
        if(is_null($detail->getAttribute())) {
            $attr = $this->createAttributes($article, $detail, $parent);
            $detail->setAttribute($attr);
        } else return $detail->getAttribute();

        return $attr;
    }

    /**
     * create attributes for price if not existing
     *
     * @param Price $price
     * @return \Shopware\Models\Attribute\ArticlePrice
     */
    public function createPriceAttributes(Price &$price)
    {
        if(is_null($price->getAttribute()))
        {
            $priceAttr = new \Shopware\Models\Attribute\ArticlePrice();
            $price->setAttribute($priceAttr);
        } else return $price->getAttribute();

        return $priceAttr;
    }

    /**
     * creates article attributes and assign to detail
     *
     * @param Article $article
     * @param Detail $detail
     * @param string $parent
     * @return \Shopware\Models\Attribute\Article
     */
    public function createAttributes(Article $article, Detail $detail, $parent = '')
    {
        $attr = new \Shopware\Models\Attribute\Article();

        $attr->setArticle($article);
        $attr->setArticleDetail($detail);

        if($parent) {
            $attr->setAfterbuyParentId($parent);
        }

        return $attr;
    }


    /**
     * @param Detail $detail
     * @param Group $group
     * @param float $value
     * @param float $pseudoPrice
     * @return mixed|Price
     */
    public function storePrices(Detail &$detail, Group $group, float $value, $pseudoPrice = 0.00)
    {
        $this->customerGroup = $group;

        $price = $detail->getPrices()->filter(function(Price $price) {
            return $price->getCustomerGroup() === $this->customerGroup;
        })->first();

        if(!$price)
        {
            $price = new Price();
            $price->setArticle($detail->getArticle());
            $price->setDetail($detail);
            $price->setCustomerGroup($group);

            $this->createPriceAttributes($price);
        }

        $price->setPrice($value);
        $price->setPseudoPrice($pseudoPrice);



        //assign price to variant
        if(!$detail->getPrices()->contains($price)) $detail->getPrices()->add($price);

        return $price;
    }


    /**
     * @param string $supplier
     * @return Supplier|string
     */
    public function getSupplier(string $supplier) {
        if(!$this->suppliers) {
            $this->suppliers = $this->getSuppliers();
        }

        if(array_key_exists($supplier, $this->suppliers)) {
            return $this->suppliers[$supplier];
        }
        else {
            $supplier = $this->createSupplier($supplier);
            $this->suppliers = $this->getSuppliers();

            return $supplier;
        }
    }

    /**
     * @param string $name
     * @return Supplier
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createSupplier(string $name) {
        $supplier = new Supplier();
        $supplier->setName($name);

        $attribute = new ArticleSupplier();
        $supplier->setAttribute($attribute);

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        return $supplier;
    }

    /**
     * @return array
     */
    public function getSuppliers() {
        $supplier = $this->entityManager->createQueryBuilder()
            ->select('supplier')
            ->from('\Shopware\Models\Article\Supplier', 'supplier', 'supplier.name')
            ->getQuery()
            ->getResult();

        $this->suppliers = $supplier;
        return $supplier;
    }

    /**
     * @param string $number
     * @return object|\Shopware\Models\Attribute\Article|null
     */
    public function getArticleFromAttribute(string $number) {
        $article = $this->entityManager->getRepository('Shopware\Models\Attribute\Article')->findOneBy(array('afterbuyParentId' => $number));
        return $article;
    }

    /**
     * returns article. if not available article is needs to be created
     *
     * @param string $number
     * @param string $name
     * @param string $parent
     * @return \Shopware\Models\Article\Article
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getMainArticle(string $number, string $name, $parent = '')
    {
        $article = null;

        if($parent) {
            $article = $this->getArticleFromAttribute($parent);
        } else {
            /**
             * @var Article $article
             */
            $article = $this->getArticleFromAttribute($number);

            if(!$article) {
                $article = $this->entityManager->getRepository('Shopware\Models\Article\Detail')->findOneBy(array('number' => $number));
            }
            else {
                //If Baseproduct we just will set the name
                $article->getArticle()->setName($name);
                $this->entityManager->persist($article);
                return null;
            }
        }

        if(!is_null($article)) {
            return $article->getArticle();
        }
        else {
            return $this->createMainArticle($number, $parent);
        }
    }

    /**
     * returns detail. if not available article is needs to be created
     *
     * @return \Shopware\Models\Article\Detail
     */
    public function getDetail(string $number, Article &$article)
    {
        $detail = $this->entityManager->getRepository('Shopware\Models\Article\Detail')->findOneBy(array('number' => $number));

        if(is_null($detail)) $detail = $this->createDetail($number);

        if(!$article->getDetails()->contains($detail)) {
            $article->getDetails()->add($detail);
        }

        if($detail->getArticle() !== $article) {
            $detail->setArticle($article);
        }

        if(!$article->getMainDetail()) {
            $article->setMainDetail($detail);
        }

        return $detail;
    }

    /**
     * creates and returns the main article
     *
     * @param string $parent
     * @return \Shopware\Models\Article\Article
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createMainArticle(string $parent)
    {
        $article = new Article();

        $article->setName(uniqid());

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $article;
    }

    /**
     * creates and returns the detail
     *
     * @return \Shopware\Models\Article\Detail
     */
    public function createDetail(string $number)
    {
        $detail = new Detail();
        $detail->setNumber($number);

        return $detail;
    }

    /**
     * @param array $variants
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getAssignableConfiguratorGroups(array $variants) {
        if(!$this->configuratorGroups) {
            $this->getConfiguratorGroups();
        }

        $groups = [];

        foreach($variants as $variant) {
            if(array_key_exists($variant['option'] , $this->configuratorGroups)) {
                array_push($groups, $this->configuratorGroups[$variant['option']]);
            }
            else {
                array_push($groups, $this->createConfiguratorGroup($variant['option']));
                $this->getConfiguratorGroups();
            }

        }

        return $groups;
    }

    /**
     * @param string $name
     * @return \Shopware\Models\Article\Configurator\Group
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createConfiguratorGroup(string $name) {
        $group = new \Shopware\Models\Article\Configurator\Group();
        $group->setName($name);
        $group->setDescription($name);
        $group->setPosition(1337);

        $this->entityManager->persist($group);
        $this->entityManager->flush($group);

        return $group;
    }

    /**
     *
     */
    public function getConfiguratorGroups() {
        $groups = $this->entityManager->createQueryBuilder()
            ->select('groups')
            ->from('\Shopware\Models\Article\Configurator\Group', 'groups', 'groups.name')
            ->getQuery()
            ->getResult();

        $this->configuratorGroups = $groups;
    }

    /**
     * add an option to a given set
     *
     * @param Set $set
     * @param $options
     * @return Set
     */
    public function addSetOptions(Set &$set, $options)
    {
        $setOptions = $set->getOptions();

        foreach($options as $option)
        {
            // add missing options
            if(!$setOptions->contains($option)) {
                $setOptions->add($option);
            }
        }

        return $set;
    }

    /**
     * add group to a given set
     *
     * @param Set $set
     * @param $groups
     * @return Set
     */
    public function addSetGroups(Set &$set, $groups)
    {
        $setGroups = $set->getGroups();

        foreach($groups as $group)
        {
            // add missing groups
            if(!$setGroups->contains($group)) $setGroups->add($group);
        }

        return $set;
    }

    /**
     * @param bool $force
     * @return array|\Doctrine\ORM\QueryBuilder
     */
    public function getUnexportedArticles($force = false, $exportAll = true) {
        $lastExport = $this->entityManager->getRepository("\FatchipAfterbuy\Models\Status")->find(1);

        if($lastExport) {
            $lastExport = $lastExport->getLastProductExport();
        }

        $articles = $this->entityManager->createQueryBuilder()
            ->select(['articles'])
            ->from('\Shopware\Models\Article\Article', 'articles', 'articles.id')
            ->leftJoin('articles.details', 'details')
            ->leftJoin('details.attribute', 'attributes')
        ;

        if(!$exportAll) {
            $articles->where('attributes.afterbuyExportEnabled = 1');
        }

        if(!$force) {
            $articles = $articles->andWhere("(attributes.afterbuyId IS NULL OR attributes.afterbuyId = '') OR articles.changed >= :lastExport")
            ->setParameters(array('lastExport' => $lastExport))
            ;
        }

        $articles = $articles->getQuery()
            ->setMaxResults(250)
            ->getResult();

        return $articles;
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     */
    public function setArticlesWithoutAnyActiveVariantToInactive()
    {
        $sql = "UPDATE s_articles SET active = 0 WHERE id IN (
                SELECT articleID FROM s_articles_details GROUP BY articleID HAVING BIT_OR(active) = 0 
                );";

        Shopware()->Db()->exec($sql);

        $sql = "UPDATE s_articles SET active = 0 WHERE id IN (
                SELECT articleID FROM s_articles_details GROUP BY articleID HAVING BIT_OR(instock) = 0 
                );";

        Shopware()->Db()->exec($sql);
    }
}