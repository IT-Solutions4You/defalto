{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
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
                    <div class="details col-lg-12 col-xl-12 order-1 px-0">
