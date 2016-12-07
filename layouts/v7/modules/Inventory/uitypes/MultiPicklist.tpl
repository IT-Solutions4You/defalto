{*<!--
/*+****************************************************************************
* The contents of this file are subject to the vtiger CRM Commercial License
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Code is vtiger.
* All Rights Reserved. Copyright (C) vtiger.
*******************************************************************************/
-->*}

{strip}
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues($REF_ENTITY_TYPE, $REFERENCE_FIELD_ID)}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if !is_array($FIELD_MODEL->get('fieldvalue'))}
		{assign var="FIELD_VALUE_LIST" value=explode(' |##| ', $FIELD_MODEL->get('fieldvalue'))}
	{/if}
        {if (!$FIELD_NAME)}
            {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
        {/if}
	{if $IS_CLONE_COPY_ROW}
		{assign var=PRODUCT_PICKLISTS value=$FIELD_MODEL->getPicklistValues('Products', $REFERENCE_FIELD_ID)}
		{assign var="PRODUCT_PICKLIST_VALUES" value=array()}
		{foreach key=PICKLIST_NAME item=PICKLIST_VALUE from=$PRODUCT_PICKLISTS}
			{append var="PRODUCT_PICKLIST_VALUES" value=$PICKLIST_VALUE index=Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}
		{/foreach}

		{assign var=SERVICE_PICKLISTS value=$FIELD_MODEL->getPicklistValues('Services', $REFERENCE_FIELD_ID)}
		{assign var="SERVICE_PICKLIST_VALUES" value=array()}
		{foreach key=PICKLIST_NAME item=PICKLIST_VALUE from=$SERVICE_PICKLISTS}
			{append var="SERVICE_PICKLIST_VALUES" value=$PICKLIST_VALUE index=Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}
		{/foreach}
	{/if}

	<input type="hidden" name="{$FIELD_NAME}" value="" data-fieldtype="multipicklist"/>
	<select class="{if !($IS_CLONE_COPY_ROW)}select2{/if}" multiple name="{$FIELD_NAME}[]"
			data-fieldinfo='{$FIELD_INFO}' data-fieldtype="multipicklist" id="{$MODULE}_{$smarty.request.view}_fieldName_{$FIELD_NAME}"
			{if $IS_CLONE_COPY_ROW} data-product-picklist-values='{Zend_Json::encode($PRODUCT_PICKLIST_VALUES)}'{/if}
			{if $IS_CLONE_COPY_ROW} data-service-picklist-values='{Zend_Json::encode($SERVICE_PICKLIST_VALUES)}'{/if}
			{if $FIELD_MODEL->isMandatory() eq true}
				data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				{if !empty($SPECIAL_VALIDATOR)}
					data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'
				{/if}
			{/if}>

		{foreach item=PICKLIST_VALUE from=$PICKLIST_VALUES}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $MODULE)}</option>
		{/foreach}
	</select>
{/strip}
