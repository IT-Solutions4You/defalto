{*<!--
/*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************/
-->*}
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12 py-3">
            {assign var=CALCULATION_INFO value="{ucfirst(vtranslate('LBL_IN', $MODULE))} <strong>{getCurrencyName($USER_MODEL->get('currency_id'), false)}</strong><br /><br /> <table class = 'table table-bordered'><thead><th width='40%'>{vtranslate('LBL_PRODUCT_NAME', $MODULE)} </th><th width='35%'>{vtranslate('LBL_PRICE_QUANTITY', $MODULE)}</th><th class='w-25'>{vtranslate('LBL_TOTAL', $MODULE)}</th></thead>{foreach item=SUB_PRODUCT_COST_INFO key=SUB_PRODUCT_ID from=$SUB_PRODUCTS_COSTS_INFO}<tr><td>{$SUB_PRODUCT_COST_INFO.productName}</td><td>{$SUB_PRODUCT_COST_INFO.actualPrice} X {$SUB_PRODUCT_COST_INFO.quantityInBundle}</td> <td align='right'> {$SUB_PRODUCT_COST_INFO.priceInUserFormat}</td></tr>{/foreach}<tr></table><br /><div class = 'pull-right' style = 'padding-right:5px'><strong>{vtranslate('LBL_BUNDLE_TOTAL_COST', $MODULE)} : {$SUB_PRODUCTS_TOTAL_COST}</strong></div>"}
            <label>
                <span>{vtranslate('LBL_BUNDLE_TOTAL_COST', $MODULE)}</span>
                <span class="me-2">:</span>
                <a class="totalCostCalculationInfo me-2" role="button" data-bs-trigger="focus" title="{vtranslate('LBL_BUNDLE_TOTAL_COST', $MODULE)}" tabindex="0" data-bs-toggle="popover" data-bs-content="{$CALCULATION_INFO}">
                    <span class="subProductsTotalCost">{$SUB_PRODUCTS_TOTAL_COST}</span>
                </a>
            </label>
            {assign var=PRODUCT_ACTUAL_PRICE value="{CurrencyField::convertToUserFormat((float)$PARENT_RECORD->get('unit_price'), '', true, true)}"}
            <button type="button" id="updatePrice" class="btn btn-secondary" {if $SUB_PRODUCTS_TOTAL_COST eq $PRODUCT_ACTUAL_PRICE && $USER_MODEL->get('currency_id') eq $PARENT_RECORD->get('currency_id')}disabled{/if}>
                <i class="fa fa-refresh"></i>
                <strong class="ms-2">{vtranslate('LBL_UPDATE_BUNDLE_PRICE', $MODULE)}</strong>
            </button>
        </div>
    </div>
</div>
