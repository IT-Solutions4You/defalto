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
    <div class="containerLabels container-fluid border rounded py-2 visually-hidden">
        <div class="containerCloneLabels visually-hidden">
            {include file='uitypes/LabelsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE='' FIELD_NAME=''}
        </div>
        <div class="containerSelectedLabels">
            {foreach from=$SELECTED_VALUES key=SELECTED_KEY item=SELECTED_VALUE}
                {include file='uitypes/LabelsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE_KEY=$SELECTED_KEY FIELD_VALUE=$SELECTED_VALUE}
            {/foreach}
        </div>
    </div>
{/strip}