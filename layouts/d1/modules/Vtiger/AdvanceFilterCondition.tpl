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
    {assign var=FILTER_SOURCE_MODULE value=$FILTER_EDITOR->getSourceModule()}
    {assign var=SELECTED_FIELD_MODEL value=false}
    <div class="row conditionRow">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="columnname">
                <option value="none">{vtranslate('LBL_SELECT_FIELD', $FILTER_EDITOR->getTranslationModule())}</option>
                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
                    <optgroup label="{vtranslate($BLOCK_LABEL, $FILTER_SOURCE_MODULE)|escape}">
                        {foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
                            {assign var=FIELD_INFO value=$FILTER_EDITOR->getFieldInfo($FIELD_MODEL)}
                            {assign var=FIELD_MODULE_MODEL value=$FIELD_MODEL->getModule()}
                            {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
                            {assign var=FIELD_COLUMN value=$FILTER_EDITOR->getColumnName($FIELD_MODEL)}
                            {assign var=FIELD_OPERATORS value=$FILTER_EDITOR->getOperators($FIELD_MODEL)}
                            <option value="{$FIELD_COLUMN|escape}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME|escape}"
                                {if isset($CONDITION_INFO['columnname']) && decode_html($FIELD_COLUMN) eq decode_html($CONDITION_INFO['columnname'])}
                                    {assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
                                    {$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
                                    selected="selected"
                                {/if}
                                data-condition-operators='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_OPERATORS))}'
                                data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
                                {if !empty($SPECIAL_VALIDATOR)}data-validator='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SPECIAL_VALIDATOR))}'{/if}>
                                {if $FILTER_SOURCE_MODULE neq $FIELD_MODULE_MODEL->get('name')}
                                    ({vtranslate($FIELD_MODULE_MODEL->get('name'), $FIELD_MODULE_MODEL->get('name'))|escape}) {vtranslate($FIELD_MODEL->get('label'), $FIELD_MODULE_MODEL->get('name'))|escape}
                                {else}
                                    {vtranslate($FIELD_MODEL->get('label'), $FILTER_SOURCE_MODULE)|escape}
                                {/if}
                            </option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
        <div class="conditionComparator col-lg-3 col-md-3 col-sm-3">
            <select class="comparatorSelect {if empty($NOCHOSEN)}select2{/if} col-lg-12" name="comparator">
                <option value="none">{vtranslate('LBL_NONE', $FILTER_EDITOR->getTranslationModule())}</option>
                {if $SELECTED_FIELD_MODEL}
                    {foreach key=ADVANCE_FILTER_OPTION item=OPERATOR_LABEL from=$FILTER_EDITOR->getOperators($SELECTED_FIELD_MODEL)}
                        <option value="{$ADVANCE_FILTER_OPTION|escape}" {if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}selected{/if}>{$OPERATOR_LABEL|escape}</option>
                    {/foreach}
                {/if}
            </select>
        </div>
        <div class="col-lg col-md col-sm fieldUiHolder">
            <input name="{if $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')|escape}{/if}" data-value="value" class="form-control inputElement col-lg-12" type="text" value="{$CONDITION_INFO['value']|default:''|escape}" />
        </div>
        <div class="col-auto rowConditionConnector invisible">
            {if empty($CONDITION)}{assign var=CONDITION value="and"}{/if}
            <select name="column_condition" class="form-select w-auto" aria-label="{vtranslate('LBL_CONDITION', 'Core')}">
                <option value="and" {if $CONDITION eq 'and'}selected{/if}>{vtranslate('LBL_AND', 'Core')}</option>
                <option value="or" {if $CONDITION eq 'or'}selected{/if}>{vtranslate('LBL_OR', 'Core')}</option>
            </select>
        </div>
        <div class="col-lg-auto col-md-auto col-sm-auto text-end">
            <button type="button" class="deleteCondition btn btn-outline-secondary bg-white text-secondary" title="{vtranslate('LBL_DELETE', $MODULE)}">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
{/strip}
