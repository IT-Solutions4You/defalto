{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}{strip}
    {assign var=ITEM_DETAILS_BLOCK value=$BLOCK_LIST['LBL_ITEM_DETAILS']}
    {assign var=LINEITEM_FIELDS value=$ITEM_DETAILS_BLOCK->getFields()}

    {assign var=COL_SPAN1 value=0}
    {assign var=COL_SPAN2 value=0}
    {assign var=COL_SPAN3 value=2}
    {assign var=IMAGE_VIEWABLE value=false}
    {assign var=PRODUCT_VIEWABLE value=false}
    {assign var=QUANTITY_VIEWABLE value=false}
    {assign var=PURCHASE_COST_VIEWABLE value=false}
    {assign var=LIST_PRICE_VIEWABLE value=false}
    {assign var=MARGIN_VIEWABLE value=false}
    {assign var=COMMENT_VIEWABLE value=false}
    {assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=false}
    {assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=false}
    {assign var=SH_PERCENT_VIEWABLE value=false}
    {assign var=DISCOUNT_AMOUNT_VIEWABLE value=false}
    {assign var=DISCOUNT_PERCENT_VIEWABLE value=false}

    {if $LINEITEM_FIELDS['image']}
        {assign var=IMAGE_VIEWABLE value=$LINEITEM_FIELDS['image']->isViewable()}
        {if $IMAGE_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['productid']}
        {assign var=PRODUCT_VIEWABLE value=$LINEITEM_FIELDS['productid']->isViewable()}
        {if $PRODUCT_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['quantity']}
        {assign var=QUANTITY_VIEWABLE value=$LINEITEM_FIELDS['quantity']->isViewable()}
        {if $QUANTITY_VIEWABLE}{assign var=COL_SPAN1 value=($COL_SPAN1)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['purchase_cost']}
        {assign var=PURCHASE_COST_VIEWABLE value=$LINEITEM_FIELDS['purchase_cost']->isViewable()}
        {if $PURCHASE_COST_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['listprice']}
        {assign var=LIST_PRICE_VIEWABLE value=$LINEITEM_FIELDS['listprice']->isViewable()}
        {if $LIST_PRICE_VIEWABLE}{assign var=COL_SPAN2 value=($COL_SPAN2)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['margin']}
        {assign var=MARGIN_VIEWABLE value=$LINEITEM_FIELDS['margin']->isViewable()}
        {if $MARGIN_VIEWABLE}{assign var=COL_SPAN3 value=($COL_SPAN3)+1}{/if}
    {/if}
    {if $LINEITEM_FIELDS['comment']}
        {assign var=COMMENT_VIEWABLE value=$LINEITEM_FIELDS['comment']->isViewable()}
    {/if}
    {if $LINEITEM_FIELDS['discount_amount']}
        {assign var=ITEM_DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['discount_amount']->isViewable()}
    {/if}
    {if $LINEITEM_FIELDS['discount_percent']}
        {assign var=ITEM_DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['discount_percent']->isViewable()}
    {/if}
    {if $LINEITEM_FIELDS['hdnS_H_Percent']}
        {assign var=SH_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnS_H_Percent']->isViewable()}
    {/if}
    {if $LINEITEM_FIELDS['hdnDiscountAmount']}
        {assign var=DISCOUNT_AMOUNT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountAmount']->isViewable()}
    {/if}
    {if $LINEITEM_FIELDS['hdnDiscountPercent']}
        {assign var=DISCOUNT_PERCENT_VIEWABLE value=$LINEITEM_FIELDS['hdnDiscountPercent']->isViewable()}
    {/if}
    <input type="hidden" class="isCustomFieldExists" value="false">
    {assign var=FINAL_DETAILS value=$RELATED_PRODUCTS[1]['final_details']}
    <div class="details block mt-3">
        <div class="lineItemTableDiv">
            <div class="rounded bg-body">
                <div class="p-3 border-bottom">
                    <div class="text-truncate d-flex align-items-center">
                        <span class="btn btn-outline-secondary">
                            <i class="fa-solid fa-list"></i>
                        </span>
                        <span class="ms-3 fs-4 fw-bold">{vtranslate('LBL_ITEM_DETAILS', $MODULE_NAME)}</span>
                    </div>
                </div>
                <div class="container-fluid p-3">
                    <div class="row">
                        <div class="col-sm">
                            <div class="p-2">
                                {assign var=REGION_LABEL value=''}
                                {if $RECORD->get('region_id') && $LINEITEM_FIELDS['region_id'] && $LINEITEM_FIELDS['region_id']->isViewable()}
                                    {assign var=TAX_REGION_MODEL value=Inventory_TaxRegion_Model::getRegionModel($RECORD->get('region_id'))}
                                    {if $TAX_REGION_MODEL}
                                        {assign var=REGION_LABEL value="{vtranslate($LINEITEM_FIELDS['region_id']->get('label'), $MODULE_NAME)} : {$TAX_REGION_MODEL->getName()}"}
                                    {/if}
                                {/if}
                                <span class="me-2">{vtranslate('Tax Region', $MODULE_NAME)}:</span>
                                <strong>{$REGION_LABEL}</strong>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="p-2">
                                {assign var=CURRENCY_INFO value=$RECORD->getCurrencyInfo()}
                                <span class="me-2">{vtranslate('LBL_CURRENCY', $MODULE_NAME)}:</span>
                                <strong>{vtranslate($CURRENCY_INFO['currency_name'],$MODULE_NAME)}({$CURRENCY_INFO['currency_symbol']})</strong>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="p-2">
                                <span class="me-2">{vtranslate('LBL_TAX_MODE', $MODULE_NAME)}:</span>
                                <strong>{vtranslate($FINAL_DETAILS.taxtype, $MODULE_NAME)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 p-3 rounded bg-body">
                <table class="table table-borderless lineItemsTable lh-lg mb-0">
                    <thead>
                        <tr>
                            {if $IMAGE_VIEWABLE}
                                <th class="lineItemFieldName">
                                    <span>{vtranslate({$LINEITEM_FIELDS['image']->get('label')},$MODULE)}</span>
                                </th>
                            {/if}
                            {if $PRODUCT_VIEWABLE}
                                <th class="lineItemFieldName">
                                    <span>{vtranslate({$LINEITEM_FIELDS['productid']->get('label')},$MODULE_NAME)}</span>
                                </th>
                            {/if}
                            {if $QUANTITY_VIEWABLE}
                                <th class="lineItemFieldName w-10">
                                    <span>{vtranslate({$LINEITEM_FIELDS['quantity']->get('label')},$MODULE_NAME)}</span>
                                </th>
                            {/if}
                            {if $PURCHASE_COST_VIEWABLE}
                                <th class="lineItemFieldName w-10">
                                    <span>{vtranslate({$LINEITEM_FIELDS['purchase_cost']->get('label')},$MODULE_NAME)}</span>
                                </th>
                            {/if}
                            {if $LIST_PRICE_VIEWABLE}
                                <th class="text-nowrap text-end">
                                    <span>{vtranslate({$LINEITEM_FIELDS['listprice']->get('label')},$MODULE_NAME)}</span>
                                </th>
                            {/if}
                            <th class="lineItemFieldName text-end w-10">
                                <span>{vtranslate('LBL_TOTAL',$MODULE_NAME)}</span>
                            </th>
                            <th class="lineItemFieldName text-end w-10">
                                {if $MARGIN_VIEWABLE}
                                    <span>{vtranslate({$LINEITEM_FIELDS['margin']->get('label')},$MODULE_NAME)}</span>
                                {/if}
                            </th>
                            <th class="lineItemFieldName text-end w-10">
                                <span>{vtranslate('LBL_NET_PRICE',$MODULE_NAME)}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach key=INDEX item=LINE_ITEM_DETAIL from=$RELATED_PRODUCTS}
                        <tr class="border-top">
                            {if $IMAGE_VIEWABLE}
                                <td class="text-center">
                                    <img src='{$LINE_ITEM_DETAIL["productImage$INDEX"]}' height="42" width="42">
                                </td>
                            {/if}

                            {if $PRODUCT_VIEWABLE}
                                <td>
                                    <div>
                                        {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                            {$LINE_ITEM_DETAIL["productName$INDEX"]}
                                        {else}
                                            <a class="fs-5 fw-bold" href="index.php?module={$LINE_ITEM_DETAIL["entityType$INDEX"]}&view=Detail&record={$LINE_ITEM_DETAIL["hdnProductId$INDEX"]}" target="_blank">{$LINE_ITEM_DETAIL["productName$INDEX"]}</a>
                                        {/if}
                                    </div>
                                    {if $LINE_ITEM_DETAIL["productDeleted$INDEX"]}
                                        <div class="text-danger deletedItem">
                                            {if empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                                                {vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
                                            {else}
                                                {vtranslate('LBL_THIS',$MODULE)} {$LINE_ITEM_DETAIL["entityType$INDEX"]} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
                                            {/if}
                                        </div>
                                    {/if}
                                    <div>
                                        {$LINE_ITEM_DETAIL["subprod_names$INDEX"]}
                                    </div>
                                    {if $COMMENT_VIEWABLE && !empty($LINE_ITEM_DETAIL["productName$INDEX"])}
                                        <div>
                                            {decode_html($LINE_ITEM_DETAIL["comment$INDEX"])|nl2br}
                                        </div>
                                    {/if}
                                </td>
                            {/if}

                            {if $QUANTITY_VIEWABLE}
                                <td>
                                    <div>{$LINE_ITEM_DETAIL["qty$INDEX"]}</div>
                                </td>
                            {/if}

                            {if $PURCHASE_COST_VIEWABLE}
                                <td>
                                    <div>{$LINE_ITEM_DETAIL["purchaseCost$INDEX"]}</div>
                                </td>
                            {/if}

                            {if $LIST_PRICE_VIEWABLE}
                                <td class="text-nowrap text-end">
                                    <div>
                                        {$LINE_ITEM_DETAIL["listPrice$INDEX"]}
                                    </div>
                                    {if $ITEM_DISCOUNT_AMOUNT_VIEWABLE || $ITEM_DISCOUNT_PERCENT_VIEWABLE}
                                        <div>
                                            {assign var=DISCOUNT_INFO value="{if $LINE_ITEM_DETAIL["discount_type$INDEX"] == 'amount'} {vtranslate('LBL_DIRECT_AMOUNT_DISCOUNT',$MODULE_NAME)} = {$LINE_ITEM_DETAIL["discountTotal$INDEX"]}{elseif $LINE_ITEM_DETAIL["discount_type$INDEX"] == 'percentage'} {$LINE_ITEM_DETAIL["discount_percent$INDEX"]} % {vtranslate('LBL_OF',$MODULE_NAME)} {$LINE_ITEM_DETAIL["productTotal$INDEX"]} = {$LINE_ITEM_DETAIL["discountTotal$INDEX"]}{/if}"}
                                            <a href="javascript:void(0)" class="individualDiscount inventoryLineItemDetails" tabindex="0" role="tooltip" data-bs-html="true" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="{vtranslate('LBL_DISCOUNT',$MODULE_NAME)}" data-bs-content="{$DISCOUNT_INFO}">
                                                <span class="me-2">(-)</span>
                                                <strong>{vtranslate('LBL_DISCOUNT',$MODULE_NAME)}</strong>
                                            </a>
                                        </div>
                                    {/if}
                                    <div>
                                        <strong>{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)}</strong>
                                    </div>
                                    {if $FINAL_DETAILS.taxtype neq 'group'}
                                        <div class="individualTaxContainer text-end">
                                            {assign var=INDIVIDUAL_TAX_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT', $MODULE_NAME)} = {$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}<br /><br />{foreach item=tax_details from=$LINE_ITEM_DETAIL['taxes']}{if $LINEITEM_FIELDS["{$tax_details['taxname']}"]}{$tax_details['taxlabel']} : \t{$tax_details['percentage']}%  {vtranslate('LBL_OF',$MODULE_NAME)}  {if $tax_details['method'] eq 'Compound'}({/if}{$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}{if $tax_details['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$tax_details['compoundon']}{if $FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']} + {$FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']}{/if}{/foreach}){/if} = {$tax_details['amount']}<br />{/if}{/foreach}<br /><br />{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$LINE_ITEM_DETAIL["taxTotal$INDEX"]}"}
                                            <a href="javascript:void(0)" class="individualTax inventoryLineItemDetails" tabindex="0" role="tooltip" id="example" data-bs-title="{vtranslate('LBL_TAX',$MODULE_NAME)}" data-bs-toggle="popover" data-bs-html="true" data-bs-trigger="focus" data-bs-content="{$INDIVIDUAL_TAX_INFO}">
                                                <span class="me-2">(+)</span>
                                                <strong>{vtranslate('LBL_TAX',$MODULE_NAME)}</strong>
                                            </a>
                                        </div>
                                    {/if}
                                </td>
                            {/if}
                            <td class="text-end">
                                <div>{$LINE_ITEM_DETAIL["productTotal$INDEX"]}</div>
                                {if $ITEM_DISCOUNT_AMOUNT_VIEWABLE || $ITEM_DISCOUNT_PERCENT_VIEWABLE}
                                    <div>{$LINE_ITEM_DETAIL["discountTotal$INDEX"]}</div>
                                {/if}
                                <div>{$LINE_ITEM_DETAIL["totalAfterDiscount$INDEX"]}</div>
                                {if $FINAL_DETAILS.taxtype neq 'group'}
                                    <div>{$LINE_ITEM_DETAIL["taxTotal$INDEX"]}</div>
                                {/if}
                            </td>
                            <td class="text-end">
                                {if $MARGIN_VIEWABLE}
                                    <div>{$LINE_ITEM_DETAIL["margin$INDEX"]}</div>
                                {/if}
                            </td>
                            <td class="text-end">
                                <div>{$LINE_ITEM_DETAIL["netPrice$INDEX"]}</div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-body rounded mt-3">
                <table class="table table-borderless lineItemsTable lh-lg mb-0">
                    <thead>
                        <tr class="border-bottom">
                            <th class="text-end">
                                <strong>{vtranslate('LBL_ITEMS_TOTAL',$MODULE_NAME)}</strong>
                            </th>
                            <th class="text-end w-30">
                                <strong>{$FINAL_DETAILS["hdnSubTotal"]}</strong>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    {if $DISCOUNT_AMOUNT_VIEWABLE || $DISCOUNT_PERCENT_VIEWABLE}
                        <tr class="border-bottom">
                            <td>
                                <div class="text-end">
                                    {assign var=FINAL_DISCOUNT_INFO value="{vtranslate('LBL_FINAL_DISCOUNT_AMOUNT',$MODULE_NAME)} = {if $DISCOUNT_PERCENT_VIEWABLE && $FINAL_DETAILS['discount_type_final'] == 'percentage'} {$FINAL_DETAILS['discount_percentage_final']}	% {vtranslate('LBL_OF',$MODULE_NAME)} {$FINAL_DETAILS['hdnSubTotal']} = {/if}{$FINAL_DETAILS['discountTotal_final']}"}
                                    <a class="inventoryLineItemDetails" href="javascript:void(0)" id="finalDiscount" tabindex="0" role="tooltip" data-bs-trigger="focus" data-bs-toggle="popover" data-bs-html="true" data-bs-content="{$FINAL_DISCOUNT_INFO}" data-bs-title="{vtranslate('LBL_OVERALL_DISCOUNT',$MODULE_NAME)}">
                                        <span class="me-2">(-)</span>
                                        <strong>{vtranslate('LBL_OVERALL_DISCOUNT',$MODULE_NAME)}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text-end">
                                    {$FINAL_DETAILS['discountTotal_final']}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    {if $SH_PERCENT_VIEWABLE}
                        <tr class="border-bottom">
                            <td>
                                <div class="text-end">
                                    {assign var=CHARGES_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['totalAfterDiscount']}<br /><br />{foreach key=CHARGE_ID item=CHARGE_INFO from=$SELECTED_CHARGES_AND_ITS_TAXES} {if $CHARGE_INFO['deleted']}({strtoupper(vtranslate('LBL_DELETED',$MODULE_NAME))}){/if} {$CHARGE_INFO['name']} {if $CHARGE_INFO['percent']}: {$CHARGE_INFO['percent']}% {vtranslate('LBL_OF',$MODULE_NAME)} {$FINAL_DETAILS['totalAfterDiscount']}{/if} = {$CHARGE_INFO['amount']}<br />{/foreach}<br /><h5>{vtranslate('LBL_CHARGES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS['shipping_handling_charge']}</h5>"}
                                    <a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" data-bs-trigger="focus" data-bs-placement="left" data-bs-toggle="popover" data-bs-html="true" data-bs-title="{vtranslate('LBL_CHARGES',$MODULE_NAME)}" data-bs-content="{$CHARGES_INFO}">
                                        <span class="me-2">(+)</span>
                                        <strong>{vtranslate('LBL_CHARGES',$MODULE_NAME)}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text-end">
                                    {$FINAL_DETAILS["shipping_handling_charge"]}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    <tr class="border-bottom">
                        <td>
                            <div class="text-end">
                                <strong>{vtranslate('LBL_PRE_TAX_TOTAL', $MODULE_NAME)} </strong>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                                {$FINAL_DETAILS["preTaxTotal"]}
                            </div>
                        </td>
                    </tr>
                    {if $FINAL_DETAILS.taxtype eq 'group'}
                        <tr class="border-bottom">
                            <td>
                                <div class="text-end">
                                    {assign var=GROUP_TAX_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['totalAfterDiscount']}<br /><br />{foreach item=tax_details from=$FINAL_DETAILS['taxes']}{$tax_details['taxlabel']} : \t{$tax_details['percentage']}% {vtranslate('LBL_OF',$MODULE_NAME)} {if $tax_details['method'] eq 'Compound'}({/if}{$FINAL_DETAILS['totalAfterDiscount']}{if $tax_details['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$tax_details['compoundon']}{if $FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']} + {$FINAL_DETAILS['taxes'][$COMPOUND_TAX_ID]['taxlabel']}{/if}{/foreach}){/if} = {$tax_details['amount']}<br />{/foreach}<br />{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['tax_totalamount']}"}
                                    <a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" id="finalTax" data-bs-trigger="focus" data-bs-placement="left" data-bs-title="{vtranslate('LBL_TAX',$MODULE_NAME)}" data-bs-toggle="popover" data-bs-html="true" data-content="{$GROUP_TAX_INFO}">
                                        <span class="me-2">(+)</span>
                                        <strong>{vtranslate('LBL_TAX',$MODULE_NAME)}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text-end">
                                    {$FINAL_DETAILS['tax_totalamount']}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    {if $SH_PERCENT_VIEWABLE}
                        <tr class="border-bottom">
                            <td>
                                <div class="text-end">
                                    {assign var=CHARGES_TAX_INFO value="{vtranslate('LBL_CHARGES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS["shipping_handling_charge"]}<br /><br />{foreach key=CHARGE_ID item=CHARGE_INFO from=$SELECTED_CHARGES_AND_ITS_TAXES}{if $CHARGE_INFO['taxes']}{if $CHARGE_INFO['deleted']}({strtoupper(vtranslate('LBL_DELETED',$MODULE_NAME))}){/if} {$CHARGE_INFO['name']}<br />{foreach item=CHARGE_TAX_INFO from=$CHARGE_INFO['taxes']}&emsp;{$CHARGE_TAX_INFO['name']}: &emsp;{$CHARGE_TAX_INFO['percent']}% {vtranslate('LBL_OF',$MODULE_NAME)} {if $CHARGE_TAX_INFO['method'] eq 'Compound'}({/if}{$CHARGE_INFO['amount']} {if $CHARGE_TAX_INFO['method'] eq 'Compound'}{foreach item=COMPOUND_TAX_ID from=$CHARGE_TAX_INFO['compoundon']}{if $CHARGE_INFO['taxes'][$COMPOUND_TAX_ID]['name']} + {$CHARGE_INFO['taxes'][$COMPOUND_TAX_ID]['name']}{/if}{/foreach}){/if} = {$CHARGE_TAX_INFO['amount']}<br />{/foreach}<br />{/if}{/foreach}\r\n{vtranslate('LBL_TOTAL_TAX_AMOUNT',$MODULE_NAME)} = {$FINAL_DETAILS['shtax_totalamount']}"}
                                    <a class="inventoryLineItemDetails" tabindex="0" role="tooltip" data-bs-title="{vtranslate('LBL_TAXES_ON_CHARGES',$MODULE_NAME)}" data-bs-trigger="focus" data-bs-placement="left" data-bs-toggle="popover" data-bs-html="true" href="javascript:void(0)" id="taxesOnChargesList" data-bs-content="{$CHARGES_TAX_INFO}">
                                        <span class="me-2">(+)</span>
                                        <strong>{vtranslate('LBL_TAXES_ON_CHARGES',$MODULE_NAME)}</strong>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="text-end">
                                    {$FINAL_DETAILS["shtax_totalamount"]}
                                </div>
                            </td>
                        </tr>
                    {/if}
                    <tr class="border-bottom">
                        <td>
                            <div class="text-end">
                                {assign var=DEDUCTED_TAXES_INFO value="{vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$MODULE_NAME)} = {$FINAL_DETAILS["totalAfterDiscount"]}<br /><br />{foreach key=DEDUCTED_TAX_ID item=DEDUCTED_TAX_INFO from=$FINAL_DETAILS['deductTaxes']}{if $DEDUCTED_TAX_INFO['selected'] eq true}{$DEDUCTED_TAX_INFO['taxlabel']}: \t{$DEDUCTED_TAX_INFO['percentage']}%  = {$DEDUCTED_TAX_INFO['amount']}\r\n{/if}{/foreach}\r\n\r\n{vtranslate('LBL_DEDUCTED_TAXES_TOTAL',$MODULE_NAME)} = {$FINAL_DETAILS['deductTaxesTotalAmount']}"}
                                <a class="inventoryLineItemDetails" tabindex="0" role="tooltip" href="javascript:void(0)" id="deductedTaxesList" data-bs-trigger="focus" data-bs-toggle="popover" data-bs-html="true" data-bs-title="{vtranslate('LBL_DEDUCTED_TAXES',$MODULE_NAME)}" data-bs-placement="left" data-bs-content="{$DEDUCTED_TAXES_INFO}">
                                    <span class="me-2">(-)</span>
                                    <strong>{vtranslate('LBL_DEDUCTED_TAXES',$MODULE_NAME)}</strong>
                                </a>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                                {$FINAL_DETAILS['deductTaxesTotalAmount']}
                            </div>
                        </td>
                    </tr>
                    <tr class="border-bottom">
                        <td>
                            <div class="text-end">
                                <strong>{vtranslate('LBL_ADJUSTMENT',$MODULE_NAME)}</strong>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                                {$FINAL_DETAILS["adjustment"]}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="text-end">
                                <strong>{vtranslate('LBL_GRAND_TOTAL',$MODULE_NAME)}</strong>
                            </div>
                        </td>
                        <td>
                            <div class="text-end">
                                {$FINAL_DETAILS["grandTotal"]}
                            </div>
                        </td>
                    </tr>
                    {if $MODULE_NAME eq 'Invoice' or $MODULE_NAME eq 'PurchaseOrder'}
                        <tr class="border-top">
                            <td>
                                {if $MODULE_NAME eq 'Invoice'}
                                    <div class="text-end">
                                        <strong>{vtranslate('LBL_RECEIVED',$MODULE_NAME)}</strong>
                                    </div>
                                {else}
                                    <div class="text-end">
                                        <strong>{vtranslate('LBL_PAID',$MODULE_NAME)}</strong>
                                    </div>
                                {/if}
                            </td>
                            <td>
                                {if $MODULE_NAME eq 'Invoice'}
                                    <div class="text-end">
                                        {if $RECORD->getDisplayValue('received')}
                                            {$RECORD->getDisplayValue('received')}
                                        {else}
                                            0
                                        {/if}
                                    </div>
                                {else}
                                    <div class="text-end">
                                        {if $RECORD->getDisplayValue('paid')}
                                            {$RECORD->getDisplayValue('paid')}
                                        {else}
                                            0
                                        {/if}
                                    </div>
                                {/if}
                            </td>
                        </tr>
                        <tr class="border-top">
                            <td>
                                <div class="text-end">
                                    <strong>{vtranslate('LBL_BALANCE',$MODULE_NAME)}</strong>
                                </div>
                            </td>
                            <td>
                                <div class="text-end">
                                    {if $RECORD->getDisplayValue('balance')}
                                        <span>{$RECORD->getDisplayValue('balance')}</span>
                                    {else}
                                        <span>0</span>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                    {/if}
                </table>
            </div>
        </div>
    </div>
{/strip}