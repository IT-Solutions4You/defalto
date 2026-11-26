{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="modal-dialog aiAvailabilityDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="aiAvailabilityTitle" class="modal-title fs-5">{vtranslate('LBL_AI_SETUP', 'Core')}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{vtranslate('LBL_CLOSE', 'Core')}"></button>
            </div>
            <div class="modal-body">
                <ol class="list-group list-group-flush mb-3">
                    {foreach from=$AI_STATUS.steps key=STEP item=COMPLETE}
                        <li class="list-group-item d-flex align-items-start gap-2 px-0">
                            {if $COMPLETE}
                                <i class="bi bi-check-square-fill text-success" aria-hidden="true"></i>
                            {else}
                                <i class="bi bi-square text-secondary" aria-hidden="true"></i>
                            {/if}
                            <div>
                                <div class="fw-semibold">{vtranslate($STEP, 'Core')}</div>
                                <div class="small text-body-secondary">{vtranslate($STEP|cat:'_DESCRIPTION', 'Core')}</div>
                            </div>
                            <span class="ms-auto text-body-secondary">{if $COMPLETE}{vtranslate('LBL_YES', 'Core')}{else}{vtranslate('LBL_NO', 'Core')}{/if}</span>
                        </li>
                    {/foreach}
                </ol>
                <div class="alert alert-info mb-0">
                    {vtranslate($AI_STATUS.next, 'Core')}
                    {if $AI_STATUS.contactAdmin}
                        <div>{vtranslate('LBL_AI_CONTACT_ADMIN', 'Core')}</div>
                    {/if}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{vtranslate('LBL_CLOSE', 'Core')}</button>
                {if $AI_STATUS.action === 'license'}
                    <a class="btn btn-primary" href="index.php?module=Installer&amp;view=Index#UpdateLicense">{vtranslate('LBL_LICENSE_MANAGE', 'Installer')}</a>
                {elseif $AI_STATUS.action === 'install'}
                    <a class="btn btn-primary" href="index.php?module=Installer&amp;view=Index#UpdateAI">{vtranslate('LBL_INSTALLER', 'Installer')}</a>
                {elseif $AI_STATUS.action === 'enable'}
                    <a class="btn btn-primary" href="index.php?module=ModuleManager&amp;parent=Settings&amp;view=List">{vtranslate('ModuleManager', 'Settings:ModuleManager')}</a>
                {elseif $AI_STATUS.action === 'settings'}
                    <button type="button" class="btn btn-primary" data-ai-setup-action="showSetting">{vtranslate('LBL_CONFIGURE', 'Core')}</button>
                {elseif $AI_STATUS.action === 'chat'}
                    <button type="button" class="btn btn-primary" data-ai-setup-action="showChat" data-comment-prompt="{vtranslate('LBL_CREATE_OR_MODIFY_COMMENT', 'AI')|escape:'html'}">{vtranslate('LBL_AI_OPEN', 'Core')}</button>
                {/if}
            </div>
        </div>
    </div>
{/strip}
