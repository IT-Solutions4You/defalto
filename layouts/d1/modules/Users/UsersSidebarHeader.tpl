{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-{$SELECTED_MENU_CATEGORY}">
    <div class="row" title="{vtranslate("LBL_SETTINGS",$MODULE)}">
        <span class="app-indicator-icon cursorPointer fa fa-cog"></span>
    </div>
</div>
        
{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}