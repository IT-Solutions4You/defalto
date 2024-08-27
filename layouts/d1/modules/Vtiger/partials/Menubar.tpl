{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{if $MENU_STRUCTURE}
{assign var="topMenus" value=$MENU_STRUCTURE->getTop()}
{assign var="moreMenus" value=$MENU_STRUCTURE->getMore()}

<div id="modules-menu" class="modules-menu d-flex flex-column align-items-center">
	{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
	<div class="app-indicator-icon-container px-0 py-2 m-0" data-menu-category="{$SELECTED_MENU_CATEGORY}">
		<div class="app-indicator-active active" title="{if $MODULE eq 'Home' || !$MODULE} {vtranslate('LBL_DASHBOARD')} {else}{vtranslate("LBL_$SELECTED_MENU_CATEGORY")}{/if}">
			<span class="app-indicator-icon rounded">
				<i class="fa {$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}"></i>
			</span>
		</div>
	</div>
	{foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
		{assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
		<div title="{$translatedModuleLabel}" class="px-0 py-2 m-0">
			<div class="app-module-container rounded {if $MODULE eq $moduleName}active{else}opacity-50{/if}">
				<a href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{$translatedModuleLabel}">
					{$moduleModel->getModuleIcon('14px')}
				</a>
			</div>
		</div>
	{/foreach}
</div>
{/if}
