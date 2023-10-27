{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    {include file="modules/Vtiger/partials/Topbar.tpl"}
    <div class="container-fluid app-nav">
        <div class="row">
            {include file='partials/SidebarHeader.tpl'|vtemplate_path:$MODULE}
            {include file='ModuleHeader.tpl'|vtemplate_path:$MODULE}
        </div>
    </div>
    </nav>
     <div id='overlayPageContent' class='fade modal overlayPageContent content-area' tabindex='-1' role='dialog' aria-hidden='true'>
        <div class="data h-100">
        </div>
        <div class="modal-dialog">
        </div>
    </div>
    <div class="container-fluid main-container">
        <div class="row">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            <div class="detailViewContainer viewContent col p-0 overflow-auto">
                <div class="content-area container-fluid px-0 pb-lg-4 px-lg-4">
                    {include file='DetailViewHeader.tpl'|vtemplate_path:$MODULE}
                    <div class="detailview-content row">
                        <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
                        {include file="ModuleRelatedTabs.tpl"|vtemplate_path:$MODULE}
                        <div class="details col-lg-9 col-xl-10 order-1 px-0">
{/strip}