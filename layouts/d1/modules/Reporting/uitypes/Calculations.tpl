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
    <div class="containerCalculations">
        <div class="py-2 text-secondary">{vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}</div>
        <div class="py-2">
            <textarea class="numberFieldsCalculations visually-hidden">{json_encode($NUMBER_FIELDS)}</textarea>
            <div class="containerCloneCalculations visually-hidden">
                {include file='uitypes/CalculationsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=[]}
            </div>
            <div class="containerSelectedCalculations">
                {foreach from=$SELECTED_VALUES item=SELECTED_VALUE}
                    {include file='uitypes/CalculationsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=$SELECTED_VALUE}
                {/foreach}
            </div>
        </div>
    </div>
{/strip}