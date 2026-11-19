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
	{assign var="FIELD_VALUE_LIST" value=explode(' |##| ', (string)$FIELD_MODEL->get('fieldvalue'))}
	{assign var=PICKLIST_VALUES value=$FIELD_INFO['editablepicklistvalues']}
	{assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']|default:[]}
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""  data-fieldtype="multipicklist"/>
	<select id="{$MODULE}_{$REQUEST_INSTANCE.view}_fieldName_{$FIELD_MODEL->getFieldName()}" multiple class="select2" name="{$FIELD_MODEL->getFieldName()}[]" data-fieldtype="multipicklist"
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if php7_count($FIELD_INFO['validator'])} 
				data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
			{/if}
			>
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if isset($PICKLIST_COLORS[$PICKLIST_NAME]) && $PICKLIST_COLORS[$PICKLIST_NAME]}data-picklist-color="{$PICKLIST_COLORS[$PICKLIST_NAME]|escape}"{/if} {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_VALUE, $MODULE)}</option>
		{/foreach}
	</select>
{/strip}