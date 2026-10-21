{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Activity history of one mailbox (History.js) — My Settings and the admin's
   mailbox history; HISTORY = MailManager_ProHistory_Model::getPage() *}
{strip}
{assign var=HISTORY_LANG value='Settings:MailManager'}
<div class="its4you-history" data-mailbox="{$HISTORY_MAILBOX_ID}">
    <div class="its4you-history-toolbar">
        <input type="text" class="form-control its4you-history-search inputElement"
               placeholder="{vtranslate('LBL_HISTORY_SEARCH', $HISTORY_LANG)|escape}">
        <select class="form-select its4you-history-action inputElement">
            <option value="">{vtranslate('LBL_HISTORY_ALL_ACTIONS', $HISTORY_LANG)}</option>
            {foreach key=ACTION_KEY item=ACTION_INFO from=$HISTORY_ACTIONS}
                <option value="{$ACTION_KEY}">{vtranslate($ACTION_INFO[1], $HISTORY_LANG)}</option>
            {/foreach}
        </select>
    </div>
    <table class="table table-bordered its4you-history-table">
        <thead>
            <tr>
                <th class="its4you-w-150">{vtranslate('LBL_DATE', $HISTORY_LANG)}</th>
                <th>{vtranslate('LBL_SUBJECT', $HISTORY_LANG)}</th>
                <th class="its4you-w-200">{vtranslate('LBL_SENDER', $HISTORY_LANG)}</th>
                <th class="its4you-w-130">{vtranslate('LBL_ACTION', $HISTORY_LANG)}</th>
                <th>{vtranslate('LBL_HISTORY_CHANGE', $HISTORY_LANG)}</th>
                <th class="its4you-w-160">{vtranslate('LBL_HISTORY_BY', $HISTORY_LANG)}</th>
            </tr>
        </thead>
        <tbody>
            {include file="Pro/partials/HistoryRows.tpl"|vtemplate_path:'MailManager' ENTRIES=$HISTORY.entries}
        </tbody>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-outline-secondary its4you-history-more {if !$HISTORY.has_more}hide{/if}">
            {vtranslate('LBL_LOAD_MORE', 'MailManager')}
        </button>
    </div>
</div>
{/strip}
