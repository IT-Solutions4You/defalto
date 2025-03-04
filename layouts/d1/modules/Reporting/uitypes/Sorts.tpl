{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=PRIMARY_MODULE value=$RECORD->get('primary_module')}
    {assign var=FIELD_UITYPE value=$FIELD_MODEL->getUITypeModel()}
    {assign var=SELECTED_VALUES value=$FIELD_UITYPE->getSelectedValue($FIELD_VALUE)}
    {assign var=LABEL_OPTIONS value=$FIELD_UITYPE->getLabelOptions($PRIMARY_MODULE, $RECORD->getLabels())}
    <div class="containerSorts container-fluid">
        <div class="fieldLabel py-2 text-secondary">{vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}</div>
        <div class="containerCloneSorts visually-hidden">
            {include file='uitypes/SortsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE='' FIELD_DISPLAY_VALUE=''}
        </div>
        <div class="containerSelectedSorts">
            {foreach from=$SELECTED_VALUES item=SELECTED_VALUE}
                {include file='uitypes/SortsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=$SELECTED_VALUE FIELD_DISPLAY_VALUE=$SELECTED_VALUE}
            {/foreach}
        </div>
    </div>
{/strip}