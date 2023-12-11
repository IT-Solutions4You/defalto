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
    {assign var=WORKFLOW_MODULE value="Settings:Workflows"}
    <div class="popupUi modal-dialog modal-md hide" data-backdrop="false">
        <div class="modal-content">
            {assign var=HEADER_TITLE value={vtranslate('LBL_SET_VALUE',$WORKFLOW_MODULE)}}

            {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <select class="textType">
                            <option data-ui="textarea" value="rawtext">{vtranslate('LBL_RAW_TEXT',$WORKFLOW_MODULE)}</option>
                        </select>
                    </div>
                    <div class="col-sm-4 hide useFieldContainer">
                    <span name="{$MODULE_MODEL->get('name')}" class="useFieldElement">
                        {assign var=MODULE_FIELDS value=$MODULE_MODEL->getFields()}
                        <select class="useField" data-placeholder="{vtranslate('LBL_USE_FIELD',$WORKFLOW_MODULE)}">
                            <option></option>
                            {foreach from=$RECORD_STRUCTURE  item=FIELDS}
                                {foreach from=$FIELDS item=MODULE_FIELD}
                                    <option value="{$MODULE_FIELD->get('workflow_columnname')}">{vtranslate($MODULE_FIELD->get('workflow_columnlabel'),$MODULE_MODEL->get('name'))}</option>
                                {/foreach}
                            {/foreach}
                        </select>
                    </span>
                        {if $RELATED_MODULE_MODEL neq ''}
                            <span name="{$RELATED_MODULE_MODEL->get('name')}" class="useFieldElement">
                            {assign var=MODULE_FIELDS value=$RELATED_MODULE_MODEL->getFields()}
                            <select class="useField" data-placeholder="{vtranslate('LBL_USE_FIELD',$WORKFLOW_MODULE)}">
                                <option></option>
                                    {foreach from=$MODULE_FIELDS item=MODULE_FIELD}
                                        <option value="{$MODULE_FIELD->getName()}">{vtranslate($MODULE_FIELD->get('label'),$WORKFLOW_MODULE)}</option>
                                    {/foreach}
                            </select>
                        </span>
                        {/if}
                    </div>
                    <div class="col-sm-4 hide useFunctionContainer">
                        <select class="useFunction" data-placeholder="{vtranslate('LBL_USE_FUNCTION',$WORKFLOW_MODULE)}">
                            <option></option>
                            {foreach from=$FIELD_EXPRESSIONS key=FIELD_EXPRESSION_VALUE item=FIELD_EXPRESSIONS_KEY}
                                <option value="{$FIELD_EXPRESSIONS_KEY}">{vtranslate($FIELD_EXPRESSION_VALUE,$WORKFLOW_MODULE)}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <br>
                <div class="row fieldValueContainer">
                    <div class="col-sm-12">
                        <textarea data-textarea="true" class="fieldValue inputElement hide form-control" style="height: inherit;"></textarea>
                    </div>
                </div>
                <br>
                <div id="rawtext_help" class="alert alert-info helpmessagebox hide">
                    <p><h5>{vtranslate('LBL_RAW_TEXT',$WORKFLOW_MODULE)}</h5></p>
                    <p>2000</p>
                    <p>{vtranslate('LBL_VTIGER',$WORKFLOW_MODULE)}</p>
                </div>
                <div id="fieldname_help" class="helpmessagebox alert alert-info hide">
                    <p><h5>{vtranslate('LBL_EXAMPLE_FIELD_NAME',$WORKFLOW_MODULE)}</h5></p>
                    <p>{vtranslate('LBL_ANNUAL_REVENUE',$WORKFLOW_MODULE)}</p>
                    <p>{vtranslate('LBL_NOTIFY_OWNER',$WORKFLOW_MODULE)}</p>
                </div>
                <div id="expression_help" class="alert alert-info helpmessagebox hide">
                    <p><h5>{vtranslate('LBL_EXAMPLE_EXPRESSION',$WORKFLOW_MODULE)}</h5></p>
                    <p>{vtranslate('LBL_ANNUAL_REVENUE',$WORKFLOW_MODULE)}/12</p>
                    <p>{vtranslate('LBL_EXPRESSION_EXAMPLE2',$WORKFLOW_MODULE)}</p>
                </div>
            </div>
            {include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
    <div class="clonedPopUp"></div>
{/strip}
