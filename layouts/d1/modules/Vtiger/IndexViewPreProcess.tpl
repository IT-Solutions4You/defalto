{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Vtiger/views/Index.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
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
