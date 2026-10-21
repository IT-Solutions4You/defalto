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
            <h5 class="modal-title">{vtranslate($EXTENSION_INSTALL->getDownloadLabel(), $QUALIFIED_MODULE)}: {$EXTENSION_INSTALL->getLabel()|escape}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body h-50vh overflow-auto">
            <div class="extensionChangelog">
                <h5 class="mb-3">{vtranslate('LBL_CHANGELOG_INFORMATION', $QUALIFIED_MODULE)}</h5>
                {if $EXTENSION_CHANGELOG}
                    {foreach item=CHANGELOG_ENTRY from=$EXTENSION_CHANGELOG name=extensionChangelog}
                        <section class="{if !$smarty.foreach.extensionChangelog.first}border-top pt-3{/if} mb-3">
                            <h6 class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary">{$CHANGELOG_ENTRY.version|escape}</span>
                                {if $CHANGELOG_ENTRY.date}<span class="text-body-secondary">{$CHANGELOG_ENTRY.date|escape}</span>{/if}
                            </h6>
                            <ul class="mb-0">
                                {foreach item=CHANGE from=$CHANGELOG_ENTRY.changes}
                                    <li>{$CHANGE|escape}</li>
                                {/foreach}
                            </ul>
                        </section>
                    {/foreach}
                {else}
                    <div class="alert alert-warning mb-0">{vtranslate('LBL_MISSING_CHANGELOG', $QUALIFIED_MODULE)}</div>
                {/if}
            </div>
            <div class="extensionInstallProgress hide">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary extensionInstallSpinner mb-3" role="status">
                        <span class="visually-hidden">{vtranslate('LBL_INSTALLATION_IN_PROGRESS', $QUALIFIED_MODULE)}</span>
                    </div>
                    <h5 class="mb-2" data-install-status>{vtranslate('LBL_INSTALLATION_IN_PROGRESS', $QUALIFIED_MODULE)}</h5>
                    <div class="text-body-secondary">
                        {$EXTENSION_INSTALL->getLabel()|escape}
                        {if $EXTENSION_INSTALL->getUpdateVersion()}
                            &middot; {vtranslate('LBL_VERSION', $QUALIFIED_MODULE)} {$EXTENSION_INSTALL->getUpdateVersion()|escape}
                        {/if}
                    </div>
                </div>
                <div class="progress mb-3" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar progress-bar-striped progress-bar-animated w-100"></div>
                </div>
                <details class="extensionInstallLogDetails">
                    <summary class="btn btn-sm btn-outline-secondary">{vtranslate('LBL_DOWNLOAD_LOG', $QUALIFIED_MODULE)}</summary>
                    <div class="downloadLog mt-3" data-download-log="{$EXTENSION_INSTALL->getDownloadUrl()}"></div>
                </details>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary extensionInstallClose" data-bs-dismiss="modal">{vtranslate('LBL_CLOSE', $QUALIFIED_MODULE)}</button>
            <button type="button" class="btn btn-primary extensionInstallStart">{vtranslate($EXTENSION_INSTALL->getDownloadLabel(), $QUALIFIED_MODULE)}</button>
            <a href="#" class="btn btn-primary extensionInstallFinish hide">{vtranslate('LBL_OPEN_MODULE', $QUALIFIED_MODULE)}</a>
        </div>
    </div>
</div>
