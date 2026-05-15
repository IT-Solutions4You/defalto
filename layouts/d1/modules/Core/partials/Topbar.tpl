{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{include file="Header.tpl"|vtemplate_path:$MODULE}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<nav class="fixed-top app-fixed-navbar bg-body-secondary">
    <div class="container-fluid global-nav">
        <div class="row align-items-center flex-lg-nowrap">
            <div class="col-auto p-0 app-navigator-container bg-body">
                <div id="appnavigator" class="app-switcher-container h-100 cursorPointer p-1" data-bs-toggle="offcanvas" data-bs-target="#app-menu" data-app-class="{if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}">
                    <div class="app-navigator dt-menu-button rounded h-100 w-100 d-flex align-items-center justify-content-center">
                        <i class="fs-4 app-icon dt-menu-icon fa fa-bars"></i>
                    </div>
                </div>
            </div>
            <div class="col w-50 module-breadcrumb module-breadcrumb-{$REQUEST_INSTANCE.view}">
                <div class="row align-items-center flex-nowrap">
                    {if 'Settings' eq $REQUEST_INSTANCE.parent}
                        {assign var=SETTINGS_INDEX_URL value='index.php?module=Vtiger&parent=Settings&view=Index'}
                        {assign var=SETTINGS_LABEL value=vtranslate('SINGLE_Settings', 'Vtiger')}
                        <div class="col-auto ps-4">
                            <a class="module-title fs-4 fw-bold" title="{$SETTINGS_LABEL}" href="{$SETTINGS_INDEX_URL}">{$SETTINGS_LABEL}</a>
                        </div>
                        {if $MODULE neq 'Vtiger' || $REQUEST_INSTANCE.view neq 'Index'}
                            {if isset($ACTIVE_BLOCK.menu) && $ACTIVE_BLOCK.menu neq '' && isset($SETTINGS_MENU_ITEMS[$ACTIVE_BLOCK.menu])}
                                {assign var=SINGLE_MODULE_LABEL value=vtranslate($ACTIVE_BLOCK.block, $QUALIFIED_MODULE)}
                                {assign var=DEFAULT_FILTER_URL value=$SETTINGS_MENU_ITEMS[$ACTIVE_BLOCK.menu]->getUrl()}
                            {else}
                                {assign var=SINGLE_MODULE_LABEL value=vtranslate('SINGLE_'|cat:$MODULE, $QUALIFIED_MODULE)}
                                {assign var=SETTINGS_MODULE_MODEL value=Settings_Vtiger_Module_Model::getInstance(join(['Settings:',$MODULE]))}
                                {assign var=DEFAULT_FILTER_URL value=$SETTINGS_MODULE_MODEL->getDefaultUrl()}
                            {/if}
                            <a class="col-auto p-0 fs-4 text-secondary" href="{$SETTINGS_INDEX_URL}">/</a>
                            <a class="col fs-4 text-truncate text-secondary" href="{$DEFAULT_FILTER_URL}" title="{$SINGLE_MODULE_LABEL}">{$SINGLE_MODULE_LABEL}</a>
                        {/if}
                    {elseif $MODULE}
                        {assign var=MODULE_MODEL value=Vtiger_Module_Model::getInstance($MODULE)}
                        {assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
                        {assign var=SINGLE_MODULE_LABEL value=vtranslate($SINGLE_MODULE_NAME, $MODULE)}
                        {assign var=CUSTOM_VIEW_URL value=$MODULE_MODEL->getCustomViewUrl($SELECTED_MENU_CATEGORY)}
                        {if $REQUEST_INSTANCE.view eq 'List'}
                            {assign var=SINGLE_MODULE_LABEL value=vtranslate($MODULE, $MODULE)}
                        {/if}
                        <div class="col-auto ps-4">
                            <a class="module-title fs-4 fw-bold" title="{$SINGLE_MODULE_LABEL}" href='{$CUSTOM_VIEW_URL}'>{$SINGLE_MODULE_LABEL}</a>
                        </div>
                        <a class="col-auto p-0 fs-4 text-secondary" href="{$CUSTOM_VIEW_URL}">/</a>
                        {if isset($RECORD) and $REQUEST_INSTANCE.view eq 'Edit'}
                            <a class="col-auto fs-4 text-secondary" href="{$RECORD->getEditViewUrl()}">{vtranslate('LBL_EDIT', $MODULE)}</a>
                            <a class="col-auto p-0 fs-4 text-secondary" href="{$RECORD->getEditViewUrl()}">/</a>
                            <a class="col fs-4 text-truncate text-secondary" href="{$RECORD->getDetailViewUrl()}">{$RECORD->get('label')}</a>
                        {elseif isset($RECORD) and $REQUEST_INSTANCE.view eq 'Detail'}
                            <a class="col fs-4 text-truncate text-secondary" href="{$RECORD->getDetailViewUrl()}" title="{$RECORD->get('label')}">{$RECORD->get('label')}</a>
                        {elseif $REQUEST_INSTANCE.view eq 'Edit'}
                            <a class="col-auto fs-4 text-secondary">{vtranslate('LBL_ADD_RECORD', $MODULE)}</a>
                        {elseif $REQUEST_INSTANCE.view eq 'List' and isset($MODULE_MODEL) and $MODULE_MODEL->isEntityModule()}
                            {include file="partials/CustomView.tpl"|vtemplate_path:$MODULE}
                        {else}
                            <a class="col fs-4 text-secondary">{vtranslate($REQUEST_INSTANCE.view, $MODULE)}</a>
                        {/if}
                    {else}
                        {assign var=SINGLE_MODULE_LABEL value=vtranslate('SINGLE_Settings', 'Vtiger')}
                        {assign var=CUSTOM_VIEW_URL value='index.php'}
                        <div class="col-auto ps-4">
                            <a class="module-title fs-4 fw-bold" title="{$SINGLE_MODULE_LABEL}" href='{$CUSTOM_VIEW_URL}'>{$SINGLE_MODULE_LABEL}</a>
                        </div>
                        <a class="col-auto p-0 fs-4 text-secondary" href="{$CUSTOM_VIEW_URL}">/</a>
                        <a class="col fs-4 text-secondary">{vtranslate($REQUEST_INSTANCE.view, $MODULE)}</a>
                    {/if}
                </div>
            </div>
            <div class="navbar-header col-auto ms-auto d-lg-none p-2 p-lg-0">
                <div class="d-flex align-items-center h-100">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#search-links-container" aria-controls="search-links-container" aria-expanded="false">
                        <i class="fa fa-search"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#navbar" aria-controls="navbar" aria-expanded="false">
                        <i class="fa fa-th"></i>
                    </button>
                </div>
            </div>
            <div id="search-links-container" class="search-links-container collapse navbar navbar-expand col-lg-auto d-lg-block bg-body-secondary px-3 p-lg-0 h-sub-header">
                <div class="d-flex align-items-center h-100 w-100">
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
            <div id="navbar" class="global-actions collapse navbar navbar-expand col-lg-auto d-lg-block bg-body-secondary px-3 ps-lg-0 pe-lg-4 h-sub-header">
                <ul class="nav navbar-nav ms-auto h-100 align-items-center">
                    {include file="partials/TopbarQuickCreate.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/TopbarCalendar.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/TopbarInstaller.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/TopbarAI.tpl"|vtemplate_path:$MODULE}
                    {include file="partials/TopbarUser.tpl"|vtemplate_path:$MODULE}
                </ul>
            </div>
        </div>
    </div>
    {/strip}
