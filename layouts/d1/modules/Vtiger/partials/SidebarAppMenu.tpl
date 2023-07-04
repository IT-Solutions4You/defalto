{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div class="app-menu w-75 shadow dropdown-menu p-0" id="app-menu">
    <div class="container-fluid">
        <div class="row">
            <div class="col-auto px-3 py-2 cursorPointer app-switcher-container" data-bs-toggle="offcanvas" data-bs-target="#app-menu">
                <div class="app-navigator dt-menu-button mx-auto">
                    <i id="menu-toggle-action" class="app-icon dt-menu-icon fa fa-bars"></i>
                </div>
            </div>
        </div>
        {assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
        {assign var=HOME_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Home')}
        {assign var=DASHBOARD_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Dashboard')}
        <div class="app-list row">
            <div class="col-lg-12 px-3 pb-3">
                <div class="container-fluid">
                    <div class="row">
                        {if $USER_PRIVILEGES_MODEL->hasModulePermission($DASHBOARD_MODULE_MODEL->getId())}
                            <div class="col-lg-3 p-0 menu-item app-item " data-default-url="{$HOME_MODULE_MODEL->getDefaultUrl()}">
                                <div class="menu-items-link rounded p-1 m-1">
                                    <span class="app-icon-list dt-menu-icon fa fa-dashboard"></span>
                                    <span class="app-name ps-2 textOverflowEllipsis">{vtranslate('LBL_DASHBOARD',$MODULE)}</span>
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
                {if !empty($APP_GROUPED_MENU.$APP_NAME)}
                    <div class="col-lg-6 app-modules-dropdown-container px-3 pb-3">
                        {foreach item=APP_MENU_MODEL from=$APP_GROUPED_MENU[$APP_NAME]}
                            {assign var=FIRST_MENU_MODEL value=$APP_MENU_MODEL}
                            {if $APP_MENU_MODEL}
                                {break}
                            {/if}
                        {/foreach}
                        {* Fix for Responsive Layout Menu - Changed data-default-url to # *}
                        <div class="menu-item" data-app-name="{$APP_NAME}" id="{$APP_NAME}_modules_dropdownMenu" aria-haspopup="true" aria-expanded="true" data-default-url="#">
                            <div class="menu-items-wrapper app-menu-items-wrapper border-bottom border-1 mx-1 p-1">
                                <span class="app-icon-list dt-menu-icon fa {$APP_IMAGE_MAP[$APP_NAME]}"></span>
                                <span class="app-name ps-2 textOverflowEllipsis">{vtranslate("LBL_$APP_NAME")}</span>
                            </div>
                            <div class="container-fluid" aria-labelledby="{$APP_NAME}_modules_dropdownMenu">
                                <div class="row">
                                    {foreach item=moduleModel key=moduleName from=$APP_GROUPED_MENU[$APP_NAME]}
                                        {assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
                                        <div class="col-lg-6 p-0">
                                            <a class="menu-items-link rounded d-block m-1 p-1" href="{$moduleModel->getDefaultUrl()}&app={$APP_NAME}" title="{$translatedModuleLabel}">
                                                <span class="dt-menu-icon module-icon">{$moduleModel->getModuleIcon()}</span>
                                                <span class="ps-2 module-name textOverflowEllipsis">{$translatedModuleLabel}</span>
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
        <div class="row border-top border-1 p-1">
            <div class="col-lg-auto">
                {assign var=MAILMANAGER_MODULE_MODEL value=Vtiger_Module_Model::getInstance('MailManager')}
                {if $USER_PRIVILEGES_MODEL->hasModulePermission($MAILMANAGER_MODULE_MODEL->getId())}
                    <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3" data-default-url="index.php?module=MailManager&view=List">
                        <div class="menu-items-wrapper">
                            <span class="app-icon-list dt-menu-icon">{$MAILMANAGER_MODULE_MODEL->getModuleIcon()}</span>
                            <span class="app-name ps-2 textOverflowEllipsis"> {vtranslate('MailManager')}</span>
                        </div>
                    </div>
                {/if}
                {assign var=DOCUMENTS_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Documents')}
                {if $USER_PRIVILEGES_MODEL->hasModulePermission($DOCUMENTS_MODULE_MODEL->getId())}
                    <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3" data-default-url="index.php?module=Documents&view=List">
                        <div class="menu-items-wrapper">
                            <span class="app-icon-list dt-menu-icon">{$DOCUMENTS_MODULE_MODEL->getModuleIcon()}</span>
                            <span class="app-name ps-2 textOverflowEllipsis"> {vtranslate('Documents')}</span>
                        </div>
                    </div>
                {/if}
                {if $USER_MODEL->isAdminUser()}
                    {if vtlib_isModuleActive('ExtensionStore')}
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3" data-default-url="index.php?module=ExtensionStore&parent=Settings&view=ExtensionStore">
                            <div class="menu-items-wrapper">
                                <span class="app-icon-list dt-menu-icon fa fa-shopping-cart"></span>
                                <span class="app-name ps-2 textOverflowEllipsis"> {vtranslate('LBL_EXTENSION_STORE', 'Settings:Vtiger')}</span>
                            </div>
                        </div>
                    {/if}
                {/if}
            </div>
            <div class="col-lg-auto ms-auto">
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
                                <span class="module-name ps-2 textOverflowEllipsis"> {vtranslate('LBL_MANAGE_USERS','Vtiger')}</span>
                            </a>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</div>
