{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="">
        <div class="sectionTagContainer"><div class="sectionTag" id="UpdateLicense"></div></div>
        <div class="container-fluid">
            <div class="row py-2 align-items-center">
                <div class="col-lg">
                    <h4 class="m-0 text-secondary"><b>{vtranslate('LBL_LICENSES', $QUALIFIED_MODULE)}</b></h4>
                </div>
                <div class="col-lg-auto">
                    <button type="button" class="btn btn-primary" data-edit-license="">
                        <i class="fa-solid fa-plus"></i>
                        <span class="ms-2">{vtranslate('LBL_ADD_LICENSE', $QUALIFIED_MODULE)}</span>
                    </button>
                    <a class="btn btn-outline-primary ms-2" data-update-information="all" href="index.php?module=Installer&view=IndexAjax&mode=updateInformation">
                        {vtranslate('LBL_UPDATE_LICENSES', $QUALIFIED_MODULE)}
                    </a>
                </div>
            </div>
        </div>
        <div class="container-fluid border rounded">
            <div class="row py-2 text-secondary">
                <div class="col-lg-4"></div>
                <div class="col-lg fw-bold">{vtranslate('LBL_TYPE', $QUALIFIED_MODULE)}</div>
                <div class="col-lg fw-bold">{vtranslate('LBL_USER_LIMIT', $QUALIFIED_MODULE)}</div>
                <div class="col-lg fw-bold">{vtranslate('Due Date', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_License_Model::getAll() item=LICENSE_MODEL}
                <div class="row border-top py-2 licenseContainer align-items-center">
                    <div class="col-lg-4 fw-bold">
                        {if $LICENSE_MODEL->isValidLicense()}
                            <span class="me-2 text-success cursorDefault" title="{vtranslate('LBL_VALID_LICENSE', $QUALIFIED_MODULE)}">
                                <i class="fa-solid fa-check me-2"></i>
                                <span>{$LICENSE_MODEL->getName()}</span>
                            </span>
                        {else}
                            <span class="me-2 text-danger cursorDefault" title="{vtranslate('LBL_INVALID_LICENSE', $QUALIFIED_MODULE)}">
                                <i class="fa-solid fa-xmark me-2"></i>
                                <span>{$LICENSE_MODEL->getName()}</span>
                            </span>
                        {/if}
                    </div>
                    <div class="col-lg">{$LICENSE_MODEL->getItemName()}</div>
                    <div class="col-lg {if $LICENSE_MODEL->isUserLimitReached()}fw-bold text-danger{/if}">{$LICENSE_MODEL->getUsersCount()} / {$LICENSE_MODEL->getDisplayUsersLimit()}</div>
                    <div class="col-lg">{Vtiger_Functions::currentUserDisplayDate($LICENSE_MODEL->getExpireDate())}</div>
                    <div class="col-lg-4">
                        <button type="button" class="btn btn-primary me-2" data-edit-license="{$LICENSE_MODEL->getId()}">
                            <i class="fa-solid fa-pencil"></i>
                            <span class="ms-2">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</span>
                        </button>
                        <button type="button" class="btn btn-danger" data-delete-license="{$LICENSE_MODEL->getId()}">
                            <i class="fa-solid fa-trash"></i>
                            <span class="ms-2">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}</span>
                        </button>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="sectionTagContainer"><div class="sectionTag" id="UpdateDefalto"></div></div>
        <div class="container-fluid">
            <div class="row py-2 pt-3">
                <div class="col">
                    <h4 class="m-0 text-secondary"><b>{vtranslate('LBL_SYSTEM', $QUALIFIED_MODULE)}</b></h4>
                </div>
            </div>
        </div>
        <div class="container-fluid rounded border">
            <div class="row py-2 align-items-center">
                <div class="col-lg fw-bold">
                    {if Installer_License_Model::isMembershipActive()}
                        <span class="bg-success text-white px-2 p-1 rounded">{vtranslate('LBL_MEMBERSHIP_ACTIVE', $QUALIFIED_MODULE)}</span>
                    {else}
                        <span class="bg-danger text-white px-2 p-1 rounded">{vtranslate('LBL_MEMBERSHIP_INACTIVE', $QUALIFIED_MODULE)}</span>
                    {/if}
                </div>
                <div class="col-lg-4 fw-bold text-secondary">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_SystemInstall_Model::getAll() item=SYSTEM_MODEL}
                <div class="row border-top py-2 align-items-center systemUpdateContainer">
                    <div class="col-lg">
                        <div class="fs-4">Defalto v{$SYSTEM_MODEL->getCurrentVersion()}</div>
                        <div>
                            <div class="systemUpdatedState row align-items-center">
                                <div class="col-auto fs-1">
                                    {if $SYSTEM_MODEL->isNewestVersion()}
                                        <i class="fa-solid fa-check text-success"></i>
                                    {else}
                                        <i class="fa-solid fa-rotate text-primary"></i>
                                    {/if}
                                </div>
                                <div class="col-6">
                                    <div>
                                        {if $SYSTEM_MODEL->isNewestVersion()}
                                            <span class="text-success">{vtranslate('LBL_UP_TO_DATE', 'Installer')}</span>
                                        {else}
                                            {vtranslate('LBL_CHECK_UPDATE_AVAILABLE', $MODULE)} {$SYSTEM_MODEL->getLabel()}
                                        {/if}
                                    </div>
                                    <div class="text-secondary small">{vtranslate('LBL_LAST_CHECK', $MODULE)}: {$SYSTEM_MODEL->getCacheDate()}</div>
                                </div>
                            </div>
                            <div class="systemUpdatedState hide row align-items-center">
                                <div class="col-auto fs-1 text-primary">
                                    <i class="fa-solid fa-rotate fa-spin"></i>
                                </div>
                                <div class="col-6">
                                    <div>{vtranslate('LBL_CHECK_UPDATING', $MODULE)}</div>
                                    <div class="text-secondary small">{vtranslate('LBL_CHECK_LOADING', $MODULE)}...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        {if $SYSTEM_MODEL->isNewestVersion()}
                            {if $SYSTEM_MODEL->isCacheDateValid()}
                                <a class="btn btn-primary" data-update-information="system" href="index.php?module=Installer&view=IndexAjax&mode=updateInformation">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <span class="ms-2">{vtranslate('LBL_CHECK_UPDATE', $MODULE)}</span>
                                </a>
                            {/if}
                        {elseif $SYSTEM_MODEL->hasDownloadUrl()}
                            <button type="button" class="btn btn-primary" data-download-system="{$SYSTEM_MODEL->getVersion()}">
                                <i class="fa-solid fa-rotate"></i>
                                <span class="ms-2 fw-bold">{vtranslate('LBL_UPDATE', $QUALIFIED_MODULE)}</span>
                            </button>
                        {else}
                            <a class="btn btn-primary" target="_blank" href="index.php?module=Installer&view=Redirect&mode=SourceForge">
                                <i class="fa-solid fa-download"></i>
                                <span class="ms-2 fw-bold">{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</span>
                            </a>
                        {/if}
                        {if !Installer_License_Model::isMembershipActive()}
                            <div class="pt-2">{$SYSTEM_MODEL->getBranding()}</div>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="container-fluid">
            <div class="row py-2 pt-3">
                <div class="col">
                    <h4 class="m-0 text-secondary"><b>{vtranslate('LBL_MODULES', $QUALIFIED_MODULE)}</b></h4>
                </div>
            </div>
        </div>
        <div class="container-fluid rounded border">
            <div class="row py-2 text-secondary">
                <div class="col-lg"></div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_UPDATE_VERSION', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_ExtensionInstall_Model::getAll() item=EXTENSION_MODEL}
                {assign var=EXTENSION_NAME value=$EXTENSION_MODEL->getName()}
                {assign var=EXTENSION_LABEL value=vtranslate($EXTENSION_NAME, $EXTENSION_NAME)}
                <div class="sectionTagContainer"><div class="sectionTag" id="Update{$EXTENSION_NAME}"></div></div>
                <div class="row border-top py-2 align-items-center">
                    <div class="col-lg">
                        <a href="{$EXTENSION_MODEL->getDefaultUrl()}">{$EXTENSION_MODEL->getLabel()}</a>
                    </div>
                    <div class="col-lg-4">{$EXTENSION_MODEL->getUpdateVersion()}</div>
                    <div class="col-lg-4 d-flex">
                        {if $EXTENSION_MODEL->hasDownloadUrl()}
                            <button type="button" class="btn btn-primary" data-download-extension="{$EXTENSION_MODEL->getName()}">
                                <i class="fa-solid fa-download"></i>
                                <span class="ms-2">{vtranslate('LBL_UPDATE', $QUALIFIED_MODULE)}</span>
                            </button>
                        {/if}
                        {assign var=EXTENSION_LINKS value=$EXTENSION_MODEL->getLinks()}
                        {if $EXTENSION_LINKS}
                            <div class="dropdown ms-auto">
                                <a class="btn btn-outline-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a>
                                <ul class="dropdown-menu">
                                    {foreach from=$EXTENSION_LINKS item=LINK}
                                        <li><a class="dropdown-item" href="{$LINK->getUrl()}">{vtranslate($LINK->getLabel(), $EXTENSION_NAME, $EXTENSION_LABEL)}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{/strip}