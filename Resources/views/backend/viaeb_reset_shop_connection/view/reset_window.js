Ext.define('Shopware.apps.viaebResetShopConnection.view.ResetWindow', {
    extend: 'Enlight.app.Window',

    id: 'reset_window',

    snippets: {
        title: '{s namespace="backend/viaebResetShopConnection" name="reset_window_title"}Reset Connection{/s}',
        infoText: '{s namespace="backend/viaebResetShopConnection" name="reset_window_info"}' +
            'Achtung!<br><br>' +
            'Mit dieser Aktion werden sämtliche Beziehungen von Artikeln, Bestellungen und Kategorien zu Afterbuy' +
            ' gelöscht. Dies ist nur dann sinnvoll, wenn sie das führende System auf Shopware wechseln oder einen' +
            ' kompletten Neuimport durchführen möchten.<br><br>' +
            'Bitte fertigen Sie vor Durchführung dieser Aktion ein Backup an. Im Anschluss an diese Aktion sollten Sie' +
            ' das nicht führende System leeren, da andernfalls Duplikate auftreten können.<br><br>' +
            'Wirklich fortfahren?{/s}',
        buttonText: '{s namespace="backend/viaebResetShopConnection" name="reset_window_button-text"}Yes{/s}',
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
            htmlEncode: true,
        });
    },

    createStartButton: function () {
        const me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.buttonText,
            cls: 'primary',
            handler: function () {
                me.fireEvent('resetShopwareAfterbuyConnection');
            },
        });
    },
});
