{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="row conditionRow mb-3">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="columnname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
                <option value="none"></option>
                {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
                    <optgroup label='{vtranslate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}'>
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
                                    {if decode_html($FIELD_MODEL->$columnNameApi()) eq $CONDITION_INFO['columnname']}
                                        {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()}
                                        {assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
                                        {$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
                                        selected="selected"
                                    {/if}
                                    {if ($MODULE_MODEL->get('name') eq 'Documents') and ($FIELD_NAME eq 'filelocationtype' or $FIELD_NAME eq 'folderid' or $FIELD_NAME eq 'filename')}
                                        {if $FIELD_NAME eq 'filelocationtype'}
                                            {assign var=PICKLIST_VALUES value = $FIELD_MODEL->getFileLocationType()}
                                            {$FIELD_INFO['type'] = 'picklist'}
                                            {assign var=FIELD_TYPE value='picklist'}
                                            {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                        {else if $FIELD_NAME eq 'folderid'}
                                            {assign var=PICKLIST_VALUES value = $FIELD_MODEL->getDocumentFolders()}
                                            {$FIELD_INFO['type'] = 'picklist'}
                                            {assign var=FIELD_TYPE value='picklist'}
                                            {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                        {else if $FIELD_NAME eq 'filename'}
                                            {$FIELD_INFO['type'] = 'string'}
                                            {assign var=FIELD_TYPE value='string'}
                                        {/if}
                                    {/if}
                                    data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
                                    {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}>
                                {if $SELECTED_MODULE_NAME neq $MODULE_MODEL->get('name')}
                                    ({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
                                {else}
                                    {vtranslate($FIELD_MODEL->get('label'), $SELECTED_MODULE_NAME)}
                                {/if}
                            </option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
        <div class="conditionComparator col-lg-3 col-md-3 col-sm-3">
            <select class="{if empty($NOCHOSEN)}select2{/if} col-lg-12" name="comparator" data-placeholder="{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}">
                <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
                {assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
                {foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
                    <option value="{$ADVANCE_FILTER_OPTION}"
                            {if $ADVANCE_FILTER_OPTION === $CONDITION_INFO['comparator']}
                                selected
                            {/if}
                    >{vtranslate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4  fieldUiHolder">
            <input name="{if $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')}{/if}" data-value="value" class="inputElement col-lg-12" type="text" value="{$CONDITION_INFO['value']|escape}"/>
        </div>
        <div class="hide">
            <!-- TODO : see if you need to respect CONDITION_INFO condition or / and  -->
            {if empty($CONDITION)}
                {assign var=CONDITION value="and"}
            {/if}
            <input type="hidden" name="column_condition" value="{$CONDITION}"/>
        </div>
        <div class="col-lg-1">
            <div class="btn btn-outline-secondary deleteCondition cursorPointer" title="{vtranslate('LBL_DELETE', $MODULE)}">
                <i class="fa fa-trash"></i>
            </div>
        </div>
    </div>
{/strip}