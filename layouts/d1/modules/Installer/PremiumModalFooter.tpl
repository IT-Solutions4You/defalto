{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if !$SUCCESS_LABEL}{assign var=SUCCESS_LABEL value='LBL_PREMIUM_MODAL_BUY'}{/if}
    {if !$CLOSE_LABEL}{assign var=CLOSE_LABEL value='LBL_PREMIUM_MODAL_CONTINUE'}{/if}
    <div class="modal-footer d-flex flex-column gap-2 px-4 py-3">
        <div class="d-grid gap-2 w-100 d-sm-flex">
            <a class="btn btn-primary w-100 active" href="{$BUY_URL}" target="_blank" rel="noopener noreferrer" data-df-buy data-bs-dismiss="modal">
                {vtranslate($SUCCESS_LABEL, $MODULE)}
            </a>
            <button class="btn btn-outline-secondary w-100" type="button" data-bs-dismiss="modal" data-df-continue>
                {vtranslate($CLOSE_LABEL, $MODULE)}
            </button>
        </div>
        <p class="text-muted small text-center mb-0">
            {vtranslate('LBL_PREMIUM_MODAL_FOOTNOTE', $MODULE)}
            <a class="text-primary ms-1" href="index.php?module=Installer&view=Index&app=TOOLS" target="_blank" rel="noopener noreferrer">
                {vtranslate('LBL_PREMIUM_MODAL_INSTALLER_LINK', $MODULE)}
            </a>
        </p>
    </div>
{/strip}