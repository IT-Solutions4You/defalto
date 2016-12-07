{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Reports/views/PivotDetail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    <input type="hidden" name='hdnrowfields' value={Zend_JSON::encode($SELECTED_ROW_FIELDS)} />
    <input type="hidden" name='hdncolumnfields' value={Zend_JSON::encode($SELECTED_COLUMN_FIELDS)} />
    <input type="hidden" name='hdndatafields' value={Zend_JSON::encode($SELECTED_DATA_FIELDS)} />
    <input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
    <input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} />
        <div class="reportsDetailHeader">
            {include file="DetailViewActions.tpl"|vtemplate_path:$MODULE}
            {if $REPORT_MODEL->isEditableBySharing()}
            <div class="contactAdd">
                <div class="filterElements well filterConditionContainer filterConditionsDiv" style="margin: 0px;">
                    <h5><strong>{vtranslate('LBL_PIVOT_FIELDS',$MODULE)}</strong></h5><br>
                    <div class="col-lg-4 marginLeftZero">
                        <strong>{vtranslate('LBL_SELECT_ROWS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <select data-placeholder="{vtranslate('LBL_ADD_ROWS',$MODULE)}" id="pivot_rowfields" name="rowfields[]" class="select2 col-lg-10 row" style="min-width: 300px;"></select>
                    </div>
                    <div class="col-lg-4 marginLeftZero">
                        <strong>{vtranslate('LBL_SELECT_COLUMNS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <select data-placeholder="{vtranslate('LBL_ADD_COLUMNS',$MODULE)}" id="pivot_columnfields" name="columnfields[]" class="select2 col-lg-10 row" style="min-width: 300px;"></select>
                    </div>
                    <div class="col-lg-4 marginLeftZero">
                        <strong>{vtranslate('LBL_SELECT_DATA_FIELDS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <select data-placeholder="{vtranslate('LBL_ADD_DATA_FIELDS',$MODULE)}" id="pivot_datafields" name="datafields[]" class="select2 col-lg-10 row" style="min-width: 300px;"></select>
                    </div><div class="clearfix">&nbsp;</div>
                </div>
                <div class ='hide'>
                    <select id="pivotfields" data-placeholder="">
                        {foreach key=PRIMARY_MODULE_NAME item=PRIMARY_MODULE from=$PRIMARY_MODULE_FIELDS}
                            {foreach key=BLOCK_LABEL item=BLOCK from=$PRIMARY_MODULE}
                                <optgroup label='{vtranslate($PRIMARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$PRIMARY_MODULE_NAME)}'>
                                    {foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
                                        {assign var=FIELD_INFO value=explode(':', $FIELD_KEY)}
                                        {if $FIELD_INFO[4] eq 'D' or $FIELD_INFO[4] eq 'DT'}
                                            <option value="{$FIELD_KEY}:Y" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}:Y">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)} ({vtranslate('LBL_YEAR', $PRIMARY_MODULE_NAME)})</option>
                                            <option value="{$FIELD_KEY}:M" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}:M">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)} ({vtranslate('LBL_MONTH', $PRIMARY_MODULE_NAME)})</option>
                                            <option value="{$FIELD_KEY}:W" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}:W">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)} ({vtranslate('LBL_WEEK', $PRIMARY_MODULE_NAME)})</option>
                                            <option value="{$FIELD_KEY}" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)}</option>
                                        {else if $FIELD_INFO[4] neq 'I' and $FIELD_INFO[4] neq 'N' and $FIELD_INFO[4] neq 'NN'}
                                            <option value="{$FIELD_KEY}" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}">{vtranslate($FIELD_LABEL, $PRIMARY_MODULE_NAME)}</option>
                                        {/if}
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        {/foreach}
                        {foreach key=SECONDARY_MODULE_NAME item=SECONDARY_MODULE from=$SECONDARY_MODULE_FIELDS}
                            {foreach key=BLOCK_LABEL item=BLOCK from=$SECONDARY_MODULE}
                                <optgroup label='{vtranslate($SECONDARY_MODULE_NAME,$MODULE)}-{vtranslate($BLOCK_LABEL,$SECONDARY_MODULE_NAME)}'>
                                    {foreach key=FIELD_KEY item=FIELD_LABEL from=$BLOCK}
                                        {assign var=FIELD_INFO value=explode(':', $FIELD_KEY)}
                                        {if $FIELD_INFO[4] eq 'D' or $FIELD_INFO[4] eq 'DT'}
                                            <option value="{$FIELD_KEY}:Y" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}:Y">{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)} ({vtranslate('LBL_YEAR', $PRIMARY_MODULE_NAME)})</option>
                                            <option value="{$FIELD_KEY}:M" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}:M">{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)} ({vtranslate('LBL_MONTH', $PRIMARY_MODULE_NAME)})</option>
                                            <option value="{$FIELD_KEY}" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}">{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
                                        {else if $FIELD_INFO[4] neq 'I' and $FIELD_INFO[4] neq 'N' and $FIELD_INFO[4] neq 'NN'}
                                            <option value="{$FIELD_KEY}" data-escaped-value="{str_replace('\\', '\\\\', $FIELD_KEY)}">{vtranslate($FIELD_LABEL, $SECONDARY_MODULE_NAME)}</option>
                                        {/if}
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        {/foreach}
                    </select>
                    <select id="datafields_element" data-placeholder="{vtranslate('LBL_SELECT_DATA_FIELDS',$MODULE)}">>
                        {foreach key=CALCULATION_FIELDS_MODULE_LABEL item=CALCULATION_FIELDS_MODULE from=$CALCULATION_FIELDS}
                            <optgroup label="{vtranslate($CALCULATION_FIELDS_MODULE_LABEL, $CALCULATION_FIELDS_MODULE_LABEL)}">
                            {foreach key=CALCULATION_FIELD_KEY item=CALCULATION_FIELD_TRANSLATED_LABEL from=$CALCULATION_FIELDS_MODULE}
                                <option value="{$CALCULATION_FIELD_KEY}">{$CALCULATION_FIELD_TRANSLATED_LABEL}</option>
                            {/foreach}
                            </optgroup>
                        {/foreach}
                        <optgroup label='{vtranslate('RECORD COUNT',$MODULE)}'>
                            <option value="record_count">{vtranslate('LBL_RECORD_COUNT', $MODULE)}</option>
                        </optgroup>
                    </select>
                </div>
                <div class='row'>
                    <span class="alert alert-danger col-lg-12 hide" id="warning1"><i class="fa fa-exclamation-triangle"></i>&nbsp;{vtranslate('LBL_SELECT_PIVOT_FIELDS_WARNING', $MODULE)}</span>
                </div>
               <br>
                <div class=''>
                    {assign var=filterConditionNotExists value=(count($SELECTED_ADVANCED_FILTER_FIELDS[1]['columns']) eq 0 and count($SELECTED_ADVANCED_FILTER_FIELDS[2]['columns']) eq 0)}
                    <button class="btn btn-default" name="modify_condition" data-val="{$filterConditionNotExists}">
                        <strong>{vtranslate('LBL_MODIFY_CONDITION', $MODULE)}</strong>&nbsp;&nbsp;
                        <i class="fa {if $filterConditionNotExists eq true} fa-chevron-right {else} fa-chevron-down {/if}"></i>
                    </button>
                </div>
                <br>
                <div id='filterContainer' class='{if $filterConditionNotExists eq true} hide {/if}'>
                    <input type="hidden" id="recordId" value="{$RECORD_ID}" />
                    {assign var=RECORD_STRUCTURE value=array()}
                    {assign var=PRIMARY_MODULE_LABEL value=vtranslate($PRIMARY_MODULE_NAME, $PRIMARY_MODULE_NAME)}
                    {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$PRIMARY_MODULE_RECORD_STRUCTURE}
                        {assign var=PRIMARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $PRIMARY_MODULE_NAME)}
                        {assign var=key value="$PRIMARY_MODULE_LABEL $PRIMARY_MODULE_BLOCK_LABEL"}
                        {if $LINEITEM_FIELD_IN_CALCULATION eq false && $BLOCK_LABEL eq 'LBL_ITEM_DETAILS'}
                            {* dont show the line item fields block when Inventory fields are selected for calculations *}
                        {else}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/if}
                    {/foreach}
                    {foreach key=MODULE_LABEL item=SECONDARY_MODULE_RECORD_STRUCTURE from=$SECONDARY_MODULE_RECORD_STRUCTURES}
                        {assign var=SECONDARY_MODULE_LABEL value=vtranslate($MODULE_LABEL, $MODULE_LABEL)}
                        {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$SECONDARY_MODULE_RECORD_STRUCTURE}
                            {assign var=SECONDARY_MODULE_BLOCK_LABEL value=vtranslate($BLOCK_LABEL, $MODULE_LABEL)}
                            {assign var=key value="$SECONDARY_MODULE_LABEL $SECONDARY_MODULE_BLOCK_LABEL"}
                            {$RECORD_STRUCTURE[$key] = $BLOCK_FIELDS}
                        {/foreach}
                    {/foreach}
                    {include file='AdvanceFilter.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE ADVANCE_CRITERIA=$SELECTED_ADVANCED_FILTER_FIELDS COLUMNNAME_API=getReportFilterColumnName}
                </div>
        <div class="row textAlignCenter hide reportActionButtons">
            <button class="btn btn-success generateReportPivot" data-mode="save" value="{vtranslate('LBL_SAVE',$MODULE)}"/>
                <strong>{vtranslate('LBL_SAVE',$MODULE)}</strong>
            </button>
        </div>
        {/if}
    </div>
<br>
<div id="reportContentsDiv" class="padding1per">
{/strip}