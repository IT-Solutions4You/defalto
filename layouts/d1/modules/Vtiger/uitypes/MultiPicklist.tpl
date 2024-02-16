{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('fieldvalue'))}
	{assign var=PICKLIST_VALUES value=$FIELD_INFO['editablepicklistvalues']}
	{assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']}
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""  data-fieldtype="multipicklist"/>
	<select id="{$MODULE}_{$REQUEST_INSTANCE.view}_fieldName_{$FIELD_MODEL->getFieldName()}" multiple class="select2" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldtype="multipicklist"
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if php7_count($FIELD_INFO['validator'])} 
				data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
			{/if}
			>
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			{assign var=CLASS_NAME value="picklistColor_{$FIELD_MODEL->getFieldName()}_{$PICKLIST_NAME|replace:' ':'_'}"}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if $PICKLIST_COLORS[$PICKLIST_NAME]}class="{$CLASS_NAME}"{/if} {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $MODULE)}</option>
		{/foreach}
	</select>
	{if $PICKLIST_COLORS}
		<style type="text/css">
			{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			{assign var=CLASS_NAME value="{$FIELD_MODEL->getFieldName()}_{$PICKLIST_NAME|replace:' ':'_'}"}
			.picklistColor_{$CLASS_NAME} {
				background-color: {$PICKLIST_COLORS[$PICKLIST_NAME]} !important;
			}
			{/foreach}
		</style>
	{/if}
{/strip}