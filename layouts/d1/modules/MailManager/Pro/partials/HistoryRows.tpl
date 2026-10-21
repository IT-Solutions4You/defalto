{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Rows of the activity history — the first page and every "Load more" / filter *}
{strip}
{assign var=HISTORY_LANG value='Settings:MailManager'}
{foreach item=ENTRY from=$ENTRIES}
    <tr>
        <td class="its4you-nowrap">{$ENTRY.created_display}</td>
        <td>
            <div class="its4you-muted">{$ENTRY.subject|escape}</div>
            {* when the mail itself arrived — an action on old mail is no new mail *}
            {if $ENTRY.mail_date_display}
                <div class="its4you-history-maildate">{vtranslate('LBL_HISTORY_MAIL_DATE', $HISTORY_LANG)}&nbsp;{$ENTRY.mail_date_display}</div>
            {/if}
        </td>
        <td>{$ENTRY.from_email|escape}</td>
        <td class="its4you-nowrap"><i class="{$ENTRY.action_icon}"></i>&nbsp;{$ENTRY.action_label}</td>
        <td>
            {if $ENTRY.action eq 'link' || $ENTRY.action eq 'unlink'}
                {$ENTRY.record_icon}&nbsp;
                {if $ENTRY.record_url}
                    <a href="{$ENTRY.record_url}" target="_blank">{$ENTRY.to_value|escape}</a>
                {else}
                    {$ENTRY.to_value|escape}
                {/if}
            {else}
                {if $ENTRY.from_value}<span class="its4you-muted">{$ENTRY.from_value|escape}</span>&nbsp;&rarr;&nbsp;{/if}
                {if $ENTRY.to_value}
                    <b>{$ENTRY.to_value|escape}</b>
                {elseif $ENTRY.action eq 'label'}
                    {vtranslate('LBL_HISTORY_NO_LABEL', $HISTORY_LANG)}
                {elseif $ENTRY.action eq 'priority'}
                    {vtranslate('LBL_NO_PRIORITY', 'MailManager')}
                {/if}
            {/if}
        </td>
        <td class="its4you-nowrap"><i class="{$ENTRY.by_icon}"></i>&nbsp;{$ENTRY.by|escape}</td>
    </tr>
{foreachelse}
    <tr class="its4you-history-empty">
        <td colspan="6" class="text-center">{vtranslate('LBL_HISTORY_EMPTY', $HISTORY_LANG)}</td>
    </tr>
{/foreach}
{/strip}
