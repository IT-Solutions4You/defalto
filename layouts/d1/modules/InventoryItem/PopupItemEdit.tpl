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
                            {*<div class="col-lg-2">
                                <div class="row py-2 h-100">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE['productid'][0]}
                                    </div>
                                </div>
                            </div>*}
                            <div class="col-lg-12">
                                <div class="input-group">
                                    <input type="text" id="item_text" name="item_text" value="{$data.item_text}"
                                           class="item_text form-control autoComplete" placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)} {vtranslate({$ITEM_TYPE},$ITEM_TYPE)}"
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
                            {*<div class="col-lg-2">
                                <div class="row py-2 h-100">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE['description'][0]}
                                    </div>
                                </div>
                            </div>*}
                            <div class="col-lg-12 py-2">
                                <textarea id="description" name="description" class="description form-control">{$data.description}</textarea>
                            </div>
                        </div>
                    {/if}

                    {if $FORMATTED_RECORD_STRUCTURE neq ''}
                        {foreach from=$FORMATTED_RECORD_STRUCTURE item=FIELD_DATA key=FIELD_NAME name=formatted_structure_loop}
                            {if $smarty.foreach.formatted_structure_loop.iteration % 2 == 1}
                                <div class="d-flex flex-row py-2">
                            {/if}
                            <div class="col-lg-2">
                                <div class="py-2 h-100 paddingLeft5px">
                                    <div class="fieldlabel text-truncate medium">
                                        {$FIELD_DATA.0}
                                    </div>
                                </div>
                            </div>
                            {assign var=FIELD value=$FIELD_DATA.1}
                            <div class="col-lg-3">
                                {if $FIELD->getFieldDataType() eq 'integer' or $FIELD->getFieldDataType() eq 'double' or $FIELD->getFieldDataType() eq 'currency' or $FIELD->getFieldDataType() eq 'percentage'}
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text"
                                           class="{$FIELD_NAME} inputElement form-control replaceCommaWithDot allowOnlyNumbers textAlignRight" value="{$data[$FIELD_NAME]}"/>
                                {else}
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="text" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}"/>
                                {/if}
                            </div>
                            {*<div class="col-lg-1"></div>*}
                            {if $smarty.foreach.formatted_structure_loop.iteration % 2 == 0 or $smarty.foreach.formatted_structure_loop.last}
                                </div>
                            {/if}
                        {/foreach}
                    {/if}

                    <div class="d-flex flex-row py-2">
                        {if $HARD_FORMATTED_RECORD_STRUCTURE.unit neq ''}
                            <div class="col-lg-2">
                                <div class="py-2 h-100 paddingLeft5px">
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
                        {else}
                            <div class="col-lg-5">
                            </div>
                        {/if}
                        {if $HARD_FORMATTED_RECORD_STRUCTURE.purchase_cost neq ''}
                            {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.purchase_cost.1}
                            {assign var=FIELD_NAME value=$FIELD->get('name')}
                            <div class="col-lg-2">
                                <div class="py-2 h-100 paddingLeft5px">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE.purchase_cost.0}
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <input type="text" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value="{$data[$FIELD_NAME]}"
                                           class="{$FIELD_NAME} inputElement form-control textAlignRight allowOnlyNumbers replaceCommaWithDot"/>
                                    <span class="input-group-addon input-group-text">{$CURRENCY_SYMBOL}</span>
                                </div>
                                <div class="display_{$FIELD_NAME} textAlignRight hide">{$data[$FIELD_NAME]}</div>
                            </div>
                        {else}
                            <div class="col-lg-5">
                            </div>
                        {/if}
                        <div class="col-lg-2 textAlignRight">
                            {vtranslate('Amount', 'InventoryItem')}
                        </div>
                    </div>

                    <div class="d-flex flex-row py-2">
                        {if $HARD_FORMATTED_RECORD_STRUCTURE.quantity neq ''}
                            <div class="col-lg-2">
                                <div class="py-2 h-100 paddingLeft5px">
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
                        {if $HARD_FORMATTED_RECORD_STRUCTURE.price neq ''}
                            <div class="col-lg-2">
                                <div class="py-2 h-100 paddingLeft5px">
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
                            {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE['subtotal'][1]}
                            {assign var=FIELD_NAME value=$FIELD->get('name')}
                            <div class="col-lg-2">
                                <div class="py-2 h-100">
                                    <div class="display_subtotal textAlignRight">{$data[$FIELD_NAME]}</div>
                                </div>
                            </div>
                            <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control" value="{$data[$FIELD_NAME]}"/>
                        {/if}
                    </div>

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.discount neq ''}
                        <div class="full_row">
                            <div class="d-flex flex-row py-2">
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.discount.1}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-2">
                                    <div class="py-2 h-100 paddingLeft5px">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE.discount.0}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
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
                                <div class="col-lg-2">
                                </div>
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <input type="text" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value="{$data[$FIELD_NAME]}"
                                               class="{$FIELD_NAME} inputElement form-control textAlignRight allowOnlyNumbers replaceCommaWithDot"/>
                                        <span class="input-group-addon input-group-text discountSymbol">{$CURRENCY_SYMBOL}</span>
                                    </div>
                                    <input type="hidden" name="currency_symbol" id="currency_symbol" value="{$CURRENCY_SYMBOL}"/>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.discount_amount.1}
                                <div class="col-lg-2">
                                    <div class="py-2 h-100">
                                        <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                                    </div>
                                </div>
                                <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                       value="{$data[$FIELD->get('name')]}"/>
                            </div>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.price_after_discount neq ''}
                        {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.price_after_discount.1}
                        <div class="d-flex flex-row py-2">
                            <div class="col-lg-10 textAlignRight">
                                <div class="fieldlabel text-truncate medium">
                                    {$HARD_FORMATTED_RECORD_STRUCTURE.price_after_discount.0}
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                            </div>
                            <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                   value="{$data[$FIELD->get('name')]}"/>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.overall_discount neq ''}
                        <div class="full_row">
                            <div class="d-flex flex-row py-2">
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.overall_discount.1}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-10 textAlignRight">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE.overall_discount.0} (<span class="display_overall_discount">{$data[$FIELD_NAME]}</span> %)
                                    </div>
                                    <input type="hidden" id="{$FIELD_NAME}" name="{$FIELD_NAME}" value="{$data[$FIELD_NAME]}"
                                           class="{$FIELD_NAME} inputElement form-control textAlignRight allowOnlyNumbers replaceCommaWithDot" readonly="readonly"/>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.overall_discount_amount.1}
                                <div class="col-lg-2">
                                    <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                                </div>
                                <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                       value="{$data[$FIELD->get('name')]}"/>
                            </div>
                            {if $HARD_FORMATTED_RECORD_STRUCTURE.price_after_overall_discount neq ''}
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.price_after_overall_discount.1}
                                <div class="d-flex flex-row py-2">
                                    <div class="col-lg-10 textAlignRight">
                                        <div class="fieldlabel text-truncate medium">
                                            {$HARD_FORMATTED_RECORD_STRUCTURE.price_after_overall_discount.0}
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                                    </div>
                                    <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                           value="{$data[$FIELD->get('name')]}"/>
                                </div>
                            {/if}
                        </div>
                    {/if}


                    {if $HARD_FORMATTED_RECORD_STRUCTURE.tax neq ''}
                        <div class="full_row">
                            <div class="d-flex flex-row py-2">
                                <div class="col-lg-5"></div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.tax.1}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-2">
                                    <div class="py-2 h-100 paddingLeft5px">
                                        <div class="fieldlabel text-truncate medium">
                                            {*$HARD_FORMATTED_RECORD_STRUCTURE.tax.0*}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div id="tax_div">
                                        <select name="{$FIELD_NAME}" id="{$FIELD_NAME}" class="inputElement select2 form-select {$FIELD_NAME}">
                                            <option value="0" data-taxid="0">{vtranslate('No Tax', 'InventoryItem')}</option>
                                            {foreach item=taxDetails from=$data['taxes']}
                                                <option value="{$taxDetails.percentage}" data-taxid="{$taxDetails.taxid}"
                                                        {if $data.tax eq $taxDetails.selected}selected{/if}>{$taxDetails.tax_label} ({$taxDetails.percentage}%)
                                                </option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.tax_amount.1}
                                <div class="col-lg-2">
                                    <div class="py-2 h-100">
                                        <div class="display_{$FIELD->get('name')} textAlignRight">{$data[$FIELD->get('name')]}</div>
                                    </div>
                                </div>
                                <input id="{$FIELD->get('name')}" name="{$FIELD->get('name')}" type="hidden" class="{$FIELD->get('name')} inputElement form-control"
                                       value="{$data[$FIELD->get('name')]}"/>
                            </div>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.price_total neq ''}
                        <div class="full_row">
                            <div class="d-flex flex-row py-2">
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.price_total.1}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-10 textAlignRight">
                                    <div class="fieldlabel text-truncate medium font-bold">
                                        {vtranslate('Item Total', 'InventoryItem')}
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <div class="display_{$FIELD_NAME} textAlignRight font-bold">{$data[$FIELD_NAME]}</div>
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control"
                                           value="{$data[$FIELD_NAME]}"/>
                                </div>
                            </div>
                        </div>
                    {/if}

                    {if $HARD_FORMATTED_RECORD_STRUCTURE.margin neq ''}
                        <div class="full_row">
                            <div class="d-flex flex-row py-2">
                                {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.margin.1}
                                {assign var=FIELD_NAME value=$FIELD->get('name')}
                                <div class="col-lg-10 textAlignRight">
                                    <div class="fieldlabel text-truncate medium">
                                        {$HARD_FORMATTED_RECORD_STRUCTURE.margin.0} (<span class="display_{$FIELD_NAME}"></span> %)
                                    </div>
                                    <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control"
                                           value="{$data[$FIELD_NAME]}"/>
                                </div>
                                {if $HARD_FORMATTED_RECORD_STRUCTURE.margin_amount neq ''}
                                    {assign var=FIELD value=$HARD_FORMATTED_RECORD_STRUCTURE.margin_amount.1}
                                    {assign var=FIELD_NAME value=$FIELD->get('name')}
                                    <div class="col-lg-2">
                                        <div class="display_{$FIELD_NAME} textAlignRight">{$data[$FIELD_NAME]}</div>
                                        <input id="{$FIELD_NAME}" name="{$FIELD_NAME}" type="hidden" class="{$FIELD_NAME} inputElement form-control"
                                               value="{$data[$FIELD_NAME]}"/>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>
