{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {include file="modules/Vtiger/partials/Topbar.tpl"}

    <div class="container-fluid app-nav">
        <div class="row">
            {include file="modules/Reports/partials/SidebarHeader.tpl"}
            {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
</nav>
<div class="clearfix main-container">
    <div>
        <div class="editViewPageDiv viewContent">
            <div class="reports-content-area">
{/strip}