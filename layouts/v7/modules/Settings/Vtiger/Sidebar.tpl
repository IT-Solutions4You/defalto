{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
	{if $USER_MODEL->isAdminUser()}
		{assign var=SETTINGS_MENU_LIST value=Settings_Vtiger_Module_Model::getSettingsMenuList()}	
		<div class="settingsgroup">
			<div>
				<input type="text" placeholder="{vtranslate('LBL_SEARCH_FOR_SETTINGS', $QUALIFIED_MODULE)}" class="search-list col-lg-8" id='settingsMenuSearch'>
			</div>
			<br><br>
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
				{foreach item=BLOCK_MENUS key=BLOCK_NAME from=$SETTINGS_MENU_LIST}
					<div class="settingsgroup-panel panel panel-default instaSearch">
						<div id="{$BLOCK_NAME}_accordion" class="app-nav" role="tab">
							<div class="app-settings-accordion">
								<div class="settingsgroup-accordion">
									<a data-toggle="collapse" data-parent="#accordion" class='collapsed' href="#{$BLOCK_NAME}">
										<i class="indicator fa{if $ACTIVE_BLOCK['block'] eq $BLOCK_NAME} fa-chevron-down {else} fa-chevron-right {/if}"></i>
										&nbsp;<span>{vtranslate($BLOCK_NAME,$QUALIFIED_MODULE)}</span>
									</a>
								</div>
							</div>
						</div>
						<div id="{$BLOCK_NAME}" class="panel-collapse collapse ulBlock {if $ACTIVE_BLOCK['block'] eq $BLOCK_NAME} in {/if}">
							<ul class="list-group widgetContainer">
								{foreach item=URL key=MENU from=$BLOCK_MENUS}
									{assign var=MENU_URL value='#'}
									{assign var=MENU_LABEL value=$MENU}

									{if $MENU eq 'My Preferences'}
										{assign var=MENU_URL value=$USER_MODEL->getPreferenceDetailViewUrl()}
									{elseif $MENU eq 'Calendar Settings'}
										{assign var=MENU_URL value=$USER_MODEL->getCalendarSettingsDetailViewUrl()}
									{elseif $MENU === $URL}
										{if $SETTINGS_MENU_ITEMS[$MENU]}
											{assign var=MENU_URL value=$SETTINGS_MENU_ITEMS[$MENU]->getURL()}
										{/if}
										{if $MENU eq 'LBL_EDIT_FIELDS'}
											{assign var=MENU_LABEL value='LBL_MODULE_CUSTOMIZATION'}
										{elseif $MENU eq 'LBL_TAX_SETTINGS'}
											{assign var=MENU_LABEL value='LBL_TAX_MANAGEMENT'}
										{elseif $MENU eq 'INVENTORYTERMSANDCONDITIONS'}
											{assign var=MENU_LABEL value='LBL_TERMS_AND_CONDITIONS'}
										{elseif $MENU eq 'LBL_PHONE_CALLS'}
											{assign var=MENU_LABEL value='LBL_PHONE_CONFIGURATION'}
										{/if}
									{elseif is_string($URL)}
										{assign var=MENU_URL value=$URL}
									{/if}
									<li><a data-name = "{$MENU}" href="{$MENU_URL}" class="menuItemLabel {if $ACTIVE_BLOCK['menu'] eq $MENU} settingsgroup-menu-color {/if}">{vtranslate($MENU_LABEL,$QUALIFIED_MODULE)}</a></li>
								{/foreach}
							</ul>
						</div>
					</div>  
				{/foreach}
			</div>
		</div>
	{else}
		{include file='modules/Users/UsersSidebar.tpl'}
	{/if}
{/strip}
