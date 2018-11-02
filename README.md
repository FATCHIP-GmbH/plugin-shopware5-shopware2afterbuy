# Fatchip Shopware2Afterbuy for Shopware 5
This plugin allows you export articles to your afterbuy account. 

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/FATCHIP-GmbH/plugin-shopware5-hermes/badges/quality-score.png?b=master&s=c6562e886c35cfe94bf70e9faa86794f44705275)](https://scrutinizer-ci.com/g/FATCHIP-GmbH/plugin-shopware5-shopware2afterbuy/?branch=master)
[![License: proprietary](https://img.shields.io/badge/License-proprietary-lightgrey.svg)](LICENSE.md)

## Installation and documentation

Visit our Wiki pages to read the plugin documentation.  
https://wiki.fatchip.de/public/faqshopware2afterbuy

## Functionality since Release v1.0.0
There is additional Functionality in the master branch. But this is not ready to be released in any means.
### API extension
#### getCatalogsFromAfterbuy
This method pulls catalogs (categories) from AfterBuy.
#### getShopProductsFromAfterbuy
This method pulls products from AfterBuy.
### Converter
#### CatalogsToCategoriesConverter
This is the first approach to convert catalogs to categories. It is uncomplete and untested. It is intended to convert
the given catalog array to a category array, that can be imported by ImportProductsCronJob class.
#### ProductsToArticlesConverter
##### convertProducts2Articles($products, $categoryId)
This method converts the given products array to an article array, that can be imported by ImportProductsCronJob class.
### ImportProductsCronJob
#### importProducts2Shopware
This method is intended to pull all the needed data from AfterBuy, convert to shopware readable format and import it to
shopware. 
#### writeArticles($articles)
Imports the given articles array into shopware and returns an array with all imported productId's.
### ImageCrawler
#### retrieveImages($products)
This method maps each productId to an image link.
### Caching
To avoid, that every time the cron job runs, all the data will be loaded from AfterBuy, there is a JSONCache class. It
is intended cache all the data in .json files, so that only changed data will be loaded from AfterBuy. At the moment it
only is able to store data in files, list those files, get the latest cache date and delete the whole cache.
### viaebShopware2AfterbuyTriggerCronJob (controller)
#### triggerAction
This action triggers the ImportProductsCronJob#importProducts2Shopware method. This usefull for development purposes but
should be removed in production mode. 
### Conclusion
With little changes to the code, especially in ImportProductsCronJob#importProducts2Shopware, the module is able to
import products from AfterBuy, convert them to articles and import them to Shopware. It is possible to import articles
to the correct category and also to store variant images. 

## Author

FATCHIP GmbH | [web](https://www.fatchip.de/) | [support@fatchip.de](mailto:support@fatchip.de)

## License Details
Please see the [License File](LICENSE.md) for more information.
