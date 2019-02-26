Ext.define('Shopware.apps.abaccResetShopConnection', {

    extend: 'Enlight.app.SubApplication',

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.abaccResetShopConnection',


    launch: function () {
        var me = this;


        // Ext.Ajax.request({
        //     url: '{url controller=saltyUpdateCurrencyRates action=reset}',
        //     success: function() {
        //         Shopware.Notification.createGrowlMessage(
        //             '{s namespace="backend/salty" name="update_title"}{/s}',
        //             '{s namespace="backend/salty" name="update_success"}{/s}',
        //             'UpdateCurrencyRates'
        //         );
        //
        //     }
        // });

        // Shopware.Notification.createGrowlMessage(
        //     'a','b','c'
        // );

        console.log('DBG');
    }
});
