{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/InventoryItem/views/ConversionModal.php *}
{strip}
    {foreach key=index item=jsModel from=$SCRIPTS}
        <script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
    {/foreach}

    <div class="modal-dialog modal-xl inventoryItemConversionModal">
        <div class="modal-content">
            <form id="inventoryItemConversionForm" class="form-horizontal recordEditView" name="inventoryItemConversion" method="post" action="javascript:void(0);" data-fields-info="{Vtiger_Util_Helper::toSafeHTML($FIELDS_INFO)}">
                <input type="hidden" name="module" value="InventoryItem"/>
                <input type="hidden" name="action" value="ConversionSave"/>
                <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
                <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
                <input type="hidden" name="targetModule" value="{$TARGET_MODULE}"/>
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_CONVERSION_TITLE', $MODULE)}
                <div class="modal-body">
                    {if !empty($HEADER_FIELD_MODELS)}
                        <div class="massEditTable container-fluid conversionHeader mb-3">
                            <div class="row">
                                {foreach key=FIELD_NAME item=FIELD_MODEL from=$HEADER_FIELD_MODELS}
                                    <div class="py-2 {if $FIELD_MODEL->isTableFullWidth()}col-lg-12{else}col-lg-6{/if}">
                                        <div class="row">
                                            <div class="fieldLabel col-lg-12 pb-2 text-secondary">
                                                <span>{vtranslate($FIELD_MODEL->get('label'), $TARGET_MODULE)}</span>
                                                {if $FIELD_MODEL->isMandatory() eq true}
                                                    <span class="text-danger ms-2">*</span>
                                                {/if}
                                            </div>
                                            <div class="fieldValue col-lg-12">
                                                {* Only the address field is shown, without the selector of the address to copy from. *}
                                                {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $TARGET_MODULE) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$TARGET_MODULE MODULE_NAME=$TARGET_MODULE RECORD='' HIDE_ADDRESS_SELECT=true}
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    {/if}
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless table-hover align-middle conversionItemsTable">
                            <thead>
                                {* Bootstrap paints the header cells with the table background, so they are made transparent to show the row background as in the list view. *}
                                <tr class="bg-body-secondary text-secondary border-transparent">
                                    <th class="bg-transparent ps-3">{vtranslate('LBL_ITEM_NAME', $MODULE)}</th>
                                    <th class="bg-transparent text-end">{vtranslate('Quantity', $MODULE)}</th>
                                    <th class="bg-transparent"></th>
                                    <th class="bg-transparent text-end">{vtranslate('Price', $MODULE)}</th>
                                    <th class="bg-transparent text-end">{vtranslate('Discount', $MODULE)}</th>
                                    <th class="bg-transparent text-end">{vtranslate('Tax', $MODULE)}</th>
                                    <th class="bg-transparent text-center pe-3">{vtranslate('LBL_REMOVE', $MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=ITEM from=$INVENTORY_ITEMS}
                                    {assign var=ITEM_ID value=$ITEM.inventoryitemid}
                                    <tr class="conversionItem" data-item-id="{$ITEM_ID}">
                                        <td class="font-bold">{$ITEM.item_text}</td>
                                        <td class="text-end conversionQuantity">
                                            {if in_array('quantity', $EDITABLE_COLUMNS)}
                                                <input type="text" name="items[{$ITEM_ID}][quantity]" value="{$ITEM.quantity}"
                                                       class="inputElement form-control text-end ms-auto replaceCommaWithDot allowOnlyNumbers conversionValue"/>
                                            {else}
                                                {$ITEM.quantity}
                                            {/if}
                                        </td>
                                        <td class="text-start text-secondary conversionUnit">{$ITEM.unit_display}</td>
                                        <td class="text-end conversionPrice">
                                            {if in_array('price', $EDITABLE_COLUMNS)}
                                                <input type="text" name="items[{$ITEM_ID}][price]" value="{$ITEM.price}"
                                                       class="inputElement form-control text-end ms-auto replaceCommaWithDot allowOnlyNumbers conversionValue"/>
                                            {else}
                                                {$ITEM.price}
                                            {/if}
                                        </td>
                                        <td class="text-end">{if $ITEM.discount_type neq ''}{$ITEM.discount_display}{if $ITEM.discount_type eq 'Percentage'} %{/if}{/if}</td>
                                        <td class="text-end">{$ITEM.tax}</td>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input conversionRemove" name="items[{$ITEM_ID}][remove]" value="1"/>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="container-fluid p-0">
                        <div class="row">
                            <div class="col-auto col-lg-6">
                                <a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="ms-auto col-auto col-lg-6 text-end">
                                <button type="submit" class="btn btn-primary active conversionSave" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}
