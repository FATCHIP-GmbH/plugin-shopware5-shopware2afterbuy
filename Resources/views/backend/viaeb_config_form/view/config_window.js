Ext.define('Shopware.apps.viaebConfigForm.view.ConfigWindow', {
    extend: 'Enlight.app.Window',

    id: 'config_window',

    snippets: {
        config_window_title: '{s namespace="backend/viaebConfigForm" name="config_window_title"}Konfiguration{/s}',
        error: '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
        getConfigValuesError: '{s namespace="backend/afterbuy" name="getConfigValuesError"}Konfigurationsdaten konnten nicht gelesen werden!{/s}',
        config_connection_title: '{s namespace="backend/viaebConfigForm" name="config_connection_title"}Verbindung{/s}',
        connection_settings: '{s namespace="backend/viaebConfigForm" name=connection_settings}Verbindungsdaten{/s}',
        config_general_title: '{s namespace="backend/viaebConfigForm" name="config_general_title"}Allg. Einstellungen{/s}',
        general_settings: '{s namespace="backend/viaebConfigForm" name=general_settings}Einstellungen{/s}',
        config_payment_mapping_title: '{s namespace="backend/viaebConfigForm" name="config_payment_mapping_title"}Zahlungsarten Zuordnungen{/s}',
        payment_mapping: '{s namespace="backend/viaebConfigForm" name=payment_mapping}Zahlungsarten{/s}',
        yes: '{s namespace="backend/viaebConfigForm" name=yes}ja{/s}',
        no: '{s namespace="backend/viaebConfigForm" name=no}nein{/s}',
        shopware: '{s namespace="backend/viaebConfigForm" name=shopware}Shopware{/s}',
        afterbuy: '{s namespace="backend/viaebConfigForm" name=afterbuy}Afterbuy{/s}',
        productId: '{s namespace="backend/viaebConfigForm" name=productId}Produkt ID{/s}',
        articleNr: '{s namespace="backend/viaebConfigForm" name=articleNr}Artikelnummer{/s}',
        label_user: '{s namespace="backend/viaebConfigForm" name=label_user}Afterbuy User{/s}',
        label_userpw: '{s namespace="backend/viaebConfigForm" name=label_userpw}User Password{/s}',
        label_partnerid: '{s namespace="backend/viaebConfigForm" name=label_partnerid}Partner ID:{/s}',
        label_partnerpw: '{s namespace="backend/viaebConfigForm" name=label_partnerpw}Partner Pw:{/s}',
        mainSystem: '{s namespace="backend/viaebConfigForm" name=mainSystem}Datenführendes System{/s}',
        ordernumberMapping: '{s namespace="backend/viaebConfigForm" name=ordernumberMapping}Bestellnummer Mapping{/s}',
        baseCategory: '{s namespace="backend/viaebConfigForm" name=baseCategory}Stammkategorie{/s}',
        ExportAllArticles: '{s namespace="backend/viaebConfigForm" name=ExportAllArticles}Alle Artikel exportieren{/s}',
        MinOrderDate: '{s namespace="backend/viaebConfigForm" name=MinOrderdate}Bestellungen exportieren ab{/s}',
        targetShop: '{s namespace="backend/viaebConfigForm" name=targetShop}Zielshop für Bestellungen{/s}',
        shipping: '{s namespace="backend/viaebConfigForm" name=shipping}Versandart{/s}',
        customerGroup: '{s namespace="backend/viaebConfigForm" name=customerGroup}Kundengruppe{/s}',
        saveButton: '{s namespace="backend/viaebConfigForm" name=saveButton}Speichern{/s}',
        testButton: '{s namespace="backend/viaebConfigForm" name=testButton}Verbindungstest{/s}',
        payment: {
            INVOICE: '{s namespace="backend/viaebConfigForm" name=paymentINVOICE}Vorkasse / Überweisung{/s}',
            CREDIT_CARD: '{s namespace="backend/viaebConfigForm" name=paymentCREDIT_CARD}Kreditkarte{/s}',
            DIRECT_DEBIT: '{s namespace="backend/viaebConfigForm" name=paymentDIRECT_DEBIT}Bankeinzug{/s}',
            TRANSFER: '{s namespace="backend/viaebConfigForm" name=paymentTRANSFER}Überweisung{/s}',
            CASH_PAID: '{s namespace="backend/viaebConfigForm" name=paymentCASH_PAID}Bar / Abholung{/s}',
            CASH_ON_DELIVERY: '{s namespace="backend/viaebConfigForm" name=paymentCASH_ON_DELIVERY}Nachnahme{/s}',
            PAYPAL: '{s namespace="backend/viaebConfigForm" name=paymentPAYPAL}PayPal{/s}',
            INVOICE_TRANSFER: '{s namespace="backend/viaebConfigForm" name=paymentINVOICE_TRANSFER}Überweisung / Rechnung{/s}',
            CLICKANDBUY: '{s namespace="backend/viaebConfigForm" name=paymentCLICKANDBUY}ClickAndBuy{/s}',
            EXPRESS_CREDITWORTHINESS: '{s namespace="backend/viaebConfigForm" name=paymentEXPRESS_CREDITWORTHINESS}Expresskauf / Bonicheck{/s}',
            PAYNET: '{s namespace="backend/viaebConfigForm" name=paymentPAYNET}Sofortüberweisung (PayNet){/s}',
            COD_CREDITWORTHINESS: '{s namespace="backend/viaebConfigForm" name=paymentCOD_CREDITWORTHINESS}Nachnahme / Bonicheck{/s}',
            EBAY_EXPRESS: '{s namespace="backend/viaebConfigForm" name=paymentEBAY_EXPRESS}Ebay Express{/s}',
            MONEYBOOKERS: '{s namespace="backend/viaebConfigForm" name=paymentMONEYBOOKERS}Moneybookers{/s}',
            CREDIT_CARD_MB: '{s namespace="backend/viaebConfigForm" name=paymentCREDIT_CARD_MB}Kreditkarte Moneybookers{/s}',
            DIRECT_DEBIT_MB: '{s namespace="backend/viaebConfigForm" name=paymentDIRECT_DEBIT_MB}Lastschrift Moneybookers{/s}',
            OTHERS: '{s namespace="backend/viaebConfigForm" name=paymentOTHERS}Sonstige{/s}',
        },
    },

    height: 600,
    width: 800,
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

        me.title = me.snippets.config_window_title;

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
                    me.snippets.error,
                    me.snippets.getConfigValuesError,
                    'Afterbuy Conncetor'
                );
            },
        });
    },

    resetFieldValues: function (item) {
        const me = this;

        if(me.configValues['minOrderDate'] && me.configValues['minOrderDate'].includes('T')) {
            var myDate = Ext.Date.parse(me.configValues['minOrderDate'].substring(0,10), 'Y-m-d');
            me.configValues['minOrderDate'] = Ext.Date.format(myDate, 'd.m.Y');
        }

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

        me.tabPanel = me.createTabPanel();

        return Ext.create('Ext.form.Panel', {
            url: '{url controller="viaebConfigForm" action="saveConnectionConfig"}',
            layout: 'fit',
            items: me.tabPanel,
            buttons: [
                me.createTestButton(),
                me.createSubmitButton(),
            ],
        });
    },

    setActiveTab: function (index) {
        this.tabPanel.setActiveTab(index);
    },

    createTabPanel: function () {
        const me = this;

        me.configCollection = new Ext.util.MixedCollection();
        me.configCollection.add(me.createConnectionConfigPanel());
        me.configCollection.add(me.createGeneralConfigPanel());
        me.configCollection.add(me.createPaymentMappingConfigPanel());

        return Ext.create('Ext.tab.Panel', {
            layout: 'fit',
            defaults: {
                overflowY: 'scroll',
                htmlEncode: true,
                bodyPadding: 10,
            },

            items: me.configCollection.getRange(),
        });
    },

    createConnectionConfigPanel: function () {
        const me = this;

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.config_connection_title,
            items: [
                Ext.create('Shopware.apps.viaebConfigForm.view.ColumnFieldSet', {
                    title: me.snippets.connection_settings,
                    childDefaults: {
                        xtype: 'textfield',
                        forceSelection: true,
                        allowBlank: false,
                    },
                    items: me.createConnectionConfigFields(),
                }),
            ],
        });
    },

    createGeneralConfigPanel: function () {
        const me = this;

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.config_general_title,
            items: [
                Ext.create('Shopware.apps.viaebConfigForm.view.ColumnFieldSet', {
                    title: me.snippets.general_settings,
                    childDefaults: {
                        xtype: 'combobox',
                        forceSelection: true,
                        allowBlank: false,
                        displayField: 'name',
                        valueField: 'id',
                    },
                    items: me.createGeneralConfigFields(),
                }),
                Shopware.Notification.createBlockMessage('{s namespace="backend/viaebConfigForm" name=adviceMapping}Bestellnummer Mapping: Bei Nutzung der Option "Produkt ID" werden von Afterbuy importierte Artikel mit der Bestellnummer entsprechend der Afterbuy-ProduktId versehen. Diese ist eineindeutig. Alternativ haben Sie hier die Möglichkeit, als Bestellnummer die Afterbuy-Artikelnummer zu vergeben. Dies wird zu Problemen führen, wenn Artikelnummern doppelt oder gar nicht vergeben sind!{/s}', 'error'),
            ],
        });
    },

    createPaymentMappingConfigPanel: function () {
        const me = this;
        const fields = me.createPaymentMappingConfigFields();

        return Ext.create('Ext.form.Panel', {
            title: me.snippets.config_payment_mapping_title,
            items: [
                Ext.create('Shopware.apps.viaebConfigForm.view.ColumnFieldSet', {
                    title: me.snippets.payment_mapping,
                    items: fields,
                    childDefaults: {
                        xtype: 'combo',
                        forceSelection: true,
                        allowBlank: false,
                        displayField: 'description',
                        valueField: 'id',
                        store: me.createRemoteStore(Shopware.apps.Base.store.Payment),
                    },
                }),
            ],
        });
    },

    createSystemsStore: function () {
        const me = this;

        return Ext.create('Ext.data.Store', {
            fields: [
                'value',
                'display',
            ],
            data: [
                {
                    'value': "1",
                    'display': me.snippets.shopware,
                },
                {
                    'value': "2",
                    'display': me.snippets.afterbuy,
                },
            ]
        });
    },

    createOrdernumberMappingStore: function () {
        const me = this;

        return Ext.create('Ext.data.Store', {
            fields: [
                'value',
                'display',
            ],
            data: [
                {
                    'value': "0",
                    'display': me.snippets.productId,
                },
                {
                    'value': "1",
                    'display': me.snippets.articleNr,
                },
            ]
        });
    },

    createYesNoStore: function () {
        const me = this;

        return Ext.create('Ext.data.Store', {
            fields: [
                'value',
                'display',
            ],
            data: [
                {
                    'value': "0",
                    'display': me.snippets.no,
                },
                {
                    'value': "1",
                    'display': me.snippets.yes,
                },
            ]
        });
    },

    createRemoteStore: function (storeCls) {
        const store = Ext.create(storeCls);

        store.load();

        return store;
    },

    createConnectionConfigFields: function () {
        const me = this;

        return [
            {
                xtype: 'textfield',
                fieldLabel: me.snippets.label_user,
                name: 'userName',
            },
            {
                fieldLabel: me.snippets.label_userpw,
                name: 'userPassword',
                inputType: 'password',
            },
            {
                fieldLabel: me.snippets.label_partnerid,
                name: 'partnerId',
            },
            {
                fieldLabel: me.snippets.label_partnerpw,
                name: 'partnerPassword',
                inputType: 'password',
            },
        ];
    },

    createGeneralConfigFields: function () {
        const me = this;

        return [
            {
                fieldLabel: me.snippets.mainSystem,
                store: me.createSystemsStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                name: 'mainSystem',
            },
            {
                fieldLabel: me.snippets.ordernumberMapping,
                store: me.createOrdernumberMappingStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                value: 0,
                name: 'ordernumberMapping',
            },
            {
                fieldLabel: me.snippets.baseCategory,
                store: me.createRemoteStore(Shopware.apps.Base.store.Category),
                name: 'baseCategory',
                forceSelection: false,
                allowBlank: true,
            },
            {
                xtype: 'datefield',
                fieldLabel: me.snippets.MinOrderDate,
                //dateFormat:'Y-m-dTH:i:s',
                submitFormat:'Y-m-d 00:00:00',
                format: 'd.m.Y',
                name: 'minOrderDate',
                allowBlank: true,
            },
            {
                fieldLabel: me.snippets.ExportAllArticles,
                store: me.createYesNoStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                name: 'ExportAllArticles',
            },
            {
                fieldLabel: me.snippets.targetShop,
                store: me.createRemoteStore(Shopware.apps.Base.store.Shop),
                name: 'targetShop',
            },
            {
                fieldLabel: me.snippets.shipping,
                store: me.createRemoteStore(Shopware.apps.Base.store.Dispatch),
                name: 'shipping',
            },
            {
                fieldLabel: me.snippets.customerGroup,
                store: me.createRemoteStore(Shopware.apps.Base.store.CustomerGroup),
                name: 'customerGroup',
            },
        ];
    },

    createPaymentMappingConfigFields: function () {
        const me = this;

        return [
            {
                fieldLabel: me.snippets.payment.INVOICE,
                name: 'paymentINVOICE',
            },
            {
                fieldLabel: me.snippets.payment.CREDIT_CARD,
                name: 'paymentCREDIT_CARD',
            },
            {
                fieldLabel: me.snippets.payment.DIRECT_DEBIT,
                name: 'paymentDIRECT_DEBIT',
            },
            {
                fieldLabel: me.snippets.payment.TRANSFER,
                name: 'paymentTRANSFER',
            },
            {
                fieldLabel: me.snippets.payment.CASH_PAID,
                name: 'paymentCASH_PAID',
            },
            {
                fieldLabel: me.snippets.payment.CASH_ON_DELIVERY,
                name: 'paymentCASH_ON_DELIVERY',
            },
            {
                fieldLabel: me.snippets.payment.PAYPAL,
                name: 'paymentPAYPAL',
            },
            {
                fieldLabel: me.snippets.payment.INVOICE_TRANSFER,
                name: 'paymentINVOICE_TRANSFER',
            },
            {
                fieldLabel: me.snippets.payment.CLICKANDBUY,
                name: 'paymentCLICKANDBUY',
            },
            {
                fieldLabel: me.snippets.payment.EXPRESS_CREDITWORTHINESS,
                name: 'paymentEXPRESS_CREDITWORTHINESS',
            },
            {
                fieldLabel: me.snippets.payment.PAYNET,
                name: 'paymentPAYNET',
            },
            {
                fieldLabel: me.snippets.payment.COD_CREDITWORTHINESS,
                name: 'paymentCOD_CREDITWORTHINESS',
            },
            {
                fieldLabel: me.snippets.payment.EBAY_EXPRESS,
                name: 'paymentEBAY_EXPRESS',
            },
            {
                fieldLabel: me.snippets.payment.MONEYBOOKERS,
                name: 'paymentMONEYBOOKERS',
            },
            {
                fieldLabel: me.snippets.payment.CREDIT_CARD_MB,
                name: 'paymentCREDIT_CARD_MB',
            },
            {
                fieldLabel: me.snippets.payment.DIRECT_DEBIT_MB,
                name: 'paymentDIRECT_DEBIT_MB',
            },
            {
                fieldLabel: me.snippets.payment.OTHERS,
                name: 'paymentOTHERS',
            },
        ];
    },

    createSubmitButton: function () {
        const me = this;

        return {
            text: me.snippets.saveButton,
            cls: 'button primary',
            handler: function () {
                me.fireEvent('saveAfterbuyConfig', me.form);
            },
        };
    },

    createTestButton: function () {
        const me = this;

        return {
            text: me.snippets.testButton,
            cls: 'button secondary',
            handler: function () {
                me.fireEvent('testAfterbuyConfig', me.form);
            },
        };
    },
});
