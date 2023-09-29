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
		{assign var=SETTINGS_MODULE_MODEL value= Settings_Vtiger_Module_Model::getInstance()}
		{assign var=SETTINGS_MENUS value=$SETTINGS_MODULE_MODEL->getMenus()}
		<div class="settingsgroup py-3">
			<div class="px-3 pb-3">
				<input type="text" placeholder="{vtranslate('LBL_SEARCH_FOR_SETTINGS', $QUALIFIED_MODULE)}" class="settingsSearchInput form-control" id="settingsMenuSearch">
			</div>
			<div class="panel-group accordion border-0" id="accordion" role="tablist" aria-multiselectable="true">
				{foreach item=BLOCK_MENUS from=$SETTINGS_MENUS}
					{assign var=BLOCK_NAME value=$BLOCK_MENUS->getLabel()}
					{assign var=BLOCK_MENU_ITEMS value=$BLOCK_MENUS->getMenuItems()}
					{assign var=NUM_OF_MENU_ITEMS value= $BLOCK_MENU_ITEMS|@sizeof}
					{if $NUM_OF_MENU_ITEMS gt 0}
						<div class="accordion-item border-0 settingsSearch">
							<div id="{$BLOCK_NAME}_accordion" class="settingsSearchHeader accordion-header">
								<button class="settingsSearchButton accordion-button bg-transparent fw-bold p-3 {if $ACTIVE_BLOCK['block'] neq $BLOCK_NAME}collapsed{/if}" type="button" data-bs-toggle="collapse" data-bs-target="#{$BLOCK_NAME}_colapse">
									{vtranslate($BLOCK_NAME,$QUALIFIED_MODULE)}
								</button>
							</div>
							<div id="{$BLOCK_NAME}_colapse" class="settingsSearchTab border-0 accordion-collapse collapse {if $ACTIVE_BLOCK['block'] eq $BLOCK_NAME}show{/if}">
								<ul class="nav nav-pills flex-column">
									{foreach item=MENUITEM from=$BLOCK_MENU_ITEMS}
										{assign var=MENU value= $MENUITEM->get('name')}
										{assign var=MENU_LABEL value=$MENU}
										{if $MENU eq 'LBL_EDIT_FIELDS'}
											{assign var=MENU_LABEL value='LBL_MODULE_CUSTOMIZATION'}
										{elseif $MENU eq 'LBL_TAX_SETTINGS'}
											{assign var=MENU_LABEL value='LBL_TAX_MANAGEMENT'}
										{elseif $MENU eq 'INVENTORYTERMSANDCONDITIONS'}
											{assign var=MENU_LABEL value='LBL_TERMS_AND_CONDITIONS'}
										{/if}
										{assign var=MENU_URL value=$MENUITEM->getUrl()}
										{assign var=USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
										{if $MENU eq 'My Preferences'}
											{assign var=MENU_URL value=$USER_MODEL->getPreferenceDetailViewUrl()}
										{elseif $MENU eq 'Calendar Settings'}
											{assign var=MENU_URL value=$USER_MODEL->getCalendarSettingsDetailViewUrl()}
										{/if}
										<li class="tab-item nav-link p-3 fs-6 settingsSearchTabItem">
											<div class="d-flex justify-content-between">
												<a href="{$MENU_URL}" data-name="{$MENU}" class="settingsSearchLabel {if $ACTIVE_BLOCK['menu'] eq $MENU}settingsSearchActiveLabel fw-bold{/if}">
													{vtranslate($MENU_LABEL,$QUALIFIED_MODULE)}
												</a>
												<a id="{$MENUITEM->getId()}_menuItem" data-id="{$MENUITEM->getId()}" class="pinUnpinShortCut"
													 data-actionurl="{$MENUITEM->getPinUnpinActionUrl()}"
													 data-pintitle="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}"
													 data-unpintitle="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}"
													 data-pinimageurl="{{vimage_path('pin.png')}}"
													 data-unpinimageurl="{{vimage_path('unpin.png')}}"
														{if $MENUITEM->isPinned()}
													title="{vtranslate('LBL_UNPIN',$QUALIFIED_MODULE)}" src="{vimage_path('unpin.png')}" data-action="unpin"
														{else}
													title="{vtranslate('LBL_PIN',$QUALIFIED_MODULE)}" src="{vimage_path('pin.png')}" data-action="pin"
														{/if}>

													{if $MENUITEM->isPinned()}
														<i class="fa-solid fa-link-slash"></i>
													{else}
														<i class="fa-solid fa-link"></i>
													{/if}
												</a>
											</div>
										</li>
									{/foreach}
								</ul>
							</div>
						</div>
					{/if}
				{/foreach}
			</div>
		</div>
	{else}
		{include file='modules/Users/UsersSidebar.tpl'}
	{/if}
{/strip}
