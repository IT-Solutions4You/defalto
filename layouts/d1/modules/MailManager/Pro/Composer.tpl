{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="its4you-composer"
     data-mode="{$COMPOSE_MODE}" data-thread="{$PREFILL.thread}" data-reply-message="{$PREFILL.reply_message}"
     data-quote-message="{$PREFILL.quote_message}" data-draft-message="{$PREFILL.draft_message}"
     data-auto-draft="{if $AI_AUTO_DRAFT}1{else}0{/if}" data-upload-max="{$UPLOAD_MAX_BYTES}" data-send-delay="{$SEND_DELAY}" data-schedule-default="{$SCHEDULE_DEFAULT}" data-draft-check="{if $DRAFT_CHECK}1{else}0{/if}">
    <div class="its4you-composer-head modal-header">
        <h5 class="modal-title">{if $COMPOSE_MODE eq 'forward'}{vtranslate('LBL_MAIL_FORWARD', $MODULE)}
            {elseif $COMPOSE_MODE eq 'replyall'}{vtranslate('LBL_MAIL_REPLY_ALL', $MODULE)}
            {elseif $COMPOSE_MODE eq 'reply'}{vtranslate('LBL_MAIL_REPLY', $MODULE)}
            {elseif $COMPOSE_MODE eq 'draft'}{vtranslate('LBL_EDIT_DRAFT', $MODULE)}
            {else}{vtranslate('LBL_COMPOSE', $MODULE)}{/if}</h5>
        <button type="button" class="btn-close its4you-composer-cancel its4you-composer-close" title="{vtranslate('LBL_CANCEL', $MODULE)|escape}"></button>
    </div>
    {* A ticket-linked reply goes out as a mail or as a comment on the ticket
       (the CRM's comment workflows mail the customer); JS remembers the choice *}
    {if $REPLY_TICKET}
        {* Bootstrap nav tabs — ProComposer.js marks the active item and its link *}
        <div class="related-tabs its4you-composer-channel-wrap">
            <ul class="nav nav-tabs its4you-composer-channel">
                <li class="nav-item tab-item active" data-channel="mail">
                    <a href="javascript:void(0);" class="nav-link active"><i class="fa-regular fa-envelope"></i>&nbsp;&nbsp;{vtranslate('LBL_REPLY_BY_EMAIL', $MODULE)}</a>
                </li>
                <li class="nav-item tab-item" data-channel="comment">
                    <a href="javascript:void(0);" class="nav-link"><i class="fa-regular fa-comment"></i>&nbsp;&nbsp;{vtranslate('LBL_REPLY_BY_COMMENT', $MODULE)}</a>
                </li>
            </ul>
        </div>
        <div class="its4you-composer-comment-only its4you-composer-comment-note">
            <div class="its4you-composer-comment-ticket">
                <i class="fa-regular fa-comment"></i>&nbsp;<b>{$REPLY_TICKET.ticket_no|escape}</b>&nbsp;&middot;&nbsp;{$REPLY_TICKET.label|escape}
            </div>
            <div class="{if $COMMENT_WORKFLOW}its4you-muted{else}alert alert-warning{/if}">
                {if $COMMENT_WORKFLOW}{vtranslate('LBL_COMMENT_WF_SENDS', $MODULE)}{else}{vtranslate('LBL_COMMENT_WF_NONE', $MODULE)}{/if}
            </div>
            <label class="its4you-comment-private-label">
                <input type="checkbox" class="form-check-input its4you-comment-private">&nbsp;{vtranslate('LBL_COMMENT_PRIVATE', $MODULE)}
            </label>
        </div>
    {/if}
    <div class="its4you-composer-row its4you-composer-mail-only">
        <label>{vtranslate('LBL_FROM', $MODULE)}</label>
        <input type="text" class="form-control inputElement" readonly value="{$MAILBOX_NAME|escape} &lt;{$MAILBOX_EMAIL|escape}&gt;">
        {* Copy rows are rarely needed — they open on demand (Thunderbird
           pattern); a prefilled one (reply all, a draft) starts open *}
        <span class="its4you-composer-copy-links">
            <a href="javascript:void(0);" class="its4you-copy-link {if $PREFILL.cc}hide{/if}" data-row="cc">{vtranslate('LBL_CC', $MODULE)}</a>
            <a href="javascript:void(0);" class="its4you-copy-link {if $PREFILL.bcc}hide{/if}" data-row="bcc">{vtranslate('LBL_BCC', $MODULE)}</a>
        </span>
    </div>
    <div class="its4you-composer-row its4you-composer-mail-only">
        <label>{vtranslate('LBL_TO', $MODULE)}<span class="text-danger ms-2">*</span></label>
        {* CRM records only — the picked addresses are the option values (ProComposer.js
           joins them comma separated for the send action); prefilled reply
           addresses stay even when the sender is not in the CRM *}
        <select class="its4you-recipient-picker inputElement form-select" name="to" multiple
                data-placeholder="{vtranslate('LBL_PICK_RECIPIENT', $MODULE)|escape}">
            {foreach item=TO_ADDRESS from=explode(',', $PREFILL.to)}
                {if trim($TO_ADDRESS)}
                    <option value="{trim($TO_ADDRESS)|escape}" selected>{trim($TO_ADDRESS)|escape}</option>
                {/if}
            {/foreach}
        </select>
    </div>
    <div class="its4you-composer-row its4you-composer-mail-only its4you-copy-row {if !$PREFILL.cc}hide{/if}" data-row="cc">
        <label>{vtranslate('LBL_CC', $MODULE)}</label>
        <input type="text" class="form-control inputElement" name="cc" value="{$PREFILL.cc|escape}" placeholder="name@company.com, ...">
        <a href="javascript:void(0);" class="its4you-copy-close" title="{vtranslate('LBL_REMOVE', $MODULE)}">&times;</a>
    </div>
    <div class="its4you-composer-row its4you-composer-mail-only its4you-copy-row {if !$PREFILL.bcc}hide{/if}" data-row="bcc">
        <label>{vtranslate('LBL_BCC', $MODULE)}</label>
        <input type="text" class="form-control inputElement" name="bcc" value="{$PREFILL.bcc|escape}" placeholder="name@company.com, ...">
        <a href="javascript:void(0);" class="its4you-copy-close" title="{vtranslate('LBL_REMOVE', $MODULE)}">&times;</a>
    </div>
    <div class="its4you-composer-row its4you-composer-mail-only">
        <label>{vtranslate('LBL_SUBJECT', $MODULE)}<span class="text-danger ms-2">*</span></label>
        <input type="text" class="form-control inputElement" name="subject" value="{$PREFILL.subject|escape}">
    </div>
    {* Files to send — collected in JS (a FileList cannot have items removed),
       posted with the mail as multipart *}
    {* No label — the button says it all and the row gains the full width *}
    <div class="its4you-composer-row its4you-composer-files-row">
        <div class="its4you-composer-files">
            <button type="button" class="btn btn-outline-secondary btn-sm its4you-attach-btn">
                <i class="fa-solid fa-paperclip"></i>&nbsp;{vtranslate('LBL_ATTACH_FILES', $MODULE)}
            </button>
            <input type="file" class="its4you-attach-input hide" multiple>
            <span class="its4you-attach-list"></span>
        </div>
    </div>
    {if $PREFILL.attachments}
        <div class="its4you-composer-attachments its4you-composer-mail-only">
            {foreach item=ATTACHMENT from=$PREFILL.attachments}
                <label class="its4you-composer-attachment">
                    <input type="checkbox" class="form-check-input its4you-fwd-attachment" value="{$ATTACHMENT.attachmentid}" {if $ATTACHMENT.checked}checked{/if}>
                    <i class="fa-solid fa-paperclip"></i>&nbsp;{$ATTACHMENT.filename|escape}
                    <span class="its4you-muted">&middot;&nbsp;{$ATTACHMENT.size_display}</span>
                </label>
            {/foreach}
        </div>
    {/if}
    {* the Documents of an EMAIL Maker template, sent with the mail *}
    <div class="its4you-composer-attachments its4you-template-documents its4you-composer-mail-only hide"></div>
    {* The editor holds ONLY the reply — CKEditor mangles real-world mail
       HTML; the original renders below in a sandboxed frame and the send
       action appends the quote to the outgoing mail server-side *}
    <div class="its4you-composer-body">
        <textarea class="form-control" id="its4youComposerBody" name="body">{$PREFILL.draft_body|escape}</textarea>
    </div>
    {if $PREFILL.quote_message}
        <div class="its4you-composer-quote">
            <div class="its4you-composer-quote-head">
                <i class="fa-solid fa-quote-right"></i>&nbsp;{$PREFILL.quote_header|escape}
            </div>
            <iframe class="its4you-message-frame" sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox" srcdoc="{$PREFILL.quote_html|escape}"></iframe>
        </div>
    {/if}
    {* the knowledge the last AI draft answered from (Composer.js) *}
    <div class="its4you-ai-sources hide"></div>
    {* the checks of an AI draft – unverified facts, suspicious mail (I16) *}
    <div class="its4you-ai-check hide"></div>
    <div class="its4you-composer-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2">
        {* why the mail did not go out — shown here, a notification would sit behind the overlay (ProComposer.js) *}
        <div class="alert alert-danger w-100 mb-0 py-2 its4you-composer-error hide" role="alert"></div>
        {* EMAIL Maker templates — filled for the conversation's record and the recipient (ProComposer.js) *}
        {if $EMAIL_TEMPLATES}
            <select class="form-select w-auto inputElement its4you-template-select its4you-composer-mail-only" title="{vtranslate('LBL_EMAIL_TEMPLATE', $MODULE)|escape}">
                <option value="">{vtranslate('LBL_EMAIL_TEMPLATE', $MODULE)}</option>
                {foreach item=TEMPLATE_GROUP from=$EMAIL_TEMPLATES}
                    {if $TEMPLATE_GROUP.label}<optgroup label="{$TEMPLATE_GROUP.label|escape}">{/if}
                    {foreach key=TEMPLATE_ID item=TEMPLATE_NAME from=$TEMPLATE_GROUP.templates}
                        <option value="{$TEMPLATE_ID}">{$TEMPLATE_NAME|escape}</option>
                    {/foreach}
                    {if $TEMPLATE_GROUP.label}</optgroup>{/if}
                {/foreach}
            </select>
        {/if}
        {if $AI_DRAFT_ENABLED}
            {* AI assist — the same
               button GENERATES into an empty editor and IMPROVES a written
               draft; editable, never auto-sent *}
            <span class="its4you-composer-ai d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-outline-secondary text-nowrap flex-shrink-0 its4you-ai-draft">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>&nbsp;<span class="its4you-ai-draft-label">{vtranslate('LBL_AI_DRAFT', $MODULE)}</span>
                </button>
                <select class="form-select w-auto its4you-ai-tone inputElement" title="{vtranslate('LBL_TONE', $MODULE)}">
                    {* meaningful only for a written draft — JS enables it *}
                    <option value="mystyle" class="its4you-tone-mystyle" disabled>{vtranslate('LBL_TONE_MYSTYLE', $MODULE)}</option>
                    <option value="professional" selected>{vtranslate('LBL_TONE_PROFESSIONAL', $MODULE)}</option>
                    <option value="friendly">{vtranslate('LBL_TONE_FRIENDLY', $MODULE)}</option>
                    <option value="short">{vtranslate('LBL_TONE_SHORT', $MODULE)}</option>
                </select>
                <select class="form-select w-auto its4you-ai-lang inputElement" title="{vtranslate('LBL_LANGUAGE', $MODULE)}">
                    <option value="auto" data-label="{vtranslate('LBL_LANG_AUTO', $MODULE)|escape}">{vtranslate('LBL_LANG_AUTO', $MODULE)}{if $AUTO_LANGUAGE} – {$AUTO_LANGUAGE|escape}{/if}</option>
                    <option value="sk">Slovenčina</option>
                    <option value="cz">Čeština</option>
                    <option value="en">English</option>
                    <option value="de">Deutsch</option>
                </select>
                <a href="javascript:void(0);" class="btn btn-link its4you-ai-undo hide">{vtranslate('LBL_RESTORE_MY_TEXT', $MODULE)}</a>
            </span>
        {/if}
        {* Primary actions on the RIGHT — matches the vtiger Compose Email modal *}
        <span class="its4you-composer-actions d-flex flex-wrap align-items-center gap-2 ms-auto">
            <button type="button" class="btn btn-primary active its4you-composer-send">
                <span class="its4you-composer-mail-only"><i class="fa-solid fa-paper-plane"></i>&nbsp;&nbsp;{vtranslate('LBL_SEND', $MODULE)}</span>
                <span class="its4you-composer-comment-only"><i class="fa-regular fa-comment"></i>&nbsp;&nbsp;{vtranslate('LBL_SAVE_COMMENT', $MODULE)}</span>
            </button>
            {if $CAN_MARK_DONE}
                <button type="button" class="btn btn-outline-secondary its4you-composer-send its4you-composer-mail-only" data-done="1">
                    <i class="fa-solid fa-check"></i>&nbsp;&nbsp;{vtranslate('LBL_SEND_AND_DONE', $MODULE)}
                </button>
            {/if}
            {* "Send later" — the time in the user's CRM time zone (ProComposer.js) *}
            <span class="its4you-schedule-box its4you-composer-mail-only">
                <button type="button" class="btn btn-outline-secondary its4you-schedule-toggle">
                    <i class="fa-regular fa-clock"></i>&nbsp;&nbsp;{vtranslate('LBL_SEND_LATER', $MODULE)}
                </button>
                <span class="its4you-schedule-row hide">
                    <span class="d-inline-flex align-items-center gap-2">
                        <input type="datetime-local" class="form-control inputElement w-auto its4you-schedule-time">
                        <button type="button" class="btn btn-primary active its4you-schedule-send">{vtranslate('LBL_SCHEDULE', $MODULE)}</button>
                    </span>
                </span>
            </span>
            <button type="button" class="btn btn-outline-secondary its4you-composer-savedraft its4you-composer-mail-only">
                {vtranslate('LBL_SAVE_AS_DRAFT', $MODULE)}
            </button>
            <button type="button" class="btn btn-primary its4you-composer-cancel">{vtranslate('LBL_CANCEL', $MODULE)}</button>
        </span>
    </div>
</div>
{/strip}
