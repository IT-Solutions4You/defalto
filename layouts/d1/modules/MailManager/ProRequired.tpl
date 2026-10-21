{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {* the Pro offer opens on the button only, not on its own (Installer PremiumModal.js) *}
    <input type="hidden" id="showMailManagerLicenseAlert" value="0">
    <div class="px-4 pb-4 pt-3" id="mmProRequired">
        <div class="bg-body rounded p-4">
            <h4 class="mb-3"><i class="fa-solid fa-envelopes-bulk me-2"></i>{vtranslate('LBL_MAIL_MANAGER_PRO', 'MailManager')}</h4>
            <p class="mb-3">{vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager')}</p>
            {if $PREMIUM_ITEMS}
                <ul class="list-unstyled mb-4">
                    {foreach item=PREMIUM_ITEM from=$PREMIUM_ITEMS}
                        <li class="d-flex gap-2 mb-2"><i class="fa-solid fa-check text-success mt-1"></i><span>{$PREMIUM_ITEM}</span></li>
                    {/foreach}
                </ul>
            {/if}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary active mmProButton">{vtranslate('LBL_PRO_BUTTON_TITLE', 'MailManager')}</button>
                {if $INSTALLER_URL}
                    <a class="btn btn-outline-secondary" href="{$INSTALLER_URL}">{vtranslate('Installer', 'Installer')}</a>
                {/if}
                {if $STORED_MESSAGES}
                    <a class="btn btn-outline-danger" href="{$PURGE_URL}">{vtranslate('LBL_PRO_STORED_DATA', 'MailManager')}</a>
                {/if}
            </div>
        </div>
    </div>
{/strip}
