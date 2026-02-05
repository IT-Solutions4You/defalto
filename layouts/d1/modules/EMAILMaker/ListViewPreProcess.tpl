{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {include file=vtemplate_path('partials/Topbar.tpl', $MODULE)}
    <div class="container-fluid app-nav">
        <div class="row">
            {include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
            {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
        </div>
    </div>
    </nav>
    <div class="container-fluid main-container main-container-{$MODULE}">
        <div class="row">
            {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
            {include file='ModuleSidebar.tpl'|vtemplate_path:$MODULE}
            <div class="listViewContainerJs col-lg px-0 bg-body mb-lg-4 mx-lg-4 rounded overflow-hidden">
                <div class="listViewModuleLinksJs">
                    {include file="ModuleLinks.tpl"|vtemplate_path:$MODULE}
                </div>
                <div id="listViewContent" class="listViewPageDiv content-area">
{/strip}