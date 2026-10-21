{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="its4you-conversation-head" data-threadid="{$THREAD.threadid}" data-triage-pending="{if $TRIAGE_PENDING}1{else}0{/if}">
    {* Action toolbar – reply group and the AI assistant left, mailbox actions right.
       Where the row does not fit the column the buttons keep their icons only
       (see fitConversationToolbar) *}
    <div class="its4you-conversation-toolbar">
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-reply-btn" data-composemode="reply">
            <i class="fa-solid fa-reply"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_MAIL_REPLY', $MODULE)}</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-reply-btn" data-composemode="replyall">
            <i class="fa-solid fa-reply-all"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_MAIL_REPLY_ALL', $MODULE)}</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-reply-btn" data-composemode="forward">
            <i class="fa-solid fa-share"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_MAIL_FORWARD', $MODULE)}</span>
        </button>
        {* the AI assistant (summary, questions, actions) – a tab of the right column *}
        {if $AI_SUMMARY_ENABLED}
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-ai-assistant-btn">
                <i class="fa-solid fa-magic"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_AI_ASSISTANT', $MODULE)}</span>
            </button>
        {/if}
        <span class="its4you-toolbar-spacer"></span>
        {if $JUNK_TYPE}
            {* thrown away – the way back: "Not spam" trusts the sender too *}
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-thread-restore">
                <i class="fa-solid fa-undo"></i><span class="its4you-btn-text ms-1">{if $JUNK_TYPE eq 'spam'}{vtranslate('LBL_NOT_SPAM', $MODULE)}{else}{vtranslate('LBL_RESTORE', $MODULE)}{/if}</span>
            </button>
        {/if}
        {* a conversation without a record is closed and reopened here *}
        {if $THREAD.state}
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-thread-state" data-state="{if $THREAD.state.is_done}open{else}done{/if}">
                {if $THREAD.state.is_done}
                    <i class="fa-regular fa-folder-open"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_REOPEN', $MODULE)}</span>
                {else}
                    <i class="fa-solid fa-check"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_MARK_DONE', $MODULE)}</span>
                {/if}
            </button>
        {/if}
        <span class="dropdown its4you-move-dd">
            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle its4you-btn-icon" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-folder"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_PRO_MOVE_TO', $MODULE)}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end its4you-move-menu">
                {* Type-ahead filter – searches the whole folder path *}
                <li class="its4you-move-search">
                    <input type="text" class="form-control form-control-sm inputElement" placeholder="{vtranslate('LBL_SEARCH', $MODULE)}">
                </li>
                {foreach item=MOVE_FOLDER from=$FOLDERS}
                    <li class="its4you-move-item">
                        <a href="javascript:void(0);" class="dropdown-item its4you-thread-move its4you-move-depth-{$MOVE_FOLDER.depth}"
                           data-folderid="{$MOVE_FOLDER.folderid}" data-path="{$MOVE_FOLDER.name|escape}">
                            <i class="{$MOVE_FOLDER.icon} me-1"></i>{$MOVE_FOLDER.display|escape}
                        </a>
                    </li>
                {/foreach}
            </ul>
        </span>
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-btn-icon its4you-thread-archive">
            <i class="fa-solid fa-archive"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_ARCHIVE', $MODULE)}</span>
        </button>
        {* unread again – a colleague notices it (the read state is the mailbox's, shared) *}
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-btn-icon its4you-thread-unread">
            <i class="fa-solid fa-eye-slash"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_MARK_UNREAD', $MODULE)}</span>
        </button>
        {if !$JUNK_TYPE}
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-btn-icon its4you-thread-spam">
                <i class="fa-solid fa-ban"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_SPAM', $MODULE)}</span>
            </button>
        {/if}
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-btn-icon its4you-thread-delete">
            <i class="fa-solid fa-trash"></i><span class="its4you-btn-text ms-1">{vtranslate('LBL_DELETE', $MODULE)}</span>
        </button>
    </div>
    {* With a driving record the subject links to it and carries its module icon *}
    <div class="its4you-conversation-subject">
        {if $THREAD.driving}
            <a class="its4you-subject-link" href="index.php?module={$THREAD.driving.module}&view=Detail&record={$THREAD.driving.crmid}"
               target="_blank" rel="noopener" title="{$THREAD.driving.module_label|escape}: {$THREAD.driving.record_label|escape}">
                <span class="its4you-subject-icon">{$THREAD.driving.icon}</span>
                {if $THREAD.subject}{$THREAD.subject|escape}{else}({vtranslate('LBL_NO_SUBJECT', $MODULE)}){/if}
            </a>
        {else}
            {if $THREAD.subject}{$THREAD.subject|escape}{else}({vtranslate('LBL_NO_SUBJECT', $MODULE)}){/if}
        {/if}
    </div>
    <div class="its4you-conversation-meta">
        {* Clickable label – a wrong AI classification is fixed by hand here *}
        <span class="dropdown its4you-label-dd">
            <a href="javascript:void(0);" class="its4you-label-edit" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_CHANGE_LABEL', $MODULE)}">
                {if $THREAD.label_name}
                    <span class="its4you-label-chip" style="background: {$THREAD.label_color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($THREAD.label_color)};"
                          {if $THREAD.label_by_rule}title="{vtranslate('LBL_LABEL_BY_RULE', $MODULE)}"{/if}>
                        {if $THREAD.label_by_rule}<i class="fa-solid fa-graduation-cap me-1"></i>{/if}{$THREAD.label_name|escape}
                    </span>
                {else}
                    <span class="its4you-label-chip its4you-label-chip-empty"><i class="fa-solid fa-tag"></i></span>
                {/if}
                <i class="fa-solid fa-caret-down its4you-label-caret"></i>
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="javascript:void(0);" class="dropdown-item its4you-label-set" data-labelid="0">{vtranslate('LBL_NO_LABEL', $MODULE)}</a>
                </li>
                {foreach item=LABEL_OPTION from=$LABELS}
                    <li>
                        <a href="javascript:void(0);" class="dropdown-item its4you-label-set" data-labelid="{$LABEL_OPTION.labelid}">
                            <span class="its4you-label-chip" style="background: {$LABEL_OPTION.color|escape}; color: {Settings_MailManager_Label_Model::getTextColor($LABEL_OPTION.color)};">{$LABEL_OPTION.name|escape}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </span>
        {* Clickable priority – the AI's assessment is corrected by hand here *}
        <span class="dropdown its4you-priority-dd">
            <a href="javascript:void(0);" class="its4you-priority-edit" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_CHANGE_PRIORITY', $MODULE)}">
                {if $THREAD.priority}
                    <span class="its4you-priority its4you-priority-{$THREAD.priority}"><i class="fa-solid fa-flag me-1"></i>{vtranslate('LBL_PRIORITY_'|cat:strtoupper($THREAD.priority), $MODULE)}</span>
                {else}
                    <span class="its4you-priority its4you-priority-none"><i class="fa-regular fa-flag"></i></span>
                {/if}
                <i class="fa-solid fa-caret-down its4you-label-caret"></i>
            </a>
            <ul class="dropdown-menu">
                {foreach item=PRIORITY_OPTION from=$PRIORITIES}
                    <li>
                        <a href="javascript:void(0);" class="dropdown-item its4you-priority-set" data-priority="{$PRIORITY_OPTION}">
                            <span class="its4you-priority its4you-priority-{$PRIORITY_OPTION}"><i class="fa-solid fa-flag me-1"></i>{vtranslate('LBL_PRIORITY_'|cat:strtoupper($PRIORITY_OPTION), $MODULE)}</span>
                        </a>
                    </li>
                {/foreach}
                <li><hr class="dropdown-divider"></li>
                <li><a href="javascript:void(0);" class="dropdown-item its4you-priority-set" data-priority="">{vtranslate('LBL_NO_PRIORITY', $MODULE)}</a></li>
            </ul>
        </span>
        <span>{$THREAD.message_count}<span class="ms-1">{vtranslate('LBL_MESSAGES_IN_THREAD', $MODULE)}</span></span>
        {* mail of the conversation tried to instruct the AI (I16) – automatic actions are off *}
        {if $THREAD.ai_suspicious eq 1}
            <span class="its4you-ai-suspicious" title="{vtranslate('LBL_AI_SUSPICIOUS_HINT', $MODULE)|escape}">
                <i class="fa-solid fa-shield-halved"></i>{vtranslate('LBL_AI_SUSPICIOUS', $MODULE)}
                <a href="javascript:void(0);" class="its4you-ai-suspicious-safe">{vtranslate('LBL_AI_MARK_SAFE', $MODULE)}</a>
            </span>
        {/if}
        {* the folders outside the Inbox the conversation's mail lies in – a click opens one *}
        {foreach item=LOCATION from=$THREAD.locations}
            {* the folder of a split conversation is named by the notice below *}
            {if !$THREAD.split_target || $LOCATION.folderid neq $THREAD.split_target.folderid}
                <a href="javascript:void(0);" class="its4you-folder-chip its4you-open-folder"
                   data-folderid="{$LOCATION.folderid}" title="{vtranslate('LBL_OPEN_FOLDER', $MODULE)}">
                    <i class="fa-solid fa-folder me-1"></i>{$LOCATION.path|escape}
                </a>
            {/if}
        {/foreach}
        {* a conversation without a record has its own state and owner *}
        {if $THREAD.state}
            <span class="its4you-head-sep">|</span>
            <span class="dropdown its4you-state-dd">
                <a href="javascript:void(0);" class="its4you-state-edit" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="its4you-status-chip its4you-status-chip-{$THREAD.state.key}">{$THREAD.state.label|escape}</span>
                    <i class="fa-solid fa-caret-down its4you-label-caret"></i>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="javascript:void(0);" class="dropdown-item its4you-thread-state" data-state="{if $THREAD.state.is_done}open{else}done{/if}">
                            {if $THREAD.state.is_done}
                                <i class="fa-regular fa-folder-open me-1"></i>{vtranslate('LBL_REOPEN', $MODULE)}
                            {else}
                                <i class="fa-solid fa-check me-1"></i>{vtranslate('LBL_MARK_DONE', $MODULE)}
                            {/if}
                        </a>
                    </li>
                </ul>
            </span>
            {if $ASSIGNEES}
                {* a shared mailbox – the conversation can be handed to a colleague *}
                <span class="dropdown its4you-record-owner its4you-assign-dd">
                    <a href="javascript:void(0);" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="{vtranslate('LBL_ASSIGN_TO', $MODULE)}">
                        <i class="fa-solid fa-user its4you-muted me-1"></i>{if $THREAD.state.owner_name}{$THREAD.state.owner_name|escape}{else}{vtranslate('LBL_TAB_UNASSIGNED', $MODULE)}{/if}
                    </a>
                    <ul class="dropdown-menu">
                        {foreach key=ASSIGNEE_ID item=ASSIGNEE_NAME from=$ASSIGNEES}
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item its4you-thread-assign" data-assignee="{$ASSIGNEE_ID}">
                                    {if $ASSIGNEE_ID eq $THREAD.state.owner_id}<i class="fa-solid fa-check me-1"></i>{/if}{$ASSIGNEE_NAME|escape}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </span>
            {elseif $THREAD.state.owner_name}
                <span class="its4you-record-owner" title="{vtranslate('LBL_ASSIGNED_TO', $MODULE)}">
                    <i class="fa-solid fa-user its4you-muted me-1"></i>{$THREAD.state.owner_name|escape}
                </span>
            {/if}
        {/if}
        {* the DRIVING record carries the state and the responsible user – both managed on the record *}
        {if $THREAD.driving}
            <span class="its4you-head-sep">|</span>
            <a href="index.php?module={$THREAD.driving.module}&view=Detail&record={$THREAD.driving.crmid}" target="_blank" rel="noopener"
               class="its4you-status-chip {if $THREAD.driving.is_done}its4you-status-chip-done{else}its4you-status-chip-progress{/if}"
               title="{$THREAD.driving.module_label|escape}: {$THREAD.driving.record_label|escape}">
                {$THREAD.driving.status_display|escape}
            </a>
            <span class="its4you-record-owner" title="{vtranslate('LBL_ASSIGNED_TO', $MODULE)}">
                <i class="fa-solid fa-user its4you-muted me-1"></i>{$THREAD.driving.owner_name|escape}
            </span>
        {/if}
    </div>
    {* New mail in the Inbox of a conversation filed in another folder – one click moves it to the rest *}
    {if $THREAD.split_target}
        <div class="its4you-split-notice">
            <i class="fa-solid fa-folder-open"></i>
            <div>
                {vtranslate('LBL_SPLIT_NOTICE', $MODULE)}
                <div class="its4you-split-folder">
                    <a href="javascript:void(0);" class="its4you-open-folder fw-bold" data-folderid="{$THREAD.split_target.folderid}"
                       title="{vtranslate('LBL_OPEN_FOLDER', $MODULE)}">{$THREAD.split_target.path|escape}</a>
                </div>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-file-with-thread" data-folderid="{$THREAD.split_target.folderid}">
                <i class="fa-solid fa-share me-1"></i>{vtranslate('LBL_PRO_MOVE_TO', $MODULE)} {$THREAD.split_target.display|escape}
            </button>
        </div>
    {/if}
</div>
{* JS moves the AI suggestion card (rendered by the CRM panel partial) here *}
<div class="its4you-ai-suggestion-slot"></div>
<div class="its4you-conversation-body">
    {foreach item=MESSAGE from=$MESSAGES name=msgs}
        {* the customer's latest message opens (MailManager_ProConversation_Helper); newer mail of ours
           shows above it as one-line bars, older messages collapse to their headers *}
        <div class="its4you-message {if $MESSAGE.direction eq 'out'}its4you-message-out{/if} {if $MESSAGE.view_state neq 'open'}its4you-message-collapsed{/if} {if $MESSAGE.view_state eq 'bar'}its4you-message-bar{/if}">
            <div class="its4you-message-head">
                <span>
                    {if $MESSAGE.is_note}
                        <i class="fa-regular fa-comment its4you-msg-icon"></i>
                    {elseif $MESSAGE.direction eq 'out'}
                        <i class="fa-solid {if $MESSAGE.is_forward}fa-share{else}fa-reply{/if} its4you-msg-icon"
                           title="{if $MESSAGE.is_forward}{vtranslate('LBL_MAIL_FORWARD', $MODULE)}{else}{vtranslate('LBL_MAIL_REPLY', $MODULE)}{/if}"></i>
                    {else}
                        <i class="fa-regular fa-envelope its4you-msg-icon"></i>
                    {/if}
                    <b>{$MESSAGE.from_display|escape}</b>
                    {if $MESSAGE.view_state eq 'bar'}<span class="its4you-message-snippet">&nbsp;&mdash;&nbsp;{$MESSAGE.snippet_display|escape}</span>{/if}
                    {if $MESSAGE.from_name && $MESSAGE.from_email}<span class="its4you-muted ms-1">&lt;{$MESSAGE.from_email|escape}&gt;</span>{/if}
                    {if $MESSAGE.to_display}<span class="its4you-muted ms-1">{vtranslate('LBL_MSG_TO', $MODULE)} {$MESSAGE.to_display|escape}</span>{/if}
                    {* a reply written as a comment on the ticket – it left through the CRM, not the mailbox *}
                    {if $MESSAGE.is_note}
                        <a class="its4you-draft-chip" target="_blank" rel="noopener"
                           href="index.php?module=HelpDesk&view=Detail&record={$MESSAGE.note_crmid}&mode=showAllComments">
                            {vtranslate('LBL_TICKET_COMMENT', $MODULE)}{if $MESSAGE.is_private} &middot; {vtranslate('LBL_COMMENT_INTERNAL', $MODULE)}{/if}
                        </a>
                    {/if}
                    {if $MESSAGE.is_draft}
                        <span class="its4you-draft-chip">{vtranslate('LBL_DRAFT', $MODULE)}</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm its4you-edit-draft" data-message="{$MESSAGE.messageid}">
                            <i class="fa-solid fa-pencil me-1"></i>{vtranslate('LBL_EDIT_DRAFT', $MODULE)}
                        </button>
                    {/if}
                    {* the copy recipients on their own line *}
                    {if $MESSAGE.cc_display}
                        <span class="its4you-message-cc">
                            <span class="its4you-message-cc-label me-1">{vtranslate('LBL_MSG_CC', $MODULE)}</span>{$MESSAGE.cc_display|escape}
                        </span>
                    {/if}
                    {if $MESSAGE.bcc_display}
                        <span class="its4you-message-cc">
                            <span class="its4you-message-cc-label me-1">{vtranslate('LBL_BCC', $MODULE)}</span>{$MESSAGE.bcc_display|escape}
                        </span>
                    {/if}
                </span>
                {* translation into the reader's language – on a click; tabs Translation | Original follow (ProConversation.js) *}
                {if $TRANSLATE_OFFERED && $MESSAGE.direction eq 'in' && !$MESSAGE.is_note && !$MESSAGE.is_draft}
                    <a href="javascript:void(0);" class="its4you-translate" data-message="{$MESSAGE.messageid}"
                       data-translation="{vtranslate('LBL_TRANSLATION', $MODULE)|escape}"
                       data-original="{vtranslate('LBL_ORIGINAL', $MODULE)|escape}">
                        <i class="fa-solid fa-language me-1"></i>{vtranslate('LBL_TRANSLATE', $MODULE)}
                    </a>
                {/if}
                {* opens and clicks of mail sent from the CRM – indicative only (blocked images, Apple MPP) *}
                {if $MESSAGE.tracking}
                    <span class="its4you-muted its4you-tracking me-2"
                          title="{if $MESSAGE.tracking.last_display}{vtranslate('LBL_TRACKING_LAST', $MODULE)|escape}: {$MESSAGE.tracking.last_display|escape}{else}{vtranslate('LBL_TRACKING_NOT_OPENED', $MODULE)|escape}{/if}">
                        <i class="fa-regular fa-eye me-1"></i>{$MESSAGE.tracking.opens_display|escape}
                        &nbsp;&middot;&nbsp;{$MESSAGE.tracking.clicks_display|escape}
                    </span>
                {/if}
                <span class="its4you-muted">{$MESSAGE.time_display}</span>
            </div>
            {* remote content (images, fonts) is blocked by the frame's policy until allowed *}
            {if $MESSAGE.body_html && $MESSAGE.has_remote}
                <div class="its4you-remote-blocked alert alert-warning d-flex align-items-center justify-content-between py-1 px-2 mb-0">
                    <span>{vtranslate('LBL_BLOCKED_REMOTE_CONTENT', $MODULE)}</span>
                    <button type="button" class="btn btn-warning btn-sm its4you-allow-remote">{vtranslate('LBL_ALLOW', $MODULE)}</button>
                </div>
            {/if}
            <div class="its4you-message-body">
                {if $MESSAGE.body_html}
                    {* Sanitized server-side; the sandbox blocks scripts, allow-same-origin only for the auto-height.
                       Collapsed messages keep their HTML as an inert data attribute, JS assigns srcdoc on expand *}
                    {if $MESSAGE.view_state eq 'open'}
                        <iframe class="its4you-message-frame" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" srcdoc="{$MESSAGE.body_html|escape}"></iframe>
                    {else}
                        <iframe class="its4you-message-frame" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" data-src="{$MESSAGE.body_html|escape}"></iframe>
                    {/if}
                {else}
                    <pre class="its4you-message-plain">{$MESSAGE.body_plain|escape}</pre>
                {/if}
            </div>
            {if $MESSAGE.attachments}
                <div class="its4you-message-attachments">
                    {foreach item=ATTACHMENT from=$MESSAGE.attachments}
                        <a class="its4you-attachment-chip"
                           href="index.php?module={$MODULE}&view=ProAttachment&attachment={$ATTACHMENT.attachmentid}"
                           {if $ATTACHMENT.preview_type}data-preview="{$ATTACHMENT.preview_type}" data-ready="{if $ATTACHMENT.is_ready}1{else}0{/if}" data-name="{$ATTACHMENT.filename|escape}" title="{vtranslate('LBL_PREVIEW', $MODULE)}"{else}title="{vtranslate('LBL_DOWNLOAD', $MODULE)}"{/if}>
                            <i class="fa-solid fa-paperclip me-1"></i>{$ATTACHMENT.filename|escape}
                            <span class="its4you-muted ms-1">&middot; {$ATTACHMENT.size_display}</span>
                        </a>
                    {/foreach}
                </div>
            {/if}
        </div>
    {/foreach}
</div>
{/strip}
