{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
{assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
    <div class="app-menu shadow dropdown-menu p-0" id="app-menu">
        <div class="container-fluid">
            <div class="row border-bottom border-1">
                <div class="col-auto py-2 cursorPointer app-switcher-container d-flex align-items-end justify-content-center h-header" data-bs-toggle="offcanvas" data-bs-target="#app-menu">
                    <div class="app-navigator dt-menu-button rounded text-primary bg-menu-icon">
                        <i id="menu-toggle-action" class="app-icon dt-menu-icon fa fa-bars"></i>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center justify-content-center">
                    <div class="fs-3 text-primary">{$COMPANY_NAME}</div>
                </div>
                <div class="col-lg text-end text-secondary d-flex flex-wrap align-items-center justify-content-end">
                    {assign var=DOCUMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Documents')}
                    {if $USER_PRIVILEGES_MODEL->hasModulePermission($DOCUMENTS_MODULE_MODEL->getId())}
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="index.php?module=Documents&view=List">
                                    <span class="app-icon-list dt-menu-icon">{$DOCUMENTS_MODULE_MODEL->getModuleIcon()}</span>
                                    <span class="app-name ms-2 textOverflowEllipsis"> {vtranslate('Documents')}</span>
                                </a>
                            </div>
                        </div>
                    {/if}
                    {assign var=MAILMANAGER_MODULE_MODEL value=Vtiger_Module_Model::getInstance('MailManager')}
                    {if $USER_PRIVILEGES_MODEL->hasModulePermission($MAILMANAGER_MODULE_MODEL->getId())}
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="index.php?module=MailManager&view=List">
                                    <span class="app-icon-list dt-menu-icon">{$MAILMANAGER_MODULE_MODEL->getModuleIcon()}</span>
                                    <span class="app-name ps-2 textOverflowEllipsis"> {vtranslate('MailManager')}</span>
                                </a>
                            </div>
                        </div>
                    {/if}
                    {if $USER_MODEL->isAdminUser()}
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="?module=Vtiger&parent=Settings&view=Index">
                                    <span class="fa fa-cog module-icon dt-menu-icon"></span>
                                    <span class="module-name ps-2 textOverflowEllipsis"> {vtranslate('LBL_CRM_SETTINGS','Vtiger')}</span>
                                </a>
                            </div>
                        </div>
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="?module=Users&parent=Settings&view=List">
                                    <span class="fa fa-user module-icon dt-menu-icon"></span>
                                    <span class="module-name ms-2 textOverflowEllipsis"> {vtranslate('LBL_MANAGE_USERS','Vtiger')}</span>
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            {assign var=HOME_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Home')}
            {assign var=DASHBOARD_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Dashboard')}
            <div class="app-list row flex-wrap">
                <div class="col-sm py-3">
                    <div class="container-fluid">
                        <div class="row">
                            {if $USER_PRIVILEGES_MODEL->hasModulePermission($DASHBOARD_MODULE_MODEL->getId())}
                                <div class="col-lg-12 p-0 menu-item app-item " data-default-url="{$HOME_MODULE_MODEL->getDefaultUrl()}">
                                    <div class="menu-items-link rounded p-3 mb-3 text-secondary">
                                        <span class="app-icon-list dt-menu-icon fa fa-dashboard"></span>
                                        <span class="app-name ms-3 textOverflowEllipsis">{vtranslate('LBL_DASHBOARD',$MODULE)}</span>
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
                {assign var=APP_GROUPED_MENU value=Settings_MenuEditor_Module_Model::getAllVisibleModules()}
                {assign var=APP_LIST value=Vtiger_MenuStructure_Model::getAppMenuList()}
                {foreach item=APP_NAME from=$APP_LIST}
                    {if $APP_NAME eq 'ANALYTICS'} {continue}{/if}
                    {if !empty($APP_GROUPED_MENU[$APP_NAME])}
                        {assign var=IS_SELECTED_CATEGORY value=$SELECTED_MENU_CATEGORY eq $APP_NAME}
                        <div class="col-sm py-3 app-modules-dropdown-container">
                            {foreach item=APP_MENU_MODEL from=$APP_GROUPED_MENU[$APP_NAME]}
                                {assign var=FIRST_MENU_MODEL value=$APP_MENU_MODEL}
                                {if $APP_MENU_MODEL}
                                    {break}
                                {/if}
                            {/foreach}
                            {* Fix for Responsive Layout Menu - Changed data-default-url to # *}
                            <div class="menu-item" data-app-name="{$APP_NAME}" id="{$APP_NAME}_modules_dropdownMenu" aria-haspopup="true" aria-expanded="true" data-default-url="#">
                                <div class="menu-items-wrapper app-menu-items-wrapper mb-3 p-3 text-truncate fw-bold rounded {if $IS_SELECTED_CATEGORY}bg-menu-icon{else}text-secondary{/if}">
                                    <span class="app-icon-list dt-menu-icon fa {$APP_IMAGE_MAP[$APP_NAME]} {if $IS_SELECTED_CATEGORY}text-primary{/if}"></span>
                                    <span class="app-name ms-3 textOverflowEllipsis">{vtranslate("LBL_$APP_NAME")}</span>
                                </div>
                                <div class="container-fluid" aria-labelledby="{$APP_NAME}_modules_dropdownMenu">
                                    <div class="row">
                                        {foreach item=moduleModel key=moduleName from=$APP_GROUPED_MENU[$APP_NAME]}
                                            {assign var=translatedModuleLabel value=vtranslate($moduleModel->get('label'),$moduleName)}
                                            {assign var=IS_SELECTED_MODULE value=$IS_SELECTED_CATEGORY and $moduleName eq $MODULE}
                                            <div class="col-lg-12 p-0">
                                                <a class="menu-items-link rounded d-block mb-2 p-3 text-truncate fw-bold {if !$IS_SELECTED_MODULE}text-secondary{/if}" href="{$moduleModel->getDefaultUrl()}&app={$APP_NAME}" title="{$translatedModuleLabel}">
                                                    <span class="dt-menu-icon module-icon {if $IS_SELECTED_MODULE}text-primary{/if}">{$moduleModel->getModuleIcon()}</span>
                                                    <span class="ms-3 module-name textOverflowEllipsis">{$translatedModuleLabel}</span>
                                                </a>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
{/strip}
