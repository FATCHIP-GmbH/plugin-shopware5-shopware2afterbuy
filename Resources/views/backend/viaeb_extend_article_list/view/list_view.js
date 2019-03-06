//{block name="backend/article_list/view/main/grid"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.viaeb_extend_article_list.view.main.Grid', {
    override: 'Shopware.apps.ArticleList.view.main.Grid',

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

    getRendererForColumn: function (column) {
        const me = this;

        if (column.alias === 'Detail_number') {
            return me.doubleLineRenderer;
        }

        return me.callParent(arguments);
    },

    /**
     * Column renderer for columns with two lines
     *
     * @param value
     * @param metaData
     * @param record
     * @returns string
     */
    doubleLineRenderer: function (value, metaData, record) {
        const firstLine = this.defaultColumnRenderer(value);
        const afterbuyId = record['data']['Attribute_afterbuyId'];

        let secondLine = '';

        if (afterbuyId !== null) {
            secondLine = '<br>(' + this.defaultColumnRenderer(afterbuyId) + ')';
        }

        return firstLine + secondLine;
    },
});
//{/block}
