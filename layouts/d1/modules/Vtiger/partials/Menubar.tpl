{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{if $MENU_STRUCTURE}
{assign var="topMenus" value=$MENU_STRUCTURE->getTop()}
{assign var="moreMenus" value=$MENU_STRUCTURE->getMore()}

<div id="modules-menu" class="modules-menu">
	{assign var=APP_IMAGE_MAP value=Vtiger_MenuStructure_Model::getAppIcons()}
	<ul class="app-indicator-icon-container px-0 py-2 m-0" data-menu-category="{$SELECTED_MENU_CATEGORY}">
		<li class="app-indicator-active active" title="{if $MODULE eq 'Home' || !$MODULE} {vtranslate('LBL_DASHBOARD')} {else}{vtranslate("LBL_$SELECTED_MENU_CATEGORY")}{/if}">
			<span class="app-indicator-icon mx-auto rounded">
				<i class="fa {if $MODULE eq 'Home' || !$MODULE}fa-dashboard{else}{$APP_IMAGE_MAP[$SELECTED_MENU_CATEGORY]}{/if}"></i>
			</span>
		</li>
	</ul>
	{foreach key=moduleName item=moduleModel from=$SELECTED_CATEGORY_MENU_LIST}
		{assign var='translatedModuleLabel' value=vtranslate($moduleModel->get('label'),$moduleName )}
		<ul title="{$translatedModuleLabel}" class="module-qtip px-0 py-2 m-0">
			<li class="{if $MODULE eq $moduleName}active{/if}">
				<a href="{$moduleModel->getDefaultUrl()}&app={$SELECTED_MENU_CATEGORY}" class="mx-auto" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{$translatedModuleLabel}">
					{$moduleModel->getModuleIcon()}
				</a>
			</li>
		</ul>
	{/foreach}
</div>
{/if}
