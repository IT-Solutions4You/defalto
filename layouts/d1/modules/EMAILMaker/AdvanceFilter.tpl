{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=ALL_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[1] }
    {assign var=ANY_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[2] }

    {if empty($ALL_CONDITION_CRITERIA) }
        {assign var=ALL_CONDITION_CRITERIA value=array()}
    {/if}

    {if empty($ANY_CONDITION_CRITERIA) }
        {assign var=ANY_CONDITION_CRITERIA value=array()}
    {/if}
    <div class="filterContainer">
        <input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}'/>
        <input type="hidden" name="advanceFilterOpsByFieldType" data-value='{ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS_BY_TYPE)}'/>
        {foreach key=ADVANCE_FILTER_OPTION_KEY item=ADVANCE_FILTER_OPTION from=$ADVANCED_FILTER_OPTIONS}
            {$ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION_KEY] = vtranslate($ADVANCE_FILTER_OPTION, $MODULE)}
        {/foreach}
        <input type=hidden name="advanceFilterOptions" data-value='{ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS)}'/>
        <div class="allConditionContainer conditionGroup contentsBackground pb-3">
            <div class="header">
                <span><strong>{vtranslate('LBL_ALL_CONDITIONS',$MODULE)}</strong></span> &nbsp;
                <span>({vtranslate('LBL_ALL_CONDITIONS_DESC',$MODULE)})</span>
            </div>
            <div class="contents">
                <div class="conditionList">
                    {foreach item=CONDITION_INFO from=$ALL_CONDITION_CRITERIA['columns']}
                        {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE}
                    {/foreach}
                    {if count($ALL_CONDITION_CRITERIA) eq 0}
                        {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array()}
                    {/if}
                </div>
                <div class="hide basic">
                    {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=array() MODULE=$MODULE NOCHOSEN=true}
                </div>
                <div class="addCondition">
                    <button type="button" class="btn btn-outline-secondary">{vtranslate('LBL_ADD_CONDITION',$MODULE)}</button>
                </div>
                <div class="groupCondition">
                    {assign var=GROUP_CONDITION value=$ALL_CONDITION_CRITERIA['condition']}
                    {if empty($GROUP_CONDITION)}
                        {assign var=GROUP_CONDITION value="and"}
                    {/if}
                    <input type="hidden" name="condition" value="{$GROUP_CONDITION}"/>
                </div>
            </div>
        </div>
        <div class="anyConditionContainer conditionGroup contentsBackground">
            <div class="header">
                <span><strong>{vtranslate('LBL_ANY_CONDITIONS',$MODULE)}</strong></span>
                <span>({vtranslate('LBL_ANY_CONDITIONS_DESC',$MODULE)})</span>
            </div>
            <div class="contents">
                <div class="conditionList">
                    {foreach item=CONDITION_INFO from=$ANY_CONDITION_CRITERIA['columns']}
                        {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE CONDITION="or"}
                    {/foreach}
                    {if count($ANY_CONDITION_CRITERIA) eq 0}
                        {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array() CONDITION="or"}
                    {/if}
                </div>
                <div class="hide basic">
                    {include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array() CONDITION="or" NOCHOSEN=true}
                </div>
                <div class="addCondition">
                    <button type="button" class="btn btn-outline-secondary">{vtranslate('LBL_ADD_CONDITION',$MODULE)}</button>
                </div>
            </div>
        </div>
    </div>
{/strip}