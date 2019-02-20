//{block name="backend/order/view/list/list"}
//{$smarty.block.parent}

console.log('list_view');
Ext.define('Shopware.apps.abacc_extend_order.view.list.List', {
    override: 'Shopware.apps.Order.view.list.List',

    mySnippets: {
        columns: {
            afterbuyOrderId: '{s name=column/afterbuyOrderId}Afterbuy Order ID{/s}',
            hermes: '{s name=column/hermes}Hermes{/s}',
            noHermesDispatch: '{s name=column/noHermesDispatch}Kein Hermes Versand{/s}',
            createJob: '{s name=column/createJob}Auftrag erstellen{/s}',
            createLabel: '{s name=column/createLabel}Label erzeugen{/s}',
            downloadLabel: '{s name=column/downloadLabel}Label herunterladen{/s}',
        },
        toolbar: {
            createJobs: '{s name=toolbar/createJobs}Hermes Aufträge erstellen{/s}',
            createLabels: '{s name=toolbar/createLabels}Hermes Labels erzeugen{/s}',
            downloadLabels: '{s name=toolbar/downloadLabels}Hermes Labels herunterladen{/s}',
        }
    },

    myViewConfig: {
        getRowClass: function (record) {
            console.log('getRowClass');
            return (record.data.afterbuyOrderId === '') ? '' : 'afterbuy-grid-row';
        }
    },

    initComponent: function () {
        const me = this;

        for (let [key, value] of Object.entries(me.myViewConfig)) {
            me.viewConfig[key] = value;
        }
        console.log('init view');

        me.callParent(arguments);
    },

    getColumns: function () {
        const me = this;
        const columns = me.callParent(arguments);

        columns.splice(columns.length - 1, 0,
            {
                header: me.mySnippets.columns.afterbuyOrderId,
                dataIndex: 'afterbuyOrderId',
                flex: 1,
            }
        );

        return columns;
    },
});
//{/block}
