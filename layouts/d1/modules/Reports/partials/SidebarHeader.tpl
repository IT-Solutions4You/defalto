{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}

<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-{$SELECTED_MENU_CATEGORY}">
    <div class="row" title="{strtoupper(vtranslate($MODULE, $MODULE))}">
        <span class="app-indicator-icon fa fa-bar-chart"></span>
    </div>
</div>

{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}