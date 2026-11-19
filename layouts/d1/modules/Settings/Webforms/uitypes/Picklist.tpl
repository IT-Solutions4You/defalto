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
	{assign var="PICKLIST_VALUES" value=$FIELD_INFO['picklistvalues']}
	{assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']|default:[]}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<select data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="picklist" class="inputElement select2 {if $OCCUPY_COMPLETE_WIDTH} row {/if}" type="picklist" data-field-id= '{$FIELD_MODEL->get('id')}' name="{$FIELD_MODEL->getFieldName()}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if} data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'
			{if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
			{if php7_count($FIELD_INFO['validator'])}
				data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
			{/if}
			{if $FIELD_MODEL->getFieldName() eq 'targetmodule' and $MODE eq 'edit'}disabled='disabled'{/if}
			>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
			<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if !empty($PICKLIST_COLORS[$PICKLIST_NAME])}data-picklist-color="{$PICKLIST_COLORS[$PICKLIST_NAME]|escape}"{/if} {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
		{/foreach}
	</select>
	{if $FIELD_MODEL->getFieldName() eq 'targetmodule' and $MODE eq 'edit'}
	<input type="hidden" name="targetmodule" value="{$FIELD_MODEL->get('fieldvalue')}" />
	{/if}
{/strip}
