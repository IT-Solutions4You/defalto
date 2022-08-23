{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="modal">
		<div class="modal-header contentsBackground">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			{if $RECORD_ID}
				<h3>{vtranslate('LBL_EDIT_CONFIGURATION', $QUALIFIED_MODULE_NAME)} </h3>
			{else}
				<h3>{vtranslate('LBL_ADD_CONFIGURATION', $QUALIFIED_MODULE_NAME)} </h3>
			{/if}
		</div>
		<form class="form-horizontal" id="smsConfig" method="POST">
			<div class="modal-body configContent">
				{if $RECORD_ID}
					<input type="hidden" value="{$RECORD_ID}" name="record" id="recordId"/>
				{/if}
				{foreach item=FIELD_MODEL from=$EDITABLE_FIELDS}
					<div class="control-group">
						{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
						<span class="control-label">
							<strong>
								{vtranslate($FIELD_NAME, $QUALIFIED_MODULE_NAME)}
							</strong>
						</span>
						<div class="controls">
							{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
							{assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}
							{if $FIELD_TYPE == 'picklist'}
								<select {if $FIELD_VALUE && $FIELD_NAME eq 'providertype'} disabled="disabled" {/if} class="select2 span3 marginLeftZero providerType" name="{$FIELD_NAME}" placeholder="{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE_NAME)}">
									<option></option>
									{foreach item=PROVIDER_MODEL from=$PROVIDERS}
										{assign var=PROVIDER_NAME value=$PROVIDER_MODEL->getName()}
										<option value="{$PROVIDER_NAME}" {if $FIELD_VALUE eq $PROVIDER_NAME} selected {/if}> 
											{vtranslate($PROVIDER_NAME, $QUALIFIED_MODULE_NAME)} 
										</option> 
									{/foreach}
								</select>
								{if $FIELD_VALUE && $FIELD_NAME eq 'providertype'}<input type="hidden" name="{$FIELD_NAME}" value="{$FIELD_VALUE}" />{/if}
							{else if $FIELD_TYPE == 'radio'}
								<input type="radio" name="{$FIELD_NAME}" value='1' {if $FIELD_VALUE} checked="checked" {/if} />&nbsp;{vtranslate('LBL_YES', $QUALIFIED_MODULE_NAME)}&nbsp;&nbsp;&nbsp;
								<input type="radio" name="{$FIELD_NAME}" value='0' {if !$FIELD_VALUE} checked="checked" {/if}/>&nbsp;{vtranslate('LBL_NO', $QUALIFIED_MODULE_NAME)}
							{else if $FIELD_TYPE == 'password'}
								<input type="password" name="{$FIELD_NAME}" class="span3" data-validation-engine="validate[required]" value="{$FIELD_VALUE}" />
							{else}
								<input type="text" name="{$FIELD_NAME}" class="span3" {if $FIELD_NAME == 'username'} data-validation-engine="validate[required]" {/if} value="{$FIELD_VALUE}" />
							{/if}
						</div>
					</div>
				{/foreach}
				<div id="provider">
					{if $RECORD_MODEL->get('providertype') neq ''}
						{foreach key=PROVIDER_NAME item=PROVIDER_MODEL from=$PROVIDERS_FIELD_MODELS}
							{if $PROVIDER_NAME eq $RECORD_MODEL->get('providertype')}
								<div id="{$PROVIDER_NAME}_container" class="providerFields">
									{assign var=TEMPLATE_NAME value=Settings_SMSNotifier_ProviderField_Model::getEditFieldTemplateName($PROVIDER_NAME)}
									{include file=$TEMPLATE_NAME|@vtemplate_path:$QUALIFIED_MODULE_NAME RECORD_MODEL=$RECORD_MODEL}
								</div>
							{/if}
						{/foreach}
					{/if}
				</div>
				<div class="row-fluid">
					<span class="controls-row"></span>
					<span id='phoneFormatWarning'> 
						<i rel="popover" data-placement="right" id="phoneFormatWarningPop" class="icon-question-sign pushDown" style="padding-right : 5px; padding-left : 5px" data-original-title="{vtranslate('LBL_WARNING',$MODULE)}" data-trigger="hover" data-content="{vtranslate('LBL_PHONEFORMAT_WARNING_CONTENT',$MODULE)}"></i>
						{vtranslate('LBL_PHONE_FORMAT_WARNING', $MODULE)}
					</span>
				</div>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}
