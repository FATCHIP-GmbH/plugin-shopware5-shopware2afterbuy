Ext.define('Shopware.apps.abaccResetShopConnection.view.ResetWindow', {
    extend: 'Enlight.app.Window',

    snippets: {
        title: '{s namespace="backend/abaccResetShopConnection" name="reset_window_title"}Reset Connection{/s}',
        infoText: '{s namespace="backend/abaccResetShopConnection" name="reset_window_info"}Information{/s}',
        buttonText: '{s namespace="backend/abaccResetShopConnection" name="reset_window_button-text"}Yes{/s}',
    },

    height: 420,
    width: 300,
    border: true,
    layout: 'fit',
    autoShow: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Disable window resize
     * @boolean
     */
    resizable: false,

    /**
     * Disables the maximize button in the window header
     * @boolean
     */
    maximizable: false,
    /**
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    initComponent: function () {
        const me = this;

        me.title = me.snippets.title;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function () {
        const me = this;

        return Ext.create('Ext.container.Container', {
            layout: {
                type: 'vbox',
                align: 'center',
            },
            items: [
                me.createInfoBox(),
                me.createStartButton()
            ],
        });
    },

    createInfoBox: function () {
        const me = this;

        return Ext.create('Ext.form.field.Display', {
            flex: 1,
            width: '100%',
            value: me.snippets.infoText,
        });
    },

    createStartButton: function () {
        const me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.buttonText,
            cls: 'primary',
        });
    },
});
