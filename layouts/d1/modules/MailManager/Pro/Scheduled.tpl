{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* The user's "Send later" mail: send now, another time, cancel (ProComposer.js) *}
{strip}
<div class="modal-dialog modal-lg its4you-scheduled-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><i class="fa-regular fa-clock me-2"></i>{vtranslate('LBL_SCHEDULED', $MODULE)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            {if !$SCHEDULED}
                <div class="its4you-muted text-center py-3">{vtranslate('LBL_SCHEDULED_EMPTY', $MODULE)}</div>
            {/if}
            {foreach item=ITEM from=$SCHEDULED}
                <div class="row py-2 border-bottom align-items-center its4you-scheduled-row" data-scheduled="{$ITEM.scheduledid}">
                    <div class="col-lg-5">
                        <div class="fw-semibold text-truncate">{$ITEM.subject|escape}</div>
                        <div class="its4you-muted text-truncate">{$ITEM.to_email|escape}</div>
                        {if $ITEM.status eq 'failed'}
                            <div class="text-danger small"><i class="fa-solid fa-triangle-exclamation me-1"></i>{vtranslate('LBL_SCHEDULED_FAILED', $MODULE)}{if $ITEM.error}: {$ITEM.error|escape}{/if}</div>
                        {/if}
                    </div>
                    <div class="col-lg-3">
                        <input type="datetime-local" class="form-control inputElement its4you-scheduled-time" value="{$ITEM.time_input}" title="{$ITEM.time_display|escape}">
                    </div>
                    <div class="col-lg-4 text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary its4you-scheduled-op" data-op="reschedule" title="{vtranslate('LBL_SCHEDULE_CHANGE', $MODULE)|escape}"><i class="fa-regular fa-clock"></i></button>
                            <button type="button" class="btn btn-outline-secondary its4you-scheduled-op" data-op="send" title="{vtranslate('LBL_SEND_NOW', $MODULE)|escape}"><i class="fa-solid fa-paper-plane"></i></button>
                            <button type="button" class="btn btn-outline-secondary its4you-scheduled-op" data-op="cancel" title="{vtranslate('LBL_SCHEDULE_CANCEL', $MODULE)|escape}"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="modal-footer">
            <div class="text-center w-100"><button type="button" class="btn btn-primary cancelLink" data-bs-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</button></div>
        </div>
    </div>
</div>
{/strip}
