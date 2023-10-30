{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{include file="modules/Vtiger/partials/Topbar.tpl"}

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
<div class="main-container main-container-{$MODULE}">
    {assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
    <div id="modnavigator" class="module-nav">
        <div class="hidden-xs hidden-sm mod-switcher-container">
        </div>
    </div>
    <div id="sidebar-essentials" class="sidebar-essentials {if $LEFTPANELHIDE eq '1'} hide {/if}">
        {include file="partials/SidebarEssentials.tpl"|vtemplate_path:$MODULE}
    </div>

    <div class="listViewPageDiv content-area {if $LEFTPANELHIDE eq '1'} full-width {/if}" id="listViewContent">