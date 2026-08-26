{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{vtranslate('LBL_CHECK_LICENSE', $MODULE)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body h-50vh overflow-auto">
            <h5>{vtranslate('LBL_LICENSE_CHECK_LOG', $MODULE)}</h5>
            <div class="licenseCheckLog">
                <div class="alert alert-info">{vtranslate('LBL_LICENSE_CHECK_READY', $MODULE)}</div>
                {foreach from=$LICENSE_MODELS item=LICENSE_MODEL}
                    <div class="row py-2 border-bottom">
                        <div class="col-lg-5 fw-bold text-secondary">{$LICENSE_MODEL->getItemName()}:</div>
                        <div class="col-lg">
                            {if $LICENSE_MODEL->isValidLicense()}
                                <span class="text-success">{vtranslate('LBL_LICENSE_ACTIVE', $MODULE)}</span>
                            {else}
                                <span class="text-danger">{vtranslate('LBL_LICENSE_INACTIVE', $MODULE)}</span>
                            {/if}
                            <div class="small text-secondary">{$LICENSE_MODEL->getDisplayLastSuccessfulCheck()}</div>
                            {if $LICENSE_MODEL->getErrorMessage()}
                                <div class="small text-danger">{$LICENSE_MODEL->getErrorMessage()}</div>
                            {/if}
                        </div>
                    </div>
                {foreachelse}
                    <div class="alert alert-warning mt-3 mb-0">{vtranslate('LBL_NO_LICENSE_KEY', $MODULE)}</div>
                {/foreach}
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="licenseCheckContinue btn btn-primary">
                {vtranslate('LBL_LICENSE_CHECK_CONTINUE', $MODULE)}
            </button>
            <a href="index.php?module=Installer&view=Index" class="licenseCheckFinish btn btn-primary hide">
                {vtranslate('LBL_FINISH', $MODULE)}
            </a>
            <button type="button" class="licenseCheckClose btn btn-outline-secondary" data-bs-dismiss="modal">
                {vtranslate('LBL_CLOSE', $MODULE)}
            </button>
        </div>
    </div>
</div>
