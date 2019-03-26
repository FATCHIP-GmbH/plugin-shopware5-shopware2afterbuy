Ext.define('Shopware.apps.viaebConfigForm.view.ColumnFieldSet', {
    extend: 'Ext.form.FieldSet',
    layout: {
        type: 'column',
    },
    defaults: {
        columnWidth: 0.5,
        xtype: 'container',
    },

    initItems: function () {
        const me = this;
        const leftFields = me.items.slice(0, me.items.length / 2);
        const rightFields = me.items.slice(me.items.length / 2);

        me.items = [
            {
                defaults: {
                    xtype: 'combo',
                    forceSelection: true,
                    allowBlank: false,
                    displayField: 'description',
                    valueField: 'id',
                    store: me.defaultStore,
                },
                items: leftFields,
            },
            {
                defaults: {
                    xtype: 'combo',
                    forceSelection: true,
                    allowBlank: false,
                    displayField: 'description',
                    valueField: 'id',
                    store: me.defaultStore,
                },
                items: rightFields,
            },
        ];

        me.callParent(arguments);
    },
});
