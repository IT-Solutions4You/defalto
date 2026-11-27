{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{assign var=CHECKLIST_CONTEXT value=$FIELD_MODEL->getUITypeModel()->getChecklistContext($RECORD->getId())}
{assign var=CHECKLIST_EDITABLE value=$CHECKLIST_CONTEXT.editable}
<div class="js-checklists fw-normal" data-record="{$RECORD->getId()}" data-field="{$FIELD_MODEL->getId()}"
     data-revision="{$CHECKLIST_CONTEXT.checklist.revision|escape:'html'}"
     data-invalid-text="{vtranslate('LBL_CHECKLIST_INVALID_TEXT','Core')|escape:'html'}"
     data-saved="{vtranslate('LBL_CHECKLIST_SAVED','Core')|escape:'html'}">
    {if !empty($CHECKLIST_FORM) && $FIELD_MODEL->isEditable() && !$FIELD_MODEL->isRecordActionLocked()}
        {if $CHECKLIST_CONTEXT.editable || !$RECORD->getId()}
            <textarea class="js-checklist-field-value d-none" name="{$FIELD_MODEL->getName()|escape:'html'}" aria-hidden="true">{$FIELD_MODEL->get('fieldvalue')|escape:'html':'UTF-8':false}</textarea>
        {/if}
    {/if}
    <div class="js-checklist-list">
        {include file='uitypes/ChecklistCard.tpl'|vtemplate_path:'Core' CHECKLIST=$CHECKLIST_CONTEXT.checklist}
    </div>
    {if !$RECORD->getId()}
        <p class="text-secondary">{vtranslate('LBL_CHECKLIST_SAVE_FIRST','Core')}</p>
    {/if}
    {if $CHECKLIST_EDITABLE}
        <template class="js-checklist-item-template">
            {include file='uitypes/ChecklistItem.tpl'|vtemplate_path:'Core' CHECKLIST_ITEM=['id'=>0,'text'=>'','checked'=>false]}
        </template>
    {/if}
</div>
