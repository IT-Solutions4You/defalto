{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Knowledge files of one scope (general = the admin's, personal = the user's
   own); the rows are drawn by ProKnowledge.js from data-files *}
{strip}
<div class="its4you-knowledge" data-scope="{$KNOWLEDGE_SCOPE|escape}" data-files="{$KNOWLEDGE_FILES_JSON|escape}"
     data-ready="{vtranslate('LBL_KNOWLEDGE_READY', 'Settings:MailManager')|escape}"
     data-waiting="{vtranslate('LBL_KNOWLEDGE_WAITING', 'Settings:MailManager')|escape}"
     data-bad-type="{vtranslate('LBL_KNOWLEDGE_BAD_TYPE', 'Settings:MailManager')|escape}"
     data-delete="{vtranslate('LBL_DELETE', 'Vtiger')|escape}">
    <div class="form-text mb-2">
        {if $KNOWLEDGE_SCOPE eq 'general'}
            {vtranslate('LBL_KNOWLEDGE_GENERAL_HINT', 'Settings:MailManager')}
        {else}
            {vtranslate('LBL_KNOWLEDGE_PERSONAL_HINT', 'Settings:MailManager')}
        {/if}
    </div>
    <div class="d-flex justify-content-end mb-2">
        <label class="btn btn-outline-secondary m-0">
            <i class="fa-solid fa-upload"></i>&nbsp;{vtranslate('LBL_UPLOAD', 'Vtiger')}
            <input type="file" class="d-none its4you-knowledge-input" multiple accept=".md,.markdown,.txt,.html,.htm">
        </label>
    </div>
    <table class="table table-bordered its4you-knowledge-table">
        <thead>
            <tr>
                <th class="its4you-knowledge-actions">{vtranslate('LBL_ACTIONS', 'Vtiger')}</th>
                <th>{vtranslate('LBL_KNOWLEDGE_FILE', 'Settings:MailManager')}</th>
                <th class="its4you-knowledge-state">{vtranslate('LBL_KNOWLEDGE_STATE', 'Settings:MailManager')}</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div class="form-text its4you-knowledge-empty d-none">{vtranslate('LBL_NO_RECORDS_FOUND', 'Vtiger')}</div>
</div>
{/strip}
