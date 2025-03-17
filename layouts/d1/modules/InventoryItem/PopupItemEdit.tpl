{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate($MODULE,$MODULE)}}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv col-sm-12">
                <form id="InventoryItemPopupForm">
                    <input type="hidden" name="module" value="{$MODULE}" />
                    <input type="hidden" name="record" value="{$RECORD}" />
                    <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
                    <input type="hidden" name="source_record" value="{$SOURCE_RECORD}" />
                    {foreach from=$FORMATTED_RECORD_STRUCTURE item=INVENTORY_ROW}
                        <div class="col-lg-12">{$INVENTORY_ROW.0}</div>
                        <div class="d-flex flex-row">
                            {if $INVENTORY_ROW.1.0 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.0}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-8">
                                    {if $FIELD_NAME eq 'productid'}
                                        <div class="input-group">
                                            <input type="text" id="{$item_text}" name="{$item_text}" value="{$data.item_text}" class="item_text form-control {if $row_no neq 0}autoComplete{/if}" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}" data-rule-required=true {if !empty($data.$item_text)}disabled="disabled"{/if}>
                                            <input type="hidden" id="{$hdnProductId}" name="{$hdnProductId}" value="{$data.productid}" class="productid"/>
                                            <input type="hidden" id="lineItemType{$row_no}" name="lineItemType{$row_no}" value="{$entityType}" class="lineItemType"/>
                                            {if !$data.$productDeleted}
                                                <span class="input-group-addon input-group-text cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
                                                    <i class="fa fa-xmark"></i>
                                                </span>
                                            {/if}
                                            <span class="input-group-text lineItemPopup cursorPointer" data-popup="{$ITEM_TYPE}Popup" title="{vtranslate($ITEM_TYPE,$MODULE)}" data-module-name="{$ITEM_TYPE}">{Vtiger_Module_Model::getModuleIconPath($ITEM_TYPE)}</span>
                                        </div>
                                    {elseif $FIELD_NAME eq 'description'}
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}" />
                                    {elseif $FIELD_NAME eq 'discount'}
                                        <div class="input-group">
                                            <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control textAlignRight" value="{$data[$FIELD_NAME]}" readonly="readonly" />
                                            <span class="input-group-addon input-group-text cursorPointer editProductDiscount" title="{vtranslate('LBL_EDIT',$MODULE)}">
                                                <i class="fa fa-pencil"></i>
                                            </span>
                                        </div>
                                        <div class="position-relative">
                                            <div class="popover lineItemPopover border-1 bs-popover-auto fade discountSettingsDiv" id="discountSettingsDiv" role="tooltip" style="position: absolute; inset: 0px 0px auto auto; margin: 0px; opacity: 1; visibility: visible; transform: translate(-51px, -126px); display: none;" data-popper-placement="left">
                                                <h3 class="popover-header p-3 m-0 border-bottom">{vtranslate('Discount of', 'InventoryItem')} <span class="subtotal_in_discount_div">{$data.subtotal}</span></h3>
                                                <div class="popover-body popover-content">
                                                    <div class="validCheck">
                                                        <table class="table table-borderless popupTable m-0">
                                                            <tbody>
                                                            <tr>
                                                                <td class="p-3">{vtranslate('Discount type', 'InventoryItem')}</td>
                                                                <td>
                                                                    <select name="discount_type" id="discount_type" class="inputElement select2 form-select discount_type">
                                                                        <option value="Percentage" {if $data.discount_type eq 'Percent'}selected{/if}>{vtranslate('Percentage', 'InventoryItem')}</option>
                                                                        <option value="Direct" {if $data.discount_type eq 'Amount'}selected{/if}>{vtranslate('Direct', 'InventoryItem')}</option>
                                                                        <option value="Product Unit Price" {if $data.discount_type eq 'Product Unit Price'}selected{/if}>{vtranslate('Product Unit Price', 'InventoryItem')}</option>
                                                                    </select>
                                                                    <input type="hidden" name="original_discount_type" id="original_discount_type" value="{$data.discount_type}" class="original_discount_type">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="p-3">{vtranslate('Discount', 'InventoryItem')}</td>
                                                                <td>
                                                                    <input type="text" size="5" data-compound-on="" name="discount_popup" id="discount_popup" value="{$data.discount}" class="form-control discount_popup replaceCommaWithDot textAlignRight doNotRecalculateOnChange" data-rule-positive="true" data-rule-inventory_percentage="true" aria-invalid="false">
                                                                    <input type="hidden" id="original_discount" name="original_discount" value="{$data.discount}" class="original_discount">
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td class="p-3">{vtranslate('Computed value', 'InventoryItem')}</td>
                                                                <td class="text-end">
                                                                    <input type="text" size="6" name="discount_computed_value" id="discount_computed_value" style="cursor:pointer;" value="{$data.discount_amount}" readonly="readonly" class="form-control discount_computed_value textAlignRight" aria-invalid="false">
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <input id="original_{$FIELD_NAME}" name="original_{$FIELD_NAME}" type="hidden" class="original_{$FIELD_NAME}" value="{$data[$FIELD_NAME]}"/>
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
                                    {elseif $FIELD_NAME eq 'price'}
                                        <div class="input-group">
                                            <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data[$FIELD_NAME]}" />
                                            <span class="input-group-addon input-group-text cursorPointer choosePriceBook" title="{vtranslate('LBL_EDIT',$MODULE)}">
                                                {Vtiger_Module_Model::getModuleIconPath('PriceBooks')}
                                            </span>
                                        </div>
                                        <input id="pricebookid" name="pricebookid" class="pricebookid" type="hidden" value="{$data.pricebookid}" />
                                    {elseif $FIELD_NAME eq 'tax'}
                                        <select name="{$FIELD_NAME}" id="{$FIELD_NAME}" class="inputElement select2 form-select {$FIELD_NAME}">
                                            <option value="0" data-taxid="0" {if $data.discount_type eq '0'}selected{/if}>{vtranslate('No Tax', 'InventoryItem')}</option>
                                            {foreach item=taxDetails from=$data['taxes']}
                                                <option value="{$taxDetails.percentage}" data-taxid="{$taxDetails.taxid}" {if $data.tax eq $taxDetails.selected}selected{/if}>{$taxDetails.tax_label} ({$taxDetails.percentage}%)</option>
                                            {/foreach}
                                        </select>
                                    {elseif $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'percentage'}
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data[$FIELD_NAME]}" />
                                    {else}
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}" />
                                    {/if}
                                </div>
                            {/if}
                            {if $INVENTORY_ROW.1.1 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.1}
                                <div class="col-lg-2">
                                    <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="text" class="{$FIELD->get('name')} inputElement form-control" value="{$data[$FIELD->get('name')]}" />
                                </div>
                            {/if}
                            {if $INVENTORY_ROW.1.2 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.2}
                                <div class="col-lg-2">
                                    <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="text" class="{$FIELD->get('name')} inputElement form-control" value="{$data[$FIELD->get('name')]}" />
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                </form>
            </div>
        </div>
    </div>
</div>
