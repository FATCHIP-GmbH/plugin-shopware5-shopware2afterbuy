Ext.define('Shopware.apps.viaebConfigForm', {

    extend: 'Enlight.app.SubApplication',

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.viaebConfigForm',
    bulkLoad: true,
    loadPath: '{url action=load}',

    /**
     * Requires class for the module (subapplication)
     */
    requires: [
        'Shopware.container.Viewport'
    ],

    views: [
        'ConfigWindow'
    ],

    controllers: [
        'ConfigController'
    ],


    launch() {
        const me = this;
        const controller = me.getController('ConfigController');

        return controller.configWindow;
    }
});
