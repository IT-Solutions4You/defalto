{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var=PICKLIST_VALUES value=$FIELD_INFO['editablepicklistvalues']}
{assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']}
<select data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="picklist" class="inputElement select2 {if $OCCUPY_COMPLETE_WIDTH} row {/if}" type="picklist" name="{$FIELD_MODEL->getFieldName()}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'
	{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
	{if php7_count($FIELD_INFO['validator'])}
		data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
	{/if}
	>
	{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{assign var=CURRENT_VALUE value=trim(decode_html($FIELD_MODEL->get('fieldvalue')))}
	{assign var=CURRENT_VALUE_FOUND value=false}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
		{assign var=CLASS_NAME value="picklistColor_{$FIELD_MODEL->getFieldName()}_{$PICKLIST_NAME|replace:' ':'_'}"}
		<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if isset($PICKLIST_COLORS[$PICKLIST_NAME]) && $PICKLIST_COLORS[$PICKLIST_NAME]}class="{$CLASS_NAME}"{/if} {if $CURRENT_VALUE eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
		{if $CURRENT_VALUE eq trim($PICKLIST_NAME)}
			{assign var=CURRENT_VALUE_FOUND value=true}
		{/if}
	{/foreach}
	{if $CURRENT_VALUE_FOUND eq false && $CURRENT_VALUE neq ''}
		{assign var=ALL_PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
		{if isset($ALL_PICKLIST_VALUES[$CURRENT_VALUE])}
		<option value="{$CURRENT_VALUE}" selected>{vtranslate($CURRENT_VALUE, $MODULE)}</option>
		{/if}
	{/if}
</select>
{if $PICKLIST_COLORS}
	<style type="text/css">
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
		{assign var=CLASS_NAME value="{$FIELD_MODEL->getFieldName()}_{$PICKLIST_NAME|replace:' ':'_'}"}
		.picklistColor_{$CLASS_NAME} {
			{if isset($PICKLIST_COLORS[$PICKLIST_NAME])}
				background-color: {$PICKLIST_COLORS[$PICKLIST_NAME]} !important;
				{if $PICKLIST_COLORS[$PICKLIST_NAME] eq '#ffffff'}
				color: #000000 !important;
				{/if}
			{/if}
		}
		.picklistColor_{$CLASS_NAME}.select2-highlighted {
			white: #ffffff !important;
			background-color: #337ab7 !important;
		}
		{/foreach}
	</style>
{/if}
{/strip}
