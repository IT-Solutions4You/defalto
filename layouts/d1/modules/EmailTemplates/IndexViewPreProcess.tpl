{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{include file="modules/Vtiger/partials/Topbar.tpl"}
<div class="container-fluid app-nav">
    <div class="row">
        {include file="modules/Settings/Vtiger/SidebarHeader.tpl"}
        {assign var=ACTIVE_BLOCK value=['block' => 'Templates',
                                        'menu' => $smary.request.module]}
        {include file="modules/Settings/Vtiger/ModuleHeader.tpl"}
    </div>
</div>
</nav>
 <div id='overlayPageContent' class='fade modal overlayPageContent content-area' tabindex='-1' role='dialog' aria-hidden='true'>
        <div class="data">
        </div>
        <div class="modal-dialog">
        </div>
    </div>
