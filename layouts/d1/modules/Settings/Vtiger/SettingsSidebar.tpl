{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="settings-sidebar" id="settings_sidebar" >
		<div class="sidebar-container lists-menu-container">
			<h3 class="lists-header">
				<a style="color: white; cursor: default;" href="index.php?module=Vtiger&parent=Settings&view=Index">
					{vtranslate('LBL_SETTINGS', $QUALIFIED_MODULE)}</a>
			</h3>
			<hr>
			<div class="settings-menu">
				{foreach item=MENU from=$SETTINGS_MENUS}
					<div class="col-sm-12 settings-flip show_hide" style="width:100% !important">
						<span class="col-sm-10 col-xs-10" style="font-size: 18px;color: #fff">
							{vtranslate($MENU->getLabel(), $QUALIFIED_MODULE)}
						</span>
						<span class="col-sm- col-xs-2">
							<i class="fa fa-chevron-down"></i> 
						</span>
					</div>
					<div class="col-sm-12 settings-menu-items slidingDiv">
						{foreach item=MENUITEM from=$MENU->getMenuItems()}
							<a href="{$MENUITEM->getUrl()}" data-id="{$MENUITEM->getId()}" data-menu-item="true" >{vtranslate($MENUITEM->get('name'), $QUALIFIED_MODULE)}</a><br>
						{/foreach}
					</div>
				{/foreach}
			</div>
		</div>
	</div>
{/strip}
