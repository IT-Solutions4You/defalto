{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

<div class="js-checklist-item d-flex align-items-center gap-3 py-2 rounded" data-id="{$CHECKLIST_ITEM.id}">
    <label class="js-checklist-check-target d-inline-flex align-items-center justify-content-center flex-shrink-0 mb-0">
        <input type="checkbox" class="form-check-input js-checklist-check mt-0" {if $CHECKLIST_ITEM.checked}checked{/if} {if !$CHECKLIST_EDITABLE || !$RECORD->getId()}disabled{/if} aria-label="{$CHECKLIST_ITEM.text|escape:'html'}" />
    </label>
    <div class="js-checklist-item-editor flex-grow-1 text-break">
        {if $CHECKLIST_EDITABLE}
            <button type="button" class="js-checklist-text js-checklist-edit btn btn-link p-0 text-start fw-normal {if $CHECKLIST_ITEM.checked}text-decoration-line-through text-secondary{else}text-decoration-none text-body{/if}">{$CHECKLIST_ITEM.text|escape:'html'}</button>
        {else}
            <span class="js-checklist-text {if $CHECKLIST_ITEM.checked}text-decoration-line-through text-secondary{/if}">{$CHECKLIST_ITEM.text|escape:'html'}</span>
        {/if}
    </div>
    {if $CHECKLIST_EDITABLE}
        <button type="button" class="btn btn-link p-0 text-secondary invisible js-checklist-item-delete" title="{vtranslate('LBL_DELETE','Core')|escape:'html'}" aria-label="{vtranslate('LBL_DELETE','Core')|escape:'html'}"><i class="fa fa-xmark"></i></button>
    {/if}
</div>
