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
    {assign var=FIELD_UITYPE value=$FIELD_MODEL->getUITypeModel()}
    {assign var=SELECTED_VALUES value=$FIELD_UITYPE->getSelectedValue($FIELD_VALUE)}
    <div class="containerAlign container-fluid">
        <div class="containerCloneAlign visually-hidden">
            {include file='uitypes/AlignSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE='' FIELD_DISPLAY_VALUE=''}
        </div>
        <div class="containerSelectedAlign">
            {foreach from=$SELECTED_VALUES key=SELECTED_KEY item=SELECTED_VALUE}
                {include file='uitypes/AlignSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=$SELECTED_KEY FIELD_DISPLAY_VALUE=$SELECTED_VALUE}
            {/foreach}
        </div>
    </div>
{/strip}