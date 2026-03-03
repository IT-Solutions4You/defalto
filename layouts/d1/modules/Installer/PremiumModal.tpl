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
            {include file='PremiumModalHeader.tpl'|@vtemplate_path:$MODULE}
            <div class="modal-body px-4 py-3">
                <p class="mb-3"><strong>{vtranslate($FOR_MODULE, $FOR_MODULE)}</strong> {vtranslate('LBL_PREMIUM_MODAL_SUBTITLE', $FOR_MODULE)}</p>
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
            {include file='PremiumModalFooter.tpl'|@vtemplate_path:$MODULE}
        </div>
    </div>
{/strip}