/**
 * $Id: $
 */

Ext.define('Shopware.apps.viaebConfigForm.controller.ConfigController', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    requestUrl: '{url controller="viaebResetShopConnection" action="reset"}',
    snippets: {
        growlTitle: '{s name=growlMessage/title}Verbindung zurücksetzen{/s}',
        growlMessageStart: '{s name=growlMessage/start}Starte Vorgang{/s}',
        growlMessageSuccess: '{s name=growlMessage/success}Vorgang erfolgreich{/s}',
        growlMessageFailureTimeout: '{s name=growlMessage/timeout}Timeout: Server nicht erreichbar{/s}',
        growlMessageFailureServer: '{s name=growlMessage/serverFailure}Fehler: Bei der Ausführung des Vorgangs ist ein Fehler aufgetreten{/s}',
        growlModule: '{s name=growlMessage/module}viaebResetShopConnection{/s}',
        success: '{s namespace="backend/afterbuy" name="success"}Erfolg{/s}',
        saveConnection: '{s namespace="backend/afterbuy" name="saveConnection"}Verbindungsdaten erfolgreich gespeichert{/s}',
        error: '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
        saveConnectionError: '{s namespace="backend/afterbuy" name="saveConnectionError"}Verbindungsdaten konnten nicht gespeichert werden!{/s}',
        connection: '{s namespace="backend/afterbuy" name="connection"}Verbindung erfolgreich hergestellt{/s}',
        errorUnknown: '{s namespace="backend/afterbuy" name="errorUnknown"}Unbekannter Fehler{/s}',
    },

    init: function () {
        const me = this;

        me.configWindow = me.getView('ConfigWindow').create();

        me.control({
            'window[id=config_window]': {
                'saveAfterbuyConfig': me.saveAfterbuyConfig,
                'testAfterbuyConfig': me.testAfterbuyConfig,
            },
        });
    },

    testAfterbuyConfig: function (form) {
        const me = this;

        // The getForm() method returns the Ext.form.Basic instance:
        form = form.getForm();

        if (form.isValid()) {
            // Submit the Ajax request and handle the response
            form.submit({
                url: '{url controller="AfterbuyConnector" action="testConnection"}?testdata=1',
                success: function () {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.success,
                        me.snippets.connection,
                        'Afterbuy Conncetor'
                    );
                },
                failure: function (form, action) {
                    let response = action.response;

                    if (!response) {
                        Shopware.Notification.createGrowlMessage(
                            me.snippets.error,
                            me.snippets.errorUnknown,
                            'Afterbuy Conncetor'
                        );
                    }

                    let status = JSON.parse(response.responseText);

                    if (response.responseText === 'Afterbuy Connection not configured!') {
                        Shopware.Notification.createGrowlMessage(
                            me.snippets.error,
                            response.responseText,
                            'Afterbuy Conncetor'
                        );

                        return;
                    }

                    Shopware.Notification.createGrowlMessage(
                        me.snippets.error,
                        status.data.error,
                        'Afterbuy Conncetor'
                    );
                }
            });
        }
    },

    saveAfterbuyConfig: function (form) {
        const me = this;
        // The getForm() method returns the Ext.form.Basic instance:
        const basicForm = form.getForm();

        if (basicForm.isValid()) {
            // Submit the Ajax request and handle the response
            basicForm.submit({
                success: function () {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.success,
                        me.snippets.saveConnection,
                        'Afterbuy Conncetor'
                    );
                },
                failure: function () {
                    Shopware.Notification.createGrowlMessage(
                        me.snippets.error,
                        me.snippets.saveConnectionError,
                        'Afterbuy Conncetor'
                    );
                }
            });
        } else {
            const allForms = form.query("form{ getForm() }");

            let index = 0;

            for (; index < allForms.length; index++) {
                if (!allForms[index].getForm().isValid()) break;
            }

            me.configWindow.setActiveTab(index);

            console.log('dbg');
        }
    },

    onSuccess: function (resp, me) {
        const response = JSON.parse(resp.responseText);

        let message = '';

        if (response.success) {
            message = me.snippets.growlMessageSuccess;
        } else {
            message = me.snippets.growlMessageFailureServer + ':<br>';
            for (let index = 0; index < response['total']; index++) {
                message += response['data'][index] + '<br>';
            }
        }

        Shopware.Notification.createGrowlMessage(
            me.snippets.growlTitle,
            message
        );
    },

    onFailure: function (resp, me) {
        Shopware.Notification.createGrowlMessage(
            me.snippets.growlTitle,
            me.snippets.growlMessageFailureTimeout
        );
    },
});
