{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="text" class="inputElement dateTimeField" name="{$FIELD_MODEL->getFieldName()}"
    type="text" value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}' {/if} 
    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
    {if php7_count($FIELD_INFO['validator'])} 
        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
    {/if}
    />
{/strip}