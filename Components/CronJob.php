<?php

namespace Shopware\viaebShopware2Afterbuy\Components;

use Fatchip\Afterbuy\Types\Product;
use Fatchip\Afterbuy\Types\Product\AddBaseProduct;
use Fatchip\Afterbuy\Types\Product\AddBaseProducts;
use Fatchip\Afterbuy\Types\Product\ProductIdent;
use Fatchip\Afterbuy\Types\Product\ProductPicture;
use Fatchip\Afterbuy\Types\Product\ProductPictures;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Shop;

/**
 * Class CronJob
 *
 * @package Shopware\viaebShopware2Afterbuy\Components
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
        // Get all articles where the attribute afterbuyExport is true.
        // Simple articles have an undefined configuratorSetId --> NULL
        // unlike article configurator sets that have multiple details.
        $afterbuyArticles = array_map(
            [$this, 'collectArticleDetails'],
            Shopware()->Models()
                ->createQueryBuilder()
                ->select([
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
                ->where('images.parentId IS NULL')
                ->andWhere('attribute.afterbuyExport = 1')
                ->getQuery()->getArrayResult()
        );
        foreach ($afterbuyArticles as $article) {
            if ($article['configuratorSetId'] !== null && count($article['allDetails']) > 1) {
                $variants = [];
                $variantIds = [];
                $variantNames = [];
                $mainDetail = null;
                foreach ($article['allDetails'] as $detail) {
                    if ($detail['id'] == $article['mainDetailId']) {
                        $mainDetail = $this->mapArticleToProduct($article, $detail, ProductIdent::TYPE_VARIANT_SET, 0);
                    }
                    $variants[] = $this->mapArticleToProduct($article, $detail, ProductIdent::TYPE_NO_CHILDREN, 1);
                    $variantIds[$detail['number']] = $detail['id'];
                    $variantNames[$detail['number']] = $detail['additionalText'];
                }
                $response = $client->updateShopProducts($variants);
                $mainDetail->setAddBaseProducts(new AddBaseProducts());
                foreach ($response['Result']['NewProducts']['NewProduct'] as $newProduct) {
                    $this->addAfterbuyIdToArticleDetail(
                        $variantIds[$newProduct['UserProductID']],
                        $newProduct['ProductID']
                    );
                    $isMainDetail = $newProduct['UserProductID'] == $mainDetail->getProductIdent()->getUserProductID();
                    $children = $mainDetail->getAddBaseProducts()->getAddBaseProduct();
                    $children[] = new AddBaseProduct(
                        $newProduct['ProductID'],
                        $variantNames[$newProduct['UserProductID']],
                        $isMainDetail
                            ? AddBaseProduct::VARIANT_STANDARD
                            : AddBaseProduct::VARIANT_OPTIONAL
                    );
                    $mainDetail->getAddBaseProducts()->setAddBaseProduct($children);
                }
                $mainArticleNumber = strstr($mainDetail->getProductIdent()->getUserProductID(), '.', true);
                $mainDetailId = $mainDetail->getAnr();
                $mainDetailProductIdent = $mainDetail->setAnr(null)->getProductIdent();
                if (empty($mainDetailProductIdent->getProductID())) {
                    $mainDetailProductIdent->setAnr(null)->setUserProductID($mainArticleNumber);
                }
                $response = $client->updateShopProducts($mainDetail);
                $baseProductId = $response['Result']['NewProducts']['NewProduct']['ProductID'];
                $this->addAfterbuyIdToArticleDetail($mainDetailId, $baseProductId, true);
            } else {
                $detail = $article['allDetails'][0];
                $product = $this->mapArticleToProduct($article, $detail, ProductIdent::TYPE_NO_CHILDREN, 0);
                $response = $client->updateShopProducts($product);
                $productId = $response['Result']['NewProducts']['NewProduct']['ProductID'];
                $this->addAfterbuyIdToArticleDetail($detail['id'], $productId);
            }
        }
        return true;
    }

    /**
     * @param array $article
     * @return array
     */
    protected function collectArticleDetails($article) {
        /** @var \Shopware\Models\Article\Repository $articleRepository */
        $articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
        $ids = array_column($articleRepository->getConfiguratorListIdsQuery($article['id'])->getResult(), 'id');
        $details = $articleRepository->getDetailsByIdsQuery($ids)->getArrayResult();
        $article['allDetails'] = [];
        foreach ($details as $detail) {
            if (!empty($detail['prices']) ) {
                $detail['prices'] = $this->formatPricesFromNetToGross($detail['prices'], $article['tax']);
            }
            array_push($article['allDetails'], $detail);
        }
        return $article;
    }

    /**
     * @param array $article
     * @param array $detail
     * @param int $type
     * @param int $level
     * @return Product
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mapArticleToProduct($article, $detail, $type = 0, $level = 0)
    {
        $afterbuyFieldKey = ($type === ProductIdent::TYPE_VARIANT_SET)
            ? 'afterbuyBaseProductid'
            : 'afterbuyProductid';
        $afterbuyProductID = $detail['attribute'][$afterbuyFieldKey] ?: null;
        $product = new Product($afterbuyProductID);
        if (empty($afterbuyProductID)) {
            $product->getProductIdent()
                ->setBaseProductType($type)
                ->setUserProductID($detail['number'])
                ->setAnr($detail['id']);
        }
        $productPictures = [];
        $imageSmallURL = null;
        $imageLargeURL = null;
        $mediaRepository = Shopware()->Models()->getRepository('Shopware\Models\Media\Media');
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        foreach ($article['images'] as $index => $image) {
            $pictureNr = $index + 1;
            if ($pictureNr > 12) {
                break;
            }
            $mediaImage = $mediaRepository->findOneBy(array('id' => $image['mediaId']));
            $pictureUrl = $mediaService->getUrl($mediaImage->getPath());
            $imageLargeURL = $imageLargeURL ?: $pictureUrl;
            $productPicture = new ProductPicture();
            $productPicture
                ->setNr($pictureNr)
                ->setUrl($pictureUrl)
                ->setAltText($image['path']);
            $thumbnailPath = array_shift($mediaImage->getCreatedThumbnails());
            if (!empty($thumbnailPath)) {
                $thumbnailUrl = $mediaService->getUrl($thumbnailPath);
                $imageSmallURL = $imageSmallURL ?: $thumbnailUrl;
                $thumbnail = new ProductPicture();
                $thumbnail
                    ->setTyp(ProductPicture::PICTURE_THUMB)
                    ->setUrl($thumbnailUrl)
                    ->setAltText($thumbnailPath);
                $listPicture = new ProductPicture();
                $listPicture
                    ->setTyp(ProductPicture::PICTURE_LIST)
                    ->setUrl($thumbnailUrl)
                    ->setAltText($thumbnailPath);
                $productPicture->setChilds([
                    $thumbnail,
                    $listPicture,
                ]);
            }
            $productPictures[] = $productPicture;
        }
        $productPictures = !empty($productPictures) ? new ProductPictures($productPictures) : null;
        return $product
            ->setLevel($level)
            ->setName($article['name'])
            ->setAnr($detail['id'])
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
            ->setCanonicalUrl($this->getArticleSeoUrl($article['id']))
            ->setImageLargeURL($imageLargeURL)
            ->setImageSmallURL($imageSmallURL)
            ->setProductPictures($productPictures);
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
     * @param integer $productID
     * @param bool $isMainDetail
     * @return void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function addAfterbuyIdToArticleDetail($detailID, $productID, $isMainDetail = false)
    {
        if ($productID) {
            $detail = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')->find($detailID);
            /** @var \Shopware\Models\Attribute\Article $attributes */
            $attributes = $detail->getAttribute();
            if ($isMainDetail) {
                $attributes->setAfterbuyBaseProductid($productID);
            } else {
                $attributes->setAfterbuyProductid($productID);
            }
            Shopware()->Models()->persist($attributes);
            Shopware()->Models()->flush();
        }
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
