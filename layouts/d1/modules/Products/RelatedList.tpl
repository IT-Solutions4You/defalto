{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
