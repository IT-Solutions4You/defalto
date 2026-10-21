{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="px-4 pb-4" id="mmProPurgeData">
    <div class="bg-body rounded p-3">
        <h4 class="mb-3"><i class="fa-solid fa-trash me-2"></i>{vtranslate('LBL_PURGE_TITLE', $QUALIFIED_MODULE)}</h4>
        <p>{vtranslate('LBL_PURGE_DESCRIPTION', $QUALIFIED_MODULE)}</p>
        <p class="fw-bold">{vtranslate('LBL_PURGE_STORED_MESSAGES', $QUALIFIED_MODULE)}: <span id="mmProStoredMessages">{$STORED_MESSAGES}</span></p>
        <button type="button" class="btn btn-danger" data-mm-purge="1" {if !$STORED_MESSAGES}disabled{/if}>{vtranslate('LBL_PURGE_BUTTON', $QUALIFIED_MODULE)}</button>
    </div>
</div>
{/strip}
