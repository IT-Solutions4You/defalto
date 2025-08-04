{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="dashboardHeading">
	<div class="buttonGroups p-2 bg-body text-end rounded-bottom">
		<div class="btn-group">
			{if $SELECTABLE_WIDGETS|count gt 0}
				<button class="btn btn-outline-secondary addButton dropdown-toggle" data-bs-toggle="dropdown">
					<span>{vtranslate('LBL_ADD_WIDGET')}</span>
					<i class="caret ms-2"></i>
				</button>
				<ul class="dropdown-menu dropdown-menu-right widgetsList">
					{assign var="MINILISTWIDGET" value=""}
					{foreach from=$SELECTABLE_WIDGETS item=WIDGET}
						{if $WIDGET->getName() eq 'MiniList'}
							{assign var="MINILISTWIDGET" value=$WIDGET} {* Defer to display as a separate group *}
						{elseif $WIDGET->getName() eq 'Notebook'}
							{assign var="NOTEBOOKWIDGET" value=$WIDGET} {* Defer to display as a separate group *}
						{else}
							<li>
								<a class="dropdown-item" onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);" data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}">
									{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}
								</a>
							</li>
						{/if}
					{/foreach}
					{if $MINILISTWIDGET && $MODULE_NAME == 'Home'}
						<li class="divider"></li>
						<li>
							<a class="dropdown-item" onclick="Vtiger_DashBoard_Js.addMiniListWidget(this, '{$MINILISTWIDGET->getUrl()}')" href="javascript:void(0);" data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}">
								{vtranslate($MINILISTWIDGET->getTitle(), $MODULE_NAME)}
							</a>
						</li>
						<li>
							<a class="dropdown-item" onclick="Vtiger_DashBoard_Js.addNoteBookWidget(this, '{$NOTEBOOKWIDGET->getUrl()}')" href="javascript:void(0);" data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}">
								{vtranslate($NOTEBOOKWIDGET->getTitle(), $MODULE_NAME)}
							</a>
						</li>
					{/if}
				</ul>
			{elseif $MODULE_PERMISSION}
				<button class="btn btn-outline-secondary addButton dropdown-toggle" disabled="disabled" data-bs-toggle="dropdown">
					<strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
					<i class="caret ms-2"></i>
				</button>
			{/if}
		</div>
	</div>
</div>
