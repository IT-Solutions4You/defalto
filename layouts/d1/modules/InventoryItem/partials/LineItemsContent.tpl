{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var="deleted" value="deleted"|cat:$row_no}
	{assign var="purchaseCost" value="purchaseCost"|cat:$row_no}
	{assign var="margin" value="margin"|cat:$row_no}
    {assign var="hdnProductId" value="productid"|cat:$row_no}
    {assign var="item_text" value="item_text"|cat:$row_no}
    {assign var="comment" value="comment"|cat:$row_no}
    {assign var="productDescription" value="productDescription"|cat:$row_no}
    {assign var="qtyInStock" value="qtyInStock"|cat:$row_no}
    {assign var="quantity" value="quantity"|cat:$row_no}
    {assign var="listPrice" value="listPrice"|cat:$row_no}
    {assign var="productTotal" value="productTotal"|cat:$row_no}
    {assign var="subproduct_ids" value="subproduct_ids"|cat:$row_no}
    {assign var="subprod_names" value="subprod_names"|cat:$row_no}
	{assign var="subprod_qty_list" value="subprod_qty_list"|cat:$row_no}
    {assign var="entityIdentifier" value="entityType"|cat:$row_no}
    {assign var="entityType" value=$data.entityType}

    {assign var="discount_type" value="discount_type"|cat:$row_no}
    {assign var="discount_percent" value="discount_percent"|cat:$row_no}
    {assign var="checked_discount_percent" value="checked_discount_percent"|cat:$row_no}
    {assign var="style_discount_percent" value="style_discount_percent"|cat:$row_no}
    {assign var="discount_amount" value="discount_amount"|cat:$row_no}
    {assign var="checked_discount_amount" value="checked_discount_amount"|cat:$row_no}
    {assign var="style_discount_amount" value="style_discount_amount"|cat:$row_no}
    {assign var="checked_discount_zero" value="checked_discount_zero"|cat:$row_no}

    {assign var="discountTotal" value="discountTotal"|cat:$row_no}
    {assign var="totalAfterDiscount" value="totalAfterDiscount"|cat:$row_no}
    {assign var="taxTotal" value="taxTotal"|cat:$row_no}
    {assign var="netPrice" value="netPrice"|cat:$row_no}
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

	{assign var="productDeleted" value="productDeleted"|cat:$row_no}
	{assign var="productId" value=$data[$hdnProductId]}
	{assign var="listPriceValues" value=Products_Record_Model::getListPriceValues($productId)}
	{if $MODULE eq 'PurchaseOrder'}
		{assign var="listPriceValues" value=array()}
		{assign var="purchaseCost" value="{if $data.$purchaseCost && $RECORD_CURRENCY_RATE}{((float)$data.$purchaseCost) / ((float)$data.quantity * (float){$RECORD_CURRENCY_RATE})}{else}0{/if}"}
		{foreach item=currency_details from=$CURRENCIES}
			{append var='listPriceValues' value=$currency_details.conversionrate * $purchaseCost index=$currency_details.currency_id}
		{/foreach}
	{/if}

	<td class="text-center">
        <a class="btn deleteRow me-2">
            <i class="fa fa-trash" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
        </a>
		<a class="drag_drop_line_item">
            <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>
        </a>
		<input type="hidden" class="rowNumber" value="{$row_no}" />
        <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
        <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
	</td>

    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
        {if $INVENTORY_ITEM_FIELD_NAME eq 'productid'}
            <td>
                <!-- Product Re-Ordering Feature Code Addition Starts -->
                <input type="hidden" name="hidtax_row_no{$row_no}" id="hidtax_row_no{$row_no}" value="{$tax_row_no}"/>
                <!-- Product Re-Ordering Feature Code Addition ends -->
                <div class="itemNameDiv form-inline">
                    <div class="input-group">
                        <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control {if $row_no neq 0}autoComplete{/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-rule-required=true {if !empty($data.$item_text)}disabled="disabled"{/if}>
                        <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.productid}" class="productid"/>
                        <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
                        {if !$data.$productDeleted}
                            <span class="input-group-addon input-group-text cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
                        <i class="fa fa-xmark"></i>
                    </span>
                        {/if}
                        {if $row_no eq 0}
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                        {elseif $entityType eq '' and $PRODUCT_ACTIVE eq 'true'}
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                        {elseif $entityType eq '' and $SERVICE_ACTIVE eq 'true'}
                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                        {else}
                            {if ($entityType eq 'Services') and (!$data.$productDeleted)}
                                <span class="input-group-text lineItemPopup cursorPointer" data-popup="ServicesPopup" title="{vtranslate('Services',$MODULE)}" data-module-name="Services" data-field-name="serviceid">{Vtiger_Module_Model::getModuleIconPath('Services')}</span>
                            {elseif (!$data.$productDeleted)}
                                <span class="input-group-text lineItemPopup cursorPointer" data-popup="ProductsPopup" title="{vtranslate('Products',$MODULE)}" data-module-name="Products" data-field-name="productid">{Vtiger_Module_Model::getModuleIconPath('Products')}</span>
                            {/if}
                        {/if}
                    </div>
                </div>
                <input type="hidden" value="{$data.$subproduct_ids}" id="{$subproduct_ids}" name="{$subproduct_ids}" class="subProductIds" />
                <div id="{$subprod_names}" name="{$subprod_names}" class="subInformation">
            <span class="subProductsContainer">
                {foreach key=SUB_PRODUCT_ID item=SUB_PRODUCT_INFO from=$data.$subprod_qty_list}
                    <em> - {$SUB_PRODUCT_INFO.name} ({$SUB_PRODUCT_INFO.qty})
                        {if $SUB_PRODUCT_INFO.qty > getProductQtyInStock($SUB_PRODUCT_ID)}
                            &nbsp;-&nbsp;<span class="redColor">{vtranslate('LBL_STOCK_NOT_ENOUGH', $MODULE)}</span>
                        {/if}
                    </em><br>
                {/foreach}
            </span>
                </div>
                {if $data.$productDeleted}
                    <div class="row-fluid deletedItem redColor">
                        {if empty($data.$item_text)}
                            {vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$MODULE)}
                        {else}
                            {vtranslate('LBL_THIS',$MODULE)} {$entityType} {vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$MODULE)}
                        {/if}
                    </div>
                {else}
                    {if $COMMENT_EDITABLE}
                        <div class="mt-3">
                            <textarea id="{$comment}" name="{$comment}" class="lineItemCommentBox form-control">{decode_html($data.$comment)}</textarea>
                        </div>
                    {/if}
                {/if}
            </td>
        {elseif $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double'}
            <td>
                <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="text" class="{$INVENTORY_ITEM_FIELD_NAME} smallInputBox inputElement form-control replaceCommaWithDot"
                       data-rule-required=true data-rule-positive=true data-rule-greater_than_zero=true value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
            </td>
        {else}
            <td>{$data.$INVENTORY_ITEM_FIELD_NAME}</td>
        {/if}

    {/foreach}

    {foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
        {if !in_array($FIELD_NAME, $INVENTORY_ITEM_COLUMNS) and !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
            <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" type="hidden" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"></td>
        {/if}
    {/foreach}

{/strip}