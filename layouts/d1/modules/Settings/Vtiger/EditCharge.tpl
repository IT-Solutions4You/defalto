{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Settings/Vtiger/views/TaxAjax.php *}

{strip}
    {assign var=CHARGE_ID value=$CHARGE_MODEL->getId()}
    {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
    {assign var=CHARGE_FORMAT value=$CHARGE_MODEL->get('format')}
    {if $CHARGE_FORMAT eq 'Percent'}
        {assign var=IS_PERCENT_FORMAT value=true}
    {else}
        {assign var=IS_PERCENT_FORMAT value=false}
    {/if}
    <input type="hidden" value={$WIDTHTYPE} id="widthHeight">
    <div class="chargeModalContainer modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editCharge" class="form-horizontal">
                {if !empty($CHARGE_ID)}
                    {assign var=TITLE value={vtranslate('LBL_EDIT_CHARGE', $QUALIFIED_MODULE)}}
                {else}
                    {assign var=TITLE value={vtranslate('LBL_ADD_NEW_CHARGE', $QUALIFIED_MODULE)}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                <input type="hidden" name="chargeid" value="{$CHARGE_ID}"/>
                <div class="modal-body" id="scrollContainer">
                    <div class="container-fluid">
                        <div class="block row nameContainer my-3">
                            <div class="col-lg-3 text-end">
                                <label>
                                    <span>{vtranslate('LBL_CHARGE_NAME', $QUALIFIED_MODULE)}</span>
                                    <span class="ms-2 text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-lg-7">
                                <input class="inputElement form-control" type="text" name="name" placeholder="{vtranslate('LBL_ENTER_CHARGE_NAME', $QUALIFIED_MODULE)}" value="{$CHARGE_MODEL->getName()}" data-rule-required="true" data-prompt-position="bottomLeft"/>
                            </div>
                        </div>
                        <div class="row block formatContainer my-3">
                            <div class="col-lg-3 text-end">
                                <label>{vtranslate('LBL_CHARGE_FORMAT', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <label class="span radio-group form-check" id="flat">
                                    <input type="radio" name="format" class="input-medium form-check-input" {if !$IS_PERCENT_FORMAT OR !$CHARGE_ID}checked{/if} value="Flat"/>
                                    <span class="radio-label">{vtranslate('LBL_DIRECT_PRICE', $QUALIFIED_MODULE)}</span>
                                </label>
                                <label class="span radio-group form-check" id="percent">
                                    <input type="radio" name="format" class="input-medium form-check-input" {if $IS_PERCENT_FORMAT}checked{/if} value="Percent"/>
                                    <span class="radio-label">{vtranslate('LBL_PERCENT', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                        <div class="row block typeContainer my-3">
                            <div class="col-lg-3 text-end">
                                <label>{vtranslate('LBL_CHARGE_TYPE', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <label class="span radio-group form-check" id="fixed">
                                    <input type="radio" name="type" class="input-medium form-check-input" {if $CHARGE_MODEL->get('type') eq 'Fixed' OR !$CHARGE_ID}checked{/if} value="Fixed"/>
                                    <span class="radio-label ms-2">{vtranslate('LBL_FIXED', $QUALIFIED_MODULE)}</span>
                                </label>
                                <label class="span radio-group form-check" id="variable">
                                    <input type="radio" name="type" class="input-medium form-check-input" {if $CHARGE_MODEL->get('type') eq 'Variable'}checked{/if} value="Variable"/>
                                    <span class="radio-label ms-2">{vtranslate('LBL_VARIABLE', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                        <div class="row block chargeValueContainer my-3 {if $CHARGE_MODEL->get('type') eq 'Variable'}hide{/if}">
                            <div class="col-lg-3 text-end">
                                <label>
                                    <span>{vtranslate('LBL_CHARGE_VALUE', $QUALIFIED_MODULE)}</span>
                                    <span class="ms-2 text-danger">*</span>
                                </label>
                            </div>
                            <div class="col-lg-7">
                                <div class="input-group">
                                    {assign var=CHARGE_VALUE value="{if $CHARGE_MODEL->getValue()}{number_format({$CHARGE_MODEL->getValue()}, getCurrencyDecimalPlaces(),'.','')}{else}0{/if}"}
                                    <span class="input-group-addon input-group-text percentIcon {if !$IS_PERCENT_FORMAT}hide{/if}">%</span>
                                    <input class="inputEle form-control input-medium" type="text" name="value" placeholder="{vtranslate('LBL_ENTER_CHARGE_VALUE', $QUALIFIED_MODULE)}" value="{$CHARGE_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true" {else}data-rule-PositiveNumber="true"{/if} />
                                </div>
                            </div>
                        </div>
                        <div class="row block regionsContainer my-3 {if $CHARGE_MODEL->get('type') neq 'Variable'}hide{/if}">
                            <table class="table table-bordered regionsTable">
                                <tr>
                                    <th class="{$WIDTHTYPE}" style="width:60%;"><strong>{vtranslate('LBL_REGIONS', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="{$WIDTHTYPE}" style="text-align: center; width:40%;"><strong>{vtranslate('LBL_CHARGE_VALUE', $QUALIFIED_MODULE)}<span class="percentIcon {if !$IS_PERCENT_FORMAT}hide{/if}">&nbsp;(%)</span></strong></th>
                                </tr>
                                <tr>
                                    <td class="{$WIDTHTYPE}">
                                        <label>
                                            <span>{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}</span>
                                            <span class="ms-2 text-danger">*</span>
                                        </label>
                                    </td>
                                    <td class="{$WIDTHTYPE}" style="text-align: center;">
                                        <input class="inputElement form-control input-medium" type="text" name="defaultValue" value="{$CHARGE_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true" {else}data-rule-PositiveNumber="true"{/if} />
                                    </td>
                                </tr>
                                {assign var=i value=0}
                                {foreach item=REGIONS_INFO name=i from=$CHARGE_MODEL->getSelectedRegions()}
                                    <tr>
                                        <td class="regionsList {$WIDTHTYPE}">
                                            <span class="deleteRow btn-close me-2"></span>
                                            <span class="col-8 d-inline-block">
                                                <select id="{$i}" data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" name="regions[{$i}][list]" class="form-select regions select2 columns span3" multiple="" data-rule-required="true" style="width:90%;">'
                                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                        {assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
                                                        <option value="{$TAX_REGION_ID}" {if in_array($TAX_REGION_ID, $REGIONS_INFO['list'])}selected{/if}>{$TAX_REGION_MODEL->getName()}</option>
                                                    {/foreach}
                                                </select>
                                            </span>
                                        </td>
                                        <td class="{$WIDTHTYPE}" style="text-align: center;">
                                            {assign var=REGION_VALUE value="{if $CHARGE_MODEL->getValue()}{number_format({$REGIONS_INFO['value']}, getCurrencyDecimalPlaces(),'.','')}{else}0{/if}"}
                                            <input class="inputElement form-control valuesList input-medium" type="text" name="regions[{$i}][value]" value="{$REGION_VALUE}" data-rule-required="true" {if $IS_PERCENT_FORMAT}data-rule-inventory_percentage="true" {else}data-rule-PositiveNumber="true"{/if} />
                                        </td>
                                    </tr>
                                    {assign var=i value=$i+1}
                                {/foreach}
                                <input type="hidden" class="regionsCount" value="{$i}"/>
                            </table>
                            <span class="addNewTaxBracket">
                                <a class="btn btn-outline-secondary" href="#">{vtranslate('LBL_ADD_TAX_BRACKET', $QUALIFIED_MODULE)}</a>
                                <select class="form-select taxRegionElements hide">
                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                        <option value="{$TAX_REGION_MODEL->getId()}">{$TAX_REGION_MODEL->getName()}</option>
                                    {/foreach}
                                </select>
                            </span>
                            <div class="py-3">
                                <i class="fa fa-info-circle"></i>
                                <span class="ms-2">{vtranslate('LBL_TAX_BRACKETS_DESC', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                        <div class="row block py-3">
                            <div class="col-lg-3 text-end">
                                <label>{vtranslate('LBL_IS_TAXABLE', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" name="istaxable" value="0"/>
                                <label>
                                    <input type="checkbox" name="istaxable" value="1" class="isTaxable alignBottom form-check-input" {if $CHARGE_MODEL->get('istaxable') eq 1 OR !$CHARGE_ID} checked {/if} />
                                    <span class="ms-2">{vtranslate('LBL_ENABLE_TAXES_FOR_CHARGE', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                        <div class="row block taxContainer {if $CHARGE_MODEL->get('istaxable') neq 1 AND $CHARGE_ID}hide{/if}">
                            <div class="col-lg-3 text-end">
                                <label>
                                    <span>{vtranslate('LBL_SELECT_TAX', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="col-lg-7">
                                <div class="">
                                    <select data-placeholder="{vtranslate('LBL_SELECT_TAXES', $QUALIFIED_MODULE)}" id="selectTax" class="form-select select2 columns inputEle" multiple="" name="taxes" data-rule-required="true">
                                        {foreach key=TAX_ID item=CHARGE_TAX_MODEL from=$CHARGE_TAXES}
                                            {if $CHARGE_TAX_MODEL->isDeleted() eq false}
                                                <option value="{$TAX_ID}" {if !empty($SELECTED_TAXES) && in_array($TAX_ID, $SELECTED_TAXES)}selected=""{/if}>{$CHARGE_TAX_MODEL->getName()} ({$CHARGE_TAX_MODEL->getTax()}%)</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="">({vtranslate('LBL_SELECT_TAX_DESC', $QUALIFIED_MODULE)})</div>
                            </div>
                        </div>
                        <div class="py-3">
                            <i class="fa fa-info-circle"></i>
                            <span class="ms-2">{vtranslate('LBL_CHARGE_STORE_DISC', $QUALIFIED_MODULE)} ({Vtiger_Functions::getCurrencyName(CurrencyField::getDBCurrencyId())})</span>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}