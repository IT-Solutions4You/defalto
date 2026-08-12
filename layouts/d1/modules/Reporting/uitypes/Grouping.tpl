{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
    {assign var=CURRENT_VALUES value=$UITYPE_MODEL->getSelectedValues($FIELD_MODEL->get('fieldvalue'))}
    {assign var=CURRENT_CONFIGURATIONS value=$UITYPE_MODEL->getSelectedConfigurations($FIELD_MODEL->get('fieldvalue'))}
    {assign var=CURRENT_INTERVALS value=$UITYPE_MODEL->getSelectedIntervals($FIELD_MODEL->get('fieldvalue'))}
    {assign var=FIELD_OPTIONS value=$UITYPE_MODEL->getFieldOptions($PRIMARY_MODULE, $RECORD->getLabels())}
    {assign var=DATE_FIELDS value=$UITYPE_MODEL->getDateFieldNames($PRIMARY_MODULE)}
    {assign var=DATE_INTERVALS value=$UITYPE_MODEL->getDateGroupingIntervals()}
    <div class="groupingRequestValues">
        <input type="hidden" name="group_by[]" value="">
        {foreach from=$CURRENT_CONFIGURATIONS item=GROUPING_CONFIGURATION}
            <input type="hidden" name="group_by[]" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($GROUPING_CONFIGURATION))}">
        {/foreach}
    </div>
    <select class="inputElement select2 form-select"
            data-fieldname="group_by"
            data-fieldtype="grouping"
            data-placeholder="{vtranslate('LBL_SELECT_GROUPING_FIELD', $QUALIFIED_MODULE)}"
            name="group_by_fields[]"
            multiple>
        {foreach from=$FIELD_OPTIONS key=FIELD_VALUE item=FIELD_LABEL}
            <option value="{Vtiger_Util_Helper::toSafeHTML($FIELD_VALUE)}"
                    data-date-grouping="{if in_array($FIELD_VALUE, $DATE_FIELDS)}1{else}0{/if}"
                    {if in_array($FIELD_VALUE, $CURRENT_VALUES)}selected{/if}>{$FIELD_LABEL}</option>
        {/foreach}
    </select>
    <div class="reportingDateGroupingOptions mt-3"
         data-current-intervals='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($CURRENT_INTERVALS))}'
         data-intervals='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_INTERVALS))}'
         data-label="{vtranslate('LBL_DATE_GROUPING_INTERVAL', $QUALIFIED_MODULE)}">
    </div>
{/strip}
