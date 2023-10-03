{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{include file=vtemplate_path('partials/Topbar.tpl', $MODULE)}

<div class="container-fluid app-nav">
    <div class="row">
        {include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
        {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
    </div>
</div>
</nav>
    <div id='overlayPageContent' class='fade modal overlayPageContent content-area' tabindex='-1' role='dialog' aria-hidden='true'>
        <div class="data">
        </div>
        <div class="modal-dialog">
        </div>
    </div>
<div class="main-container container-fluid main-container-{$MODULE}">
    <div class="row h-main">
        {include file='ModuleSidebar.tpl'|vtemplate_path:$MODULE}
        <div class="col-lg px-0 bg-white mb-lg-4 mx-lg-4 rounded overflow-hidden mmModuleContainer">
            <div id="listViewContent" class="listViewPageDiv content-area h-100">
