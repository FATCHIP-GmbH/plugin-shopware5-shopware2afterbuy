//{block name="backend/article_list/view/main/grid"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.viaeb_extend_article_list.view.main.Grid', {
    override: 'Shopware.apps.ArticleList.view.main.Grid',

    getColumns: function () {
        const me = this;
        const columns = me.callParent(arguments);

        let colCount = columns.length;
        let afterbuyIdIndex = 0;
        let articleNumberIndex = 0;
        let afterbuyColumn;

        for (let index = 0; index < colCount; index++) {
            const column = columns[index];

            if (column['dataIndex'] === 'Attribute_afterbuyId') {
                column['header'] = me.getTranslationForColumnHead('Afterbuy OrderID');
                afterbuyIdIndex = index;
                column['width'] = me.getWidthForColumn({
                    'alias': 'Detail_number',
                });
            }

            if (column['dataIndex'] === 'Attribute_afterbuyExportEnabled') {
                column['header'] = me.getTranslationForColumnHead('Exportieren Nach Afterbuy');
                column['width'] = me.getWidthForColumn({
                    'alias': 'Article_active',
                });
            }

            if (column['dataIndex'] === 'Detail_number') {
                articleNumberIndex = index;
            }
        }

        afterbuyColumn = columns[afterbuyIdIndex];

        // cut out
        columns.splice(afterbuyIdIndex, 1);
        // paste in
        columns.splice(articleNumberIndex, 0, afterbuyColumn);

        return columns;
    },
});
//{/block}
