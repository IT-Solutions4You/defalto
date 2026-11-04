{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="text-body">
        <div class="sectionTagContainer"><div class="sectionTag" id="UpdateLicense"></div></div>
        <section class="bg-body border rounded mb-3" aria-labelledby="installerLicensesHeading">
            <div class="px-3 py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h4 id="installerLicensesHeading" class="m-0 fw-bold">{vtranslate('LBL_LICENSES', $QUALIFIED_MODULE)}</h4>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn fw-bold btn-outline-primary bg-white" data-edit-license=""><i class="fa-solid fa-plus me-2" aria-hidden="true"></i><span>{vtranslate('LBL_ADD_LICENSE', $QUALIFIED_MODULE)}</span></button>
                    <a class="btn fw-bold btn-outline-secondary" data-license-check href="#"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i><span>{vtranslate('LBL_CHECK_LICENSE', $QUALIFIED_MODULE)}</span></a>
                </div>
            </div>
            <div class="overflow-visible">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="table-light text-nowrap"><tr>
                        <th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_PRODUCT', $QUALIFIED_MODULE)}</th>
                        <th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_LICENSE_KEY', $QUALIFIED_MODULE)}</th>
                        <th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}</th>
                        <th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_VALID_UNTIL', $QUALIFIED_MODULE)}</th>
                        <th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</th>
                        <th scope="col" class="fs-6 fw-bold text-body-secondary px-3 py-2 text-start">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                    </tr></thead>
                    <tbody>
                        {foreach from=$LICENSE_MODELS item=STORED_LICENSE_MODEL}
                            {assign var=LICENSE_STATUS value=$STORED_LICENSE_MODEL->getDisplayStatus()}
                            {assign var=LICENSE_ERROR_MESSAGE value=$STORED_LICENSE_MODEL->getErrorMessage()}
                            {assign var=LICENSE_EXTENSIONS value=$STORED_LICENSE_MODEL->getExtensionEntitlements()}
                            <tr class="licenseContainer border-top">
                                <td class="px-3 py-2 fw-semibold">{$STORED_LICENSE_MODEL->getItemName()}</td>
                                <td class="px-3 py-2"><span class="text-nowrap font-monospace" data-license-key-display data-masked-key="{$STORED_LICENSE_MODEL->getMaskedName()|escape:'html'}" data-full-key="{$STORED_LICENSE_MODEL->getName()|escape:'html'}">{$STORED_LICENSE_MODEL->getMaskedName()}</span></td>
                                <td class="px-3 py-2 text-nowrap">
                                    {if !$STORED_LICENSE_MODEL->isUserUsageApplicable()}
                                        <span class="text-secondary">{vtranslate('LBL_NOT_APPLICABLE', $QUALIFIED_MODULE)}</span>
                                    {elseif !$STORED_LICENSE_MODEL->isUsersLimitAvailable()}
                                        <span class="text-secondary">{vtranslate('LBL_USER_COUNT_UNAVAILABLE', $QUALIFIED_MODULE)}</span>
                                    {else}
                                        <div class="fw-semibold text-nowrap">{$STORED_LICENSE_MODEL->getUsersCount()} {vtranslate('LBL_ACTIVE_USERS_SHORT', $QUALIFIED_MODULE)} / {if $STORED_LICENSE_MODEL->getUsersLimit() > 0}{$STORED_LICENSE_MODEL->getUsersLimit()} {vtranslate('LBL_PURCHASED_USERS_SHORT', $QUALIFIED_MODULE)}{else}{vtranslate('Unlimited', $QUALIFIED_MODULE)}{/if}</div>
                                        {if $STORED_LICENSE_MODEL->getUsersLimit() <= 0}
                                            <div class="small text-secondary">{vtranslate('LBL_ACTIVE_CRM_USERS_EXPLANATION', $QUALIFIED_MODULE)} · {vtranslate('LBL_NO_SEAT_LIMIT', $QUALIFIED_MODULE)}</div>
                                        {elseif $STORED_LICENSE_MODEL->isUserLimitReached()}
                                            <div class="small text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>{vtranslate('LBL_USER_LIMIT_EXCEEDED_BY', $QUALIFIED_MODULE, $STORED_LICENSE_MODEL->getExceededUsersCount())}</div>
                                        {elseif $STORED_LICENSE_MODEL->getAvailableSeats() == 0}
                                            <div class="small text-secondary">{vtranslate('LBL_ACTIVE_CRM_USERS_EXPLANATION', $QUALIFIED_MODULE)} · {vtranslate('LBL_NO_SEATS_AVAILABLE', $QUALIFIED_MODULE)}</div>
                                        {else}
                                            <div class="small text-secondary">{vtranslate('LBL_ACTIVE_CRM_USERS_EXPLANATION', $QUALIFIED_MODULE)} · {vtranslate('LBL_SEATS_AVAILABLE', $QUALIFIED_MODULE, $STORED_LICENSE_MODEL->getAvailableSeats())}</div>
                                        {/if}
                                    {/if}
                                </td>
                                <td class="px-3 py-2">{if !$STORED_LICENSE_MODEL->hasExpireDate()}<span class="text-secondary">{vtranslate('LBL_UNAVAILABLE', $QUALIFIED_MODULE)}</span>{elseif $STORED_LICENSE_MODEL->isLifetimeLicense()}{vtranslate('LBL_NO_EXPIRY', $QUALIFIED_MODULE)}{else}{$STORED_LICENSE_MODEL->getDisplayExpireDate()}{/if}</td>
                                <td class="px-3 py-2">
                                    <div class="dropdown">
                                        {if 'active' === $LICENSE_STATUS}
                                            <button type="button" class="badge fs-6 fw-bold px-3 py-2 bg-success-subtle text-success-emphasis border border-success-subtle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_LICENSE_DETAILS', $QUALIFIED_MODULE)}"><i class="fa-solid fa-check me-2" aria-hidden="true"></i>{vtranslate('LBL_LICENSE_ACTIVE_STATUS', $QUALIFIED_MODULE)}</button>
                                        {elseif 'expired' === $LICENSE_STATUS}
                                            <button type="button" class="badge fs-6 fw-bold px-3 py-2 bg-danger-subtle text-danger-emphasis border border-danger-subtle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_LICENSE_DETAILS', $QUALIFIED_MODULE)}"><i class="fa-solid fa-calendar-xmark me-2" aria-hidden="true"></i>{vtranslate('LBL_LICENSE_EXPIRED_STATUS', $QUALIFIED_MODULE)}</button>
                                        {elseif 'verification_unavailable' === $LICENSE_STATUS}
                                            <button type="button" class="badge fs-6 fw-bold px-3 py-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_LICENSE_DETAILS', $QUALIFIED_MODULE)}"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>{vtranslate('LBL_LICENSE_VERIFICATION_UNAVAILABLE', $QUALIFIED_MODULE)}</button>
                                        {else}
                                            <button type="button" class="badge fs-6 fw-bold px-3 py-2 bg-body-secondary text-body-secondary border-0" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_LICENSE_DETAILS', $QUALIFIED_MODULE)}"><i class="fa-solid fa-pause me-2" aria-hidden="true"></i>{vtranslate('LBL_LICENSE_INACTIVE_STATUS', $QUALIFIED_MODULE)}</button>
                                        {/if}
                                        <div class="dropdown-menu dropdown-menu-end p-3 text-wrap licenseDetailsDropdown">
                                            <div class="mb-3"><div class="fw-bold">{vtranslate('LBL_LAST_SUCCESSFUL_LICENSE_CHECK', $QUALIFIED_MODULE)}</div><div>{$STORED_LICENSE_MODEL->getDisplayLastSuccessfulCheck()}</div></div>
                                            {if $LICENSE_ERROR_MESSAGE}<div class="mb-3"><div class="fw-bold">{vtranslate('LBL_LICENSE_ERROR', $QUALIFIED_MODULE)}</div><div class="text-danger">{$LICENSE_ERROR_MESSAGE}</div></div>{else}<div class="mb-3 text-success"><i class="fa-solid fa-check me-2" aria-hidden="true"></i>{vtranslate('LBL_LICENSE_ACTIVATED', $QUALIFIED_MODULE)}</div>{/if}
                                            <div><div class="fw-bold">{vtranslate('LBL_ACTIVATED_MODULES', $QUALIFIED_MODULE)}</div>{if $LICENSE_EXTENSIONS}<ul class="mb-0 ps-3">{foreach from=$LICENSE_EXTENSIONS item=LICENSE_EXTENSION}<li>{vtranslate($LICENSE_EXTENSION, $LICENSE_EXTENSION)}</li>{/foreach}</ul>{else}<div class="text-secondary">{vtranslate('LBL_NO_ACTIVATED_MODULES', $QUALIFIED_MODULE)}</div>{/if}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    <div class="d-flex align-items-center justify-content-start gap-2">
                                        <div class="dropdown">
                                            <button type="button" class="btn fw-bold btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-gear me-2" aria-hidden="true"></i>{vtranslate('LBL_MANAGE', $QUALIFIED_MODULE)}</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><button type="button" class="dropdown-item" data-toggle-license-key data-show-label="{vtranslate('LBL_SHOW_LICENSE_KEY', $QUALIFIED_MODULE)|escape:'html'}" data-hide-label="{vtranslate('LBL_HIDE_LICENSE_KEY', $QUALIFIED_MODULE)|escape:'html'}"><i class="fa-solid fa-eye me-2" aria-hidden="true"></i><span>{vtranslate('LBL_SHOW_LICENSE_KEY', $QUALIFIED_MODULE)}</span></button></li>
                                                <li><button type="button" class="dropdown-item" data-copy-license-key><i class="fa-solid fa-copy me-2" aria-hidden="true"></i>{vtranslate('LBL_COPY_LICENSE_KEY', $QUALIFIED_MODULE)}</button></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><button type="button" class="dropdown-item" data-edit-license="{$STORED_LICENSE_MODEL->getId()}"><i class="fa-solid fa-rotate me-2" aria-hidden="true"></i>{vtranslate('LBL_REACTIVATE', $QUALIFIED_MODULE)}<span class="d-block small text-secondary ms-4">{vtranslate('LBL_REACTIVATE_EXPLANATION', $QUALIFIED_MODULE)}</span></button></li>
                                                <li><button type="button" class="dropdown-item text-danger" data-delete-license="{$STORED_LICENSE_MODEL->getId()}"><i class="fa-solid fa-trash me-2" aria-hidden="true"></i>{vtranslate('LBL_REMOVE_LICENSE', $QUALIFIED_MODULE)}</button></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr class="border-top"><td colspan="6" class="px-3 py-3 text-secondary">{vtranslate('LBL_NO_LICENSE_KEY', $QUALIFIED_MODULE)}</td></tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </section>

        <div class="sectionTagContainer"><div class="sectionTag" id="UpdateDefalto"></div></div>
        <section class="bg-body border rounded mb-3" aria-labelledby="installerSystemHeading">
            <div class="px-3 py-2 border-bottom"><h4 id="installerSystemHeading" class="m-0 fw-bold">{vtranslate('LBL_SYSTEM', $QUALIFIED_MODULE)}</h4></div>
            {foreach from=$SYSTEM_MODELS item=SYSTEM_MODEL}
                <div class="px-3 py-3 systemUpdateContainer d-flex flex-wrap align-items-center gap-3">
                    <div class="fs-2 lh-1 {if $SYSTEM_MODEL->isNewestVersion()}text-success{else}text-primary{/if}"><i class="fa-solid {if $SYSTEM_MODEL->isNewestVersion()}fa-circle-check{else}fa-circle-up{/if}" aria-hidden="true"></i></div>
                    <div class="me-auto">
                        <div class="d-flex flex-wrap align-items-center gap-2"><span class="fs-5 fw-bold">Defalto v{$SYSTEM_MODEL->getCurrentVersion()}</span>{if Installer_License_Model::isMembershipActive()}<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">{vtranslate('LBL_MEMBERSHIP_ACTIVE', $QUALIFIED_MODULE)}</span>{else}<span class="badge bg-body-secondary text-body-secondary border">{vtranslate('LBL_MEMBERSHIP_INACTIVE', $QUALIFIED_MODULE)}</span>{/if}</div>
                        <div class="systemUpdatedState">{if $SYSTEM_MODEL->isNewestVersion()}<span class="text-secondary">{vtranslate('LBL_SYSTEM_UP_TO_DATE', $QUALIFIED_MODULE)}</span>{else}<span class="fw-semibold">{vtranslate('LBL_CHECK_UPDATE_AVAILABLE', $MODULE)} {$SYSTEM_MODEL->getLabel()}</span>{/if}</div>
                        <div class="systemUpdatedState hide text-secondary"><i class="fa-solid fa-rotate fa-spin me-2" aria-hidden="true"></i>{vtranslate('LBL_CHECK_LOADING', $MODULE)}...</div>
                    </div>
                    <div class="small text-secondary text-nowrap">{vtranslate('LBL_LAST_CHECK', $MODULE)}: {if $SYSTEM_MODEL->getCacheDate()}{$SYSTEM_MODEL->getCacheDate()}{else}{vtranslate('LBL_NOT_CHECKED_YET', $QUALIFIED_MODULE)}{/if}</div>
                    <div class="col-12 col-lg-auto text-start text-lg-end">
                        {if $SYSTEM_MODEL->isNewestVersion()}
                            {if $SYSTEM_MODEL->isCacheRefreshAllowed()}<a class="btn fw-bold btn-outline-primary" data-update-information="system" href="index.php?module=Installer&view=IndexAjax&mode=updateInformation"><i class="fa-solid fa-arrows-rotate me-2" aria-hidden="true"></i>{vtranslate('LBL_CHECK_FOR_UPDATES', $MODULE)}</a>{/if}
                        {elseif $SYSTEM_MODEL->hasDownloadUrl()}
                            <button type="button" class="btn fw-bold active btn-success bg-success text-white border-success" data-download-system="{$SYSTEM_MODEL->getVersion()}"><i class="fa-solid fa-download me-2" aria-hidden="true"></i>{vtranslate('LBL_UPDATE', $QUALIFIED_MODULE)}</button>
                        {else}
                            <a class="btn fw-bold btn-primary" target="_blank" rel="noopener noreferrer" href="index.php?module=Installer&view=Redirect&mode=SourceForge"><i class="fa-solid fa-download me-2" aria-hidden="true"></i>{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</a>
                        {/if}
                        {if !Installer_License_Model::isMembershipActive()}<div class="pt-2 small">{$SYSTEM_MODEL->getBranding()}</div>{/if}
                    </div>
                </div>
            {/foreach}
        </section>

        <section class="bg-body border rounded" aria-labelledby="installerModulesHeading">
            <div class="px-3 py-2 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <h4 id="installerModulesHeading" class="m-0 fw-bold">{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)} <span class="badge rounded-pill bg-body-secondary text-body-secondary align-middle">{$EXTENSION_COUNTS.all}</span></h4>
                <div class="row g-3 align-items-center flex-grow-1 justify-content-lg-end">
                    <div class="col-12 col-lg-auto"><div class="input-group"><span class="input-group-text bg-transparent"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span><input type="search" class="form-control border-start-0" data-module-search placeholder="{vtranslate('LBL_SEARCH_MODULES_PLACEHOLDER', $QUALIFIED_MODULE)}" aria-label="{vtranslate('LBL_SEARCH_MODULES_PLACEHOLDER', $QUALIFIED_MODULE)}"></div></div>
                    <div class="col-12 col-lg-auto" role="group" aria-label="{vtranslate('LBL_MODULE_FILTERS', $QUALIFIED_MODULE)}"><div class="nav nav-underline flex-nowrap text-nowrap overflow-x-auto">
                        <button type="button" class="nav-link active text-primary" data-module-filter="all" aria-pressed="true">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)} ({$EXTENSION_COUNTS.all})</button>
                        <button type="button" class="nav-link text-body-secondary" data-module-filter="updates" aria-pressed="false">{vtranslate('LBL_UPDATES', $QUALIFIED_MODULE)} ({$EXTENSION_COUNTS.updates})</button>
                        <button type="button" class="nav-link text-body-secondary" data-module-filter="not-installed" aria-pressed="false">{vtranslate('LBL_NOT_INSTALLED', $QUALIFIED_MODULE)} ({$EXTENSION_COUNTS.not_installed})</button>
                    </div></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="table-light text-nowrap"><tr><th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}</th><th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_INSTALLED_VERSION', $QUALIFIED_MODULE)}</th><th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_AVAILABLE_VERSION', $QUALIFIED_MODULE)}</th><th class="fs-6 fw-bold text-body-secondary px-3 py-2" scope="col">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE)}</th><th scope="col" class="fs-6 fw-bold text-body-secondary px-3 py-2 text-start">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th></tr></thead>
                    <tbody data-module-list>
                        {foreach from=$EXTENSION_MODELS item=EXTENSION_MODEL}
                            {assign var=EXTENSION_NAME value=$EXTENSION_MODEL->getName()}
                            {assign var=EXTENSION_LABEL value=vtranslate($EXTENSION_NAME, $EXTENSION_NAME)}
                            {assign var=EXTENSION_STATUS value=$EXTENSION_MODEL->getInstallerStatus()}
                            {assign var=EXTENSION_LINKS value=$EXTENSION_MODEL->getLinks()}
                            <tr class="border-top" data-module-row data-module-name="{$EXTENSION_MODEL->getLabel()|escape:'html'}" data-module-update="{if $EXTENSION_MODEL->hasAvailableUpdate()}1{else}0{/if}" data-module-installed="{if $EXTENSION_MODEL->isInstalled()}1{else}0{/if}">
                                <td class="px-3 py-2"><div class="sectionTagContainer"><div class="sectionTag" id="Update{$EXTENSION_NAME}"></div></div>{if $EXTENSION_MODEL->isInstalled()}<a class="fw-semibold" href="{$EXTENSION_MODEL->getDefaultUrl()}">{$EXTENSION_MODEL->getLabel()}</a>{else}<span class="fw-semibold">{$EXTENSION_MODEL->getLabel()}</span>{/if}</td>
                                <td class="px-3 py-2">{if $EXTENSION_MODEL->isInstalled()}{$EXTENSION_MODEL->getVersion()}{else}<span class="text-secondary">{vtranslate('LBL_NOT_INSTALLED', $QUALIFIED_MODULE)}</span>{/if}</td>
                                <td class="px-3 py-2">{if $EXTENSION_MODEL->getUpdateVersion()}{$EXTENSION_MODEL->getUpdateVersion()}{else}<span class="text-secondary">—</span>{/if}</td>
                                <td class="px-3 py-2">{if 'update_available' === $EXTENSION_STATUS}<span class="badge fs-6 fw-bold px-3 py-2 bg-success-subtle text-success-emphasis border border-success-subtle"><i class="fa-solid fa-arrow-up me-2" aria-hidden="true"></i>{vtranslate('LBL_UPDATE_AVAILABLE', $QUALIFIED_MODULE)}</span>{elseif 'not_installed' === $EXTENSION_STATUS}<span class="badge fs-6 fw-bold px-3 py-2 bg-primary-subtle text-primary-emphasis border border-primary-subtle"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{vtranslate('LBL_NOT_INSTALLED', $QUALIFIED_MODULE)}</span>{elseif 'up_to_date' === $EXTENSION_STATUS}<span class="badge fs-6 fw-bold px-3 py-2 bg-body-secondary text-body-secondary"><i class="fa-solid fa-check me-2" aria-hidden="true"></i>{vtranslate('LBL_MODULE_UP_TO_DATE', $QUALIFIED_MODULE)}</span>{else}<span class="badge fs-6 fw-bold px-3 py-2 bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i>{vtranslate('LBL_UNAVAILABLE', $QUALIFIED_MODULE)}</span>{/if}</td>
                                <td class="px-3 py-2">
                                    <div class="row g-2 align-items-center flex-nowrap">
                                        <div class="col-6">{if $EXTENSION_MODEL->isInstallAvailable()}<button type="button" class="btn fw-bold active {if $EXTENSION_MODEL->isInstalled()}btn-success bg-success text-white border-success{else}btn-primary{/if}" data-download-extension="{$EXTENSION_MODEL->getName()}"><i class="fa-solid {if $EXTENSION_MODEL->isInstalled()}fa-download{else}fa-plus{/if} me-2" aria-hidden="true"></i>{vtranslate($EXTENSION_MODEL->getDownloadLabel(), $QUALIFIED_MODULE)}</button>{elseif 'unavailable' === $EXTENSION_STATUS}<span class="small text-secondary">{vtranslate('LBL_LICENSE_DOWNLOAD_UNAVAILABLE', $QUALIFIED_MODULE)}</span>{/if}</div>
                                        <div class="col-6 text-end">{if $EXTENSION_LINKS && $EXTENSION_MODEL->isInstalled()}<div class="dropdown"><button class="btn fw-bold btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-gear me-2" aria-hidden="true"></i>{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</button><ul class="dropdown-menu dropdown-menu-end">{foreach from=$EXTENSION_LINKS item=LINK}<li><a class="dropdown-item" href="{$LINK->getUrl()}">{vtranslate($LINK->getLabel(), $EXTENSION_NAME, $EXTENSION_LABEL)}</a></li>{/foreach}</ul></div>{/if}</div>
                                    </div>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr class="border-top"><td colspan="5" class="px-3 py-3 text-secondary">{vtranslate('LBL_NO_MODULE_UPDATES', $QUALIFIED_MODULE)}</td></tr>
                        {/foreach}
                        <tr class="d-none border-top" data-module-empty><td colspan="5" class="px-3 py-4 text-center"><div class="text-secondary mb-2">{vtranslate('LBL_NO_MODULES_MATCH', $QUALIFIED_MODULE)}</div><button type="button" class="btn fw-bold btn-link" data-clear-module-search>{vtranslate('LBL_CLEAR_SEARCH', $QUALIFIED_MODULE)}</button></td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
{/strip}
