Ext.define('Shopware.apps.abaccResetShopConnection', {

    extend: 'Enlight.app.SubApplication',

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.abaccResetShopConnection',
    bulkLoad: true,
    loadPath: '{url action=load}',

    /**
     * Requires class for the module (subapplication)
     */
    requires: [
        'Shopware.container.Viewport'
    ],

    views: [
        'ResetWindow'
    ],

    controllers: [
        'ResetController'
    ],


    launch() {
        const me = this;
        const controller = me.getController('ResetController');

        return controller.resetWindow;
    }
});
