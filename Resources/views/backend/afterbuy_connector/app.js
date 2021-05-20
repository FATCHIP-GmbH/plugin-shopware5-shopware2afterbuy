Ext.define('Shopware.apps.AfterbuyConnector', {

    extend: 'Enlight.app.SubApplication',

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name:'Shopware.apps.AfterbuyConnector',


    launch: function() {
        var me = this;


        Ext.Ajax.request({
            url: '{url controller=AfterbuyConnector action=testConnection}',
            success: function(response) {

                if(response.responseText == 'Afterbuy Connection not configured!') {
                    Shopware.Notification.createGrowlMessage(
                        '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
                        response.responseText,
                        'Afterbuy Conncetor'
                    );

                    return;
                }

                var status = JSON.parse(response.responseText);

                if(status.success == true) {
                    Shopware.Notification.createGrowlMessage(
                        '{s namespace="backend/afterbuy" name="success"}Erfolg{/s}',
                        '{s namespace="backend/afterbuy" name="connection"}Verbindung erfolgreich hergestellt{/s}',
                        'Afterbuy Conncetor'
                    );
                }
                else {
                    Shopware.Notification.createGrowlMessage(
                        '{s namespace="backend/afterbuy" name="error"}Fehler{/s}',
                        status.data.error,
                        'Afterbuy Conncetor'
                    );
                }
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage(
                    '{s namespace="backend/afterbuy" name="error"}Error{/s}',
                    'Unbekannter Fehler beim Testen der Verbindung',
                    'Afterbuy Conncetor'
                );
            }
        });

    }
});




