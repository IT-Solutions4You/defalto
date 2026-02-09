{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{include file="modules/Vtiger/partials/Topbar.tpl"}
<div class="container-fluid app-nav">
    <div class="row">
        {include file='partials/SidebarHeader.tpl'|vtemplate_path:$MODULE}
        {include file='ModuleHeader.tpl'|vtemplate_path:$MODULE}
    </div>
</div>
</nav>
<div class="container-fluid main-container">
    <div class="row">
        {include file='ModuleNavigator.tpl'|vtemplate_path:$MODULE}
        <div class="detailViewContainer viewContent col p-0 overflow-auto">
            <div class="content-area container-fluid px-0 pb-lg-4 px-lg-4">
                {include file='DetailViewHeader.tpl'|vtemplate_path:$MODULE}
                <div class="detailview-content row">
                    <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
                    <div class="details col-lg-12 col-xl-12 order-1 px-0">