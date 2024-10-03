{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {include file="partials/Topbar.tpl"|vtemplate_path:$MODULE}
    <div class="container-fluid app-nav">
        <div class="row">
            {include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
            {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
    </nav>
    {include file="OverlayPageContent.tpl"|vtemplate_path:$MODULE}
    <div class="container-fluid main-container main-container-{$MODULE}">
        <div class="row">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            <div class="col-lg px-0 mb-lg-4 mx-lg-4 rounded overflow-hidden">


{/strip}
