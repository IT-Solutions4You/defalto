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
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUITypeModel()}
    {assign var=SELECTED_VALUES value=$UITYPE_MODEL->getSelectedValue($FIELD_VALUE, $PRIMARY_MODULE)}
    {assign var=FIELD_OPTIONS value=$UITYPE_MODEL->getFieldOptions($PRIMARY_MODULE)}
    {assign var=MODULE_OPTIONS value=$UITYPE_MODEL->getModuleOptions($PRIMARY_MODULE)}
    {assign var=LABEL_OPTIONS value=$UITYPE_MODEL->getLabelOptions($PRIMARY_MODULE, $RECORD->getLabels())}
    <div class="containerFields" data-field="" data-label="">
        <div class="labelFields visually-hidden">{json_encode($LABEL_OPTIONS)}</div>
        <div class="fieldOptions visually-hidden">{json_encode($FIELD_OPTIONS)}</div>
        <div class="modalFields visually-hidden">
            {include file='uitypes/FieldsEditLabelModal.tpl'|vtemplate_path:$QUALIFIED_MODULE}
            {include file='uitypes/FieldsNewFieldModal.tpl'|vtemplate_path:$QUALIFIED_MODULE}
        </div>
        <div class="overflow-auto border p-1 pb-5 mb-3">
            <div class="d-flex flex-nowrap">
                <div class="containerCloneFields visually-hidden">
                    {include file='uitypes/FieldsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE='' FIELD_DISPLAY_VALUE=''}
                </div>
                <div class="containerSelectedFields d-flex">
                    {foreach from=$SELECTED_VALUES item=SELECTED_VALUE}
                        {include file='uitypes/FieldsSelected.tpl'|vtemplate_path:$QUALIFIED_MODULE FIELD_VALUE=$SELECTED_VALUE FIELD_DISPLAY_VALUE=$UITYPE_MODEL->getLabelForValue($SELECTED_VALUE, $LABEL_OPTIONS)}
                    {/foreach}
                </div>
                <div class="containerSelectFields">
                    <button type="button" class="openSelectFields p-2 bg-body-secondary border-dashed text-nowrap me-1">{vtranslate('LBL_CLICK_HERE_ADD_COLUMN', $QUALIFIED_MODULE)}</button>
                </div>
            </div>
        </div>
    </div>
{/strip}