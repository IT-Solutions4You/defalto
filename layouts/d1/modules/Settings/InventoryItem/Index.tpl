{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{assign var=ACTIVE_TAB value='fields'}
{if isset($smarty.request.mode) && ($smarty.request.mode eq 'decimals' || $smarty.request.mode eq 'conversions')}
    {assign var=ACTIVE_TAB value=$smarty.request.mode}
{/if}
<div class="listViewPageDiv detailViewContainer px-4 pb-4" id="listViewContent">
    <div class="bg-body rounded" id="InventoryItemsContainer">
        <div class="p-3 border-bottom">
            <h4 class="m-0">{vtranslate('LBL_FIELDS_IN_ITEMS_BLOCK',$QUALIFIED_MODULE)}</h4>
        </div>
        <div class="contents tabbable pt-3">
            <ul class="nav nav-tabs massEditTabs border-bottom">
                <li class="tab-item fieldsTab {if $ACTIVE_TAB eq 'fields'}active{/if} ms-3">
                    <a class="nav-link {if $ACTIVE_TAB eq 'fields'}active{/if}" data-bs-toggle="tab" href="#fields">
                        <strong>{vtranslate('Fields', $QUALIFIED_MODULE)}</strong>
                    </a>
                </li>
                <li class="tab-item decimalsTab {if $ACTIVE_TAB eq 'decimals'}active{/if} ms-3">
                    <a class="nav-link {if $ACTIVE_TAB eq 'decimals'}active{/if}" data-bs-toggle="tab" href="#decimals">
                        <strong>{vtranslate('Decimals', $QUALIFIED_MODULE)}</strong>
                    </a>
                </li>
                <li class="tab-item conversionsTab {if $ACTIVE_TAB eq 'conversions'}active{/if} ms-3">
                    <a class="nav-link {if $ACTIVE_TAB eq 'conversions'}active{/if}" data-bs-toggle="tab" href="#conversions">
                        <strong>{vtranslate('LBL_CONVERSIONS', $QUALIFIED_MODULE)}</strong>
                    </a>
                </li>
            </ul>
            <div class="tab-content layoutContent pb-3 overflowVisible">
                <div class="tab-pane {if $ACTIVE_TAB eq 'fields'}active{/if}" id="fields">
                    <form name="inventoryItemFields" id="inventoryItemFields" method="POST">
                        <input type="hidden" name="module" value="InventoryItem">
                        <input type="hidden" name="parent" value="Settings">
                        <input type="hidden" name="action" value="FieldsSave">
                    <div class="detailViewInfo container-fluid pt-3 px-3">
                        <div class="row form-group align-items-center">
                            <div class="col-sm-3 control-label fieldLabel pb-3">
                                <label class="fieldLabel ">{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="fieldValue col-sm-6 pb-3">
                                <select class="select2 inputElement" id="selectedModule" name="selectedModule">
                                    <option value="0">{vtranslate('LBL_GENERAL_SETTING',$QUALIFIED_MODULE)}</option>
                                    {foreach item=INVENTORY_MODULE_NAME key=INVENTORY_MODULE_ID from=$SUPPORTED_MODULES}
                                        <option {if $SELECTED_MODULE eq $INVENTORY_MODULE_ID} selected="" {/if} value="{$INVENTORY_MODULE_ID}">{vtranslate($INVENTORY_MODULE_NAME, $INVENTORY_MODULE_NAME)}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="row form-group align-items-center">
                            <div class="col-sm-3 control-label fieldLabel pb-3">
                                <label class="fieldLabel ">{vtranslate('LBL_SELECT_VISIBLE_FIELDS',$QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="fieldValue col-sm-6 pb-3">
                                <select class="select2 form-control" id="selectedFields" multiple name="selectedFields[]">
                                    {foreach key=FIELD_KEY item=FIELD_MODEL from=$FIELD_MODEL_LIST}
                                        <option value="{$FIELD_KEY}" data-id="{$FIELD_KEY}" {if in_array($FIELD_KEY,$SELECTED_FIELDS)} selected {/if}>
                                            {vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
                                        </option>
                                    {/foreach}
                                </select>
                                <input type="hidden" name="columnslist" value='{Vtiger_Functions::jsonEncode($SELECTED_FIELDS)}' />
                                <input id="mandatoryFieldsList" type="hidden" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($MANDATORY_FIELDS))}' />
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid py-3">
                        <div class="row">
                            <div class="col text-end"><a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
                            <div class="col"><button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
                        </div>
                    </div>
                    </form>
                </div>
                <div class="tab-pane {if $ACTIVE_TAB eq 'decimals'}active{/if}" id="decimals">
                    <form name="decimalsForm" id="decimalsForm" method="POST">
                        <input type="hidden" name="module" value="InventoryItem">
                        <input type="hidden" name="parent" value="Settings">
                        <input type="hidden" name="action" value="DecimalsSave">
                        <div class="detailViewInfo container-fluid pt-3 px-3">
                            <div class="row form-group align-items-center">
                                <div class="col-sm-3 control-label fieldLabel pb-3">
                                    <label class="fieldLabel ">{vtranslate('Number of decimal places in field "Quantity"', $QUALIFIED_MODULE)}</label>
                                </div>
                                <div class="fieldValue col-sm-6 pb-3">
                                    <select class="select2 inputElement" id="quantityDecimals" name="quantityDecimals">
                                        {for $places=0 to 4}
                                            <option value="{$places}" {if $DECIMALS.quantity eq $places}selected="selected"{/if}>{$places}</option>
                                        {/for}
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group align-items-center">
                                <div class="col-sm-3 control-label fieldLabel pb-3">
                                    <label class="fieldLabel ">{vtranslate('Number of decimal places in field "Unit Price"', $QUALIFIED_MODULE)}</label>
                                </div>
                                <div class="fieldValue col-sm-6 pb-3">
                                    <select class="select2 inputElement" id="priceDecimals" name="priceDecimals">
                                        {for $places=0 to 4}
                                            <option value="{$places}" {if $DECIMALS.price eq $places}selected="selected"{/if}>{$places}</option>
                                        {/for}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="container-fluid py-3">
                            <div class="row">
                                <div class="col text-end"><a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a></div>
                                <div class="col"><button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="tab-pane {if $ACTIVE_TAB eq 'conversions'}active{/if}" id="conversions">
                    <form name="conversionsForm" id="conversionsForm" method="POST">
                        <input type="hidden" name="module" value="InventoryItem">
                        <input type="hidden" name="parent" value="Settings">
                        <input type="hidden" name="action" value="ConversionsSave">
                        <div class="detailViewInfo container-fluid pt-3 px-3">
                            <p class="text-secondary">{vtranslate('LBL_CONVERSIONS_DESCRIPTION', $QUALIFIED_MODULE)}</p>
                            <div class="table-responsive">
                                <table class="table align-middle conversionsTable">
                                    <thead>
                                        <tr>
                                            <th>{vtranslate('LBL_CONVERSION', $QUALIFIED_MODULE)}</th>
                                            <th class="text-center">{vtranslate('LBL_CONVERSION_USE_MODAL', $QUALIFIED_MODULE)}</th>
                                            <th>{vtranslate('LBL_CONVERSION_ITEM_COLUMNS', $QUALIFIED_MODULE)}</th>
                                            <th>{vtranslate('LBL_CONVERSION_HEADER_FIELDS', $QUALIFIED_MODULE)}</th>
                                            <th class="text-center">{vtranslate('LBL_CONVERSION_MAPPING', $QUALIFIED_MODULE)}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach item=CONVERSION from=$CONVERSIONS}
                                            <tr class="conversionRow">
                                                <td>
                                                    <strong>{$CONVERSION.sourceLabel}</strong> <i class="fa fa-arrow-right mx-1 text-secondary"></i> <strong>{$CONVERSION.targetLabel}</strong>
                                                    <input type="hidden" name="conversions[{$CONVERSION.id}][id]" value="{$CONVERSION.id}">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input conversionUseModal" name="conversions[{$CONVERSION.id}][use_modal]" value="1" {if $CONVERSION.useModal}checked{/if}>
                                                </td>
                                                <td style="min-width: 200px;">
                                                    <select class="select2 form-control conversionColumns" multiple name="conversions[{$CONVERSION.id}][columns][]">
                                                        {foreach item=COLUMN_NAME from=$CONVERSION_COLUMNS}
                                                            <option value="{$COLUMN_NAME}" {if in_array($COLUMN_NAME, $CONVERSION.columns)}selected{/if}>{vtranslate($FIELD_MODEL_LIST[$COLUMN_NAME]->get('label'), 'InventoryItem')}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td style="min-width: 260px;">
                                                    <select class="select2 form-control conversionHeaderFields" multiple name="conversions[{$CONVERSION.id}][header_fields][]">
                                                        {foreach key=FIELD_NAME item=FIELD_LABEL from=$CONVERSION.headerFieldOptions}
                                                            <option value="{$FIELD_NAME}" {if in_array($FIELD_NAME, $CONVERSION.headerFields)}selected{/if}>{$FIELD_LABEL}</option>
                                                        {/foreach}
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm editConversionMapping" data-conversion-id="{$CONVERSION.id}"><i class="fa fa-pencil"></i> {vtranslate('LBL_CONVERSION_MAPPING_EDIT', $QUALIFIED_MODULE)}</button>
                                                </td>
                                            </tr>
                                        {foreachelse}
                                            <tr><td colspan="5" class="text-secondary">{vtranslate('LBL_NO_CONVERSIONS', $QUALIFIED_MODULE)}</td></tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="container-fluid py-3">
                            <div class="row">
                                <div class="col text-center"><button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>