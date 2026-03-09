{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
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
{/strip}