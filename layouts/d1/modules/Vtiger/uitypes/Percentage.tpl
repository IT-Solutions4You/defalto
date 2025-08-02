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
	{if (!$FIELD_NAME)}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
	{/if}
	{assign var="FIELD_VALUE" value=$FIELD_MODEL->get('fieldvalue')}
	<div class="input-group inputElement">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="form-control inputElement percentageField replaceCommaWithDot" data-field-id="{$FIELD_MODEL->get('id')}" name="{$FIELD_NAME}"
			value="{if !empty($FIELD_VALUE) or $FIELD_VALUE neq NULL}{$FIELD_MODEL->getEditViewDisplayValue($FIELD_VALUE)}{/if}" {if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if} step="any"
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if php7_count($FIELD_INFO['validator'])}
				data-specific-rules="{ZEND_JSON::encode($FIELD_INFO["validator"])}"
			{/if}
			/>
		<span class="input-group-addon input-group-text">%</span>
	</div>
{/strip}
