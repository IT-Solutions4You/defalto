{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog modal-lg">
        <div class="modal-content" role="document" aria-labelledby="dfPremiumTitle">
            <div class="modal-header bg-primary text-white px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 border border-white border-opacity-25 rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 18h16l-2-9-6 5-6-5-2 9z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="modal-title h3 mb-0" id="dfPremiumTitle">
                            {vtranslate('LBL_PREMIUM_MODAL_TITLE_PREFIX', $MODULE)} {vtranslate($FOR_MODULE, $FOR_MODULE)}
                        </h2>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 py-3">
                <p class="mb-3"><strong>{vtranslate($FOR_MODULE, $FOR_MODULE)}</strong> {vtranslate('LBL_PREMIUM_MODAL_LEAD', $FOR_MODULE)}</p>

                <h3 class="h6">{vtranslate('LBL_PREMIUM_MODAL_WHAT_YOU_GET', $MODULE)}</h3>

                {if !empty($PREMIUM_ITEMS)}
                    <ul class="list-unstyled mb-3">
                        {foreach from=$PREMIUM_ITEMS item=PREMIUM_ITEM name=premiumLoop}
                            <li class="d-flex gap-2{if !$smarty.foreach.premiumLoop.last} mb-2{/if}">
                            <span class="text-success" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                                <span>{$PREMIUM_ITEM}</span>
                            </li>
                        {/foreach}
                    </ul>
                {/if}

                <div class="alert alert-info d-flex gap-2 align-items-start" role="note">
                <span class="text-success" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                    <div>{vtranslate('LBL_PREMIUM_MODAL_HIGHLIGHT', $MODULE)}</div>
                </div>
            </div>

            <div class="modal-footer d-flex flex-column gap-2 px-4 py-3">
                <div class="d-grid gap-2 w-100 d-sm-flex">
                    <a class="btn btn-primary w-100 active" href="{$BUY_URL}" target="_blank" rel="noopener noreferrer" data-df-buy data-bs-dismiss="modal">
                        {vtranslate('LBL_PREMIUM_MODAL_BUY', $MODULE)}
                    </a>
                    <button class="btn btn-outline-secondary w-100" type="button" data-bs-dismiss="modal" data-df-continue>
                        {vtranslate('LBL_PREMIUM_MODAL_CONTINUE', $MODULE)}
                    </button>
                </div>
                <p class="text-muted small text-center mb-0">
                    {vtranslate('LBL_PREMIUM_MODAL_FOOTNOTE', $MODULE)}
                    <a class="text-primary" href="index.php?module=Installer&view=Index&app=TOOLS" target="_blank" rel="noopener noreferrer">
                        {vtranslate('LBL_PREMIUM_MODAL_INSTALLER_LINK', $MODULE)}
                    </a>
                </p>
            </div>
        </div>
    </div>
{/strip}