{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="row conditionRow">
	<div class="col-lg-4 col-md-4 col-sm-4">
		<select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="columnname">
			<option value="none">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
                    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
					{if !empty($COLUMNNAME_API)}
						{assign var=columnNameApi value=$COLUMNNAME_API}
					{else}
						{assign var=columnNameApi value=getCustomViewColumnName}
					{/if}
					<option value="{$FIELD_MODEL->$columnNameApi()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
					{if isset($CONDITION_INFO['columnname']) && decode_html($FIELD_MODEL->$columnNameApi()) eq decode_html($CONDITION_INFO['columnname'])}
						{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
						{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
						{if $FIELD_MODEL->getFieldDataType() == 'reference'  ||  $FIELD_MODEL->getFieldDataType() == 'multireference'}
							{$FIELD_TYPE='V'}
						{/if}
						{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
						selected="selected"
					{/if}
					{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
						{assign var=referenceList value=$FIELD_MODEL->getWebserviceFieldObject()->getReferenceList()}
						{if is_array($referenceList) && in_array('Users', $referenceList)}
								{assign var=USERSLIST value=array()}
								{assign var=CURRENT_USER_MODEL value = Users_Record_Model::getCurrentUserModel()}
								{assign var=ACCESSIBLE_USERS value = $CURRENT_USER_MODEL->getAccessibleUsers()}
								{foreach item=USER_NAME from=$ACCESSIBLE_USERS}
										{$USERSLIST[$USER_NAME] = $USER_NAME}
								{/foreach}
								{$FIELD_INFO['picklistvalues'] = $USERSLIST}
								{$FIELD_INFO['type'] = 'picklist'}
						{/if}
					{/if}
					data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}' 
                    {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}>
					{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
						({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))}) {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
					{else}
						{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
					{/if}
				</option>
				{/foreach}
				</optgroup>
			{/foreach}
		</select>
	</div>
	<div class="conditionComparator col-lg-3 col-md-3 col-sm-3">
		<select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="comparator">
			 <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
			{if isset($FIELD_TYPE) && isset($ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE])}
				{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
				{if $FIELD_TYPE eq 'D' || $FIELD_TYPE eq 'DT'}
					{assign var=DATE_FILTER_CONDITIONS value=array_keys($DATE_FILTERS)}
					{assign var=ADVANCE_FILTER_OPTIONS value=array_merge($ADVANCE_FILTER_OPTIONS,$DATE_FILTER_CONDITIONS)}
				{/if}
				{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
					<option value="{$ADVANCE_FILTER_OPTION}"
					{if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}
							selected
					{/if}
					>{vtranslate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}</option>
				{/foreach}
			{/if}
		</select>
	</div>
	<div class="col-lg col-md col-sm fieldUiHolder">
		<input name="{if isset($SELECTED_FIELD_MODEL) && $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')}{/if}" data-value="value" class="form-control inputElement col-lg-12" type="text" value="{if isset($CONDITION_INFO['value'])}{$CONDITION_INFO['value']|escape}{/if}" />
	</div>
	<span class="hide">
		<!-- TODO : see if you need to respect CONDITION_INFO condition or / and  -->
		{if empty($CONDITION)}
			{assign var=CONDITION value="and"}
		{/if}
		<input type="hidden" name="column_condition" value="{$CONDITION}" />
	</span>
	 <div class="col-lg-auto col-md-auto col-sm-auto text-end">
		<span class="deleteCondition btn btn-outline-secondary" title="{vtranslate('LBL_DELETE', $MODULE)}">
			<i class="fa fa-trash"></i>
		</span>
	</div>
</div>
{/strip}