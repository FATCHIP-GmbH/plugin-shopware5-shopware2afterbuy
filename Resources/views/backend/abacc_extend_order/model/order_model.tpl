{extends file="parent:backend/order/model/order.js"}
{block name="backend/order/model/order/fields"}
    {$smarty.block.parent}

    { name : 'afterbuyOrderId', type: 'string' },
{/block}
