Ext.define('Shopware.apps.viaebConfigForm.view.ConfigWindow', {
    extend: 'Enlight.app.Window',

    id: 'config_window',

    snippets: {
        title: '{s namespace="backend/viaebConfigForm" name="config_window_title"}Konfiguration{/s}',
    },

    height: 420,
    width: 500,
    border: true,
    layout: 'fit',
    autoShow: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 1,

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
    minimizable: true,

    initComponent: function () {
        const me = this;
        me.getConfigValues();

        me.registerEvents();

        me.title = me.snippets.title;

        me.form = me.createForm();

        me.items = me.form;

        me.callParent(arguments);
    },

    getConfigValues: function () {
        const me = this;

        Ext.Ajax.request({
            url: '{url controller="viaebConfigForm" action="getConfigValues"}',
            method: 'POST',
            timeout: 180000,
            scope: me,
            success: function (resp) {
                const me = this;

                me.configValues = JSON.parse(resp.responseText).data;

                me.configCollection.each(function (form) {
                    const me = this;

                    form.getForm().getFields().each(me.resetFieldValues, me);
                }, me);
            },
            failure: function () {
                Shopware.Notification.createGrowlMessage(
                    '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
                    '{s namespace="backend/afterbuy" name="getConfigValuesError"}Konfigurationsdaten konnten nicht gelesen werden!{/s}',
                    'Afterbuy Conncetor'
                );
            },
        });
    },

    resetFieldValues: function (item) {
        const me = this;

        if (item.name in me.configValues) {
            item.setValue(me.configValues[item.name]);
        }
    },

    registerEvents: function () {
        this.addEvents(
            'saveAfterbuyConfig'
        );
    },

    createForm: function () {
        const me = this;

        return Ext.create('Ext.form.Panel', {
            url: '{url controller="viaebConfigForm" action="saveConnectionConfig"}',
            items: me.createTabPanel(),
            buttons: [
                me.createTestButton(),
                me.createSubmitButton(),
            ],
        });
    },

    createTabPanel: function () {
        const me = this;

        me.configCollection = new Ext.util.MixedCollection();
        me.configCollection.add(me.createConnectionConfigPanel());
        me.configCollection.add(me.createGeneralConfigPanel());

        return Ext.create('Ext.tab.Panel', {
            layout: {
                type: 'vbox',
                align: 'center',
            },

            items: me.configCollection.getRange(),
        });
    },

    createConnectionConfigPanel: function () {
        const me = this;

        return Ext.create('Ext.form.Panel', {
            title: '{s namespace="backend/viaebConfigForm" name="config_connection_title"}Verbindung{/s}',
            flex: 1,
            width: '100%',
            // value: me.snippets.infoText,
            htmlEncode: true,
            bodyPadding: 10,

            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                {
                    xtype: 'fieldset',
                    title: '{s namespace="backend/viaebConfigForm" name=connection_settings}Verbindungsdaten{/s}',
                    defaultType: 'textfield',
                    autoScroll: true,
                    flex: 1,
                    defaults: {
                        /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
                        readOnly: true,
                        /*{/if}*/
                        labelStyle: 'font-weight: 700; text-align: right;',
                        layout: 'anchor',
                        labelWidth: 130,
                        anchor: '100%'
                    },
                    items: me.createConnectionConfigFields(),
                }
            ],
        });
    },

    createGeneralConfigPanel: function () {
        const me = this;

        return Ext.create('Ext.form.Panel', {
            title: '{s namespace="backend/viaebConfigForm" name="config_general_title"}Allg. Einstellungen{/s}',
            flex: 1,
            width: '100%',
            htmlEncode: true,
            bodyPadding: 10,

            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                {
                    xtype: 'fieldset',
                    title: '{s namespace="backend/viaebConfigForm" name=general_settings}Einstellungen{/s}',
                    defaultType: 'combobox',
                    defaults: {
                        forceSelection: true,
                        displayField: 'name',
                        valueField: 'id',
                    },
                    autoScroll: true,
                    flex: 1,
                    items: me.createGeneralConfigFields(),
                }
            ],
        });
    },

    createConnectionConfigFields: function () {
        return [
            {
                xtype: 'textfield',
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=label_user}Afterbuy User{/s}',
                name: 'userName',
                allowBlank: false,
                checkChangeBuffer: 300,
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=label_userpw}User Password{/s}',
                name: 'userPassword',
                inputType: 'password',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=label_partnerid}Partner ID:{/s}',
                name: 'partnerId',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=label_partnerpw}Partner Pw:{/s}',
                name: 'partnerPassword',
                inputType: 'password',
            },
        ];
    },

    createSystemsStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: [
                'value',
                'display',
            ],
            data: [
                { 'value': 1, 'display': 'Shopware' },
                { 'value': 2, 'display': 'Afterbuy' },
            ]
        });
    },

    createYesNoStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: [
                'value',
                'display',
            ],
            data: [
                { 'value': 1, 'display': 'Ja' },
                { 'value': 0, 'display': 'Nein' },
            ]
        });
    },

    createRemoteStore: function(storeCls) {
        const store = Ext.create(storeCls);

        store.load();

        return store;
    },

    createGeneralConfigFields: function () {
        const me = this;

        return [
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=mainSystem}Datenführendes System{/s}',
                store: this.createSystemsStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                name: 'mainSystem',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=baseCategory}Stammkategorie{/s}',
                store: me.createRemoteStore(Shopware.apps.Base.store.Category),
                name: 'baseCategory',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=ExportAllArticles}Alle Artikel exportieren{/s}',
                store: this.createYesNoStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                name: 'ExportAllArticles',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=targetShop}Zielshop für Bestellungen{/s}',
                store: me.createRemoteStore(Shopware.apps.Base.store.Shop),
                name: 'targetShop',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=shipping}Versandart{/s}',
                store: me.createRemoteStore(Shopware.apps.Base.store.Dispatch),
                name: 'shipping',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=customerGroup}Kundengruppe{/s}',
                store: me.createRemoteStore(Shopware.apps.Base.store.CustomerGroup),
                name: 'customerGroup',
            },
        ];
    },

    createSubmitButton: function () {
        const me = this;

        return {
            text: '{s namespace="backend/viaebConfigForm" name=saveButton}Speichern{/s}',
            cls: 'button primary',
            handler: function () {
                me.fireEvent('saveAfterbuyConfig', me.form);
            },
        };
    },

    createTestButton: function () {
        const me = this;

        return {
            text: '{s namespace="backend/viaebConfigForm" name=testButton}Verbindungstest{/s}',
            cls: 'button secondary',
            handler: function () {
                me.fireEvent('testAfterbuyConfig', me.form);
            },
        };
    },
});
