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
    {assign var=FIELD_OPTIONS value=$UITYPE_MODEL->getSelectedFieldOptions($PRIMARY_MODULE, $RECORD->getFields(), $RECORD->getLabels())}
    <select class="inputElement select2 form-select"
            data-fieldname="group_by"
            data-fieldtype="grouping"
            data-placeholder="{vtranslate('LBL_SELECT_GROUPING_FIELD', $QUALIFIED_MODULE)}"
            name="group_by[]"
            multiple>
        {foreach from=$FIELD_OPTIONS key=FIELD_VALUE item=FIELD_LABEL}
            <option value="{Vtiger_Util_Helper::toSafeHTML($FIELD_VALUE)}" {if in_array($FIELD_VALUE, $CURRENT_VALUES)}selected{/if}>{$FIELD_LABEL}</option>
        {/foreach}
    </select>
{/strip}
