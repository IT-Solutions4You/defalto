{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
	
	<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="password" class="inputElement form-control {if $FIELD_MODEL->isNameField()}nameField{/if}"
		name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_MODEL->get('fieldvalue')}"
        {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} 
        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
        {if php7_count($FIELD_INFO['validator'])} 
            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
        {/if}
        />
{/strip}