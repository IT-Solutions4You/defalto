{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="row my-3">
        <div class="col-lg-2">{vtranslate('LBL_RECEPIENTS',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-6">
                    <input type="text" class="inputElement fields form-control" data-rule-required="true" name="sms_recepient" value="{$TASK_OBJECT->sms_recepient}"/>
                </div>
                <div class="col-lg-6">
                    <select class="select2 task-fields" data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}">
                        <option></option>
                        {foreach from=$RECORD_STRUCTURE_MODEL->getFieldsByType('phone') item=FIELD key=FIELD_VALUE}
                            <option value=",${$FIELD_VALUE}">({vtranslate($FIELD->getModule()->get('name'),$FIELD->getModule()->get('name'))}) {vtranslate($FIELD->get('label'),$FIELD->getModule()->get('name'))}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-lg-2">
            <span>{vtranslate('LBL_ADD_FIELDS',$QUALIFIED_MODULE)}</span>
        </div>
        <div class="col-lg-6">
            <select class="select2 task-fields form-select" data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}">
                <option></option>
                {$ALL_FIELD_OPTIONS}
            </select>
        </div>
    </div>
    <div class="row my-3">
        <div class="col-lg-2">
            <span>{vtranslate('LBL_SMS_TEXT',$QUALIFIED_MODULE)}</span>
        </div>
        <div class="col-lg-6">
            <textarea name="content" class="inputElement fields form-control" style="height: inherit;">{$TASK_OBJECT->content}</textarea>
        </div>
    </div>
{/strip}	
