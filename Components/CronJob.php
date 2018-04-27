<?php

namespace Shopware\FatchipShopware2Afterbuy\Components;

use Fatchip\Afterbuy\Types\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Shop;

/**
 * Class CronJob
 *
 * @package Shopware\FatchipShopware2Afterbuy\Components
 */
class CronJob
{
    /**
     * @return bool
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Zend_Db_Statement_Exception
     */
    public function exportMainArticles2Afterbuy()
    {
        // TODO: Handle this in the constructor?
        // TODO: Does not work with an empty or wrong configuration
        $client = Shopware()->Container()->get('afterbuy_api_client');

        // Get all Articles where after Attribute is set
        // Main Articles have no configurator_set_id defined
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select([
            'article',
            'mainDetail',
            'tax',
            'attribute',
            'supplier',
            'categories',
            'similar',
            'accessories',
            'accessoryDetail',
            'similarDetail',
            'images',
            'links',
            'downloads',
            'linkAttribute',
            'customerGroups',
            'imageAttribute',
            'downloadAttribute',
            'propertyValues',
            'imageMapping',
            'mappingRule',
            'ruleOption',
        ])
            ->from('Shopware\Models\Article\Article', 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('article.categories', 'categories', null, null, 'categories.id')
            ->leftJoin('article.similar', 'similar')
            ->leftJoin('article.related', 'accessories')
            ->leftJoin('accessories.mainDetail', 'accessoryDetail')
            ->leftJoin('similar.mainDetail', 'similarDetail')
            ->leftJoin('article.images', 'images')
            ->leftJoin('article.links', 'links')
            ->leftJoin('article.downloads', 'downloads')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('mainDetail.attribute', 'attribute')
            ->leftJoin('article.supplier', 'supplier')
            ->leftJoin('links.attribute', 'linkAttribute')
            ->leftJoin('article.customerGroups', 'customerGroups')
            ->leftJoin('images.attribute', 'imageAttribute')
            ->leftJoin('downloads.attribute', 'downloadAttribute')
            ->leftJoin('article.propertyValues', 'propertyValues')
            ->leftJoin('images.mappings', 'imageMapping')
            ->leftJoin('imageMapping.rules', 'mappingRule')
            ->leftJoin('mappingRule.option', 'ruleOption')
            ->where('attribute.afterbuyExport = 1')
            ->andWhere('images.parentId IS NULL');
            // ->andWhere('article.configuratorSetId IS NULL');

        $afterbuyArticles = $builder->getQuery()->getArrayResult();
        foreach ($afterbuyArticles as $article) {
            /** @var \Shopware\Models\Article\Repository $articleRepo */
            $articleRepo = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
            $idQuery = $articleRepo->getConfiguratorListIdsQuery(
                $article['id']
            );
            $ids = $idQuery->getArrayResult();

            foreach ($ids as $key => $id) {
                $ids[$key] = $id['id'];
            }

            $query = $articleRepo->getDetailsByIdsQuery($ids);
            $details = $query->getArrayResult();

            foreach ($details as $key => $detail) {
                if (empty($detail['prices']) ) {
                    continue;
                }
                $detail['prices'] = $this->formatPricesFromNetToGross($detail['prices'], $article['tax']);
                // TODO: Check if the articles get their Afterbuy product ID properly
                $response = $client->updateShopProducts($this->mapArticleToProduct($article, $detail));
                $this->addAfterbuyIdToArticle($detail['id'], $response);
            }
        }
        return true;
    }

    /**
     * @param $article
     * @param $detail
     * @return Product
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mapArticleToProduct($article, $detail)
    {
        $afterbuyProductID = $detail['attribute']['afterbuyProductid'];
        $product = new Product();
        if ($afterbuyProductID) {
            $product->getProductIdent()->setProductID($afterbuyProductID);
        } else {
            $product->getProductIdent()
                ->setProductInsert(true)
                ->setBaseProductType(0)
                ->setUserProductID($detail['id'])
                ->setAnr($detail['id'])
                ->setEAN($detail['number']);
        }
        // TODO: Map the product pictures
        return $product
            ->setName($article['name'])
            ->setAnr($detail['id'])
            ->setEAN($detail['number'])
            ->setProductBrand($article['supplier']['name'])
            ->setManufacturerPartNumber($detail['supplierNumber'])
            ->setDescription($article['descriptionLong'])
            ->setShortDescription($article['description'])
            ->setKeywords($article['keywords'])
            ->setQuantity($detail['inStock'])
            ->setMinimumStock($detail['stockMin'])
            ->setSellingPrice(str_replace('.', ',', $detail['prices']['EK']['price']))
            ->setBuyingPrice(str_replace('.', ',', $detail['purchasePrice']))
            ->setDealerPrice(str_replace('.', ',', round($detail['prices'][0]['price'], 2)))
            ->setTaxRate(str_replace('.', ',', $article['tax']['tax']))
            ->setWeight(str_replace('.', ',', $detail['weight']))
            ->setDeliveryTime($detail['shippingTime'])
            ->setCanonicalUrl($this->getArticleSeoUrl($article['id']));
    }


    /**
     * @param $article Article
     * @param $detail Detail
     * @return \fcafterbuyart|mixed
     * @throws \Zend_Db_Statement_Exception
     */
    private function mapAfterbuyArticleAttributes($article, $detail)
    {
        $fcAfterbuyArt = new \fcafterbuyart();
        $fcAfterbuyArt = $this->mapRequiredAfterbuyArticleAttributes($fcAfterbuyArt, $article);
        $fcAfterbuyArt = $this->mapImageAfterbuyArticleAttributes($fcAfterbuyArt, $article);

        $fcAfterbuyArt->UserProductID           = null;  // Integer
        $fcAfterbuyArt->Anr                     = $article['id']; //Float
        $fcAfterbuyArt->EAN                     = $article['mainDetail']['number']; // String
        $fcAfterbuyArt->ProductID               = $article['mainDetail']['attribute']['afterbuyProductid']; // Integer
        $fcAfterbuyArt->ShortDescription        = $article['description']; // String
        $fcAfterbuyArt->Memo                    = null; // String
        $fcAfterbuyArt->Description             = $article['descriptionLong'];
        $fcAfterbuyArt->Keywords                = $article['keywords']; // String Kelkoo Keywords
        $fcAfterbuyArt->Quantity                = $article['mainDetail']['inStock']; // Integer
        $fcAfterbuyArt->AuctionQuantity         = null; // Integer
        $fcAfterbuyArt->AddQuantity             = null;
        $fcAfterbuyArt->AddAuctionQuantity      = null;
        $fcAfterbuyArt->Stock                   = null; // bool
        $fcAfterbuyArt->Discontinued            = null; // bool
        $fcAfterbuyArt->MergeStock              = null; // bool
        $fcAfterbuyArt->UnitOfQuantity          = null; //$this->mapUnitQuantity($); // float???
        $fcAfterbuyArt->BasepriceFactor         = null;
        $fcAfterbuyArt->MinimumStock            = $article['mainDetail']['stockMin'];;
        $fcAfterbuyArt->SellingPrice            = str_replace('.', ',',$detail['prices']['EK']['price']);
        $fcAfterbuyArt->BuyingPrice             = str_replace('.', ',',$article['mainDetail']['purchasePrice']);
        $fcAfterbuyArt->DealerPrice             = str_replace('.', ',',round($detail['prices'][0]['price'], 2));
        $fcAfterbuyArt->Level                   = null;
        $fcAfterbuyArt->Position                = null;
        $fcAfterbuyArt->TitleReplace            = null;
        $fcAfterbuyArt->ScaledQuantity          = null;
        $fcAfterbuyArt->ScaledPrice             = null;
        $fcAfterbuyArt->ScaledDPrice            = null;
        $fcAfterbuyArt->TaxRate                 = str_replace('.', ',',$article['tax']['tax']);
        $fcAfterbuyArt->Weight                  = $article['mainDetail']['weight'];
        $fcAfterbuyArt->Stocklocation_1         = null;
        $fcAfterbuyArt->Stocklocation_2         = null;
        $fcAfterbuyArt->Stocklocation_3         = null;
        $fcAfterbuyArt->Stocklocation_4         = null;
        $fcAfterbuyArt->CountryOfOrigin         = null;
        $fcAfterbuyArt->SearchAlias             = null;
        $fcAfterbuyArt->Froogle                 = null;
        $fcAfterbuyArt->Kelkoo                  = null;
        $fcAfterbuyArt->ShippingGroup           = null;
        $fcAfterbuyArt->ShopShippingGroup       = null;
        $fcAfterbuyArt->CrossCatalogID          = null;
        $fcAfterbuyArt->FreeValue1              = null;
        $fcAfterbuyArt->FreeValue2              = null;
        $fcAfterbuyArt->FreeValue3              = null;
        $fcAfterbuyArt->FreeValue4              = null;
        $fcAfterbuyArt->FreeValue5              = null;
        $fcAfterbuyArt->FreeValue6              = null;
        $fcAfterbuyArt->FreeValue7              = null;
        $fcAfterbuyArt->FreeValue8              = null;
        $fcAfterbuyArt->FreeValue9              = null;
        $fcAfterbuyArt->FreeValue10             = null;
        $fcAfterbuyArt->DeliveryTime            = $article['mainDetail']['shippingTime'];
        $fcAfterbuyArt->ImageSmallURL           = null;
        $fcAfterbuyArt->ImageLargeURL           = null;
        $fcAfterbuyArt->ImageName               = null;
        $fcAfterbuyArt->ImageSource             = null;
        $fcAfterbuyArt->ManufacturerStandardProductIDType         = null;
        $fcAfterbuyArt->ManufacturerStandardProductIDValue         = null;
        $fcAfterbuyArt->ProductBrand            = $article['supplier']['name'];
        $fcAfterbuyArt->CustomsTariffNumber     = null;
        $fcAfterbuyArt->ManufacturerPartNumber  = $article['mainDetail']['supplierNumber'];
        $fcAfterbuyArt->GoogleProductCategory   = null;
        $fcAfterbuyArt->Condition               = null;
        $fcAfterbuyArt->Pattern                 = null;
        $fcAfterbuyArt->Material                = null;
        $fcAfterbuyArt->ItemColor               = null;
        $fcAfterbuyArt->ItemSize                = null;
        $fcAfterbuyArt->CanonicalUrl            = $this->getArticleSeoUrl($article['id']);
        $fcAfterbuyArt->EnergyClass             = null;
        $fcAfterbuyArt->EnergyClassPictureUrl   = null;
        $fcAfterbuyArt->Gender                  = null;
        $fcAfterbuyArt->AgeGroup                = null;

        return $fcAfterbuyArt;
    }


    /**
     * @param $fcAfterbuyArt \fcafterbuyart
     * @param $article Article
     * @return \fcafterbuyart|mixed
     */
    private function  mapRequiredAfterbuyArticleAttributes($fcAfterbuyArt, $article)
    {
        $fcAfterbuyArt->Name                    = $article['name']; // String
        return $fcAfterbuyArt;
    }

    /**
     * @param $fcAfterbuyArt \fcafterbuyart
     * @param $article Article
     * @return  \fcafterbuyart|mixed
     */
    private function  mapImageAfterbuyArticleAttributes($fcAfterbuyArt, $article)
    {
        $i = 1;
        foreach ($article['images'] as $image){
            // only 12 pictures are supported by afterbuy
            if ($i > 12){
                continue;
            }
            $varName_PicNr = "ProductPicture_Nr_".$i;
            $varName_PicUrl = "ProductPicture_Url_".$i;
            $varName_PicAltText = "ProductPicture_AltText_".$i;

            $fcAfterbuyArt->{$varName_PicNr} = $i;
            $fcAfterbuyArt->{$varName_PicUrl} = $this->getImageSeoUrl($image['mediaId']);
            $fcAfterbuyArt->{$varName_PicAltText} = $image['path']; // TODO: Better description?
            $i++;
        }
        return $fcAfterbuyArt;
    }

    /**
     * Internal helper function to convert gross prices to net prices.
     *
     * @param $prices
     * @param $tax
     * @return array
     */
    protected function formatPricesFromNetToGross($prices, $tax)
    {
        foreach ($prices as $key => $price) {
            $customerGroup = $price['customerGroup'];
            if ($customerGroup['taxInput']) {
                $price['price'] = str_replace('.', ',', $price['price'] / 100 * (100 + $tax['tax']));
                $price['pseudoPrice'] = str_replace('.', ',',$price['pseudoPrice'] / 100 * (100 + $tax['tax']));
            } else {
                $price['price'] = str_replace('.', ',', $price['price']);
                $price['pseudoPrice'] = str_replace('.', ',',$price['pseudoPrice']);
            }
            // Use customerGroup's key as new key
            // TODO: What to do with non-standard customerGroups?
            if ($customerGroup['key'] == 'H' ){
                $prices['H'] = $price;
            }
            if ($customerGroup['key'] == 'EK' ){
                $prices['EK'] = $price;
            }
        }
        return $prices;
    }

    /**
     * Adds Afterbuy's product ID to the article details
     *
     * @param integer $detailID
     * @param string $response
     * @return void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function addAfterbuyIdToArticle($detailID, $response)
    {
        $oXml = simplexml_load_string($response);
        $productID = (string) $oXml->Result->NewProducts->NewProduct->ProductID;
        if ($productID) {
            $detail = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')->find($detailID);
            /** @var \Shopware\Models\Attribute\Article $attributes */
            $attributes = $detail->getAttribute();
            $attributes->setAfterbuyProductid($productID);
            Shopware()->Models()->persist($attributes);
            Shopware()->Models()->flush();
        }
    }

    /**
     * @param integer $mediaId
     * @return string
     */
    protected function getImageSeoUrl($mediaId)
    {
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $mediaRepo = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        $mediaImage = $mediaRepo->findOneBy(array('id' => $mediaId));
        // TODO: Add proper exception handling
        return $mediaService->getUrl($mediaImage->getPath());
    }

    /**
     * @param integer $articleId
     * @return string
     * @throws \Zend_Db_Statement_Exception|\Exception
     */
    protected function getArticleSeoUrl($articleId)
    {
        // TODO: Add support for sub-shops
        $this->setupContext(1);
        $host = Shopware()->Config()->BasePath;
        $db = Shopware()->Db();
        $sql = "SELECT path FROM s_core_rewrite_urls WHERE org_path = :org_path";
        $params = [ ":org_path" => "sViewport=detail&sArticle={$articleId}" ];
        $query = $db->executeQuery($sql, $params);
        $row = $query->fetch();
        // TODO: Add support for http and https
        return isset($row['path']) ? "http://{$host}/{$row['path']}" : "";
    }


    /**
     * @return bool
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    public function importOrdersFromAfterbuy()
    {
        // TODO: Does not work with an empty or wrong configuration
        /** @var \fcafterbuyapi $client */
        $client = Shopware()->Container()->get('fatchip_shopware2afterbuy_api_client');
        $response = $client->getSoldItemsFromAfterbuy();
        $xmlResponse = simplexml_load_string($response);
        if (!$xmlResponse) {
            // Nothing to do here
            // TODO: Write a log entry?
            return true;
        }
        // TODO: Validate the response (CallStatus)
        foreach ($xmlResponse->Result->Orders->Order as $xmlOrder) {
            $afterbuyOrder = new \fcafterbuyorder();
            $afterbuyOrder->createOrderByApiResponse($xmlOrder);
            // Create customer / TODO: Update existing customers
            // TODO: Uncomment the next line soon
            //$this->createCustomer($afterbuyOrder);
            $this->createOrder($afterbuyOrder);
        }
        return true;
    }


    /**
     * @param $afterbuyOrder \fcafterbuyorder
     * @throws \Exception
     */
    private function createCustomer(\fcafterbuyorder $afterbuyOrder)
    {
        // Set the shop context instance for CLI
        // TODO: Support for sub-shops
        $this->setupContext(1);
        $registerService =  Shopware()->Container()->get('shopware_account.register_service');
        /** @var ShopContext|Shop $context */
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $context = $context->getShop();
        // Check if email address already exists with accountMode ACCOUNT_MODE_FAST_LOGIN (1)
        $emailAddress = $afterbuyOrder->BuyerInfoBilling->Mail;
        /** @var Customer $customer */
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->findOneBy(['email' => $emailAddress, 'accountMode' => 'ACCOUNT_MODE_FAST_LOGIN']);
        if (!$customer){
            $swCustomer = new Customer();
            $this->mapAfterbuyCustomer($afterbuyOrder->BuyerInfoBilling, $swCustomer);
            $billingAddress  = $this->mapAfterbuyBillingAddress($afterbuyOrder->BuyerInfoBilling);
            // in case there is no shippingAdresse use billingAddress
            if (!$afterbuyOrder->BuyerInfoShipping){
                $shippingAddress = $billingAddress;
            } else {
                $shippingAddress = $this->mapAfterbuyShippingAddress($afterbuyOrder->BuyerInfoShipping);
            }
            // TODO: Log validation errors / something like this:
            /*
            $violations = $this->getManager()->validate($order);
                if ($violations->count() > 0) {
                    throw new ApiException\ValidationException($violations);
            }
            */
            $registerService->register($context, $swCustomer, $billingAddress, $shippingAddress );
        }
    }

    /**
     * @param $afterbuyOrder \fcafterbuyorder
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    private function createOrder(\fcafterbuyorder $afterbuyOrder)
    {
        // Use REST API class for order creation
        $orderResource = \Shopware\Components\Api\Manager::getResource('order');
        // Prepare order creation parameters
        $orderParams = $this->getOrderParams($afterbuyOrder);
        $orderResource->create($orderParams);
        // Prerequisites for an order:
        // - Customer
        // - Payment
        // - Dispatch
        // - Shop // TODO: Sub-shop support
        // - Billing address
        // - Shipping address
        // - Order details
    }

    /**
     * @param $billingAddress \fcafterbuyaddress
     * @param $swCustomer Customer
     * @return Customer
     */
    private function mapAfterbuyCustomer($billingAddress, $swCustomer)
    {
        $swCustomer->setAccountMode(Customer::ACCOUNT_MODE_FAST_LOGIN);
        $swCustomer->setActive(false);
        $swCustomer->setCustomerType(Customer::CUSTOMER_TYPE_PRIVATE);
        // TODO: Companies / business customers
        //$swCustomer->setCustomerType(Customer::CUSTOMER_TYPE_BUSINESS);
        //$swCustomer->setBirthday($buyerInfo->) // Missing???
        $swCustomer->setEmail($billingAddress->Mail);
        $swCustomer->setFirstname($billingAddress->FirstName);
        $swCustomer->setLastname($billingAddress->LastName);
        if ($billingAddress->Title === 'Herr'){
            $swCustomer->setSalutation('mr');
        } else {
            $swCustomer->setSalutation('ms');
        }
        // TODO: Figure out how to handle this
        //$swCustomer->setPaymentId();

        return($swCustomer);
    }

    /**
     * @param $afterbuyBillingAddress \fcafterbuyaddress
     * @return Address
     */
    private function mapAfterbuyBillingAddress($afterbuyBillingAddress)
    {
        $swBillingAddress = new Address();
        $swBillingAddress->setFirstname($afterbuyBillingAddress->FirstName);
        $swBillingAddress->setLastname($afterbuyBillingAddress->LastName);
        if ($afterbuyBillingAddress->Title === 'Herr'){
            $swBillingAddress->setSalutation('mr');
        } else {
            $swBillingAddress->setSalutation('ms');
        }
        $swBillingAddress->setStreet($afterbuyBillingAddress->Street);
        $swBillingAddress->setAdditionalAddressLine1($afterbuyBillingAddress->Street2);
        $swBillingAddress->setPhone($afterbuyBillingAddress->Phone);
        $swBillingAddress->setZipcode($afterbuyBillingAddress->PostalCode);
        $swBillingAddress->setCity($afterbuyBillingAddress->City);
        $swBillingAddress->setCountry($this->getCountryByIso($afterbuyBillingAddress->CountryISO));

        return($swBillingAddress);
    }

    /**
     * @param $afterbuyShippingAddress \fcafterbuyaddress
     * @return Address
     */
    private function mapAfterbuyShippingAddress($afterbuyShippingAddress)
    {
        $swShippingAddress = new Address();
        $swShippingAddress->setFirstname($afterbuyShippingAddress->FirstName);
        $swShippingAddress->setLastname($afterbuyShippingAddress->LastName);
        if ($afterbuyShippingAddress->Title === 'Herr'){
            $swShippingAddress->setSalutation('mr');
        } else {
            $swShippingAddress->setSalutation('ms');
        }
        $swShippingAddress->setStreet($afterbuyShippingAddress->Street);
        $swShippingAddress->setAdditionalAddressLine1($afterbuyShippingAddress->Street2);
        $swShippingAddress->setPhone($afterbuyShippingAddress->Phone);
        $swShippingAddress->setZipcode($afterbuyShippingAddress->PostalCode);
        $swShippingAddress->setCity($afterbuyShippingAddress->City);
        $swShippingAddress->setCountry($this->getCountryByIso($afterbuyShippingAddress->CountryISO));

        return($swShippingAddress);
    }

    /**
     * Sets the correct context for e.g. validation
     *
     * @param int $shopId
     *
     * @throws \Exception
     */
    private function setupContext($shopId = null)
    {
        /** @var \Shopware\Models\Shop\Repository $shopRepository */
        $shopRepository = Shopware()->Container()->get('models')->getRepository(Shop::class);

        if ($shopId) {
            /** @var Shop $shop */
            $shop = $shopRepository->getActiveById($shopId);
            if (!$shop) {
                throw new \Exception(sprintf('Shop by id %s not found', $shopId));
            }
        } else {
            $shop = $shopRepository->getActiveDefault();
        }
        $shop->registerResources();
    }


    /**
     * @param string $isoCode
     * @return Country $country
     */
    private function getCountryByIso($isoCode)
    {
        /** @var \Shopware\Models\Country\Repository $countryRepository */
        $countryRepository = Shopware()->Container()->get('models')->getRepository(Country::class);

        if ($isoCode) {
            /** @var Country $country */
            $country = $countryRepository->findOneBy(['iso' => $isoCode ]);
        } else {
            // TODO: Get default country from the shop or from the user?
            // For now we'll use DE -> Germany
            $country = $countryRepository->findOneBy(['iso' => 'DE' ]);
        }
        return $country;
    }

    /**
     * @param $afterbuyOrder \fcafterbuyorder
     * @return array $params
     */
    private function getOrderParams($afterbuyOrder)
    {
        /* Example Params:
        [
            "customerId" => 1,
            "paymentId" => 4,
            "dispatchId" => 9,
            "partnerId" => "",
            "shopId" => 1,
            "invoiceAmount" => 201.86,
            "invoiceAmountNet" => 169.63,
            "invoiceShipping" => 0,
            "invoiceShippingNet" => 0,
            "orderTime" => "2012-08-31 08:51:46",
            "net" => 0,
            "taxFree" => 0,
            "languageIso" => "1",
            "currency" => "EUR",
            "currencyFactor" => 1,
            "remoteAddress" => "217.86.205.141",
            "details" => [[
                "articleId" => 220,
                "taxId" => 1,
                "taxRate" => 19,
                "statusId" => 0,
                "articleNumber" => "SW10001",
                "price" => 35.99,
                "quantity" => 1,
                "articleName" => "Versandkostenfreier Artikel",
                "shipped" => 0,
                "shippedGroup" => 0,
                "mode" => 0,
                "esdArticle" => 0,
            ], [
                "articleId" => 219,
                "taxId" => 1,
                "taxRate" => 19,
                "statusId" => 0,
                "articleNumber" => "SW10185",
                "price" => 54.9,
                "quantity" => 1,
                "articleName" => "Express Versand",
                "shipped" => 0,
                "shippedGroup" => 0,
                "mode" => 0,
                "esdArticle" => 0,
            ], [
                "articleId" => 197,
                "taxId" => 1,
                "taxRate" => 19,
                "statusId" => 0,
                "articleNumber" => "SW10196",
                "price" => 34.99,
                "quantity" => 2,
                "articleName" => "ESD Download Artikel",
                "shipped" => 0,
                "shippedGroup" => 0,
                "mode" => 0,
                "esdArticle" => 1,
            ]],
            "documents" => [],
            "billing" => [
                "id" => 2,
                "customerId" => 1,
                "countryId" => 2,
                "stateId" => 3,
                "company" => "shopware AG",
                "salutation" => "mr",
                "firstName" => "Max",
                "lastName" => "Mustermann",
                "street" => "Mustermannstra\u00dfe 92",
                "zipCode" => "48624",
                "city" => "Sch\u00f6ppingen",
            ],
            "shipping" => [
                "id" => 2,
                "countryId" => 2,
                "stateId" => 3,
                "customerId" => 1,
                "company" => "shopware AG",
                "salutation" => "mr",
                "firstName" => "Max",
                "lastName" => "Mustermann",
                "street" => "Mustermannstra\u00dfe 92",
                "zipCode" => "48624",
                "city" => "Sch\u00f6ppingen"
            ],
            "paymentStatusId" => 17,
            "orderStatusId" => 0
        ]);
        */

        $params = [];

        // All parameters are REQUIRED!
        
        $params['customerId'] = $this->getCreateOrderCustomerId($afterbuyOrder);
        $params['paymentId'] = $this->getCreateOrderPaymentId($afterbuyOrder);
        $params['dispatchId'] = $this->getCreateOrderDispatchId($afterbuyOrder);
        $params['partnerId'] = "";
        $params['shopId'] = 1; // TODO: Sub-shop support
        $params['invoiceAmount'] = $this->getCreateOrderInvoiceAmount($afterbuyOrder);
        $params['invoiceAmountNet'] = $this->getCreateOrderInvoiceAmountNet($afterbuyOrder);
        $params['invoiceShipping'] = $this->getCreateOrderInvoiceShipping($afterbuyOrder);
        $params['invoiceShippingNet'] = $this->getCreateOrderInvoiceShippingNet($afterbuyOrder);
        $params['net'] = 0; // TODO: Support different rules for business customers
        $params['taxFree'] = 0; // TODO: Support different rules for business customers
        $params['languageIso'] = $this->getCreateOrderLanguageIso($afterbuyOrder);
        $params['currency'] = $this->getCreateOrderCurrency($afterbuyOrder);
        $params['currencyFactor'] = $this->getCreateOrderCurrencyFactor($afterbuyOrder);

        $params['paymentStatusId'] = 17;
        $params['orderStatusId'] = 0;

        $params['details'] =
        [
            [
                "articleId" => 156 ,
                "taxId" => 1,
                "taxRate" => 19,
                "statusId" => 0,
                "articleNumber" => "SW10156",
                "price" => 279.00,
                "quantity" => 1,
                "articleName" => "ADELAIDE 1",
                "shipped" => 0,
                "shippedGroup" => 0,
                "mode" => 0,
                "esdArticle" => 0,
            ],

        ];

        $params['billing'] = $this->getCreateOrderBillingFromCustomer($params['customerId']);
        $params['shipping'] = $this->getCreateOrderShippingFromCustomer($params['customerId']);

        return $params;
    }


    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderCustomerId($afterbuyOrder)
    {
        $emailAddress = $afterbuyOrder->BuyerInfoBilling->Mail;
        /** @var Customer $customer */
        $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')
            ->findOneBy(['email' => $emailAddress, 'accountMode' => 1]);
        return $customer->getId();
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderPaymentId($afterbuyOrder)
    {
        // TODO: Implement mapping of Afterbuy payments and SW payments
        // return "Vorkasse" => id = 5
        return 5;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderDispatchId($afterbuyOrder)
    {
        // TODO: Implement mapping of Afterbuy dispatches and SW dispatches
        // return "Standard Versand" => id = 9
        return 9;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderInvoiceAmount($afterbuyOrder)
    {
        return $afterbuyOrder->PaymentInfo->FullAmount;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderInvoiceAmountNet($afterbuyOrder)
    {
        // TODO: Calculate this value from the details without taxes
        return $afterbuyOrder->PaymentInfo->FullAmount;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderInvoiceShipping($afterbuyOrder)
    {
        return $afterbuyOrder->ShippingInfo->ShippingCost;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderInvoiceShippingNet($afterbuyOrder)
    {
        // TODO: Calculate this value from the details without taxes
        return $afterbuyOrder->ShippingInfo->ShippingCost;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return int
     */
    private function getCreateOrderLanguageIso($afterbuyOrder)
    {
        // TODO: Get language ISO code somehow?
        return 1;
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return string
     */
    private function getCreateOrderCurrency($afterbuyOrder)
    {
        // TODO: Get the currency from an Afterbuy order
        return 'EUR';
    }

    /**
     * @param \fcafterbuyorder $afterbuyOrder
     * @return float
     */
    private function getCreateOrderCurrencyFactor($afterbuyOrder)
    {
        // TODO: Currency factor fom SW settings?
        return 1.0;
    }

    /**
     * @param int $customerID
     * @return array
     */
    private function getCreateOrderBillingFromCustomer($customerID)
    {
        $address = [];
        // TODO: Get the customer's billing address
        return $address;
    }

    /**
     * @param int $customerID
     * @return array
     */
    private function getCreateOrderShippingFromCustomer($customerID)
    {
        $address = [];
        // TODO: Get the customer's shipping address
        return $address;
    }

}
