{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=TAX_MODEL_EXISTS value=true}
    {assign var=TAX_ID value=$TAX_RECORD_MODEL->getId()}
    {if empty($TAX_ID)}
        {assign var=TAX_MODEL_EXISTS value=false}
    {/if}
    <div class="taxesModalContainer modal-dialog modal-xl">
        <div class="modal-content">
            <form id="taxesEdit" class="form-horizontal" method="POST">
                {if $TAX_MODEL_EXISTS}
                    {assign var=TITLE value=vtranslate('LBL_EDIT_TAX', $QUALIFIED_MODULE)}
                {else}
                    {assign var=TITLE value=vtranslate('LBL_ADD_NEW_TAX', $QUALIFIED_MODULE)}
                {/if}
                {include file='ModalHeader.tpl'|vtemplate_path:$MODULE TITLE=$TITLE}
                <input type="hidden" name="record" value="{$TAX_ID}" />
                <input type="hidden" name="module" value="Core" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="action" value="Taxes" />
                <input type="hidden" name="mode" value="save" />
                <div class="modal-body" id="scrollContainer">
                    <div class="container-fluid">
                        <div class="block nameBlock row my-3">
                            <div class="col-lg-3">
                                <label>
                                    <span>{vtranslate('LBL_TAX_NAME', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="col-lg-5">
                                <input class="inputElement form-control" type="text" name="tax_label" placeholder="{vtranslate('LBL_ENTER_TAX_NAME', $QUALIFIED_MODULE)}" value="{$TAX_RECORD_MODEL->getLabel()}" data-rule-required="true" data-prompt-position="bottomLeft" />
                            </div>
                        </div>

                        <div class="block statusBlock row my-3">
                            <div class="col-lg-3">
                                <label>{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</label>
                            </div>
                            <div class="col-lg-7">
                                <input type="hidden" name="active" value="0" />
                                <label class="form-check">
                                    <input type="checkbox" name="active" value="1" class="taxStatus form-check-input" {if $TAX_RECORD_MODEL->isActive() OR !$TAX_ID}checked{/if} />
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
                                    <label class="span radio-group form-check" id="deducted">
                                        <input type="radio" name="method" class="input-medium form-check-input" {if $TAX_RECORD_MODEL->getTaxMethod() eq 'Deducted'}checked{/if} value="Deducted" />
                                        <span class="radio-label ms-2">{vtranslate('LBL_DEDUCTED', $QUALIFIED_MODULE)}</span>
                                    </label>
                                </div>
                            </div>
                        {else}
                            <input type="hidden" name="method" value="{$TAX_RECORD_MODEL->getTaxMethod()}" />
                        {/if}

                        <div class="block compoundOnContainer row my-3 {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Compound'}hide{/if}">
                            <div class="col-lg-3">
                                <label>
                                    <span>{vtranslate('LBL_COMPOUND_ON', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                            </div>
                            <div class="col-lg-5">
                                <div class="">
                                    {assign var=SELECTED_SIMPLE_TAXES value=$TAX_RECORD_MODEL->getTaxesOnCompound()}
                                    <select data-placeholder="{vtranslate('LBL_SELECT_SIMPLE_TAXES', $QUALIFIED_MODULE)}" id="compoundOn" class="select2 form-select inputElement" multiple="" name="compound_on" data-rule-required="true">
                                        {foreach item=SIMPLE_TAX_MODEL from=$SIMPLE_TAX_MODELS_LIST}
                                            <option value="{$SIMPLE_TAX_MODEL->getId()}" {if $TAX_RECORD_MODEL->isSelectedCompoundOn($SIMPLE_TAX_MODEL->getId())}selected=""{/if}>{$SIMPLE_TAX_MODEL->getName()} ({$SIMPLE_TAX_MODEL->getTax()}%)</option>
                                        {/foreach}
                                    </select>
                                </div>
                            </div>
                        </div>


                        <div class="block deductedContainer row my-3 {if $TAX_RECORD_MODEL->getTaxMethod() neq 'Deducted'}hide{/if}">
                            <div class="col-lg-3"></div>
                            <div class="col-lg-9">
                                <i class="fa fa-info-circle"></i>
                                <span class="ms-2">{vtranslate('LBL_DEDUCTED_TAX_DISC', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>

                        <div class="block regionsContainer">
                            <table class="table table-bordered regionsTable">
                                <tr>
                                    <th class="" style="width:70%;"><strong>{vtranslate('LBL_REGIONS', $QUALIFIED_MODULE)}</strong></th>
                                    <th class="" style="text-align: center; width:30%;"><strong>{vtranslate('LBL_TAX_VALUE', $QUALIFIED_MODULE)}&nbsp;(%)</strong></th>
                                </tr>
                                <tr>
                                    <td>
                                        <label>
                                            <span>{vtranslate('LBL_DEFAULT_VALUE', $QUALIFIED_MODULE)}</span>
                                            <span class="text-danger ms-2">*</span>
                                        </label>
                                    </td>
                                    <td>
                                        <input class="inputElement smallInputBox input-medium form-control" type="text" name="percentage" value="{$TAX_RECORD_MODEL->getTax()}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                    </td>
                                </tr>
                                <tr class="regionsContainerClone hide">
                                    <td class="regionsList">
                                        <div class="input-group">
                                            <span class="deleteRegionsTax btn btn-outline-secondary">
                                                <i class="fa-solid fa-xmark"></i>
                                            </span>
                                            <select data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" class="regions form-select inputElement" data-rule-required="true">
                                                {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                    <option value="{$TAX_REGION_MODEL->getId()}">{$TAX_REGION_MODEL->getName()}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input class="regionsPercentage inputElement form-control" type="text" value="" data-rule-required="true" data-rule-inventory_percentage="true" />
                                    </td>
                                </tr>
                                {assign var=REGIONS_KEY value=1}
                                {foreach item=REGIONS_INFO key=REGIONS_KEY from=$TAX_RECORD_MODEL->getRegionsInfo()}
                                    <tr>
                                        <td class="regionsList">
                                            <div class="input-group">
                                                <span class="deleteRegionsTax btn btn-outline-secondary">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </span>
                                                <select name="regions[{$REGIONS_KEY}][region_id]" data-placeholder="{vtranslate('LBL_SELECT_REGIONS', $QUALIFIED_MODULE)}" class="regions form-select inputElement" data-rule-required="true">
                                                    {foreach item=TAX_REGION_MODEL from=$TAX_REGIONS}
                                                        <option value="{$TAX_REGION_MODEL->getId()}" {if $TAX_REGION_MODEL->isSelectedRegion($REGIONS_INFO['region_id'])}selected{/if}>{$TAX_REGION_MODEL->getName()}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="regions[{$REGIONS_KEY}][value]" class="regionsPercentage inputElement form-control" type="text" value="{$REGIONS_INFO['value']}" data-rule-required="true" data-rule-inventory_percentage="true" />
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                            <span class="btn btn-outline-secondary addRegionsTax">
                                {assign var=REGIONS_KEY value=($REGIONS_KEY + 1)}
                                <input type="hidden" value="{$REGIONS_KEY}" class="regionsKey">
                                {vtranslate('LBL_ADD_REGIONS_TAX', $QUALIFIED_MODULE)}
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
                {include file='ModalFooter.tpl'|vtemplate_path:'Vtiger'}
            </form>
        </div>
    </div>
{/strip}