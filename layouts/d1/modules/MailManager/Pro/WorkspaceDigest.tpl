{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* The Workspace digest (I19) – who waits on whom over the whole Workspace; the AI lines come from the cron *}
{strip}
<div class="its4you-digest" data-stamp="{$DIGEST.stamp|escape}">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <h4 class="m-0 me-auto">{vtranslate('LBL_DIGEST_TITLE', $MODULE)}</h4>
        <span class="small text-secondary">{vtranslate('LBL_DIGEST_GENERATED', $MODULE)}&nbsp;{$DIGEST.generated|escape}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm its4you-digest-refresh">
            <i class="fa-solid fa-rotate me-1"></i>{vtranslate('LBL_REFRESH', $MODULE)}
        </button>
    </div>

    {if $DIGEST.ai}
        {if $DIGEST.summary}
            <div class="alert alert-light border its4you-digest-summary">{$DIGEST.summary|escape|nl2br}</div>
        {/if}
    {else}
        <div class="alert alert-secondary small its4you-digest-summary">{vtranslate('LBL_DIGEST_NO_AI', $MODULE)}</div>
    {/if}

    {foreach key=SECTION item=ITEMS from=$DIGEST.sections}
        <div class="its4you-digest-section">
            <h6 class="its4you-digest-section-title">
                <i class="fa-solid {if $SECTION eq 'mine'}fa-hourglass-half{elseif $SECTION eq 'unassigned'}fa-inbox{else}fa-paper-plane{/if} me-2"></i>
                {vtranslate('LBL_DIGEST_'|cat:strtoupper($SECTION), $MODULE)}
                <span class="badge text-bg-secondary ms-2">{$ITEMS|@count}</span>
            </h6>
            {foreach item=ITEM from=$ITEMS}
                <a href="javascript:void(0);" class="its4you-digest-row{if $ITEM.unread} its4you-digest-unread{/if}" data-threadid="{$ITEM.threadid}" data-mailbox="{$ITEM.mailboxid}">
                    <div class="its4you-digest-row-head">
                        {if $ITEM.priority}
                            <span class="its4you-priority its4you-priority-{$ITEM.priority|escape}" title="{vtranslate('LBL_PRIORITY_'|cat:strtoupper($ITEM.priority), $MODULE)|escape}"><i class="fa-solid fa-flag"></i></span>
                        {/if}
                        <span class="badge text-bg-light border its4you-digest-mailbox" title="{$ITEM.mailbox_name|escape}">{$ITEM.mailbox_email|escape}</span>
                        <span class="its4you-digest-contact">{$ITEM.contact|escape}</span>
                        <span class="its4you-digest-subject">{$ITEM.subject|escape}</span>
                        <span class="its4you-digest-date{if $ITEM.late} text-danger{/if}" {if $ITEM.late}title="{$ITEM.late_title|escape}"{/if}>
                            {if $ITEM.late}<i class="fa-solid fa-triangle-exclamation me-1"></i>{/if}{$ITEM.last_display|escape}
                        </span>
                    </div>
                    {if $ITEM.line}
                        <div class="its4you-digest-line{if $ITEM.line_state eq 'suspicious'} text-warning-emphasis{/if}">
                            {if $ITEM.line_state eq 'suspicious'}<i class="fa-solid fa-shield-halved me-1"></i>{/if}{$ITEM.line|escape}
                        </div>
                    {elseif $ITEM.line_state eq 'pending'}
                        <div class="its4you-digest-line its4you-digest-line-pending">{vtranslate('LBL_DIGEST_LINE_PENDING', $MODULE)}</div>
                    {/if}
                </a>
            {foreachelse}
                <div class="its4you-digest-empty">{vtranslate('LBL_DIGEST_EMPTY', $MODULE)}</div>
            {/foreach}
        </div>
    {/foreach}

    <div class="its4you-digest-section">
        <h6 class="its4you-digest-section-title">
            <i class="fa-solid fa-thumbtack me-2"></i>{vtranslate('LBL_DIGEST_RECORDS', $MODULE)}
            <span class="badge text-bg-secondary ms-2">{$DIGEST.records|@count}</span>
        </h6>
        {foreach item=RECORD from=$DIGEST.records}
            <a href="javascript:void(0);" class="its4you-digest-row" data-threadid="{$RECORD.threadid}" data-mailbox="{$RECORD.mailboxid}">
                <div class="its4you-digest-row-head">
                    <span class="its4you-digest-record-icon" title="{$RECORD.module_label|escape}">{$RECORD.icon}</span>
                    <span class="its4you-digest-subject">{$RECORD.record_label|escape}</span>
                    {if $RECORD.status_display}<span class="badge text-bg-light border">{$RECORD.status_display|escape}</span>{/if}
                    {if $RECORD.owner_name}<span class="its4you-digest-date"><i class="fa-solid fa-user me-1"></i>{$RECORD.owner_name|escape}</span>{/if}
                </div>
            </a>
        {foreachelse}
            <div class="its4you-digest-empty">{vtranslate('LBL_DIGEST_EMPTY', $MODULE)}</div>
        {/foreach}
    </div>
</div>
{/strip}
