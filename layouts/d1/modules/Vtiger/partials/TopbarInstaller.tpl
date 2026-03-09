{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <li class="ms-2">
        <div class="dropdown">
            <a href="#" class="btn btn-outline-secondary text-secondary border-secondary" data-bs-toggle="dropdown" title="{vtranslate('Installer', 'Installer')}">
                <img class="h-1rem" src="{vresource_url('layouts/$LAYOUT$/favicon/favicon-32x32.png')}" alt="D">
            </a>
            <ul class="dropdown-menu dropdown-menu-end w-30rem p-0 border-0 shadow">
                <li class="title py-3 px-4 d-flex align-items-center">
                    <img class="h-2rem fa fa-download" src="{vresource_url('layouts/$LAYOUT$/favicon/favicon-32x32.png')}" alt="D">
                    <b class="ms-3 fw-bold text-secondary">{vtranslate('LBL_INSTALLER', 'Installer')} {vtranslate('LBL_ALERTS', 'Installer')}</b>
                </li>
                <li class="p-2 bg-body-secondary h-50vh overflow-auto">
                    <div class="container-fluid">
                        {foreach from=Installer_Notification_Model::getAll() item=NOTIFICATION}
                            <a class="row py-2 align-items-center rounded bg-body mb-2" href="{$NOTIFICATION->get('link')}">
                                <div class="col-auto">
                                    <div class="w-3rem h-3rem d-flex align-items-center justify-content-center rounded text-white bg-{$NOTIFICATION->getType()}">
                                        <i class="{$NOTIFICATION->get('icon')}"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="fw-bold text-{$NOTIFICATION->getType()}">{$NOTIFICATION->getName()}</div>
                                        </div>
                                        <div class="col-auto">
                                            <small class="text-secondary">{$NOTIFICATION->getDisplayDate()}</small>
                                        </div>
                                    </div>
                                    <div class="text-secondary">{$NOTIFICATION->getDisplayDescription()}</div>
                                </div>
                            </a>
                        {/foreach}
                    </div>
                </li>
                <li class="fs-4 text-center p-2">
                    <a href="index.php?module=Installer" class="btn btn-primary">{vtranslate('LBL_MANAGE_MODULES', 'Installer')}</a>
                </li>
            </ul>
        </div>
    </li>
{/strip}