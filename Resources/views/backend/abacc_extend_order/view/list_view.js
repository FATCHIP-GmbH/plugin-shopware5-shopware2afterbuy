//{block name="backend/order/view/list/list"}
//{$smarty.block.parent}

console.log('list_view');
Ext.define('Shopware.apps.abacc_extend_order.view.list.List', {
    override: 'Shopware.apps.Order.view.list.List',

    // mySnippets: {
    //     columns: {
    //         hermes: '{s name=column/hermes}Hermes{/s}',
    //         noHermesDispatch: '{s name=column/noHermesDispatch}Kein Hermes Versand{/s}',
    //         createJob: '{s name=column/createJob}Auftrag erstellen{/s}',
    //         createLabel: '{s name=column/createLabel}Label erzeugen{/s}',
    //         downloadLabel: '{s name=column/downloadLabel}Label herunterladen{/s}',
    //     },
    //     toolbar: {
    //         createJobs: '{s name=toolbar/createJobs}Hermes Aufträge erstellen{/s}',
    //         createLabels: '{s name=toolbar/createLabels}Hermes Labels erzeugen{/s}',
    //         downloadLabels: '{s name=toolbar/downloadLabels}Hermes Labels herunterladen{/s}',
    //     }
    // },

    // initComponent: function () {
    //     const me = this;
    //
    //     me.hermesButtons = {
    //         createJobsButton: Ext.create('Ext.button.Button', {
    //             icon: '../engine/Shopware/Plugins/Community/Frontend/FatchipHermesShipping/Resources/images/hermes-logo.jpg',
    //             text: me.mySnippets.toolbar.createJobs,
    //             disabled: true,
    //             handler: function () {
    //                 me.fireEvent('createJobs', me);
    //             }
    //         }),
    //         createLabelsButton: Ext.create('Ext.button.Button', {
    //             icon: '../engine/Shopware/Plugins/Community/Frontend/FatchipHermesShipping/Resources/images/hermes-logo.jpg',
    //             text: me.mySnippets.toolbar.createLabels,
    //             disabled: true,
    //             handler: function () {
    //                 me.fireEvent('createLabels', me);
    //             }
    //         }),
    //         downloadLabelsButton: Ext.create('Ext.button.Button', {
    //             icon: '../engine/Shopware/Plugins/Community/Frontend/FatchipHermesShipping/Resources/images/hermes-logo.jpg',
    //             text: me.mySnippets.toolbar.downloadLabels,
    //             disabled: true,
    //             handler: function () {
    //                 me.fireEvent('downloadLabels', me);
    //             }
    //         }),
    //     };
    //
    //     Ext.Ajax.request({
    //         url: '{url controller="FatchipHermesOrder" action="ajaxGetHermesDispatchIDs"}',
    //         success: function (response) {
    //             me.hermesDispatchIDs = Ext.decode(response.responseText)['hermesDispatchIDs'];
    //         },
    //         failure: function (response, opts) {
    //         },
    //     });
    //
    //     me.callParent(arguments);
    //
    //     me.addEvents(
    //         'showCreateJobButton'
    //     )
    // },

    getColumns: function () {
        const me = this;
        const columns = me.callParent(arguments);

        columns.splice(columns.length - 1, 0,
            {
                header: 'test-header',
                dataIndex: 'test',
                flex:1,
                // renderer:me.
            }
        );

        return columns;
    },

    // getToolbar: function () {
    //     const me = this;
    //     const buttons = Object.values(me.hermesButtons);
    //     const toolbar = me.callParent(arguments);
    //
    //     for (let index = 0; index < buttons.length; index++) {
    //         const button = buttons[index];
    //
    //         toolbar.items.insert(
    //             index + 1,
    //             button
    //         );
    //     }
    //
    //     return toolbar;
    // },
    //
    // hermesRenderer: function (value, metaData, record, rowIndex, colIndex, store, view) {
    //     const me = this;
    //     const gridColumn = view.getGridColumns()[colIndex];
    //     const item = gridColumn.items[0];
    //
    //     item.handler = function (value, rowIndex, colIndex, item, event, record) {
    //         switch (me.hermesColumnItemIndices[rowIndex]) {
    //             case 0:
    //                 item.noHermesDispatch.handler(record);
    //                 break;
    //             case 1:
    //                 item.createJob.handler(record);
    //                 break;
    //             case 2:
    //                 item.createLabel.handler(record);
    //                 break;
    //             case 3:
    //                 item.downloadLabel.handler(record);
    //                 break;
    //             default:
    //         }
    //     };
    //
    //     if (!record.get('hermesDispatch')) {
    //         item.iconCls = item.noHermesDispatch.iconCls;
    //         item.tooltip = item.noHermesDispatch.tooltip;
    //         item.handler = item.noHermesDispatch.handler;
            // me.hermesColumnItemIndices[rowIndex] = 0;
        // } else if (record.get('hermesOrderState') === 'N/A') {
        //     item.iconCls = item.createJob.iconCls;
        //     item.tooltip = item.createJob.tooltip;
        //     item.handler = item.createJob.handler;
            // me.hermesColumnItemIndices[rowIndex] = 1;
        // } else if (record.get('hermesShipmentId') === 'N/A') {
        //     item.iconCls = item.createLabel.iconCls;
        //     item.tooltip = item.createLabel.tooltip;
        //     item.handler = item.createLabel.handler;
            // me.hermesColumnItemIndices[rowIndex] = 2;
        // } else {
        //     item.iconCls = item.downloadLabel.iconCls;
        //     item.tooltip = item.downloadLabel.tooltip;
        //     item.handler = item.downloadLabel.handler;
            // me.hermesColumnItemIndices[rowIndex] = 3;
        // }
    // },
    //
    // orderState: function (value, metaData, record) {
    //     return record.get('hermesOrderState');
    // },
    //
    // shipmentID: function (value, metaData, record) {
    //     return record.get('hermesShipmentId');
    // },
    //
    // dispatch: function (value, metaData, record) {
    //     return record.get('hermesDispatch');
    // },
    //
    // getGridSelModel: function () {
    //     const me = this;
    //     const selModel = me.callParent(arguments);
    //
    //     selModel.addListener(
    //         'selectionchange',
    //         function (selModel, selected) {
    //             me.fireEvent('selectionChange', selModel, selected, me.hermesDispatchIDs, me.hermesButtons);
    //         },
    //     );
    //
    //     return selModel;
    // },
});
//{/block}
