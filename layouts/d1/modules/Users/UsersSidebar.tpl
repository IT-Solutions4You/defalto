{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{assign var=SETTINGS_MENU_LIST value=Settings_Vtiger_Module_Model::getSettingsMenuListForNonAdmin()}
<div class="settingsgroup">
	<div class="panel-group accordion border-0" id="accordion" role="tablist" aria-multiselectable="true">
		<div class="accordion-item border-0 settingsSearch">
			{foreach item=BLOCK_MENUS key=BLOCK_NAME from=$SETTINGS_MENU_LIST}
				{assign var=NUM_OF_MENU_ITEMS value= $BLOCK_MENUS|@php7_count}
				{if $NUM_OF_MENU_ITEMS gt 0}
					<div id="{$BLOCK_NAME}_accordion" class="settingsSearchHeader accordion-header">
						<button class="settingsSearchButton accordion-button bg-transparent fw-bold p-3 {if $ACTIVE_BLOCK['block'] neq $BLOCK_NAME}collapsed{/if}" type="button" data-bs-toggle="collapse" data-bs-target="#{$BLOCK_NAME}_colapse">
							{vtranslate($BLOCK_NAME,$QUALIFIED_MODULE)}
						</button>
					</div>  
					<div id="{$BLOCK_NAME}_colapse" class="settingsSearchTab border-0 accordion-collapse collapse {if $ACTIVE_BLOCK['block'] eq $BLOCK_NAME}show{/if}">
						<ul class="nav nav-pills flex-column">
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
								{elseif is_string($URL)}
									{assign var=MENU_URL value=$URL}
								{/if}
								<li class="tab-item nav-link p-3 fs-6 settingsSearchTabItem">
									<div class="d-flex justify-content-between">
										<a href="{$MENU_URL}" data-name="{$MENU}" class="settingsSearchLabel {if $ACTIVE_BLOCK['menu'] eq $MENU}settingsSearchActiveLabel fw-bold{/if}">
											{vtranslate($MENU_LABEL,$QUALIFIED_MODULE)}
										</a>
									</div>
								</li>
							{/foreach}
						</ul>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
</div>
{/strip}