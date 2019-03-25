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
                    items: me.getConnectionConfigFields(),
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
                    autoScroll: true,
                    flex: 1,
                    // defaults: {
                    //     /*{if !{acl_is_allowed privilege=create} && !{acl_is_allowed privilege=update}}*/
                    //     readOnly: true,
                    //     /*{/if}*/
                    //     labelStyle: 'font-weight: 700; text-align: right;',
                    //     layout: 'anchor',
                    //     labelWidth: 130,
                    //     anchor: '100%'
                    // },
                    items: me.getGeneralConfigFields(),
                }
            ],
        });
    },

    getConnectionConfigFields: function () {
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

    getCategoryStore: function () {
        const store = Ext.create('Shopware.apps.Base.store.Category');

        store.load();

        return store;
    },

    getYesNoStore: function () {
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

    getSystemsStore: function () {
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

    getShopStore: function () {
        const store = Ext.create('Shopware.apps.Base.store.Shop');

        store.load();

        return store;
    },

    getGeneralConfigFields: function () {
        const me = this;

        return [
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=mainSystem}Datenführendes System{/s}',
                store: this.getSystemsStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                forceSelection: true,
                name: 'mainSystem',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=baseCategory}Stammkategorie{/s}',
                store: me.getCategoryStore(),
                displayField: 'name',
                valueField: 'id',
                forceSelection: true,
                name: 'baseCategory',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=ExportAllArticles}Alle Artikel exportieren{/s}',
                store: this.getYesNoStore(),
                queryMode: 'local',
                displayField: 'display',
                valueField: 'value',
                forceSelection: true,
                name: 'ExportAllArticles',
            },
            {
                fieldLabel: '{s namespace="backend/viaebConfigForm" name=targetShop}Zielshop für Bestellungen{/s}',
                store: me.getShopStore(),
                displayField: 'name',
                valueField: 'id',
                forceSelection: true,
                name: 'targetShop',
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

    // getConnectionConfigButtons: function () {
    //     return [
    //         {
    //             text: 'Test',
    //             cls: 'button secondary',
    //             handler: function () {
    //                 // The getForm() method returns the Ext.form.Basic instance:
    //                 const form = this.up('form').getForm();
    //                 if (form.isValid()) {
    //                     // Submit the Ajax request and handle the response
    //
    //
    //                     form.submit({
    //                         url: '{url controller="viaebConfigForm" action="testConnectionConfig"}',
    //                         success: function (form, action) {
    //                             Shopware.Notification.createGrowlMessage(
    //                                 '{s namespace="backend/afterbuy" name="success"}Erfolg{/s}',
    //                                 '{s namespace="backend/afterbuy" name="saveConnection"}Verbindungsdaten erfolgreich gespeichert{/s}',
    //                                 'Afterbuy Conncetor'
    //                             );
    //                         },
    //                         failure: function (form, action) {
    //                             Shopware.Notification.createGrowlMessage(
    //                                 '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
    //                                 '{s namespace="backend/afterbuy" name="saveConnectionError"}Verbindungsdaten konnten nicht gespeichert werden!{/s}',
    //                                 'Afterbuy Conncetor'
    //                             );
    //                         }
    //                     });
    //                 }
    //             }
    //         },
    //         {
    //             text: 'Submit',
    //             cls: 'button primary',
    //             type: 'submit',
    //             // id: 'abc_button',
    //             // handler: function () {
    //             //     const me = this;
    //             //
    //             //     console.log('fire event');
    //             //
    //             //     me.fireEvent('saveAfterbuyConfig', me.items);
    //             // }
    //         },
    //     ];
    // }
});
