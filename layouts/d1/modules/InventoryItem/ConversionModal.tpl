{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-xl inventoryItemConversionModal">
        <div class="modal-content">
            <form id="inventoryItemConversionForm" class="form-horizontal" method="post" action="javascript:void(0);">
                <input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}"/>
                <input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}"/>
                <input type="hidden" name="targetModule" value="{$TARGET_MODULE}"/>
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_CONVERSION_TITLE', $MODULE, vtranslate($TARGET_MODULE, $TARGET_MODULE))}
                <div class="modal-body">
                    <p class="text-secondary">
                        {vtranslate('LBL_CONVERSION_DESCRIPTION', $MODULE, $SOURCE_RECORD_NAME, vtranslate($SOURCE_MODULE, $SOURCE_MODULE))}
                        <span class="ms-1">({$CURRENCY_NAME} {$CURRENCY_SYMBOL})</span>
                    </p>
                    {if !empty($HEADER_FIELD_MODELS)}
                        <div class="row conversionHeader mb-3">
                            {foreach key=FIELD_NAME item=FIELD_MODEL from=$HEADER_FIELD_MODELS}
                                <div class="col-lg-6 mb-2">
                                    <div class="fieldLabel mb-1">{vtranslate($FIELD_MODEL->get('label'), $TARGET_MODULE)}</div>
                                    <div class="fieldValue">
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(), $TARGET_MODULE) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$TARGET_MODULE MODULE_NAME=$TARGET_MODULE RECORD=''}
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                    <div class="table-responsive">
                        <table class="table table-sm align-middle conversionItemsTable">
                            <thead>
                                <tr>
                                    <th>{vtranslate('LBL_ITEM_NAME', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('Quantity', $MODULE)}</th>
                                    <th>{vtranslate('Unit', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('Price', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('Discount', $MODULE)}</th>
                                    <th class="text-end">{vtranslate('Tax', $MODULE)}</th>
                                    <th class="text-center">{vtranslate('LBL_REMOVE', $MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=ITEM from=$INVENTORY_ITEMS}
                                    {assign var=ITEM_ID value=$ITEM.inventoryitemid}
                                    <tr class="conversionItem" data-item-id="{$ITEM_ID}">
                                        <td>{$ITEM.item_text}</td>
                                        <td class="text-end">
                                            {if in_array('quantity', $EDITABLE_COLUMNS)}
                                                <input type="text" name="items[{$ITEM_ID}][quantity]" value="{$ITEM.quantity}"
                                                       class="form-control form-control-sm text-end replaceCommaWithDot allowOnlyNumbers conversionValue"/>
                                            {else}
                                                {$ITEM.quantity}
                                            {/if}
                                        </td>
                                        <td>{$ITEM.unit}</td>
                                        <td class="text-end">
                                            {if in_array('price', $EDITABLE_COLUMNS)}
                                                <input type="text" name="items[{$ITEM_ID}][price]" value="{$ITEM.price}"
                                                       class="form-control form-control-sm text-end replaceCommaWithDot allowOnlyNumbers conversionValue"/>
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
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-6">
                                <a href="#" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" class="btn btn-primary active conversionContinue"><strong>{vtranslate('LBL_CONTINUE', $MODULE)}</strong></button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
{/strip}
