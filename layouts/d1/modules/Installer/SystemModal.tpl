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
            <h5 class="modal-title">{vtranslate('LBL_UPDATE_CRM', $MODULE)}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body h-50vh overflow-auto">
            <div class="downloadInfoContainer">
                <section class="mb-4 border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <h5 class="mb-0">{vtranslate('LBL_CHANGELOG_INFORMATION', $MODULE)}</h5>
                        <span class="badge bg-primary">{$SYSTEM_INSTALL->getVersion()|escape}</span>
                    </div>
                    {if $SYSTEM_CHANGELOG.status eq 'available'}
                        <div class="text-break">{$SYSTEM_CHANGELOG.html}</div>
                    {elseif $SYSTEM_CHANGELOG.status eq 'unavailable'}
                        <div class="alert alert-warning mb-0" role="status">{vtranslate('LBL_SYSTEM_CHANGELOG_UNAVAILABLE', $MODULE)}</div>
                    {else}
                        <div class="alert alert-info mb-0" role="status">{vtranslate('LBL_MISSING_CHANGELOG', $MODULE)}</div>
                    {/if}
                </section>
                <h5>{vtranslate('LBL_SYSTEM_DOWNLOAD_INFO', $MODULE)}</h5>
                <p>{vtranslate('LBL_SYSTEM_DOWNLOAD_INFO_DESC', $MODULE)}</p>
                <div class="form-check mb-3">
                    <input class="form-check-input updateValidation" type="checkbox" value="" id="backupDatabaseCheckbox">
                    <label class="form-check-label" for="backupDatabaseCheckbox">{vtranslate('LBL_BACKUP_DATABASE', $MODULE)}</label>
                    <a class="text-primary" target="_blank" href="index.php?module=Installer&view=Redirect&mode=Migration">({vtranslate('LBL_HOW_TO', $MODULE)})</a>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input updateValidation" type="checkbox" value="" id="backupSourceCheckbox">
                    <label class="form-check-label" for="backupSourceCheckbox">{vtranslate('LBL_BACKUP_SOURCE', $MODULE)}</label>
                    <a class="text-primary" target="_blank" href="index.php?module=Installer&view=Redirect&mode=Migration">({vtranslate('LBL_HOW_TO', $MODULE)})</a>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input updateValidation" type="checkbox" value="" id="requirementsCheckbox">
                    <label class="form-check-label" for="requirementsCheckbox">{vtranslate('LBL_CHECK_REQUIREMENTS', $MODULE)}</label>
                    <a class="text-primary" target="_blank" href="index.php?module=Installer&view=Redirect&mode=Requirements">({vtranslate('LBL_HOW_TO', $MODULE)})</a>
                </div>
            </div>
            <div class="downloadLogContainer hide">
                <h5>{vtranslate('LBL_DOWNLOAD_LOG', $MODULE)}</h5>
                <div class="downloadLog" data-download-log="{$SYSTEM_INSTALL->getDownloadUrl()}">
                    <div class="text-center my-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="downloadSystem btn btn-primary active">{vtranslate('LBL_UPDATE_CRM', $MODULE)}</button>
            <a href="/index.php?module=Installer" class="finish btn btn-primary active hide">{vtranslate('LBL_FINISH', $MODULE)}</a>
            <a href="/index.php?module=Installer" class="close btn btn-primary">{vtranslate('LBL_CLOSE', $MODULE)}</a>
        </div>
    </div>
</div>
