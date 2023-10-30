{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Products/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {include file='RelatedList.tpl'|@vtemplate_path}
    {assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
    {if $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS && $PARENT_RECORD->isBundle()}
        <div class="bundleCostContainer">
            {if $SUB_PRODUCTS_COSTS_INFO}
                {include file=vtemplate_path('BundleCostView.tpl',$MODULE)}
            {/if}
        </div>
    {/if}
{/strip}
