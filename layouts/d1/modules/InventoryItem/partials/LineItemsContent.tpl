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

    <td style="width: 3%" nowrap="nowrap">
        <span class="noEditLineItem">
            <a class="btn drag_drop_line_item padding0">
                {*<img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$MODULE)}"/>*}
                <i class="fa fa-arrows-v fa-fw text-secondary" title="{vtranslate('LBL_DRAG',$MODULE)}"></i>
            </a>
            <a class="btn editRow padding0">
                <i class="fa fa-pencil fa-fw text-secondary" title="{vtranslate('LBL_EDIT',$MODULE)}"></i>
            </a>
        </span>
            <span class="editLineItem hide">
            <a class="btn saveRow padding0">
                <i class="fa fa-save fa-fw text-secondary" title="{vtranslate('LBL_SAVE',$MODULE)}"></i>
            </a>
            <a class="btn cancelEditRow padding0">
                <i class="fa fa-close fa-fw text-secondary" title="{vtranslate('LBL_CANCEL',$MODULE)}"></i>
            </a>
        </span>
        <a class="btn deleteRow padding0">
            <i class="fa fa-trash-o fa-fw text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i>
        </a>
        <input type="hidden" class="rowNumber" value="{$row_no}" />
        <input type="hidden" class="lineItemId" name="lineItemId{$row_no}" value="{$data.inventoryitemid}" />
        <input type="hidden" class="rowSequence" name="sequence{$row_no}" value="{$row_no}" />
    </td>

    {foreach item=INVENTORY_ITEM_FIELD_NAME from=$INVENTORY_ITEM_COLUMNS}
        {assign var=FIELD value=$INVENTORY_ITEM_RECORD_STRUCTURE[$INVENTORY_ITEM_FIELD_NAME]}
        {if $INVENTORY_ITEM_FIELD_NAME eq 'productid'}
            <td class="minWidth20per item_text_td" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}">
                <span class="noEditLineItem display_{$item_text} font-bold">{$data.item_text}&nbsp;&nbsp;<small><a class="text-primary" href="index.php?module={$data.entityType}&view=Detail&record={$data.productid}" target="_blank"><i class="fa fa-external-link text-secondary" title="{vtranslate('LBL_DELETE',$MODULE)}"></i></a></small></span>
                <span class="editLineItem hide">
                    <div class="itemNameDiv form-inline">
                        <div class="input-group">
                            <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control {if $row_no neq 0}autoComplete{/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-rule-required=true {if !empty($data.$item_text)}disabled="disabled"{/if}>
                            <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.productid}" class="productid"/>
                            <input type="hidden" id="original_{$item_text}" name="original_{$item_text}" value="{$data.item_text}" class="original_item_text">
                            <input type="hidden" id="original_{$hdnProductId}" name="original_{$hdnProductId}" value="{$data.productid}" class="original_productid"/>
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
                    {if $DESCRIPTION_ALLOWED eq 'true'}
                        <div class="mt-3" style="position: relative;">
                            <textarea id="{'description'|cat:$row_no}" name="{'description'|cat:$row_no}" class="description lineItemCommentBox form-control" style="position: absolute;top: 100%;left: 0;width: 0;box-sizing: border-box;padding: 5px;resize: both;" rows="4">{decode_html($data.description)}</textarea>
                        </div>
                    {/if}
                </span>
            </td>
        {elseif in_array($INVENTORY_ITEM_FIELD_NAME, $SPECIAL_TREATMENT_FIELDS)}
        {elseif $INVENTORY_ITEM_FIELD_NAME eq 'discount_amount'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{$data.$INVENTORY_ITEM_FIELD_NAME}</span>
                <span class="editLineItem hide">
                    <div class="input-group">
                        <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="text" class="{$INVENTORY_ITEM_FIELD_NAME} smallInputBox inputElement form-control textAlignRight" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" readonly="readonly" />
                        <span class="input-group-addon input-group-text cursorPointer editProductDiscount" title="{vtranslate('LBL_EDIT',$MODULE)}">
                            <i class="fa fa-pencil"></i>
                        </span>
                    </div>
                    <div class="position-relative">
                        <div class="popover lineItemPopover border-1 bs-popover-auto fade discountSettingsDiv" id="discountSettingsDiv{$row_no}" role="tooltip" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; opacity: 1; visibility: visible; transform: translate(-51px, -126px); display: none;" data-popper-placement="left">
                            <h3 class="popover-header p-3 m-0 border-bottom">{vtranslate('Discount of', 'InventoryItem')} <span class="subtotal_in_discount_div">{$data.subtotal}</span></h3>
                            <div class="popover-body popover-content">
                                <div class="finalTaxUI validCheck" id="group_tax_div">
                                    <table class="table table-borderless popupTable m-0">
                                        <tbody>
                                        <tr>
                                            <td class="p-3">{vtranslate('Discount type', 'InventoryItem')}</td>
                                            <td>
                                                <select name="discount_type{$row_no}" id="discount_type{$row_no}" class="inputElement select2 form-select discount_type">
                                                    <option value="Percentage" {if $data.discount_type eq 'Percent'}selected{/if}>{vtranslate('Percentage', 'InventoryItem')}</option>
                                                    <option value="Direct" {if $data.discount_type eq 'Amount'}selected{/if}>{vtranslate('Direct', 'InventoryItem')}</option>
                                                    <option value="Product Unit Price" {if $data.discount_type eq 'Product Unit Price'}selected{/if}>{vtranslate('Product Unit Price', 'InventoryItem')}</option>
                                                </select>
                                                <input type="hidden" name="original_discount_type{$row_no}" id="original_discount_type{$row_no}" value="{$data.discount_type}" class="original_discount_type{$row_no}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-3">{vtranslate('Discount', 'InventoryItem')}</td>
                                            <td>
                                                <input type="text" size="5" data-compound-on="" name="discount{$row_no}" id="discount{$row_no}" value="{$data.discount}" class="form-control discount replaceCommaWithDot textAlignRight doNotRecalculateOnChange" data-rule-positive="true" data-rule-inventory_percentage="true" aria-invalid="false">
                                                <input type="hidden" id="original_discount{$row_no}" name="original_discount{$row_no}" value="{$data.discount}" class="original_discount{$row_no}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="p-3">{vtranslate('Computed value', 'InventoryItem')}</td>
                                            <td class="text-end">
                                                <input type="text" size="6" name="discount_computed_value{$row_no}" id="discount_computed_value{$row_no}" style="cursor:pointer;" value="{$data.discount_amount}" readonly="readonly" class="form-control discount_computed_value textAlignRight" aria-invalid="false">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer lineItemPopupModalFooter p-3">
                                <div class="container-fluid p-0">
                                    <div class="row">
                                        <div class="col-6 text-end">
                                            <a class="btn btn-outline-primary popoverCancel closeDiscountDiv">{vtranslate('LBL_CANCEL')}</a>
                                        </div>
                                        <div class="col-6 text-start">
                                            <a class="btn btn-primary active popoverButton applyDiscount font-bold">{vtranslate('LBL_APPLY')}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input id="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="original_{$INVENTORY_ITEM_FIELD_NAME}" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
                </span>
            </td>
        {elseif $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'percentage'}
            <td class="textAlignRight" title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no} ">{$data.$INVENTORY_ITEM_FIELD_NAME}</span>
                <span class="editLineItem hide">
                    <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="text" class="{$INVENTORY_ITEM_FIELD_NAME} smallInputBox inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" {if in_array($INVENTORY_ITEM_FIELD_NAME, $COMPUTED_FIELDS)}readonly="readonly"{/if}/>
                    <input id="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="original_{$INVENTORY_ITEM_FIELD_NAME}" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
                </span>
            </td>
        {else}
            <td title="{vtranslate({$FIELD->get('label')}, 'InventoryItem')}">
                <span class="noEditLineItem display_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}">{$data.$INVENTORY_ITEM_FIELD_NAME}</span>
                <span class="editLineItem hide">
                    <input id="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="text" class="{$INVENTORY_ITEM_FIELD_NAME} smallInputBox inputElement form-control" value="{$data.$INVENTORY_ITEM_FIELD_NAME}" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
                    <input id="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" name="original_{$INVENTORY_ITEM_FIELD_NAME|cat:$row_no}" type="hidden" class="original_{$INVENTORY_ITEM_FIELD_NAME}" value="{$data.$INVENTORY_ITEM_FIELD_NAME}"/>
                </span>
            </td>
        {/if}
    {/foreach}

    {foreach key=FIELD_NAME item=INVENTORY_ITEM_RECORD from=$INVENTORY_ITEM_RECORD_STRUCTURE}
        {if !in_array($FIELD_NAME, $INVENTORY_ITEM_COLUMNS) and !in_array($FIELD_NAME, $EXCLUDED_FIELDS)}
            <td style="display: none;"><input id="{$FIELD_NAME|cat:$row_no}" name="{$FIELD_NAME|cat:$row_no}" class="{$FIELD_NAME}" type="hidden" value="{$data.$FIELD_NAME}"></td>
        {/if}
    {/foreach}

{/strip}