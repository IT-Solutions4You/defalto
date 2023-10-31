{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="containerContactsText">
		{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
		{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
		{if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}
			<textarea rows="3" class="inputElement textAreaElement form-control col-lg-12 {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
			{$FIELD_MODEL->get('fieldvalue')}</textarea>
		{else}
			<textarea rows="3" class="inputElement form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}>
			{$FIELD_MODEL->get('fieldvalue')}</textarea>
			{if $MODULE_NAME neq 'Webforms' && $smarty.request.view neq 'Detail'}
				{if $FIELD_NAME eq "mailingstreet"}
					<div>
						<a class="cursorPointer" name="copyAddress" data-target="other">{vtranslate('LBL_COPY_OTHER_ADDRESS', $MODULE)}</a>
					</div>
				{elseif $FIELD_NAME eq "otherstreet"}
					<div>
						<a class="cursorPointer" name="copyAddress" data-target="mailing">{vtranslate('LBL_COPY_MAILING_ADDRESS', $MODULE)}</a>
					</div>
				{/if}
			{/if}
		{/if}
	</div>
{/strip}