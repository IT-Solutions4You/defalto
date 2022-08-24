{* /**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */ *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{include file="modules/Vtiger/partials/Topbar.tpl"}

<div class="container-fluid app-nav">
    <div class="row">
        {include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
        {include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
    </div>
</div>
</nav>    
     <div id='overlayPageContent' class='fade modal overlayPageContent content-area overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>
        <div class="data">
        </div>
        <div class="modal-dialog">
        </div>
    </div>
<div class="container-fluid main-container">
    <div class="row">
        <div id="modnavigator" class="module-nav detailViewModNavigator clearfix">
            <div class="hidden-xs hidden-sm mod-switcher-container">
                {include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
            </div>
        </div>
        <div class="detailViewContainer viewContent clearfix">
            <div class="col-sm-12 col-xs-12 content-area">
                {include file="DetailViewHeader.tpl"|vtemplate_path:$MODULE}
            </div>{*closing div of detailviewHeader*}
                <div class="detailview-content container-fluid">
                    <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
                    {include file="ModuleRelatedTabs.tpl"|vtemplate_path:$MODULE}
                    <div class="details row" style="margin-top:10px;">
