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
        <div class="col-lg-2">
            {vtranslate('LBL_MODULES_TO_CREATE_RECORD',$QUALIFIED_MODULE)} <span class="redColor">*</span>
        </div>
        <div class="col-lg-6">
            {assign var=RELATED_MODULES_INFO value=$WORKFLOW_MODEL->getDependentModules()}
            {assign var=RELATED_MODULES value=$RELATED_MODULES_INFO|array_keys}
            {assign var=RELATED_MODULE_MODEL_NAME value=$TASK_OBJECT->entity_type}
            <select class="select2" id="createEntityModule" name="entity_type" data-rule-required="true" style="min-width: 150px;">
                <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                {foreach from=$RELATED_MODULES item=MODULE}
                    <option {if $TASK_OBJECT->entity_type eq $MODULE} selected="" {/if} value="{$MODULE}">{vtranslate($MODULE,$MODULE)}</option>
                {/foreach}
            </select>
        </div>
    </div>
	<div id="addCreateEntityContainer">
		{include file="CreateEntity.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
	</div>
{/strip}
