{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{include file="modules/Vtiger/Header.tpl"}

{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<nav class="fixed-top app-fixed-navbar bg-body-secondary">
    <div class="container-fluid global-nav">
        <div class="row align-items-center">
            <div class="col-auto app-navigator-container bg-body">
                <div id="appnavigator" class="app-switcher-container py-2 h-100 cursorPointer d-flex align-items-end justify-content-center" data-bs-toggle="offcanvas" data-bs-target="#app-menu" data-app-class="{if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}">
                    <div class="app-navigator dt-menu-button rounded">
                        <i class="app-icon dt-menu-icon fa fa-bars"></i>
                    </div>
                </div>
            </div>
            <div class="col-3 col-sm-4 col-md-5 transitionsAllHalfSecond module-breadcrumb module-breadcrumb-{$REQUEST_INSTANCE.view}">
                <div class="w-100 text-truncate text-nowrap fs-4 px-xs-3 ps-lg-3">
                    {assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($MODULE)}
                    {if $MODULE_MODEL}
                        {if $MODULE_MODEL->getDefaultViewName() neq 'List'}
                            {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getDefaultUrl()}
                        {else}
                            {assign var=DEFAULT_FILTER_ID value=$MODULE_MODEL->getDefaultCustomFilter()}
                            {if $DEFAULT_FILTER_ID}
                                {assign var=CVURL value="&viewname="|cat:$DEFAULT_FILTER_ID}
                                {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrl()|cat:$CVURL}
                            {else}
                                {assign var=DEFAULT_FILTER_URL value=$MODULE_MODEL->getListViewUrlWithAllFilter()}
                            {/if}
                        {/if}
                        {assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
                        {assign var=SINGLE_MODULE_LABEL value=vtranslate($SINGLE_MODULE_NAME, $MODULE)}
                        {assign var=CUSTOM_VIEW_URL value=implode('', [$DEFAULT_FILTER_URL,'&app=',$SELECTED_MENU_CATEGORY])}
                        {if isset($smarty.session.lvs) && isset($smarty.session.lvs.$MODULE) && isset($smarty.session.lvs.$MODULE.viewname)}
                            {assign var=VIEWID value=$smarty.session.lvs.$MODULE.viewname}
                        {/if}
                        {if isset($VIEWID) && $VIEWID}
                            {assign var=CUSTOM_VIEW_URL value=implode('', [$MODULE_MODEL->getListViewUrl(), '&viewname=', $VIEWID, '&app=', $SELECTED_MENU_CATEGORY])}
                        {/if}
                    {else}
                        {assign var=SINGLE_MODULE_NAME value='SINGLE_Settings'}
                        {assign var=SINGLE_MODULE_LABEL value=vtranslate('SINGLE_Settings', 'Vtiger')}
                        {assign var=CUSTOM_VIEW_URL value='index.php'}
                    {/if}
                    <a class="module-title fs-3" title="{$SINGLE_MODULE_LABEL}" href='{$CUSTOM_VIEW_URL}'>
                        {$SINGLE_MODULE_LABEL}
                    </a>
                    {if $RECORD and $REQUEST_INSTANCE.view eq 'Edit'}
                        <span class="current-filter-slash d-inline px-2">/</span>
                        <a class="current-filter-name filter-name cursorPointer" title="{$RECORD->get('label')}">{vtranslate('LBL_EDITING', $MODULE)} : {$RECORD->get('label')}</a>
                    {elseif $REQUEST_INSTANCE.view eq 'Edit'}
                        <span class="current-filter-slash d-inline px-2">/</span>
                        <a class="current-filter-name filter-name cursorPointer">{vtranslate('LBL_ADDING_NEW', $MODULE)}</a>
                    {/if}
                    {if $RECORD and $REQUEST_INSTANCE.view eq 'Detail'}
                        <span class="current-filter-slash d-inline px-2">/</span>
                        <a class="current-filter-name filter-name cursorPointer" title="{$RECORD->get('label')}">{$RECORD->get('label')}</a>
                    {/if}
                </div>
            </div>
            <div class="navbar-header col-auto ms-auto d-lg-none p-2">
                <div class="d-flex align-items-center h-100">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#search-links-container" aria-controls="search-links-container" aria-expanded="false">
                        <i class="fa fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false">
                        <i class="fa fa-th"></i>
                    </button>
                </div>
            </div>
            <div id="search-links-container" class="search-links-container collapse ms-auto col-lg-auto d-lg-block px-2 px-lg-0 bg-body-secondary">
                <div class="d-flex align-items-center h-100">
                    <div class="search-link input-group input-group border border-secondary rounded">
                        <label for="search-keyword-input" class="d-inline-block input-group-text bg-body-secondary text-secondary border-0">
                            <i class="fa fa-search"></i>
                        </label>
                        <input id="search-keyword-input" class="keyword-input bg-body-secondary form-control border-0" type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" value="{$GLOBAL_SEARCH_VALUE}">
                        <div id="adv-search" class="adv-search input-group-text bg-body-secondary text-secondary border-0">
                            <i  class="fa fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div id="navbar" class="global-actions collapse navbar navbar-expand col-lg-auto d-lg-block px-2 py-0 bg-body-secondary">
                <div class="h-100 d-flex align-items-center">
                    <ul class="nav navbar-nav ms-auto align-items-center">
                    <li class="me-2">
                        <div class="dropdown">
                            <div data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                                <a href="#" id="menubar_quickCreate" class="btn border-1 border-secondary text-secondary qc-button btn-outline-secondary" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" aria-hidden="true">
                                    <i class="fa fa-plus-circle"></i>
                                </a>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-end dt-w-500 p-0 border-0 shadow" role="menu" aria-labelledby="dropdownMenu1">
                                <li class="title py-3 px-4 border-bottom">
                                    <strong>{vtranslate('LBL_QUICK_CREATE',$MODULE)}</strong>
                                </li>
                                <li id="quickCreateModules">
                                    <div class="container-fluid py-3 px-4">
                                        {assign var='count' value=0}
                                        {foreach key=moduleName item=moduleModel from=$QUICK_CREATE_MODULES}
                                            {if $moduleModel->isPermitted('CreateView') || $moduleModel->isPermitted('EditView')}
                                                {assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
                                                {assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
                                                {assign var=hideDiv value={!$moduleModel->isPermitted('CreateView') && $moduleModel->isPermitted('EditView')}}
                                                {if $quickCreateModule == '1'}
                                                    {if $count % 3 == 0}
                                                        <div class="row">
                                                    {/if}
                                                    {if $singularLabel == 'SINGLE_Documents'}
                                                        <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4 col-xs-4{/if} dropdown">
                                                            <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModuleSubmenu fs-6 text-muted" data-name="{$moduleModel->getName()}" data-bs-toggle="dropdown" data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                                                <span class="lh-base">{$moduleModel->getModuleIcon()}</span>
                                                                <span class="quick-create-module ps-3">
                                                                    {vtranslate($singularLabel,$moduleName)}
                                                                    <i class="fa fa-caret-down quickcreateMoreDropdownAction"></i>
                                                                </span>
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu-end quickcreateMoreDropdown" aria-labelledby="menubar_quickCreate_{$moduleModel->getName()}">
                                                                <li>
                                                                    <h6 class="dropdown-header">
                                                                        <i class="fa fa-upload"></i>
                                                                        <span class="ps-3">{vtranslate('LBL_FILE_UPLOAD', $moduleName)}</span>
                                                                    </h6>
                                                                </li>
                                                                <li id="VtigerAction">
                                                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.uploadTo('Vtiger')">
                                                                        <i class="fa fa-home"></i>
                                                                        <span class="ps-3">{vtranslate('LBL_TO_SERVICE', $moduleName, {vtranslate('LBL_VTIGER', $moduleName)})}</span>
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <h6 class="dropdown-header">
                                                                        <i class="fa fa-link"></i>
                                                                        <span class="ps-3">{vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $moduleName)}</span>
                                                                    </h6>
                                                                </li>
                                                                <li id="shareDocument">
                                                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('E')">
                                                                        <i class="fa fa-external-link"></i>
                                                                        <span class="ps-3">{vtranslate('LBL_FROM_SERVICE', $moduleName, {vtranslate('LBL_FILE_URL', $moduleName)})}</span>
                                                                    </a>
                                                                </li>
                                                                <li id="createDocument">
                                                                    <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W')">
                                                                        <i class="fa fa-file-text"></i>
                                                                        <span class="ps-3">{vtranslate('LBL_CREATE_NEW', $moduleName, {vtranslate('SINGLE_Documents', $moduleName)})}</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    {else}
                                                        <div class="{if $hideDiv}create_restricted_{$moduleModel->getName()} hide{else}col-lg-4 col-xs-4{/if}">
                                                            <a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule d-flex text-muted" data-name="{$moduleModel->getName()}" data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">
                                                                <span class="lh-base">{$moduleModel->getModuleIcon()}</span>
                                                                <span class="ps-3 quick-create-module">{vtranslate($singularLabel,$moduleName)}</span>
                                                            </a>
                                                        </div>
                                                    {/if}
                                                    {if $count % 3 == 2}
                                                        </div>
                                                        <br>
                                                    {/if}
                                                    {if !$hideDiv}
                                                        {assign var='count' value=$count+1}
                                                    {/if}
                                                {/if}
                                            {/if}
                                        {/foreach}
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </li>
                    {assign var=USER_PRIVILEGES_MODEL value=Users_Privileges_Model::getCurrentUserPrivilegesModel()}
                    {assign var=CALENDAR_MODULE_MODEL value=Vtiger_Module_Model::getInstance('Appointments')}
                    {if $CALENDAR_MODULE_MODEL and $USER_PRIVILEGES_MODEL->hasModulePermission($CALENDAR_MODULE_MODEL->getId())}
                        {assign var=CALENDAR_TODAY_COUNT value=$CALENDAR_MODULE_MODEL->getTodayRecordsCount()}
                        {assign var=CALENDAR_TODAY_RECORD value=$CALENDAR_MODULE_MODEL->getFirstTodayRecord()}
                        <li class="me-2">
                            <a href="{$CALENDAR_MODULE_MODEL->getIconUrl()}" class="btn btn-outline-secondary text-secondary border-secondary position-relative" title="{vtranslate('Appointments','Appointments')}" aria-hidden="true">
                                <i class="fa fa-calendar"></i>
                                <b class="ms-2">{date('d')}</b>
                                <small class="ms-1">{vtranslate(date('M'), 'Appointments')}</small>
                                {if !empty($CALENDAR_TODAY_COUNT)}
                                    <span class="position-absolute top-0 start-100 translate-middle pe-4">
                                        <span class="badge rounded-pill bg-primary">{$CALENDAR_TODAY_COUNT}</span>
                                    </span>
                                {/if}
                            </a>
                        </li>
                        {if $CALENDAR_TODAY_RECORD}
                            <li>
                                <a href="{$CALENDAR_TODAY_RECORD->getDetailViewUrl()}" class="btn btn-outline-secondary text-secondary border-secondary" title="{$CALENDAR_TODAY_RECORD->getName()}">
                                    <div class="text-start">
                                        <div>
                                            <i class="{$CALENDAR_TODAY_RECORD->getActivityTypeIcon()}"></i>
                                            <span class="ms-2">{$CALENDAR_TODAY_RECORD->getTimes()}</span>
                                        </div>
                                        <div>
                                            <div class="text-truncate">{$CALENDAR_TODAY_RECORD->getName()}</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        {/if}
                    {/if}
                    <li class="mx-2 me-lg-3">
                        <div class="dropdown">
                            <a href="#" class="userName" data-bs-toggle="dropdown">
                                <div class="profile-img-container d-flex align-items-center justify-content-center">
                                    {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                    {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                                        <i class='vicon-vtigeruser'></i>
                                    {else}
                                        {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                            {if !empty($IMAGE_INFO.url)}
                                                <img src="{$IMAGE_INFO.url}" width="2.2rem" height="2.2rem">
                                            {/if}
                                        {/foreach}
                                    {/if}
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end logout-content p-0 mt-2 border-0 shadow">
                                <div class="container-fluid border-bottom p-4">
                                    <div class="row text-nowrap">
                                        <div class="col-auto">
                                            <div class="profile-img-container p-0 d-flex align-items-center justify-content-center" style="width: 3.8rem; height: 3.8rem;">
                                                {assign var=IMAGE_DETAILS value=$USER_MODEL->getImageDetails()}
                                                {if $IMAGE_DETAILS neq '' && $IMAGE_DETAILS[0] neq '' && $IMAGE_DETAILS[0].path eq ''}
                                                    <i class='vicon-vtigeruser'></i>
                                                {else}
                                                    {foreach item=IMAGE_INFO from=$IMAGE_DETAILS}
                                                        {if !empty($IMAGE_INFO.url)}
                                                            <img src="{$IMAGE_INFO.url}" height="100%" width="100%">
                                                        {/if}
                                                    {/foreach}
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="profile-container">
                                                <div class="profile-name lh-base fw-bold fs-5">{$USER_MODEL->get('first_name')} {$USER_MODEL->get('last_name')}</div>
                                                <div class="profile-username lh-base fs-5 text-truncate text-secondary" title='{$USER_MODEL->get('user_name')}'>{$USER_MODEL->get('email1')}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="logout-footer" class="logout-footer container-fluid px-4 py-2">
                                    <div class="row">
                                        <div class="col-2 p-3 logout-footer-icon text-secondary text-center">
                                            <i class="fa fa-cogs"></i>
                                        </div>
                                        <a class="col py-3 fw-semibold" id="menubar_item_right_LBL_MY_PREFERENCES" href="{$USER_MODEL->getPreferenceDetailViewUrl()}">{vtranslate('LBL_MY_PREFERENCES')}</a>
                                    </div>
                                    <div class="row">
                                        <div class="col-2 p-3 logout-footer-icon text-secondary text-center">
                                            <i class="fa fa-power-off"></i>
                                        </div>
                                        <a class="col py-3 fw-semibold" id="menubar_item_right_LBL_SIGN_OUT" href="index.php?module=Users&action=Logout">{vtranslate('LBL_SIGN_OUT')}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                </div>
            </div>
        </div>
    </div>
    {/strip}
