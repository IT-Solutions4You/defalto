{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="table-actions text-secondary">
		<div class="more dropdown action">
			<div class="btn text-secondary" href="javascript:;" data-bs-toggle="dropdown">
				<i title="{vtranslate("LBL_MORE_OPTIONS",$MODULE)}" class="fa fa-ellipsis-v icon"></i>
			</div>
			<ul class="dropdown-menu">
				{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
					{if Users_Privileges_Model::isPermittedToChangeUsername($LISTVIEW_ENTRY->getId())}
						<li>
							<a class="dropdown-item" onclick="Settings_Users_List_Js.triggerChangeUsername('{$LISTVIEW_ENTRY->getChangeUsernameUrl()}');">{vtranslate('LBL_CHANGE_USERNAME', $MODULE)}</a>
						</li>
					{/if}
					<li>
						<a class="dropdown-item" onclick="Settings_Users_List_Js.triggerChangePassword('{$LISTVIEW_ENTRY->getChangePwdUrl()}');">{vtranslate('LBL_CHANGE_PASSWORD', $MODULE)}</a>
					</li>
					{if $IS_MODULE_EDITABLE && $LISTVIEW_ENTRY->get('status') eq 'Active'}
						<li>
							<a class="dropdown-item editLink" href="{$LISTVIEW_ENTRY->getEditViewUrl()}&parentblock=LBL_USER_MANAGEMENT">{vtranslate('LBL_EDIT', $MODULE)}</a>
						</li>
					{/if}
				{/if}
				{if $IS_MODULE_DELETABLE && $LISTVIEW_ENTRY->getId() != $USER_MODEL->getId()}
					{if $LISTVIEW_ENTRY->get('status') eq 'Active'}
						<li>
							<a class="dropdown-item" href='javascript:Settings_Users_List_Js.triggerDeleteUser("{$LISTVIEW_ENTRY->getDeleteUrl()}")'>{vtranslate("LBL_REMOVE_USER",$MODULE)}</i></a>
						</li>
					{else}
						<li>
							<a class="dropdown-item" onclick="Settings_Users_List_Js.restoreUser({$LISTVIEW_ENTRY->getId()}, event);">{vtranslate("LBL_RESTORE_USER",$MODULE)}</a>
						</li>
						<li>
							<a class="dropdown-item" href='javascript:Settings_Users_List_Js.triggerDeleteUser("{$LISTVIEW_ENTRY->getDeleteUrl()}", "true")'>{vtranslate("LBL_REMOVE_USER",$MODULE)}</i></a>
						</li>
					{/if}
				{/if}
			</ul>
		</div>
	</div>
{/strip}