{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="container-fluid">
        <div class="pb-3">
            <div class="row py-2">
                <div class="col-lg">
                    <h4><b>{vtranslate('LBL_LICENSES', $QUALIFIED_MODULE)}</b></h4>
                </div>
                <div class="col-lg-auto">
                    <button type="button" class="btn btn-primary" data-edit-license="">
                        <i class="fa-solid fa-plus"></i>
                        <span class="ms-2">{vtranslate('LBL_ADD_LICENSE', $QUALIFIED_MODULE)}</span>
                    </button>
                    <a class="btn btn-outline-primary ms-2" href="index.php?module=Installer&view=IndexAjax&mode=updateInformation">
                        {vtranslate('LBL_UPDATE_LICENSES', $QUALIFIED_MODULE)}
                    </a>
                </div>
            </div>
            <div class="row border py-2">
                <div class="col-lg-4"></div>
                <div class="col-lg-2 fw-bold">{vtranslate('LBL_NAME', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-2 fw-bold">{vtranslate('Due Date', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_License_Model::getAll() item=LICENSE_MODEL}
                <div class="row border border-top-0 py-2 licenseContainer align-items-center">
                    <div class="col-lg-4">
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
                    <div class="col-lg-2">{$LICENSE_MODEL->getItemName()}</div>
                    <div class="col-lg-2">{Vtiger_Functions::currentUserDisplayDate($LICENSE_MODEL->getExpireDate())}</div>
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
        <div class="pb-3">
            <div class="row">
                <div class="col-lg">
                    <h4><b>{vtranslate('LBL_SYSTEM', $QUALIFIED_MODULE)}</b></h4>
                </div>
                <div class="col-lg-auto"></div>
            </div>
            <div class="row border py-2">
                <div class="col-lg-6"></div>
                <div class="col-lg-2 fw-bold">{vtranslate('LBL_UPDATE_VERSION', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_SystemInstall_Model::getAll() item=SYSTEM_MODEL}
                <div class="row border border-top-0 py-2 align-items-center">
                    <div class="col-lg-6">{$SYSTEM_MODEL->getLabel()}</div>
                    <div class="col-lg-2">{$SYSTEM_MODEL->getVersion()}</div>
                    <div class="col-lg-4">
                        <button type="button" class="btn btn-primary" data-download-system="{$SYSTEM_MODEL->getVersion()}">
                            <i class="fa-solid fa-download"></i>
                            <span class="ms-2">{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</span>
                        </button>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="pb-3">
            <div class="row">
                <div class="col-lg">
                    <h4><b>{vtranslate('LBL_EXTENSIONS', $QUALIFIED_MODULE)}</b></h4>
                </div>
                <div class="col-lg-auto"></div>
            </div>
            <div class="row border py-2">
                <div class="col-lg-4"></div>
                <div class="col-lg-2 fw-bold">{vtranslate('LBL_VERSION', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-2 fw-bold">{vtranslate('LBL_UPDATE_VERSION', $QUALIFIED_MODULE)}</div>
                <div class="col-lg-4 fw-bold">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</div>
            </div>
            {foreach from=Installer_ExtensionInstall_Model::getAll() item=EXTENSION_MODEL}
                {assign var=EXTENSION_NAME value=$EXTENSION_MODEL->getName()}
                {assign var=EXTENSION_LABEL value=vtranslate($EXTENSION_NAME, $EXTENSION_NAME)}
                <div class="row border border-top-0 py-2 align-items-center">
                    <div class="col-lg-4">
                        <a href="{$EXTENSION_MODEL->getDefaultUrl()}">{$EXTENSION_LABEL}</a>
                    </div>
                    <div class="col-lg-2">{$EXTENSION_MODEL->getVersion()}</div>
                    <div class="col-lg-2">{$EXTENSION_MODEL->getUpdateVersion()}</div>
                    <div class="col-lg-4 d-flex">
                        {if $EXTENSION_MODEL->hasDownloadUrl()}
                            <button type="button" class="btn btn-primary" data-download-extension="{$EXTENSION_MODEL->getName()}">
                                <i class="fa-solid fa-download"></i>
                                <span class="ms-2">{vtranslate('LBL_DOWNLOAD', $QUALIFIED_MODULE)}</span>
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