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
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $BLOCK_FIELDS)}
	{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
	{if (!$FIELD_NAME)}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
	{/if}
	<div class="input-group inputElement time">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" data-format="{$TIME_FORMAT}" class="timepicker-default form-control" value="{$FIELD_VALUE}" name="{$FIELD_NAME}"
		{if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
		{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
		{if php7_count($FIELD_INFO['validator'])}
			data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
		{/if} data-rule-time="true"/>
		<span class="input-group-addon input-group-text">
			<i class="fa fa-clock-o"></i>
		</span>
	</div>
{/strip}
