{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<div class="relatedProducts container-fluid">
    {foreach item=HEADER from=$RELATED_HEADERS}
        {if $HEADER->get('label') eq "Product Name"}
            {assign var=PRODUCT_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE)}}
        {elseif $HEADER->get('label') eq "Unit Price"}
            {assign var=PRODUCT_UNITPRICE_HEADER value={vtranslate($HEADER->get('label'),$MODULE)}}
        {/if}
    {/foreach}
    <div class="row py-2">
        <div class="col-7">
            <strong>{$PRODUCT_NAME_HEADER}</strong>
        </div>
        <div class="col-5 text-end">
            <strong>{$PRODUCT_UNITPRICE_HEADER}</strong>
        </div>
    </div>
    {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
        <div class="recentActivitiesContainer row py-2">
            <div class="col-7 text-truncate">
                <a class="w-100 text-nowrap" href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('productname')}">
                    <span>{$RELATED_RECORD->getDisplayValue('productname')}</span>
                </a>
            </div>
            <div class="col-5 text-end">
                <span>{$RELATED_RECORD->getDisplayValue('unit_price')}</span>
            </div>
        </div>
    {/foreach}
    {assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
    {if $NUMBER_OF_RECORDS eq 5}
        <div class="row">
            <div class="col-12 text-end">
                <a href="javascript:void(0)" class="btn btn-primary moreRecentProducts">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
            </div>
        </div>
    {/if}
</div>
{/strip}