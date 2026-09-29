{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}

    {if empty($FILTER_EDITOR)}
        {assign var=FILTER_EDITOR value=Core_FilterEditor_Model::getInstance($SOURCE_MODULE|default:$MODULE, $MODULE, $COLUMNNAME_API|default:'getCustomViewColumnName')}
    {/if}
    {if empty($ADVANCE_CRITERIA)}
        {assign var=ADVANCE_CRITERIA value=array(1 => array())}
    {/if}

	{if !isset($SHOW_DEFAULT_CONDITIONS)}
		{assign var=SHOW_DEFAULT_CONDITIONS value=$FILTER_EDITOR->hasDefaultCondition()}
	{/if}


<div class="filterContainer filterElements well filterConditionContainer filterConditionsDiv">
	<input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
	<input type=hidden name="advanceFilterOpsByFieldType" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS_BY_TYPE))}' />
	{foreach key=ADVANCE_FILTER_OPTION_KEY item=ADVANCE_FILTER_OPTION from=$ADVANCED_FILTER_OPTIONS}
		{$ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION_KEY] = vtranslate($ADVANCE_FILTER_OPTION, $FILTER_EDITOR->getTranslationModule())}
	{/foreach}
	<input type=hidden name="advanceFilterOptions" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS))}' />
	{foreach key=GROUP_KEY item=GROUP_INFO from=$ADVANCE_CRITERIA}
	{if empty($GROUP_INFO)}{assign var=GROUP_INFO value=array()}{/if}
	{assign var=GROUP_JOIN value=$GROUP_INFO['condition']|default:'and'}
	{if $GROUP_KEY eq 1 || !empty($GROUP_INFO['columns'])}
	<div class="conditionGroup bg-body-secondary border rounded p-3 mb-0" data-group-id="{$GROUP_KEY-1}">
        <div class="header d-flex align-items-center">
			<strong class="groupTitle">{vtranslate('LBL_GROUP','Core')} {$GROUP_KEY}</strong>
			{if $GROUP_KEY gt 1}<button type="button" class="btn btn-sm btn-outline-secondary bg-white text-secondary deleteGroup ms-2" title="{vtranslate('LBL_DELETE', $MODULE)}"><i class="fa fa-trash"></i></button>{/if}
		</div>
		<div class="contents">
			<div class="conditionList">
			 {foreach item=CONDITION_INFO from=$GROUP_INFO['columns']|default:array()}
				{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE CONDITION=$CONDITION_INFO['column_condition']|default:$GROUP_JOIN}
			{/foreach}
			{if $SHOW_DEFAULT_CONDITIONS && empty($GROUP_INFO['columns'])}
				{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array() CONDITION=$GROUP_JOIN}
			{/if}
			</div>
			<div class="hide basic">
				{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=array() MODULE=$MODULE NOCHOSEN=true}
			</div>
            <div class="addCondition">
				<button type="button" class="btn btn-outline-secondary bg-white text-secondary">
					<i class="fa fa-plus"></i>
					<span class="ps-2">{vtranslate('LBL_ADD_CONDITION',$MODULE)}</span>
				</button>
			</div>
			<div class="groupCondition hide">
				<input type="hidden" name="condition" value="{$GROUP_JOIN}" />
			</div>
		</div>
	</div>
	<div class="groupConnector text-start py-2">
		<select name="groupjoin" class="form-select groupJoin d-inline-block w-auto">
			<option value="and" {if $GROUP_JOIN eq 'and'}selected{/if}>{vtranslate('LBL_AND', 'Core')}</option>
			<option value="or" {if $GROUP_JOIN eq 'or'}selected{/if}>{vtranslate('LBL_OR', 'Core')}</option>
		</select>
	</div>
	{/if}
	{/foreach}
	<button type="button" class="btn btn-outline-secondary bg-white text-secondary border addGroup mt-3"><i class="fa fa-plus"></i> {vtranslate('LBL_GROUP','Core')}</button>
</div>
{/strip}
