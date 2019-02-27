/**
 * $Id: $
 */

Ext.define('Shopware.apps.abaccResetShopConnection.controller.ResetController', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function () {
        const me = this;
        me.resetWindow = me.getView('ResetWindow').create();

        me.control({
            'window[id=reset_window]': {
                'resetShopwareAfterbuyConnection': me.resetShopwareAfterbuyConnection,
            }
        })
    },

    resetShopwareAfterbuyConnection: function () {
        console.log('dbg2');
    }
});