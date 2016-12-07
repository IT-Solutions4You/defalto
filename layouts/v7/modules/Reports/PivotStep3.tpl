{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <form class="form-horizontal recordEditView" id="report_step3" method="post" action="index.php">
        <input type="hidden" name="module" value="{$MODULE}" />
        <input type="hidden" name="action" value="PivotSave" />
        <input type="hidden" name="record" value="{$RECORD_ID}" />
        <input type="hidden" name="reportname" value="{Vtiger_Util_Helper::toSafeHTML($REPORT_MODEL->get('reportname'))}" />
        {if $REPORT_MODEL->get('members')}
            <input type="hidden" name="members" value={ZEND_JSON::encode($REPORT_MODEL->get('members'))} />
        {/if}
        <input type="hidden" name="folderid" value="{$REPORT_MODEL->get('folderid')}" />
        <input type="hidden" name="description" value="{Vtiger_Util_Helper::toSafeHTML($REPORT_MODEL->get('description'))}" />
        <input type="hidden" name="primary_module" value="{$PRIMARY_MODULE}" />
        <input type="hidden" name="secondary_modules" value={ZEND_JSON::encode($SECONDARY_MODULES)} />
        <input type="hidden" name="advanced_filter" value="" />
        <input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
        <input type="hidden" name='hdnrowfields' value={Zend_JSON::encode($SELECTED_ROW_FIELDS)} />
        <input type="hidden" name='hdncolumnfields' value={Zend_JSON::encode($SELECTED_COLUMN_FIELDS)} />
        <input type="hidden" name='hdndatafields' value={Zend_JSON::encode($SELECTED_DATA_FIELDS)} />
        <input type="hidden" class="step" value="3" />
        <input type="hidden" name="reporttype" value="pivot" />
        <input type="hidden" name="rowfields" />
        <input type="hidden" name="columnfields" />
        <input type="hidden" name="datafields" />

        <input type="hidden" name="enable_schedule" value="{$REPORT_MODEL->get('enable_schedule')}">
        <input type="hidden" name="schtime" value="{$REPORT_MODEL->get('schtime')}">
        <input type="hidden" name="schdate" value="{$REPORT_MODEL->get('schdate')}">
        <input type="hidden" name="schdayoftheweek" value={ZEND_JSON::encode($REPORT_MODEL->get('schdayoftheweek'))}>
        <input type="hidden" name="schdayofthemonth" value={ZEND_JSON::encode($REPORT_MODEL->get('schdayofthemonth'))}>
        <input type="hidden" name="schannualdates" value={ZEND_JSON::encode($REPORT_MODEL->get('schannualdates'))}>
        <input type="hidden" name="recipients" value={ZEND_JSON::encode($REPORT_MODEL->get('recipients'))}>
        <input type="hidden" name="specificemails" value={ZEND_JSON::encode($REPORT_MODEL->get('specificemails'))}>
        <input type="hidden" name="schtypeid" value="{$REPORT_MODEL->get('schtypeid')}">
        <input type="hidden" name="fileformat" value="{$REPORT_MODEL->get('fileformat')}">

        <div class="" style="border:1px solid #ccc;padding:4%;">
            <div class="block">
                <h4><strong>{vtranslate('LBL_SELECT_PIVOT_FIELDS',$MODULE)}</strong></h4><br>
                <div class="row">
                    <span class="col-lg-4">
                        <strong class="marginBottom10px">{vtranslate('LBL_SELECT_ROWS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <br>
                        <select data-placeholder="{vtranslate('LBL_ADD_ROWS',$MODULE)}" id="pivot_rowfields" class="select2 col-lg-8 rows"></select>
                    </span>
                    <span class="col-lg-4">
                        <strong class="marginBottom10px">{vtranslate('LBL_SELECT_COLUMNS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <br>
                        <select data-placeholder="{vtranslate('LBL_ADD_COLUMNS',$MODULE)}" id="pivot_columnfields" class="select2 col-lg-8 rows"></select>
                    </span>
                    <span class="col-lg-4">
                        <strong class="marginBottom10px">{vtranslate('LBL_SELECT_DATA_FIELDS',$MODULE)}</strong> ({vtranslate('LBL_MAX',$MODULE)} 3)<br>
                        <br>
                        <select data-placeholder="{vtranslate('LBL_ADD_DATA_FIELDS',$MODULE)}" id="pivot_datafields" class="select2 col-lg-8 rows"></select>
                    </span>
                </div>
                <div class ='hide'>
                    <select id="pivotfields">
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
                        <select id="datafields_element">
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
                <br><br>
                <div class='row col-lg-12'>
                    <span class="alert alert-danger col-lg-11 hide" id="warning1"><i class="fa fa-exclamation-triangle"></i>&nbsp;{vtranslate('LBL_SELECT_PIVOT_FIELDS_WARNING', $MODULE)}</span>
                </div>
                <br><br>
                <div class=''>
                    <span><h4><strong>{vtranslate('LBL_PIVOT_PREVIEW_EX', $MODULE)}</strong></h4></span>
                    <span class="padding20px" name="pivotPreview"><img src="layouts/v7/skins/images/pivotpreview.png" data-image-id="{$IMAGE_INFO.id}"></span>
                </div>
            </div>
        </div>
        <br>
        <div class="marginLeftZero modal-overlay-footer clearfix">
            <div class="row clearfix">
                <div class="textAlignCenter col-lg-12 col-md-12 col-sm-12 ">
                    <button type="button" class="btn btn-danger backStep"><strong>{vtranslate('LBL_BACK',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-success" id="generateReport"><strong>{vtranslate('LBL_GENERATE_REPORT',$MODULE)}</strong></button>&nbsp;&nbsp;
                    <a class="cancelLink" onclick="window.history.back()">{vtranslate('LBL_CANCEL',$MODULE)}</a>
                </div>
            </div>
        </div>
    </form>
</div>
{/strip}

