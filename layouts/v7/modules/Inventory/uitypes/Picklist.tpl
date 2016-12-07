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
        {if (!$FIELD_NAME)}
            {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
        {/if}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->get('fieldvalue')}

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

	<select class="{if !($IS_CLONE_COPY_ROW)}select2{/if}{if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$FIELD_NAME}" data-fieldinfo='{$FIELD_INFO}'
			id="{$MODULE}_{$smarty.request.view}_fieldName_{$FIELD_NAME}" data-selected-value='{Zend_Json::encode($FIELD_MODEL->get('fieldvalue'))}'
			data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
			{if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
			{if $IS_CLONE_COPY_ROW} data-product-picklist-values='{Zend_Json::encode($PRODUCT_PICKLIST_VALUES)}'{/if}
			{if $IS_CLONE_COPY_ROW} data-service-picklist-values='{Zend_Json::encode($SERVICE_PICKLIST_VALUES)}'{/if}>

		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}"
				{if is_array($FIELD_VALUE)}
					{if in_array(trim($PICKLIST_NAME), $FIELD_VALUE)}
						selected
					{/if}
				{else if trim(decode_html($FIELD_VALUE)) eq trim($PICKLIST_NAME)}
					selected
				{/if}>{$PICKLIST_VALUE}</option>
		{/foreach}
	</select>
{/strip}
