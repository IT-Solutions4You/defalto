{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modal-lg modal-dialog-scrollable installerLicenseModal">
    <form id="editLicenseForm" method="post" class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=vtranslate('LBL_LICENSE', $QUALIFIED_MODULE)}
        <div class="modal-body">
            <div class="mb-4">
                <label for="installerLicenseKey" class="form-label fw-semibold">{vtranslate('LBL_LICENSE_KEY', $QUALIFIED_MODULE)}</label>
                <input name="module" type="hidden" value="Installer">
                <input name="action" type="hidden" value="IndexAjax">
                <input name="mode" type="hidden" value="licenseSave">
                <input name="license_id" type="hidden" value="{$LICENSE_MODEL->getId()|escape:'html'}">
                <input id="installerLicenseKey" name="license_name" type="text" class="form-control" value="{$LICENSE_MODEL->getName()|escape:'html'}" required autocomplete="off" spellcheck="false">
            </div>
            {assign var=LICENSE_ERROR value=$LICENSE_MODEL->getErrorMessage()}
            <section class="licenseErrorHelp {if !$LICENSE_ERROR}d-none{/if}" aria-live="polite" aria-atomic="true" tabindex="-1">
                <div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
                    <i class="fa-solid fa-triangle-exclamation fs-4 mt-1" aria-hidden="true"></i>
                    <div>
                        <h6 class="fw-bold mb-1">{vtranslate('LBL_LICENSE_NOT_ACTIVATED', $MODULE)|escape:'html'}</h6>
                        <p class="licenseErrorMessage mb-0">{$LICENSE_ERROR|escape:'html'}</p>
                    </div>
                </div>
                <h6 class="fw-bold mb-3">{vtranslate('LBL_HOW_TO', $MODULE)|escape:'html'}</h6>
                <ol class="licenseErrorSteps list-unstyled mb-0">
                    {if $LICENSE_ERROR}
                        {foreach from=$LICENSE_MODEL->getErrorHelp() item=LICENSE_HELP_STEP}
                            <li class="mb-2">
                                <details class="licenseHelpStep py-1" {if $LICENSE_HELP_STEP@first}open{/if}>
                                    <summary class="d-flex align-items-center gap-3 py-2">
                                        <span class="badge rounded-pill {if $LICENSE_HELP_STEP@first}bg-primary{else}bg-light text-secondary{/if}">{$LICENSE_HELP_STEP@iteration}</span>
                                        <span class="fw-semibold">{$LICENSE_HELP_STEP.title|escape:'html'}</span>
                                        <i class="licenseHelpChevron fa-solid fa-chevron-down ms-auto text-secondary" aria-hidden="true"></i>
                                    </summary>
                                    <p class="text-secondary mt-2 mb-2">{$LICENSE_HELP_STEP.description|escape:'html'}</p>
                                </details>
                            </li>
                        {/foreach}
                    {/if}
                </ol>
            </section>
        </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE BUTTON_NAME=vtranslate('LBL_ACTIVATE_KEY', $MODULE)}
    </form>
</div>
