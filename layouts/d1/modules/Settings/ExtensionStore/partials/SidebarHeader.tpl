{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
<div class="col-sm-12 col-xs-12 app-indicator-icon-container extensionstore app-{$SELECTED_MENU_CATEGORY}"> 
    <div class="row" title="{vtranslate('LBL_EXTENSION_STORE', 'Settings:ExtensionStore')}"> 
        <span class="app-indicator-icon cursorPointer fa fa-shopping-cart"></span> 
    </div>
</div>
  
{include file="modules/Vtiger/partials/SidebarAppMenu.tpl"}
