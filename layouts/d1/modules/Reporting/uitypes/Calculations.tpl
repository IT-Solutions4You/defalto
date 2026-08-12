{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    {assign var=SELECTED_VALUES value=$FIELD_MODEL->getUITypeModel()->getSelectedValue($FIELD_VALUE)}
    {assign var=NUMBER_FIELDS value=$FIELD_MODEL->getUITypeModel()->getNumberFields($PRIMARY_MODULE)}
    {assign var=RECORD_COUNT_SELECTED value=false}
    {foreach from=$SELECTED_VALUES item=SELECTED_VALUE}
        {if isset($SELECTED_VALUE['count']) && 'Yes' eq $SELECTED_VALUE['count']}
            {assign var=RECORD_COUNT_SELECTED value=true}
        {/if}
    {/foreach}
    <input type="hidden" name="calculation[]" value="">
    <div class="containerCalculations">
        <div class="py-2 text-secondary">{vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}</div>
        <div class="py-2">
            <div class="recordCountCalculation mb-3" data-name="__record_count" data-label="{vtranslate('LBL_COUNT', $QUALIFIED_MODULE)}">
                <input name="calculation[__record_count][name]" type="hidden" value="__record_count">
                <input name="calculation[__record_count][label]" type="hidden" value="{vtranslate('LBL_COUNT', $QUALIFIED_MODULE)}">
                <label class="form-check-label input-group-text d-inline-flex">
                    <input
                        class="fieldRecordCount form-check-input m-0"
                        name="calculation[__record_count][count]"
                        type="checkbox"
                        value="Yes"
                        {if $RECORD_COUNT_SELECTED}checked="checked"{/if}
                    >
                    <span class="ms-2">{vtranslate('LBL_COUNT_RECORDS', $QUALIFIED_MODULE)}</span>
                </label>
            </div>
            <textarea class="numberFieldsCalculations visually-hidden">{json_encode($NUMBER_FIELDS)}</textarea>
            <div class="containerCloneCalculations visually-hidden">
                {include file='uitypes/CalculationsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=[]}
            </div>
            <div class="containerSelectedCalculations">
                {foreach from=$SELECTED_VALUES key=SELECTED_NAME item=SELECTED_VALUE}
                    {if '__record_count' neq $SELECTED_NAME && (!isset($SELECTED_VALUE['name']) || '__record_count' neq $SELECTED_VALUE['name'])}
                        {include file='uitypes/CalculationsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=$SELECTED_VALUE}
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
{/strip}
