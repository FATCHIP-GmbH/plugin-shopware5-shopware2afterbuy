//{block name="backend/order/view/list/list"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.viaeb_extend_order.view.list.List', {
    override: 'Shopware.apps.Order.view.list.List',

    mySnippets: {
        columns: {
            afterbuyOrderId: '{s name="column/afterbuyOrderId"}Afterbuy Order ID{/s}',
        },
    },

    initComponent: function () {
        const me = this;

        me.callParent(arguments);
    },

    getColumns: function () {
        const me = this;
        const columns = me.callParent(arguments);

        columns.splice(2, 0,
            {
                header: me.mySnippets.columns.afterbuyOrderId,
                dataIndex: 'afterbuyOrderId',
                flex: 1,
                renderer: me.afterbuyOrderNumberRenderer,
            }
        );

        return columns;
    },

    afterbuyOrderNumberRenderer: function (content, meta) {
        if (content.length > 0) {
            meta.tdCls = 'afterbuy-grid-cell';
        }

        return content;
    }
});
//{/block}
