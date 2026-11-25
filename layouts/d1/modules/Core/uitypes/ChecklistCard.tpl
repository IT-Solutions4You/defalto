<div class="js-checklist-card" data-id="{$CHECKLIST.id}">
    <div class="js-checklist-items">
        {foreach item=CHECKLIST_ITEM from=$CHECKLIST.items}
            {include file='uitypes/ChecklistItem.tpl'|vtemplate_path:'Core'}
        {/foreach}
    </div>
    {if $CHECKLIST_EDITABLE}
        <div class="js-checklist-add d-flex align-items-center gap-3 py-2">
            <button type="button" class="btn btn-link p-0 text-secondary js-checklist-item-create" title="{vtranslate('LBL_ADD','Core')|escape:'html'}" aria-label="{vtranslate('LBL_ADD','Core')|escape:'html'}"><i class="fa fa-plus"></i></button>
            <input type="text" class="form-control border-0 shadow-none bg-transparent p-0 js-checklist-item-text" placeholder="{vtranslate('LBL_CHECKLIST_ITEM_ADD','Core')|escape:'html'}" aria-label="{vtranslate('LBL_CHECKLIST_ITEM_ADD','Core')|escape:'html'}" />
        </div>
        <template class="js-checklist-editor-template">
            <div class="js-checklist-editor d-flex align-items-center gap-2 w-100">
                <input type="text" class="form-control border-0 shadow-none bg-transparent p-0 js-checklist-edit-text" aria-label="{vtranslate('LBL_EDIT','Core')|escape:'html'}" />
            </div>
        </template>
    {/if}
    <button type="button" class="btn btn-link p-0 mt-2 text-secondary js-checklist-hide {if !$CHECKLIST.completed}d-none{/if}" data-show="{vtranslate('LBL_CHECKLIST_SHOW','Core')|escape:'html'}" data-hide="{vtranslate('LBL_CHECKLIST_HIDE','Core')|escape:'html'}">{vtranslate('LBL_CHECKLIST_HIDE','Core')}</button>
</div>
