<?php

namespace FatchipAfterbuy\Services\Helper;

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
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * @param array $ids
     * @throws \Zend_Db_Adapter_Exception
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
     * @param Article $article
     * @param Detail $detail
     * @param array $variants
     * @throws \Doctrine\ORM\OptimisticLockException
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
    public function getUnexportedArticles($force = false) {
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

        if(!$force) {
            $articles = $articles->where("attributes.afterbuyId IS NULL OR attributes.afterbuyId = ''")
            ->orWhere("articles.changed >= :lastExport")
            ->setParameters(array('lastExport' => $lastExport))
            ;
        }

        $articles = $articles->getQuery()
            ->setMaxResults(250)
            ->getResult();

        return $articles;
    }



}