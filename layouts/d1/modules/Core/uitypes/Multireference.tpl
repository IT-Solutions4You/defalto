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
    {assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
    {assign var=REFERENCE_LIST value=$FIELD_MODEL->getReferenceList()}
    {assign var=REFERENCE_RECORDS value=$UITYPE_MODEL->getReferenceRecords($FIELD_VALUE)}
    {assign var=REFERENCED_MODULE_STRUCT value=$UITYPE_MODEL->getReferenceModule($FIELD_VALUE)}
    {assign var=REFERENCED_MODULE_NAME value=$REFERENCE_LIST[0]}
    {if !empty($REFERENCED_MODULE_STRUCT) && in_array($REFERENCED_MODULE_STRUCT->get('name'), $REFERENCE_LIST)}
        {assign var=REFERENCED_MODULE_NAME value=$REFERENCED_MODULE_STRUCT->get('name')}
    {/if}
    <div class="Core_Multireference_UIType referencefield-wrapper js-multireference-field">
        <input name="popupReferenceModule" type="hidden" value="{$REFERENCED_MODULE_NAME}"/>
        <input name="{$FIELD_NAME}" type="hidden" value="{$UITYPE_MODEL->convertToValue(array_keys($REFERENCE_RECORDS))}" class="sourceField" data-multiple="true"/>
        <div class="input-group flex-nowrap">
            {if php7_count($REFERENCE_LIST) > 1}
                <select class="select2 referenceModulesList">
                    {foreach item=REFERENCE_MODULE from=$REFERENCE_LIST}
                        <option value="{$REFERENCE_MODULE}" {if $REFERENCE_MODULE eq $REFERENCED_MODULE_NAME}selected{/if}>{vtranslate($REFERENCE_MODULE, $REFERENCE_MODULE)}</option>
                    {/foreach}
                </select>
            {/if}
            <select id="{$FIELD_NAME}_display" name="{$FIELD_NAME}_display[]" multiple="multiple"
                    class="inputElement form-select js-multireference-select"
                    data-fieldname="{$FIELD_NAME}"
                    data-fieldtype="multireference"
                    data-placeholder="{vtranslate('LBL_TYPE_SEARCH', $MODULE)}"
                    data-maximum-records="{$UITYPE_MODEL->getMaximumRecords()}"
                    data-maximum-records-message="{$UITYPE_MODEL->getMaximumRecordsMessage()|escape:'html'}"
                    data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}'
                    {if $FIELD_INFO['mandatory'] eq true}data-rule-required="true"{/if}>
                {foreach key=RECORD_ID item=REFERENCE_RECORD from=$REFERENCE_RECORDS}
                    <option value="{$RECORD_ID}" selected="selected">{$REFERENCE_RECORD['name']|escape:'html'}</option>
                {/foreach}
            </select>
            <span class="relatedPopup cursorPointer input-group-text" title="{vtranslate('LBL_SELECT', $MODULE)}">
                <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
            </span>
        </div>
    </div>
{/strip}
