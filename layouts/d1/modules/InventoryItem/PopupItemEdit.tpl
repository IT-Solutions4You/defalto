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
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="{vtranslate($MODULE,$MODULE)} &nbsp;&nbsp;<small>({$CURRENCY_NAME})</small>"}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv col-sm-12">
                <form id="InventoryItemPopupForm">
                    <input type="hidden" name="module" value="{$MODULE}"/>
                    <input type="hidden" name="record" value="{$RECORD}"/>
                    <input type="hidden" name="source_module" value="{$SOURCE_MODULE}"/>
                    <input type="hidden" name="source_record" value="{$SOURCE_RECORD}"/>
                    <input type="hidden" name="item_type" value="{$ITEM_TYPE}"/>

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.productid neq ''}
                        {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['productid'][1]}
                        {assign var=FIELD_NAME value=$FIELD->get('name')}
                        <div class="d-flex flex-row py-2">
                            <div class="col-lg-2">
                                <div class="row py-2 h-100">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE['productid'][0]}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-10">
                                <div class="input-group">
                                    <input type="text" id="item_text" name="item_text" value="{$data.item_text}"
                                           class="item_text form-control autoComplete" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
                                           data-rule-required=true {if !empty($data.item_text)}disabled="disabled"{/if}>
                                    <input type="hidden" id="productid" name="productid" value="{$data.productid}" class="productid"/>
                                    <input type="hidden" id="lineItemType" name="lineItemType" value="{$ITEM_TYPE}" class="lineItemType"/>
                                    {if !$data.$productDeleted}
                                        <span class="input-group-addon input-group-text cursorPointer clearLineItem" title="{vtranslate('LBL_CLEAR',$MODULE)}">
                                                    <i class="fa fa-xmark"></i>
                                                </span>
                                    {/if}
                                    <span class="input-group-text lineItemPopup cursorPointer" data-popup="{$ITEM_TYPE}Popup" title="{vtranslate($ITEM_TYPE,$MODULE)}"
                                          data-module-name="{$ITEM_TYPE}">{Vtiger_Module_Model::getModuleIconPath($ITEM_TYPE)}</span>
                                </div>
                            </div>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.description neq ''}
                        {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['description'][1]}
                        {assign var=FIELD_NAME value=$FIELD->get('name')}
                        <div class="d-flex flex-row py-2">
                            <div class="col-lg-2">
                                <div class="row py-2 h-100">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE['description'][0]}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-10 py-2">
                                <textarea id="description" name="description" class="description form-control">{$data.description}</textarea>
                            </div>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.quantity neq '' || $HARD_FORMATTED_RECORD_STRUCTURE.unit neq ''}
                        <div class="d-flex flex-row py-2">
                            {if $HARD_FORMATTED_RECORD_STRUCTURE.quantity neq ''}
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE['quantity'][0]}
                                        </div>
                                    </div>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['quantity'][1]}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-3">
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text"
                                           class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data[$FIELD_NAME]}"/>
                                </div>
                            {/if}
                            {if $HARD_FORMATTED_RECORD_STRUCTURE.unit neq ''}
                                <div class="col-lg-2"></div>
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE['unit'][0]}
                                        </div>
                                    </div>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['unit'][1]}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-3">
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}"/>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.price neq '' || $HARD_FORMATTED_RECORD_STRUCTURE.subtotal neq ''}
                        <div class="d-flex flex-row py-2">
                            {if $HARD_FORMATTED_RECORD_STRUCTURE.price neq ''}
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE['price'][0]}
                                        </div>
                                    </div>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['price'][1]}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text"
                                               class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight"
                                               value="{$data[$FIELD_NAME]}"/>
                                        <span class="input-group-addon input-group-text cursorPointer choosePriceBook" title="{vtranslate('LBL_EDIT',$MODULE)}">
                                                {Vtiger_Module_Model::getModuleIconPath('PriceBooks')}
                                            </span>
                                    </div>
                                    <input id="pricebookid" name="pricebookid" class="pricebookid" type="hidden" value="{$data.pricebookid}"/>
                                </div>
                            {/if}
                            {if $HARD_FORMATTED_RECORD_STRUCTURE.subtotal neq ''}
                                <div class="col-lg-2"></div>
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE['subtotal'][0]}
                                        </div>
                                    </div>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['subtotal'][1]}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-3">
                                    <div class="row py-2 h-100">
                                        <div class="display_subtotal textAlignRight font-bold">{$data[$FIELD_NAME]}</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 display_subtotal textAlignRight font-bold">{$data[$FIELD_NAME]}</div>
                                <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}"/>
                            {/if}
                        </div>
                    {/if}

                    {foreach from=$FORMATTED_RECORD_STRUCTURE item=INVENTORY_ROW}
                        <div class="d-flex flex-row py-2">
                            <div class="col-lg-2">
                                <div class="row py-2 h-100">
                                    <div class="fieldlabel text-truncate medium">
                                        {$INVENTORY_ROW.0}
                                    </div>
                                </div>
                            </div>
                            {if $INVENTORY_ROW.1.0 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.0}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-6">
                                    {if $FIELD_NAME eq 'discount'}
                                        <div class="d-flex flex-row">
                                            <div class="col-lg-7">
                                                <select name="discount_type" id="discount_type" class="inputElement select2 form-select discount_type">
                                                    <option value="">{vtranslate('--None--', 'InventoryItem')}</option>
                                                    <option value="Percentage"
                                                            {if $data.discount_type eq 'Percent'}selected{/if}>{vtranslate('Percentage', 'InventoryItem')}</option>
                                                    <option value="Direct"
                                                            {if $data.discount_type eq 'Amount'}selected{/if}>{vtranslate('Direct', 'InventoryItem')}</option>
                                                    <option value="Product Unit Price"
                                                            {if $data.discount_type eq 'Product Unit Price'}selected{/if}>{vtranslate('Product Unit Price', 'InventoryItem')}</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-5">
                                                <div class="input-group">
                                                    <input type="text" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value="{$data[$FIELD_NAME]}"
                                                           class="{$FIELD_NAME} inputElement form-control textAlignRight allowOnlyNumbers replaceCommaWithDot"/>
                                                    <span class="input-group-addon input-group-text discountSymbol">{$CURRENCY_SYMBOL}</span>
                                                </div>
                                                <input type="hidden" name="currency_symbol" id="currency_symbol" value="{$CURRENCY_SYMBOL}" />
                                            </div>
                                        </div>
                                    {elseif $FIELD_NAME eq 'tax'}
                                        <div id="tax_div">
                                            <select name="{$FIELD_NAME}" id="{$FIELD_NAME}" class="inputElement select2 form-select {$FIELD_NAME}">
                                                <option value="0" data-taxid="0" {if $data.discount_type eq '0'}selected{/if}>{vtranslate('No Tax', 'InventoryItem')}</option>
                                                {foreach item=taxDetails from=$data['taxes']}
                                                    <option value="{$taxDetails.percentage}" data-taxid="{$taxDetails.taxid}"
                                                            {if $data.tax eq $taxDetails.selected}selected{/if}>{$taxDetails.tax_label} ({$taxDetails.percentage}%)
                                                    </option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    {elseif $FIELD_NAME eq 'price_total'}
                                        <div class="col-lg-2">
                                            <div class="row py-2 h-100">
                                                <div class="display_{$FIELD_NAME} textAlignRight font-bold">{$data[$FIELD_NAME]}</div>
                                            </div>
                                        </div>
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control"
                                               value="{$data[$FIELD_NAME]}"/>
                                    {elseif $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'percentage'}
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text"
                                               class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data[$FIELD_NAME]}"/>
                                    {else}
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}"/>
                                    {/if}
                                </div>
                            {/if}
                            {if $INVENTORY_ROW.1.1 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.1}
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                                    </div>
                                </div>
                                <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                       value="{$data[$FIELD->get('name')]}"/>
                            {/if}
                            {if $INVENTORY_ROW.1.2 neq ''}
                                {assign var=FIELD value=$INVENTORY_ROW.1.2}
                                <div class="col-lg-2">
                                    <div class="row py-2 h-100">
                                        <div class="display_{$FIELD->get('name')} textAlignRight font-bold">{$data[$FIELD->get('name')]}</div>
                                    </div>
                                </div>
                                <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                       value="{$data[$FIELD->get('name')]}"/>
                            {/if}
                        </div>
                    {/foreach}
                </form>
            </div>
        </div>
    </div>
</div>
