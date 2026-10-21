{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{if $SETUP_PENDING}
    <div class="alert alert-info d-flex justify-content-between align-items-center m-3 mb-0">
        <span>{vtranslate('LBL_PRO_SETUP_PENDING', $MODULE)}</span>
        <a class="btn btn-primary btn-sm" href="index.php?module=MailManager&parent=Settings&view=SetupWizard">{vtranslate('LBL_PRO_SETUP_OPEN', $MODULE)}</a>
    </div>
{/if}
{* the main AI is down and the backup runs (I17) – kept current by the background refresh *}
<div class="alert alert-warning py-1 px-3 m-2 mb-0 small its4you-ai-outage-bar {if !$AI_OUTAGE}hide{/if}">
    <i class="fa-solid fa-triangle-exclamation me-1"></i><span class="its4you-ai-outage-text">{if $AI_OUTAGE}{$AI_OUTAGE.text|escape}{/if}</span>
</div>
{if !$MAILBOXES}
    <div class="its4you-mailmanager container-fluid">
        <div class="its4you-mm-empty text-center">
            <div class="its4you-mm-empty-icon"><i class="fa-regular fa-envelope"></i></div>
            <h4>{vtranslate('LBL_NO_MAILBOX_CONFIGURED', $MODULE)}</h4>
            <p class="text-secondary">{vtranslate('LBL_NO_MAILBOX_HINT', $MODULE)}</p>
            {* every user may connect an own mailbox; the admin also runs the shared ones *}
            <a class="btn btn-primary" href="index.php?module={$MODULE}&view=ProAddMailbox">
                <i class="fa-solid fa-plus"></i>
                <span class="ms-2">{vtranslate('LBL_ADD_MY_MAILBOX', $MODULE)}</span>
            </a>
            {if $IS_ADMIN}
                <a class="btn btn-outline-secondary ms-2" href="{$SETTINGS_URL}">{vtranslate('LBL_OPEN_SETTINGS', $MODULE)}</a>
            {/if}
        </div>
    </div>
{else}
    <div class="its4you-mailmanager its4you-inbox"
         data-mailbox="{$SELECTED_MAILBOX.mailboxid}"
         data-default-folder="{$DEFAULT_FOLDER_ID}"
         data-triage="{if $TRIAGE_ENABLED}1{else}0{/if}"
         data-open-thread="{$OPEN_THREAD}"
         data-list-tab="{$DEFAULT_LIST_TAB}">

        <div class="its4you-inbox-sidebar">
            <div class="its4you-sidebar-group its4you-compose-row">
                <button type="button" class="btn btn-primary active its4you-compose">
                    <i class="fa-solid fa-plus"></i>
                    <span class="ms-2">{vtranslate('LBL_COMPOSE', $MODULE)}</span>
                </button>
                <button type="button" class="btn btn-outline-secondary its4you-refresh" title="{vtranslate('LBL_REFRESH', $MODULE)}">
                    <i class="fa-solid fa-refresh"></i>
                </button>
            </div>
            {* Workspace – the WORK mail (threads with a workspace label); the entry view.
               It stands above the mailbox switch: the work queue is the user's *}
            <div class="its4you-sidebar-group">
                <ul class="its4you-folder-list">
                    <li class="its4you-folder-item its4you-workspace-item active" title="{vtranslate('LBL_WORKSPACE_HINT_SIDEBAR', $MODULE)}">
                        <i class="fa-solid fa-briefcase fa-fw me-1"></i>
                        <span class="its4you-folder-name">{vtranslate('LBL_WORKSPACE', $MODULE)}</span>
                        {if $WORKSPACE_UNREAD gt 0}
                            <span class="its4you-folder-unread">{$WORKSPACE_UNREAD}</span>
                        {/if}
                    </li>
                    {* the Workspace digest (I19) – opens in place of the list and the conversation *}
                    <li class="its4you-digest-item" title="{vtranslate('LBL_DIGEST_HINT', $MODULE)|escape}">
                        <i class="fa-solid fa-clipboard-list fa-fw me-1"></i>
                        <span class="its4you-folder-name">{vtranslate('LBL_DIGEST', $MODULE)}</span>
                    </li>
                </ul>
            </div>
            {* Account card (name + address) – a single mailbox has nothing to switch to *}
            {if $MAILBOXES|@count gt 1}
                <div class="its4you-sidebar-group">
                    <div class="its4you-sidebar-title">{vtranslate('LBL_MAILBOX', $MODULE)}</div>
                    <div class="dropdown its4you-mailbox-dd">
                        <a href="javascript:void(0);" class="its4you-mailbox-card" data-bs-toggle="dropdown" data-bs-popper-config='{ldelim}"strategy":"fixed"{rdelim}' aria-expanded="false"
                           title="{$SELECTED_MAILBOX.name|escape} – {$SELECTED_MAILBOX.email|escape}">
                            <i class="fa-solid {if $SELECTED_MAILBOX.mailbox_type eq 'shared'}fa-users{else}fa-user{/if} its4you-mailbox-icon"></i>
                            <span class="its4you-mailbox-text">
                                <span class="its4you-mailbox-name">{$SELECTED_MAILBOX.name|escape}</span>
                                <span class="its4you-mailbox-email">{$SELECTED_MAILBOX.email|escape}</span>
                            </span>
                            <i class="fa-solid fa-caret-down its4you-mailbox-caret"></i>
                        </a>
                        <ul class="dropdown-menu its4you-mailbox-menu">
                            {foreach item=MAILBOX from=$MAILBOXES}
                                <li>
                                    <a class="dropdown-item {if $MAILBOX.mailboxid eq $SELECTED_MAILBOX.mailboxid}active{/if}"
                                       href="index.php?module={$MODULE}&view=List&mailbox={$MAILBOX.mailboxid}"
                                       title="{$MAILBOX.name|escape} – {$MAILBOX.email|escape}">
                                        <i class="fa-solid {if $MAILBOX.mailbox_type eq 'shared'}fa-users{else}fa-user{/if} its4you-mailbox-icon"></i>
                                        <span class="its4you-mailbox-text">
                                            <span class="its4you-mailbox-name">{$MAILBOX.name|escape}</span>
                                            <span class="its4you-mailbox-email">{$MAILBOX.email|escape}</span>
                                        </span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/if}
            <div class="its4you-sidebar-group">
                <div class="its4you-sidebar-title">{vtranslate('LBL_FOLDERS', $MODULE)}</div>
                {* Subfolders start collapsed; the caret expands one level at a time *}
                <ul class="its4you-folder-list" data-delimiter="{if $FOLDERS}{$FOLDERS[0].delimiter|escape}{/if}">
                    {foreach item=FOLDER from=$FOLDERS}
                        <li class="its4you-folder-item its4you-folder-depth-{$FOLDER.depth} {if !$FOLDER.sync_enabled}its4you-folder-unscanned{/if} {if $FOLDER.depth gt 0}hide{/if}"
                            data-folderid="{$FOLDER.folderid}" data-scanned="{$FOLDER.sync_enabled}"
                            data-path="{$FOLDER.name|escape}" data-depth="{$FOLDER.depth}"
                            title="{if !$FOLDER.sync_enabled}{vtranslate('LBL_FOLDER_ON_DEMAND', $MODULE)}{else}{$FOLDER.name|escape}{/if}">
                            {if $FOLDER.has_children}
                                <i class="fa-solid fa-caret-right its4you-folder-caret" data-path="{$FOLDER.name|escape}" data-depth="{$FOLDER.depth}"></i>
                            {else}
                                <span class="its4you-folder-caret-spacer"></span>
                            {/if}
                            <i class="{$FOLDER.icon} its4you-folder-icon"></i>
                            <span class="its4you-folder-name">{$FOLDER.display|escape}</span>
                            {* Spam / Trash keep a badge even unsynced – sync refreshes it via remote STATUS *}
                            {if $FOLDER.unread_count gt 0 && $FOLDER.shows_unread}
                                <span class="its4you-folder-unread">{$FOLDER.unread_count}</span>
                            {/if}
                        </li>
                    {/foreach}
                </ul>
                {* mail waiting for "Send later" — opens the list (ProComposer.js) *}
                <ul class="its4you-folder-list">
                    <li class="its4you-scheduled-open {if !$SCHEDULED_COUNT}hide{/if}">
                        <span class="its4you-filter-name"><i class="fa-regular fa-clock its4you-folder-icon me-1"></i>{vtranslate('LBL_SCHEDULED', $MODULE)}</span>
                        <span class="its4you-filter-count its4you-scheduled-count">{$SCHEDULED_COUNT}</span>
                    </li>
                </ul>
            </div>
            {if $SIDEBAR_LABELS}
                <div class="its4you-sidebar-group">
                    <div class="its4you-sidebar-title">{vtranslate('LBL_LABELS', $MODULE)}</div>
                    <ul class="its4you-folder-list">
                        {foreach item=SIDE_LABEL from=$SIDEBAR_LABELS}
                            {* the badge = unread conversations (like the folders), the total in the tooltip *}
                            <li class="its4you-filter-item" data-labelid="{$SIDE_LABEL.labelid}"
                                title="{vtranslate('LBL_FILTER_TOTAL', $MODULE)|replace:'%s':$SIDE_LABEL.thread_count}">
                                <span class="its4you-filter-name">
                                    <span class="its4you-label-dot" style="background: {$SIDE_LABEL.color|escape};"></span>{$SIDE_LABEL.name|escape}
                                </span>
                                {* the chip a conversation dropped here gets in the list (ProThreadList.js) *}
                                <span class="its4you-label-chip hide" style="background: {$SIDE_LABEL.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($SIDE_LABEL.color)};">{$SIDE_LABEL.name|escape}</span>
                                {if $SIDE_LABEL.unread_count gt 0}<span class="its4you-filter-count">{$SIDE_LABEL.unread_count}</span>{/if}
                            </li>
                        {/foreach}
                    </ul>
                </div>
            {/if}
            <div class="its4you-sidebar-group its4you-sidebar-last">
                <div class="its4you-sidebar-title">{vtranslate('LBL_PRIORITY', $MODULE)}</div>
                <ul class="its4you-folder-list">
                    {foreach item=PRIORITY_COUNT key=PRIORITY_KEY from=$PRIORITY_COUNTS}
                        <li class="its4you-filter-item" data-priority="{$PRIORITY_KEY}"
                            title="{vtranslate('LBL_FILTER_TOTAL', $MODULE)|replace:'%s':$PRIORITY_COUNT.total}">
                            <span class="its4you-filter-name">
                                <i class="fa-solid fa-flag its4you-priority-{$PRIORITY_KEY} me-1"></i>{vtranslate('LBL_PRIORITY_'|cat:strtoupper($PRIORITY_KEY), $MODULE)}
                            </span>
                            {if $PRIORITY_COUNT.unread gt 0}<span class="its4you-filter-count">{$PRIORITY_COUNT.unread}</span>{/if}
                        </li>
                    {/foreach}
                </ul>
            </div>

            {* Personal settings (own mailboxes, reply style) – pinned to the bottom *}
            <div class="its4you-sidebar-footer">
                {if $MY_SETTINGS_TABS}
                    <div class="dropup its4you-my-settings-dd">
                        <a href="javascript:void(0);" class="its4you-my-settings-link" data-bs-toggle="dropdown" data-bs-popper-config='{ldelim}"strategy":"fixed"{rdelim}' aria-expanded="false"
                           title="{vtranslate('LBL_MY_SETTINGS', $MODULE)}">
                            <i class="fa-solid fa-cog me-1"></i>{vtranslate('LBL_MY_SETTINGS', $MODULE)}<i class="fa-solid fa-caret-up ms-1"></i>
                        </a>
                        <ul class="dropdown-menu its4you-my-settings-menu">
                            {foreach item=MY_TAB from=$MY_SETTINGS_TABS}
                                <li>
                                    {* opens inside the inbox (ProSettingsPane.js); the href is the page fallback *}
                                    <a href="index.php?module={$MODULE}&view=ProMySettings&tab={$MY_TAB.key}"
                                       class="dropdown-item its4you-open-settings" data-tab="{$MY_TAB.key}">{$MY_TAB.label|escape}</a>
                                </li>
                            {/foreach}
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="index.php?module={$MODULE}&view=ProAddMailbox">
                                    <i class="fa-solid fa-plus me-1"></i>{vtranslate('LBL_ADD_MY_MAILBOX', $MODULE)}
                                </a>
                            </li>
                        </ul>
                    </div>
                {else}
                    <a href="index.php?module={$MODULE}&view=ProMySettings" class="its4you-my-settings-link"
                       title="{vtranslate('LBL_MY_SETTINGS', $MODULE)}">
                        <i class="fa-solid fa-cog me-1"></i>{vtranslate('LBL_MY_SETTINGS', $MODULE)}
                    </a>
                {/if}
            </div>
        </div>

        <div class="its4you-inbox-list">
            <div class="its4you-list-search">
                <i class="fa-solid fa-search"></i>
                <input type="text" class="form-control form-control-sm its4you-search-input inputElement" placeholder="{vtranslate('LBL_SEARCH_MAIL', $MODULE)}">
                <i class="fa-solid fa-times-circle its4you-search-clear hide"></i>
            </div>
            {* Tabs follow the driving CRM record (delegation works from any mailbox) *}
            <div class="its4you-list-tabs">
                {foreach item=LIST_TAB from=$LIST_TABS}
                    <span class="its4you-list-tab{if $LIST_TAB eq $DEFAULT_LIST_TAB} active{/if}" data-filter="{$LIST_TAB}">{vtranslate('LBL_TAB_'|cat:strtoupper($LIST_TAB), $MODULE)}</span>
                {/foreach}
            </div>
            {* Bulk actions of the selected conversations (the loaded page) *}
            <div class="its4you-bulk-bar">
                <label class="its4you-bulk-all-label">
                    <input type="checkbox" class="form-check-input its4you-bulk-all me-1">{vtranslate('LBL_SELECT', $MODULE)}
                </label>
                {* the count is a switch – only the ticked conversations / the whole list again *}
                <span class="its4you-bulk-count-wrap">
                    <a href="javascript:void(0);" class="its4you-bulk-count"
                       data-show="{vtranslate('LBL_SHOW_SELECTED_ONLY', $MODULE)|escape}"
                       data-all="{vtranslate('LBL_SHOW_WHOLE_LIST', $MODULE)|escape}"></a>
                </span>
                {* moves and the delete go through the "Undo" queue, the rest at once *}
                <span class="its4you-bulk-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-queue" data-action="archive"
                            title="{vtranslate('LBL_ARCHIVE', $MODULE)}" disabled><i class="fa-solid fa-archive"></i></button>
                    {* shown instead of "Move to Spam" while Spam / Trash is open (ProThreadList.js) *}
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-queue its4you-bulk-restore hide"
                            data-action="restore" data-title-spam="{vtranslate('LBL_NOT_SPAM', $MODULE)}"
                            data-title-trash="{vtranslate('LBL_RESTORE', $MODULE)}" disabled><i class="fa-solid fa-undo"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-queue its4you-bulk-spam" data-action="spam"
                            title="{vtranslate('LBL_MOVE_TO_SPAM', $MODULE)}" disabled><i class="fa-solid fa-ban"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-queue" data-action="delete"
                            title="{vtranslate('LBL_DELETE', $MODULE)}" disabled><i class="fa-solid fa-trash"></i></button>
                    <span class="its4you-bulk-sep"></span>
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-update" data-params='{ldelim}"op":"state","state":"done"{rdelim}'
                            title="{vtranslate('LBL_MARK_DONE', $MODULE)}" disabled><i class="fa-solid fa-check"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-update" data-params='{ldelim}"op":"read"{rdelim}'
                            title="{vtranslate('LBL_MARK_READ', $MODULE)}" disabled><i class="fa-solid fa-eye"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm its4you-bulk-action its4you-bulk-update" data-params='{ldelim}"op":"unread"{rdelim}'
                            title="{vtranslate('LBL_MARK_UNREAD', $MODULE)}" disabled><i class="fa-solid fa-eye-slash"></i></button>
                    <span class="its4you-bulk-sep"></span>
                    <span class="dropdown its4you-move-dd">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle its4you-bulk-action" data-bs-toggle="dropdown"
                                aria-expanded="false" title="{vtranslate('LBL_PRO_MOVE_TO', $MODULE)}" disabled><i class="fa-regular fa-folder"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end its4you-move-menu">
                            <li class="its4you-move-search">
                                <input type="text" class="form-control form-control-sm inputElement" placeholder="{vtranslate('LBL_SEARCH', $MODULE)}">
                            </li>
                            {foreach item=MOVE_FOLDER from=$FOLDERS}
                                <li class="its4you-move-item">
                                    <a href="javascript:void(0);" class="dropdown-item its4you-bulk-queue its4you-move-depth-{$MOVE_FOLDER.depth}" data-action="folder"
                                       data-folderid="{$MOVE_FOLDER.folderid}" data-path="{$MOVE_FOLDER.name|escape}">
                                        <i class="{$MOVE_FOLDER.icon} me-1"></i>{$MOVE_FOLDER.display|escape}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </span>
                    <span class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle its4you-bulk-action" data-bs-toggle="dropdown"
                                aria-expanded="false" title="{vtranslate('LBL_CHANGE_LABEL', $MODULE)}" disabled><i class="fa-solid fa-tag"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a href="javascript:void(0);" class="dropdown-item its4you-bulk-update" data-params='{ldelim}"op":"label","labelid":0{rdelim}'>{vtranslate('LBL_NO_LABEL', $MODULE)}</a></li>
                            {foreach item=SIDE_LABEL from=$SIDEBAR_LABELS}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-bulk-update" data-params='{ldelim}"op":"label","labelid":{$SIDE_LABEL.labelid}{rdelim}'>
                                        <span class="its4you-label-chip" style="background: {$SIDE_LABEL.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($SIDE_LABEL.color)};">{$SIDE_LABEL.name|escape}</span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </span>
                    <span class="dropdown">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle its4you-bulk-action" data-bs-toggle="dropdown"
                                aria-expanded="false" title="{vtranslate('LBL_CHANGE_PRIORITY', $MODULE)}" disabled><i class="fa-solid fa-flag"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            {foreach item=PRIORITY_COUNT key=PRIORITY_KEY from=$PRIORITY_COUNTS}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-bulk-update" data-params='{ldelim}"op":"priority","priority":"{$PRIORITY_KEY}"{rdelim}'>
                                        <span class="its4you-priority its4you-priority-{$PRIORITY_KEY}"><i class="fa-solid fa-flag me-1"></i>{vtranslate('LBL_PRIORITY_'|cat:strtoupper($PRIORITY_KEY), $MODULE)}</span>
                                    </a>
                                </li>
                            {/foreach}
                            <li><hr class="dropdown-divider"></li>
                            <li><a href="javascript:void(0);" class="dropdown-item its4you-bulk-update" data-params='{ldelim}"op":"priority","priority":""{rdelim}'>{vtranslate('LBL_NO_PRIORITY', $MODULE)}</a></li>
                        </ul>
                    </span>
                </span>
            </div>
            <div class="its4you-thread-list"></div>
        </div>

        {* Drag handle – the user resizes the thread list column *}
        <div class="its4you-list-resizer" title="{vtranslate('LBL_DRAG_TO_RESIZE', $MODULE)}"></div>

        <div class="its4you-inbox-conversation">
            <div class="its4you-conversation-content">
                <div class="its4you-conversation-empty text-center">
                    <i class="fa-regular fa-comments"></i>
                    <p>{vtranslate('LBL_SELECT_CONVERSATION', $MODULE)}</p>
                </div>
            </div>
            <div class="its4you-crm-rail hide">
                {* CRM | AI assistant – the assistant tab keeps its chat, the CRM panel reloads per conversation *}
                {if $AI_ASSISTANT_ENABLED}
                    <div class="its4you-rail-tabs">
                        <a href="javascript:void(0);" class="its4you-rail-tab active" data-tab="crm">{vtranslate('LBL_CRM', $MODULE)}</a>
                        <a href="javascript:void(0);" class="its4you-rail-tab" data-tab="assistant">
                            <i class="fa-solid fa-magic me-1"></i>{vtranslate('LBL_AI_ASSISTANT', $MODULE)}
                        </a>
                    </div>
                {/if}
                <div class="its4you-crm-rail-body"></div>
                {if $AI_ASSISTANT_ENABLED}
                    <div class="its4you-assistant d-none"
                         data-action-draft="{vtranslate('LBL_ACTION_DRAFT', $MODULE)|escape}"
                         data-action-task="{vtranslate('LBL_ACTION_TASK', $MODULE)|escape}"
                         data-action-ticket="{vtranslate('LBL_ACTION_TICKET', $MODULE)|escape}"
                         data-action-done="{vtranslate('LBL_ACTION_DONE', $MODULE)|escape}">
                        <div class="its4you-assistant-hint">{vtranslate('LBL_ASSISTANT_HINT', $MODULE)}</div>
                        <div class="its4you-assistant-chips">
                            <a href="javascript:void(0);" class="its4you-assistant-chip" data-mode="summary">{vtranslate('LBL_ASK_SUMMARY', $MODULE)}</a>
                            <a href="javascript:void(0);" class="its4you-assistant-chip" data-mode="ask">{vtranslate('LBL_ASK_INTENT', $MODULE)}</a>
                            <a href="javascript:void(0);" class="its4you-assistant-chip" data-mode="ask">{vtranslate('LBL_ASK_CRM', $MODULE)}</a>
                            <a href="javascript:void(0);" class="its4you-assistant-chip" data-mode="ask">{vtranslate('LBL_ASK_REPLY', $MODULE)}</a>
                        </div>
                        <div class="its4you-assistant-messages"></div>
                        <div class="its4you-assistant-input">
                            <textarea class="form-control its4you-assistant-question" rows="2"
                                      placeholder="{vtranslate('LBL_ASSISTANT_PLACEHOLDER', $MODULE)|escape}"></textarea>
                            <button type="button" class="btn btn-outline-secondary its4you-assistant-send" title="{vtranslate('LBL_SEND', 'Vtiger')|escape}">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                        <a href="javascript:void(0);" class="its4you-assistant-clear">{vtranslate('LBL_ASSISTANT_CLEAR', $MODULE)}</a>
                    </div>
                {/if}
            </div>
        </div>

        {* My Settings inside the inbox – replaces the list and the conversation *}
        <div class="its4you-settings-pane"></div>
        {* the Workspace digest (I19) – in the same place *}
        <div class="its4you-digest-pane"></div>
    </div>
{/if}
{/strip}
