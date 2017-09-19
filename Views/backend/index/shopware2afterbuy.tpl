{extends file="parent:backend/index/parent.tpl"}

{block name="backend/base/header/css"}
{$smarty.block.parent}

<style type="text/css">
    img.icon-afterbuy {
        background-position: 0 0 !important;
        background-image: url({link file="backend/_resources/images/plugin.png"}) !important;
    }
</style>

{/block}
