{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Vtiger/views/TaxAjax.php *}
{strip}
	{assign var=TAX_MODEL_EXISTS value=true}
	{assign var=TAX_ID value=$TAX_RECORD_MODEL->getId()}
	{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
	{if empty($TAX_ID)}
		{assign var=TAX_MODEL_EXISTS value=false}
	{/if}
	<div class="taxModalContainer modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editTax" class="form-horizontal" method="POST">
                {if $TAX_MODEL_EXISTS}
                    {assign var=TITLE value={vtranslate('LBL_EDIT_TAX', $QUALIFIED_MODULE)}}
                {else}
                    {assign var=TITLE value={vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}}
                {/if}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
                    
                <input type="hidden" name="taxid" value="{$TAX_ID}" />
                <input type="hidden" name="type" value="{$TAX_TYPE}" />
                <div class="modal-body" id="scrollContainer">
                    <div class="container-fluid">
                        <div class="block nameBlock row my-3">
                            <div class="col-lg-3">
                                <label>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <input class="inputElement form-control" type="text" name="taxlabel" placeholder="{vtranslate('LBL_ENTER_TAX_NAME', $QUALIFIED_MODULE)}" value="{$TAX_RECORD_MODEL->getName()}" data-rule-required="true" data-prompt-position="bottomLeft" />
                            </div>
                        </div>
                            
                        <div class="block statusBlock row my-3">
                            <div class="col-lg-3">
                                <label>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" name="deleted" value="1" />
                                <label class="form-check">
                                    <input type="checkbox" name="deleted" value="0" class="taxStatus form-check-input" {if $TAX_RECORD_MODEL->isDeleted() eq 0 OR !$TAX_ID}checked{/if} />
                                    <span class="ms-2">{vtranslate('LBL_TAX_STATUS_DESC', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                        
                        {if $TAX_MODEL_EXISTS eq false}
                            <div class="block taxCalculationBlock row my-3">
                                <div class="col-lg-3">
                                    <label>{vtranslate('LBL_TAX_CALCULATION', $QUALIFIED_MODULE)}</label>
                                </div>
                                <div class="col-lg-7">
                                    <label class="span radio-group form-check" id="simple">
                                        <input type="radio" name="method" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Simple' OR !$TAX_ID}checked{/if} value="Simple" />
                                        <span class="radio-label ms-2">{vtranslate('LBL_SIMPLE', $QUALIFIED_MODULE)}</span>
                                    </label>
                                    <label class="span radio-group form-check" id="compound">
                                        <input type="radio" name="method" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Compound'}checked{/if} value="Compound" />
                                        <span class="radio-label ms-2">{vtranslate('LBL_COMPOUND', $QUALIFIED_MODULE)}</span>
                                    </label>
                                    {if $TAX_TYPE neq 1}
                                        <label class="span radio-group form-check" id="deducted">
                                            <input type="radio" name="method" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Deducted'}checked{/if} value="Deducted" />
                                            <span class="radio-label ms-2">{vtranslate('LBL_DEDUCTED', $QUALIFIED_MODULE)}</span>
                                        </label>
                                    {/if}
                                </div>
                            </div>
                        {else}
                            <input type="hidden" name="method" value="{$TAX_RECORD_MODEL->getTaxMethod()}" />
                        {/if}
                        
                        <div class="block compoundOnContainer row my-3 {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Compound'}hide{/if}">
                            <div class="col-lg-3">
                                <label>{vtranslate('LBL_COMPOUND_ON', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                            </div>
                            <div class="col-lg-5">
                                <div class="">
                                    {assign var=SELECTED_SIMPLE_TAXES value=$TAX_RECORD_MODEL->getTaxesOnCompound()}
                                    <select data-placeholder="{vtranslate('LBL_SELECT_SIMPLE_TAXES', $QUALIFIED_MODULE)}" id="compoundOn" class="select2 inputEle" multiple="" name="compoundon" data-rule-required="true">
                                        {foreach key=SIMPLE_TAX_ID item=SIMPLE_TAX_MODEL from=$SIMPLE_TAX_MODELS_LIST}
                                            <option value="{$SIMPLE_TAX_ID}" {if !empty($SELECTED_SIMPLE_TAXES) && in_array($SIMPLE_TAX_ID, $SELECTED_SIMPLE_TAXES)}selected=""{/if}>{$SIMPLE_TAX_MODEL->getName()} ({$SIMPLE_TAX_MODEL->getTax()}%)</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>
                                    
                        <div class="block taxTypeContainer row my-3 {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Deducted'}hide{/if}">
                            <div class="col-lg-3">
                                <label>{vtranslate('LBL_TAX_TYPE', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <label class="span radio-group form-check" id="fixed">
                                    <input type="radio" name="taxType" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxType() eq 'Fixed' OR !$TAX_ID}checked{/if} value="Fixed" />
                                    <span class="radio-label">{vtranslate('LBL_FIXED', $QUALIFIED_MODULE)}</span>
                                </label>
                                <label class="span radio-group form-check" id="variable">
                                    <input type="radio" name="taxType" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxType() eq 'Variable'}checked{/if} value="Variable" />
                                    <span class="radio-label">{vtranslate('LBL_VARIABLE', $QUALIFIED_MODULE)}</span>
                                </label>
                            </div>
                        </div>
                            
                        <div class="block taxValueContainer row my-3 {if $TAX_RECORD_MODEL->getTaxType() eq 'Variable'}hide{/if}">
                            <div class="col-lg-3">
                                <label>
                                    <span>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="col-lg-5">
                                <div class="input-group">
                                    <span class="input-group-addon input-group-text">%</span>
                                    <input class="inputElement form-control" type="text" name="percentage" placeholder="{vtranslate('LBL_ENTER_TAX_VALUE', $QUALIFIED_MODULE)}" value="{$TAX_RECORD_MODEL->getTax()}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                </div>
                            </div>
                        </div>
                                
                        <div class="control-group dedcutedTaxDesc {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Deducted'}hide{/if}">
                            <div class="text-center">
                                <i class="fa fa-info-circle"></i>
                                <span class="ms-2">{vtranslate('LBL_DEDUCTED_TAX_DISC', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                        
                        <div class="block regionsContainer {if $TAX_RECORD_MODEL->getTaxType() neq 'Variable'}hide{/if}">
                            <table class="table table-bordered regionsTable">
                                <tr>
                                    <th class="{$WIDTHTYPE}" style="width:70%;"><strong>{vtranslate('LBL_REGIONS', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="{$WIDTHTYPE}" style="text-align: center; width:30%;"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}&nbsp;(%)</strong></th>
                                </tr>
                                <tr>
                                    <td class="{$WIDTHTYPE}">
                                        <label>{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></label>
                                    </td>
                                    <td class="{$WIDTHTYPE}" style="text-align: center;">
                                        <input class="inputElement smallInputBox input-medium form-control" type="text" name="defaultPercentage" value="{$TAX_RECORD_MODEL->getTax()}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                    </td>
                                </tr>
                                {assign var=i value=0}
                                {foreach item=REGIONS_INFO name=i from=$TAX_RECORD_MODEL->getRegionTaxes()}
                                    <tr>
                                        <td class="regionsList {$WIDTHTYPE}">
                                            <span class="deleteRow btn-close me-2"></span>
                                            <span class="col-8 d-inline-block">
                                                <select id="{$i}" data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" name="regions[{$i}][list]" class="regions select2 inputElement" multiple="" data-rule-required="true" style="width: 90%;">
                                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                        {assign var=TAX_REGION_ID value=$TAX_REGION_MODEL->getId()}
                                                        <option value="{$TAX_REGION_ID}" {if in_array($TAX_REGION_ID, $REGIONS_INFO['list'])}selected{/if}>{$TAX_REGION_MODEL->getName()}</option>
                                                    {/foreach}
                                                </select>
                                            </span>
                                        </td>
                                        <td class="{$WIDTHTYPE}" style="text-align: center;">
                                            <input class="inputElement form-control" type="text" name="regions[{$i}][value]" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                        </td>
                                    </tr>
                                    {assign var=i value=$i+1}
                                {/foreach}
                                <input type="hidden" class="regionsCount" value="{$i}" />
                            </table>

                            <span class="addNewTaxBracket">
                                <a href="#" class="btn btn-outline-secondary">{vtranslate('LBL_ADD_TAX_BRACKET', $QUALIFIED_MODULE)}</a>
                                <select class="taxRegionElements hide">
                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                        <option value="{$TAX_REGION_MODEL->getId()}">{$TAX_REGION_MODEL->getName()}</option>
                                    {/foreach}
                                </select>
                            </span>
                                <br>
                                <br>
                            <div>
                                <i class="fa fa-info-circle"></i>
                                <span class="ms-2">{vtranslate('LBL_TAX_BRACKETS_DESC', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}
