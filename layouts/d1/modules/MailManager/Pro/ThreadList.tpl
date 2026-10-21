{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{foreach item=THREAD from=$THREADS}
    <div class="its4you-thread-row {if $THREAD.unread_count gt 0}unread{/if}" data-threadid="{$THREAD.threadid}" data-junk="{$JUNK_TYPE}" data-mailbox="{$THREAD.mailboxid}"
         data-lastat="{$THREAD.folder_last_at|escape}">
        <div class="its4you-thread-top">
            <span class="its4you-thread-from">
                {* bulk selection *}
                <input type="checkbox" class="form-check-input its4you-thread-check" title="{vtranslate('LBL_SELECT', $MODULE)}">
                {* the newest message when it is not a plain new mail – a copy / own reply, forward, mail *}
                {if $THREAD.kind_icon}<i class="{$THREAD.kind_icon} its4you-thread-kind its4you-thread-kind-{$THREAD.kind}" title="{$THREAD.kind_title|escape}"></i>{/if}
                {$THREAD.last_direction_marker}{$THREAD.last_from|escape}
            </span>
            <span class="its4you-thread-time">{$THREAD.time_display}</span>
            {* Hover quick actions – replace the timestamp *}
            <span class="its4you-row-actions">
                {if $THREAD.state && !$THREAD.state.is_done}
                    <a href="javascript:void(0);" class="its4you-row-state" data-state="done" title="{vtranslate('LBL_MARK_DONE', $MODULE)}">
                        <i class="fa-solid fa-check"></i>
                    </a>
                {/if}
                {if $JUNK_TYPE}
                    <a href="javascript:void(0);" class="its4you-row-action" data-action="restore"
                       title="{if $JUNK_TYPE eq 'spam'}{vtranslate('LBL_NOT_SPAM', $MODULE)}{else}{vtranslate('LBL_RESTORE', $MODULE)}{/if}">
                        <i class="fa-solid fa-undo"></i>
                    </a>
                {else}
                    <a href="javascript:void(0);" class="its4you-row-action" data-action="spam" title="{vtranslate('LBL_MOVE_TO_SPAM', $MODULE)}">
                        <i class="fa-solid fa-ban"></i>
                    </a>
                {/if}
                <a href="javascript:void(0);" class="its4you-row-action" data-action="delete" title="{vtranslate('LBL_DELETE', $MODULE)}">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </span>
        </div>
        {* the priority flag leads the subject; a high / urgent subject takes the colour too *}
        {assign var=HOT_PRIORITY value=($THREAD.priority eq 'high' || $THREAD.priority eq 'urgent')}
        <div class="its4you-thread-subject{if $HOT_PRIORITY} its4you-thread-subject-{$THREAD.priority}{/if}">{if $THREAD.priority}<span class="its4you-priority its4you-priority-{$THREAD.priority} its4you-subject-flag" title="{vtranslate('LBL_PRIORITY_'|cat:strtoupper($THREAD.priority), $MODULE)}"><i class="fa-solid fa-flag"></i></span>{/if}{if $THREAD.subject}{$THREAD.subject|escape}{else}({vtranslate('LBL_NO_SUBJECT', $MODULE)}){/if}</div>
        <div class="its4you-thread-bottom">
            <span class="its4you-thread-snippet">{$THREAD.snippet|escape}</span>
        </div>
        {* Tag line – mailbox, label, folders, count, paperclip, state and owner *}
        {if $THREAD.label_name || $THREAD.message_count gt 1 || $THREAD.has_attachments || $THREAD.driving || $THREAD.state || $THREAD.locations || $THREAD.mailbox_chip}
            <div class="its4you-thread-tags">
                {* the Workspace spans mailboxes – which one the conversation is from *}
                {if $THREAD.mailbox_chip}
                    <span class="its4you-folder-chip its4you-mailbox-chip">
                        <i class="fa-solid {if $THREAD.mailbox_chip.type eq 'shared'}fa-users{else}fa-user{/if} me-1"></i>{$THREAD.mailbox_chip.name|escape}
                    </span>
                {/if}
                {if $THREAD.label_name}<span class="its4you-label-chip" style="background: {$THREAD.label_color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($THREAD.label_color)};">{$THREAD.label_name|escape}</span>{/if}
                {* mixed views only: the folders outside the Inbox the conversation's mail lies in *}
                {foreach item=LOCATION from=$THREAD.locations}
                    <span class="its4you-folder-chip" title="{$LOCATION.path|escape}">
                        <i class="fa-solid fa-folder me-1"></i>{$LOCATION.display|escape}
                    </span>
                {/foreach}
                {if $THREAD.message_count gt 1}<span class="its4you-thread-count"><i class="fa-regular fa-envelope me-1"></i>{$THREAD.message_count}</span>{/if}
                {if $THREAD.has_attachments}<i class="fa-solid fa-paperclip its4you-thread-clip"></i>{/if}
                {* the DRIVING record's process state + owner – no manual thread status *}
                {if $THREAD.driving}
                    {if $THREAD.driving.status_display}
                        <span class="its4you-status-chip {if $THREAD.driving.is_done}its4you-status-chip-done{else}its4you-status-chip-progress{/if}"
                              title="{$THREAD.driving.module_label|escape}: {$THREAD.driving.record_label|escape}">
                            {* the list has no subject icon – the chip says which record it is *}
                            <span class="me-1">{$THREAD.driving.icon}</span>{$THREAD.driving.status_display|escape}
                        </span>
                    {/if}
                    {if $THREAD.driving.owner_initials}
                        <span class="its4you-thread-avatar" title="{$THREAD.driving.owner_name|escape}">{$THREAD.driving.owner_initials|escape}</span>
                    {/if}
                {elseif $THREAD.state}
                    {* no record – the conversation's own state and who has it *}
                    <span class="its4you-status-chip its4you-status-chip-{$THREAD.state.key}">{$THREAD.state.label|escape}</span>
                    {if $THREAD.state.owner_initials}
                        <span class="its4you-thread-avatar" title="{$THREAD.state.owner_name|escape}">{$THREAD.state.owner_initials|escape}</span>
                    {/if}
                {/if}
            </div>
        {/if}
    </div>
{foreachelse}
    {if $PAGE eq 1}
        <div class="its4you-thread-list-empty text-center">{vtranslate('LBL_NO_CONVERSATIONS', $MODULE)}</div>
    {/if}
{/foreach}
{if $HAS_MORE}
    <div class="its4you-load-more-wrap text-center">
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-load-more" data-page="{$PAGE+1}">{vtranslate('LBL_LOAD_MORE', $MODULE)}</button>
    </div>
{elseif $CAN_SEARCH_SERVER}
    {* the local search covers the stored mail only *}
    <div class="its4you-load-more-wrap text-center">
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-search-server">
            <i class="fa-solid fa-server me-1"></i>{vtranslate('LBL_SEARCH_SERVER', $MODULE)}
        </button>
        <div class="its4you-muted its4you-search-server-hint">{vtranslate('LBL_SEARCH_SERVER_HINT', $MODULE)}</div>
    </div>
{elseif $CAN_LOAD_OLDER}
    {* the store ends here – older mail is fetched from the server on request *}
    <div class="its4you-load-more-wrap text-center">
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-load-older" data-page="{$PAGE}">
            <i class="fa-solid fa-history me-1"></i>{vtranslate('LBL_LOAD_OLDER', $MODULE)}
        </button>
    </div>
{/if}
{/strip}
