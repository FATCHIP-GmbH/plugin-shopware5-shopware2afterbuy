//{block name="backend/article_list/view/main/grid"}
//{$smarty.block.parent}
Ext.override(Shopware.apps.ArticleList.view.main.Grid, {

    /**
     * Creates the grid columns
     *
     * @return [array] grid columns
     */
    getColumns:function () {
        var me = this;
        var columns = me.callParent(arguments);

        // Todo: Remove Logging

        console.log(columns);
        colLength = columns.length;
        for (i = 0; i < colLength; i++) {
            column = columns[i];
            if (column.dataIndex === 'Attribute_afterbuyExport'){
                column.header = "Afterbuy Export";
                column.hidden = false;
                column.editor = {
                    xtype: 'checkbox',
                    inputValue: 1,
                    uncheckedValue: 0
                };
                column.renderer = function (value) {
                    var checked = 'sprite-ui-check-box-uncheck';
                    if (value == true) {
                        checked = 'sprite-ui-check-box';
                    }
                    return '<span style="display:block; margin: 0 auto; height:25px; width:25px;" class="' + checked + '"></span>';
                },
                console.log("Afterbuy Attribute found and made visible")
                console.log(column);
            }
            if (column.dataIndex === 'Attribute_afterbuyProductid'){
                column.header = "Afterbuy ProductID";
                column.hidden = false;
                column.editor = {
                    xtype: 'textfield'
                };

                console.log("Afterbuy ProductID Attribute found and made visible")
                console.log(column);
            }
        }
        return columns;
    }
});
//{/block}