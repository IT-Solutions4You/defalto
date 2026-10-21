{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="px-4 pb-4 mmProSetupWizard" id="mmProSetupWizard">
    <div class="bg-body rounded p-3">
        <h4 class="mb-3"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>{vtranslate('LBL_SETUP_WIZARD', $QUALIFIED_MODULE)}</h4>
        <p class="text-secondary">{vtranslate('LBL_SETUP_WIZARD_INTRO', $QUALIFIED_MODULE)}</p>
        <form id="mmProSetupWizardForm" class="card">
            <div class="card-body">
                <h5>1. {vtranslate('LBL_SETUP_STEP_MAILBOXES', $QUALIFIED_MODULE)}</h5>
                <ul class="list-group mb-3">
                    {foreach item=MAILBOX from=$MAILBOXES}
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{$MAILBOX.name|escape} &lt;{$MAILBOX.email|escape}&gt;</span>
                            <span class="badge text-bg-secondary">{vtranslate('LBL_TYPE_'|cat:($MAILBOX.mailbox_type|upper), $QUALIFIED_MODULE)}</span>
                        </li>
                    {foreachelse}
                        <li class="list-group-item text-secondary">{vtranslate('LBL_SETUP_NO_MAILBOXES', $QUALIFIED_MODULE)}</li>
                    {/foreach}
                </ul>
                <div class="mb-3">
                    <label class="form-label" for="mmProSyncFromDays">{vtranslate('LBL_SETUP_SYNC_FROM_DAYS', $QUALIFIED_MODULE)}</label>
                    <input type="number" min="0" class="form-control w-auto inputElement" id="mmProSyncFromDays" name="sync_from_days" value="{$SYNC_FROM_DAYS|escape}">
                </div>
                <h5>2. {vtranslate('LBL_SETUP_STEP_SHARED', $QUALIFIED_MODULE)}</h5>
                <p class="text-secondary mb-1">{vtranslate('LBL_SETUP_SHARED_HINT', $QUALIFIED_MODULE)}</p>
                <p class="text-secondary">{vtranslate('LBL_SETUP_DEPUTY_HINT', $QUALIFIED_MODULE)}</p>
                <p><a class="btn btn-outline-secondary" href="index.php?module=MailManager&parent=Settings&view=Edit">{vtranslate('LBL_SETUP_ADD_SHARED', $QUALIFIED_MODULE)}</a></p>
                <h5>3. {vtranslate('LBL_SETUP_STEP_LABELS', $QUALIFIED_MODULE)}</h5>
                <p class="mb-3">
                    {foreach item=LABEL from=$LABELS}
                        <span class="badge border me-1" style="background-color: {$LABEL.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($LABEL.color)};">{$LABEL.name|escape}</span>
                    {/foreach}
                </p>
                <h5>4. {vtranslate('LBL_SETUP_STEP_FILING', $QUALIFIED_MODULE)}</h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="mmProFiling" name="ai_feature_emailfiling" value="1" {if $FILING.ai_feature_emailfiling eq '1'}checked{/if}>
                    <label class="form-check-label" for="mmProFiling">{vtranslate('LBL_SETUP_EMAIL_FILING', $QUALIFIED_MODULE)}</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="mmProAutoComment" name="ai_feature_autocomment" value="1" {if $FILING.ai_feature_autocomment eq '1'}checked{/if}>
                    <label class="form-check-label" for="mmProAutoComment">{vtranslate('LBL_SETUP_AUTO_COMMENT', $QUALIFIED_MODULE)}</label>
                </div>
                <h5>5. {vtranslate('LBL_SETUP_STEP_AI', $QUALIFIED_MODULE)}</h5>
                <p><a class="btn btn-outline-secondary" href="index.php?module=MailManager&parent=Settings&view=AISettings">{vtranslate('LBL_SETUP_OPEN_AI', $QUALIFIED_MODULE)}</a></p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-mm-setup="skip">{vtranslate('LBL_SETUP_SKIP', $QUALIFIED_MODULE)}</button>
                <button type="button" class="btn btn-primary" data-mm-setup="finish">{vtranslate('LBL_SETUP_FINISH', $QUALIFIED_MODULE)}</button>
            </div>
        </form>
    </div>
</div>
{/strip}
