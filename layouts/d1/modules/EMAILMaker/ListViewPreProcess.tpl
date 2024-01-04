{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
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
    <div id='overlayPageContent' class='fade modal overlayPageContent content-area' tabindex='-1' role='dialog' aria-hidden='true'>
        <div class="data">
        </div>
        <div class="modal-dialog">
        </div>
    </div>
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