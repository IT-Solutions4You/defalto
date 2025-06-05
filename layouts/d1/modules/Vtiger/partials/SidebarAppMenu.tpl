{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
{assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
    <div class="app-menu shadow dropdown-menu p-0 bg-body" id="app-menu">
        <div class="container-fluid">
            <div class="row border-bottom border-1">
                <div class="col-auto p-1 cursorPointer app-switcher-container h-header" data-bs-toggle="offcanvas" data-bs-target="#app-menu">
                    <div class="app-navigator dt-menu-button rounded h-100 w-100 text-primary d-flex align-items-center justify-content-center">
                        <i id="menu-toggle-action" class="fs-4 app-icon dt-menu-icon fa fa-bars"></i>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center justify-content-center">
                    <a href="index.php" class="fs-3 text-primary">{$COMPANY_NAME}</a>
                </div>
                <div class="col-lg text-end text-secondary d-flex flex-wrap align-items-center justify-content-end">
                    {if $USER_MODEL->isAdminUser()}
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="?module=Vtiger&parent=Settings&view=Index">
                                    <span class="fa fa-cog module-icon dt-menu-icon"></span>
                                    <span class="module-name ps-2 text-truncate"> {vtranslate('LBL_CRM_SETTINGS','Vtiger')}</span>
                                </a>
                            </div>
                        </div>
                        <div class="menu-item app-item app-item-misc d-inline-block p-1 me-3">
                            <div class="menu-items-wrapper">
                                <a href="?module=Users&parent=Settings&view=List">
                                    <span class="fa fa-user module-icon dt-menu-icon"></span>
                                    <span class="module-name ms-2 text-truncate"> {vtranslate('LBL_MANAGE_USERS','Vtiger')}</span>
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="app-list row flex-wrap">
                {assign var=APP_GROUPED_MENU value=Settings_MenuEditor_Module_Model::getAllVisibleModules()}
                {assign var=APP_LIST value=Vtiger_MenuStructure_Model::getAppMenuList()}
                {foreach item=APP_NAME from=$APP_LIST}
                    {if !empty($APP_GROUPED_MENU[$APP_NAME])}
                        {assign var=IS_SELECTED_CATEGORY value=$SELECTED_MENU_CATEGORY eq $APP_NAME}
                        <div class="col-sm py-3">
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
                                    <span class="app-name ms-3 text-truncate">{vtranslate("LBL_$APP_NAME")}</span>
                                </div>
                                <div class="container-fluid" aria-labelledby="{$APP_NAME}_modules_dropdownMenu">
                                    <div class="row">
                                        {foreach item=moduleModel key=moduleName from=$APP_GROUPED_MENU[$APP_NAME]}
                                            {assign var=translatedModuleLabel value=vtranslate($moduleModel->get('label'),$moduleName)}
                                            {assign var=IS_SELECTED_MODULE value=$IS_SELECTED_CATEGORY and $moduleName eq $MODULE}
                                            <div class="col-lg-12 p-0">
                                                <a class="menu-items-link rounded d-block mb-2 p-3 text-truncate fw-bold {if !$IS_SELECTED_MODULE}text-secondary{/if}" href="{$moduleModel->getDefaultUrl()}&app={$APP_NAME}" title="{$translatedModuleLabel}">
                                                    <span class="dt-menu-icon module-icon {if $IS_SELECTED_MODULE}text-primary{/if}">{$moduleModel->getModuleIcon('14px')}</span>
                                                    <span class="ms-3 module-name text-truncate">{$translatedModuleLabel}</span>
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
