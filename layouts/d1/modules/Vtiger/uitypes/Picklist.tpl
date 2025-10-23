{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
{assign var=PICKLIST_VALUES value=$FIELD_INFO['editablepicklistvalues']}
{if !isset($FIELD_INFO['picklistColors'])}
	{$FIELD_INFO['picklistColors'] = ''}
{/if}
{assign var=PICKLIST_COLORS value=$FIELD_INFO['picklistColors']}
<select data-fieldname="{$FIELD_MODEL->getFieldName()}"
		data-fieldtype="picklist"
		class="inputElement select2 form-select {if $OCCUPY_COMPLETE_WIDTH}row{/if}"
		type="picklist" name="{$FIELD_MODEL->getFieldName()}"
		{if !empty($SPECIAL_VALIDATOR)}
			data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'
		{/if}
		data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'
		{if $FIELD_INFO["mandatory"] eq true}
			data-rule-required="true"
		{/if}
		{if php7_count($FIELD_INFO['validator'])}
			data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
		{/if}
		data-minimum-results-for-search="11"
>
	{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
	{assign var=CURRENT_VALUE value=php7_trim(decode_html($FIELD_MODEL->get('fieldvalue')))}
	{assign var=CURRENT_VALUE_FOUND value=false}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
		{assign var=CLASS_NAME value=$FIELD_MODEL->getPicklistOptionClass($PICKLIST_NAME)}
		<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if isset($PICKLIST_COLORS[$PICKLIST_NAME]) && $PICKLIST_COLORS[$PICKLIST_NAME]}class="{$CLASS_NAME}"{/if} {if $CURRENT_VALUE eq php7_trim($PICKLIST_NAME)} selected {/if}>{$PICKLIST_VALUE}</option>
		{if $CURRENT_VALUE eq php7_trim($PICKLIST_NAME)}
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
		{foreach item=COLOR_VALUE key=COLOR_NAME from=$PICKLIST_COLORS}
			{if $COLOR_VALUE eq '#ffffff'}{continue}{/if}
			{assign var=COLOR_RGB value=Vtiger_Functions::hexToRGB($COLOR_VALUE)}
			{assign var=CLASS_NAME value=$FIELD_MODEL->getPicklistOptionClass($COLOR_NAME)}
			 .{$CLASS_NAME} {
				background-color: rgba({$COLOR_RGB},0.1) !important;
				color: rgba({$COLOR_RGB},1) !important;
			} ul[id*="select2-{$FIELD_MODEL->getName()}"] li[id*="{$COLOR_NAME}"] {
				background-color: rgba({$COLOR_RGB},0.1) !important;
			    color: rgba({$COLOR_RGB},1) !important;
			}
		{/foreach}
	</style>
{/if}
{/strip}
