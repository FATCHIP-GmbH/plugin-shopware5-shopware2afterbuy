<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Attribute\ArticleSupplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Article\Detail;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

class ShopwareArticleHelper extends AbstractHelper {

    protected $suppliers;

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

    public function createSupplier(string $name) {
        $supplier = new Supplier();
        $supplier->setName($name);

        $attribute = new ArticleSupplier();
        $supplier->setAttribute($attribute);

        $this->entityManager->persist($supplier);
        $this->entityManager->flush();

        return $supplier;
    }

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
     * returns article. if not available article is needs to be created
     *
     * @return \Shopware\Models\Article\Article
     */
    public function getMainArticle(string $number)
    {
        $article = $this->entityManager->getRepository('Shopware\Models\Article\Detail')->findOneBy(array('number' => $number));

        if(!is_null($article)) return $article->getArticle();
        else return $this->createMainArticle($number);
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
     * @return \Shopware\Models\Article\Article
     */
    public function createMainArticle()
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
     * returns available variant. if not available variant is gonna be created
     *
     * @return array Shopware\Models\Article\Configurator\Option
     */
    public function getConfiguratorOption(array $groups, Detail &$detail)
    {

        $options = array();

        foreach($groups as $group)
        {
            if($entity->{"get" . $group}()) {
                //TODO: create group if not exists
                $configGroup = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Group')->findOneBy(array('name' => $group));

                $option = Shopware()->Models()->getRepository('Shopware\Models\Article\Configurator\Option')->findOneBy(array('name' => $entity->{"get" . $group}(), 'groupId' => $configGroup->getId()));

                if(is_null($option) && $entity->{"get" . $group}()) {
                    $option = new Option();
                    $option->setName($entity->{"get" . $group}());
                    $option->setPosition(0);
                    $option->setGroup($configGroup);

                    Shopware()->Models()->persist($option);
                }

                array_push($options, $option);
            }
        }

        $detail->setConfiguratorOptions($options);

        return $options;
    }


    /**
     * creates a configurator set
     *
     * @param \Acid21Connector\Services\Acid21\DTO\Article $entity
     * @param $options
     * @param $groups
     * @return Set
     */
    public function createConfiguratorSet(\Acid21Connector\Services\Acid21\DTO\Article $entity, $options, $groups)
    {
        $set = new Set();

        $set->setOptions($options);
        $set->setGroups($groups);
        $set->setName($entity->getSupplierNumber());

        return $set;
    }

    /**
     * add an option to a given set
     *
     * @param Set $set
     * @param $options
     * @return Set
     */
    public function addSetOptions(Set $set, $options)
    {
        $setOptions = $set->getOptions();

        foreach($options as $option)
        {
            // add missing options
            if(!$setOptions->contains($option)) $setOptions->add($option);
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
    public function addSetGroups(Set $set, $groups)
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
     * get or create configurator set for given article
     *
     * @param ArticleModel $article
     * @param \Acid21Connector\Services\Acid21\DTO\Article $entity
     * @param $options
     * @param $groups
     * @return Set
     */
    public function getConfiguratorSet(ArticleModel &$article, \Acid21Connector\Services\Acid21\DTO\Article $entity, $options, $groups)
    {
        $set = $article->getConfiguratorSet();

        if(is_null($set))
        {
            $set = $this->createConfiguratorSet($entity, $options, $groups);
            $article->setConfiguratorSet($set);
        }
        else
        {
            $this->addSetOptions($set, $options);
            $this->addSetGroups($set, $groups);
        }

        return $set;
    }

}