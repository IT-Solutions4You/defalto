{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="its4you-crm-rail-inner" data-threadid="{$THREAD_ID}">
    {if $LINKS}
        <div class="its4you-crm-section">
            {foreach item=LINK from=$LINKS}
                <div class="its4you-crm-card {if $LINK.details}its4you-crm-card-detailed{/if}">
                    <div class="its4you-crm-card-main">
                        {* the module icon small before the name *}
                        <div class="its4you-crm-card-name">
                            <span class="its4you-crm-card-icon" title="{vtranslate($LINK.module, $LINK.module)}">{$LINK.icon}</span>
                            <a href="index.php?module={$LINK.module}&view=Detail&record={$LINK.crmid}" target="_blank" rel="noopener"
                               title="{vtranslate($LINK.module, $LINK.module)}: {$LINK.label|escape}">{$LINK.label|escape}</a>
                        </div>
                        {* each value on a line of its own *}
                        {foreach item=SUMMARY_LINE from=$LINK.summary}<div class="its4you-crm-card-summary">{$SUMMARY_LINE|escape}</div>{/foreach}
                        {* how the record came to the conversation — results, not suggestions;
                           by what it was linked shows on hover *}
                        {if $LINK.autolinked}
                            <div class="its4you-crm-card-summary its4you-crm-card-origin"
                                 title="{vtranslate('LBL_AUTOLINK_'|cat:strtoupper($LINK.autolinked), $MODULE)|escape}">
                                <i class="fa-solid fa-check"></i>&nbsp;{vtranslate('LBL_AUTOLINKED', $MODULE)|replace:':':''}
                            </div>
                        {/if}
                        {if $AUTOCOMMENT && $DRIVING && $DRIVING.crmid eq $LINK.crmid}
                            <div class="its4you-crm-card-summary its4you-crm-card-origin">
                                <i class="fa-solid fa-check"></i>&nbsp;{vtranslate('LBL_LAST_MAIL_COMMENTED', $MODULE)}
                            </div>
                        {/if}
                        {* a card with details lists the assigned user among its fields *}
                        {if $LINK.owner && !$LINK.details}
                            <div class="its4you-crm-card-summary its4you-crm-card-owner">
                                <i class="fa-solid {if $LINK.owner_group}fa-users{else}fa-user{/if}"></i>&nbsp;{$LINK.owner|escape}
                            </div>
                        {/if}
                        {* key + summary fields of what the mail is about (Layout Editor) *}
                        {if $LINK.details}
                            <div class="its4you-crm-card-details">
                                {foreach item=DETAIL from=$LINK.details}
                                    <div class="its4you-crm-card-detail">
                                        <div class="its4you-crm-card-detail-label">{$DETAIL.label|escape}</div>
                                        <div class="its4you-crm-card-detail-value">{$DETAIL.value|escape}</div>
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                    </div>
                    <a href="javascript:void(0);" class="its4you-crm-unlink" data-crmid="{$LINK.crmid}" title="{vtranslate('LBL_UNLINK', $MODULE)}">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            {/foreach}
        </div>
    {/if}

    {* ONE "Create" menu for everything made from the conversation: the new
       records the mail can start (organization, contact, ticket, opportunity,
       project…) and what is added to the records it already has — a comment
       on the DRIVING record (it logs the mail content there), an event (the
       core quick-create, related to it after the save) and a task of the linked
       project. Every item keeps its own rule; only those that apply show *}
    {assign var=HAS_ADD_ITEMS value=($ACTIONS && ($ACTIONS.comments || $ACTIONS.activities || $ACTIONS.task_json))}
    {if $CREATE_PREFILLS || $HAS_ADD_ITEMS}
        <div class="its4you-crm-section">
            {* the panel's actions in one row like the record actions of Defalto – they wrap when narrow *}
            <div class="d-flex flex-wrap gap-2 its4you-crm-actions">
                <div class="btn-group its4you-crm-menu its4you-crm-create-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-plus me-1"></i>{vtranslate('LBL_CREATE', $MODULE)}
                    </button>
                    <ul class="dropdown-menu">
                        {foreach key=CREATE_MODULE item=PREFILL from=$CREATE_PREFILLS}
                            <li>
                                {* A project task first needs its target project — the
                                   item opens the picker box instead of the quick-create *}
                                <a href="javascript:void(0);"
                                   class="dropdown-item {if $PREFILL.projects_json}its4you-crm-create-task{else}its4you-crm-create{/if}"
                                   data-module="{$CREATE_MODULE}" data-prefill="{$PREFILL.json|escape}"
                                   data-prefill-labels="{$PREFILL.labels_json|escape}"
                                   {if $PREFILL.projects_json}data-projects="{$PREFILL.projects_json|escape}"{/if}>
                                    <span class="its4you-crm-create-icon">{$PREFILL.icon}</span>{$PREFILL.label}
                                </a>
                            </li>
                        {/foreach}
                        {if $CREATE_PREFILLS && $HAS_ADD_ITEMS}
                            <li><hr class="dropdown-divider"></li>
                        {/if}
                        {if $HAS_ADD_ITEMS}
                            {* a task of the linked project, made from the mail being read *}
                            {if $ACTIONS.task_json}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-crm-create" data-module="ProjectTask"
                                       data-prefill="{$ACTIONS.task_json|escape}"
                                       data-prefill-labels="{$ACTIONS.task_labels_json|escape}">
                                        <span class="its4you-crm-create-icon"><i class="fa-solid fa-list-check"></i></span>{vtranslate('SINGLE_ProjectTask', 'ProjectTask')}
                                    </a>
                                </li>
                            {/if}
                            {* An appointment (the Defalto calendar) — without a case
                               record it still relates to the organization / person *}
                            {if $ACTIONS.activities}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-crm-activity"
                                       data-crmid="{$ACTIONS.event_crmid}" title="{$ACTIONS.event_label|escape}">
                                        <span class="its4you-crm-create-icon"><i class="fa-regular fa-calendar-plus"></i></span>{vtranslate('SINGLE_Appointments', 'Appointments')}
                                    </a>
                                </li>
                            {/if}
                            {if $ACTIONS.comments}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-crm-comment-toggle">
                                        <span class="its4you-crm-create-icon"><i class="fa-regular fa-comment"></i></span>{vtranslate('SINGLE_ModComments', 'ModComments')}
                                    </a>
                                </li>
                            {/if}
                        {/if}
                    </ul>
                </div>
                {if $PICKER_MODULES}
                    {* Link an EXISTING record — the item opens the core record popup of the
                       module; with an organization linked it lists that organization's
                       records unless the user unticks the filter *}
                    <div class="btn-group its4you-crm-menu its4you-crm-pick-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-link me-1"></i>{vtranslate('LBL_LINK_EXISTING', $MODULE)}
                        </button>
                        <ul class="dropdown-menu">
                            {if $PICKER_PARENT}
                                {assign var=PICKER_PARENT_LABEL value=$PICKER_PARENT.label|escape}
                                <li class="its4you-crm-pick-scope px-3 py-1">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input its4you-crm-pick-filter" checked="checked"
                                               data-parent="{$PICKER_PARENT.crmid}">
                                        &nbsp;{vtranslate('LBL_LINK_ONLY_OF', $MODULE)|replace:'%s':$PICKER_PARENT_LABEL}
                                    </label>
                                </li>
                            {/if}
                            {foreach key=PICK_MODULE item=PICK from=$PICKER_MODULES}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-crm-pick" data-module="{$PICK_MODULE}"
                                       data-filtered="{if $PICK.filtered}1{else}0{/if}"
                                       {if $PICK.parent_id}data-parent-module="{$PICK.parent_module}" data-parent-id="{$PICK.parent_id}"{/if}>
                                        <span class="its4you-crm-create-icon">{$PICK.icon}</span>{$PICK.label}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
            {if $CREATE_PREFILLS}
                <div class="its4you-crm-project-box hide">
                    <div class="its4you-crm-title">{vtranslate('LBL_PICK_PROJECT', $MODULE)}</div>
                    <select class="inputElement form-select w-100 its4you-crm-project-select"></select>
                    <div class="its4you-crm-comment-buttons">
                        <button type="button" class="btn btn-primary btn-sm its4you-crm-project-create">{vtranslate('LBL_CREATE', $MODULE)}</button>
                        <a href="javascript:void(0);" class="its4you-crm-project-cancel">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                </div>
            {/if}
            {* the comment window — a template the "Comment" item shows as a
               modal (the narrow panel is no place to read and edit a mail) *}
            {if $HAS_ADD_ITEMS && $ACTIONS.comments}
                <div class="its4you-crm-comment-modal hide" data-crmid="{$DRIVING.crmid}">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            {include file="ModalHeader.tpl"|vtemplate_path:'Vtiger' TITLE={vtranslate('LBL_ADD_COMMENT', $MODULE)}}
                            <div class="modal-body">
                                <textarea class="inputElement form-control its4you-crm-comment-text" rows="16"
                                          placeholder="{vtranslate('LBL_ADD_COMMENT', $MODULE)}">{$ACTIONS.comment_prefill|escape}</textarea>
                            </div>
                            {include file="ModalFooter.tpl"|vtemplate_path:'Vtiger'}
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    {else}
        {* nothing to create yet — the menu stays in its place and says why *}
        <div class="its4you-crm-section">
            <div class="d-flex flex-wrap gap-2 its4you-crm-actions">
                <div class="btn-group its4you-crm-menu its4you-crm-create-group">
                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled="disabled">
                        <i class="fa-solid fa-plus me-1"></i>{vtranslate('LBL_CREATE', $MODULE)}
                    </button>
                </div>
                {if $PICKER_MODULES}
                    {* Link an EXISTING record — the item opens the core record popup of the
                       module; with an organization linked it lists that organization's
                       records unless the user unticks the filter *}
                    <div class="btn-group its4you-crm-menu its4you-crm-pick-group">
                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-link me-1"></i>{vtranslate('LBL_LINK_EXISTING', $MODULE)}
                        </button>
                        <ul class="dropdown-menu">
                            {if $PICKER_PARENT}
                                {assign var=PICKER_PARENT_LABEL value=$PICKER_PARENT.label|escape}
                                <li class="its4you-crm-pick-scope px-3 py-1">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input its4you-crm-pick-filter" checked="checked"
                                               data-parent="{$PICKER_PARENT.crmid}">
                                        &nbsp;{vtranslate('LBL_LINK_ONLY_OF', $MODULE)|replace:'%s':$PICKER_PARENT_LABEL}
                                    </label>
                                </li>
                            {/if}
                            {foreach key=PICK_MODULE item=PICK from=$PICKER_MODULES}
                                <li>
                                    <a href="javascript:void(0);" class="dropdown-item its4you-crm-pick" data-module="{$PICK_MODULE}"
                                       data-filtered="{if $PICK.filtered}1{else}0{/if}"
                                       {if $PICK.parent_id}data-parent-module="{$PICK.parent_module}" data-parent-id="{$PICK.parent_id}"{/if}>
                                        <span class="its4you-crm-create-icon">{$PICK.icon}</span>{$PICK.label}
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
            <div class="its4you-crm-add-hint">{vtranslate('LBL_ADD_NEEDS_LINK', $MODULE)}</div>
        </div>
    {/if}

    {if $SUGGESTIONS}
        <div class="its4you-crm-section">
            <div class="its4you-crm-title">{vtranslate('LBL_SUGGESTIONS', $MODULE)}</div>
            {foreach item=SUGGESTION from=$SUGGESTIONS}
                <div class="its4you-crm-card">
                    <div class="its4you-crm-card-main">
                        <div class="its4you-crm-card-name">
                            <span class="its4you-crm-card-icon" title="{vtranslate($SUGGESTION.module, $SUGGESTION.module)}">{$SUGGESTION.icon}</span>
                            <a href="index.php?module={$SUGGESTION.module}&view=Detail&record={$SUGGESTION.crmid}" target="_blank" rel="noopener">{$SUGGESTION.label|escape}</a>
                        </div>
                        {foreach item=SUMMARY_LINE from=$SUGGESTION.summary}<div class="its4you-crm-card-summary">{$SUMMARY_LINE|escape}</div>{/foreach}
                        {if $SUGGESTION.reason eq 'name'}<div class="its4you-crm-card-summary"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;{vtranslate('LBL_SUGGEST_BY_NAME', $MODULE)}</div>{/if}
                        {if $SUGGESTION.reason eq 'sender_name'}<div class="its4you-crm-card-summary"><i class="fa-solid fa-triangle-exclamation"></i>&nbsp;{vtranslate('LBL_SUGGEST_BY_SENDER_NAME', $MODULE)}</div>{/if}
                        {if $SUGGESTION.reason eq 'potential'}<div class="its4you-crm-card-summary">{vtranslate('LBL_SUGGEST_PROJECT_OF_POTENTIAL', $MODULE)}</div>{/if}
                        {if $SUGGESTION.reason eq 'project'}<div class="its4you-crm-card-summary">{vtranslate('LBL_SUGGEST_TASK_OF_PROJECT', $MODULE)}</div>{/if}
                        {if $SUGGESTION.reason eq 'ticket_no'}<div class="its4you-crm-card-summary">{vtranslate('LBL_SUGGEST_TICKET_IN_SUBJECT', $MODULE)}</div>{/if}
                    </div>
                    <button type="button" class="btn btn-primary btn-sm its4you-crm-link" data-crmid="{$SUGGESTION.crmid}">{vtranslate('LBL_LINK', $MODULE)}</button>
                    {* not this record — the suggestion is not offered for the conversation again *}
                    <a href="javascript:void(0);" class="its4you-crm-dismiss" data-crmid="{$SUGGESTION.crmid}" title="{vtranslate('LBL_DISMISS', $MODULE)}">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            {/foreach}
        </div>
    {/if}

    {if !$LINKS && !$SUGGESTIONS}
        <div class="its4you-crm-empty">{vtranslate('LBL_NO_CRM_MATCHES', $MODULE)}</div>
    {/if}


    {if ($TRIAGE && $TRIAGE.state eq 'suggested') || $LINK_STEPS}
        {* Suggestion card (fixed rules — a matching address, a missing record —
           plus the AI sorting result when the assistant is on) — rendered
           here (single data flow with the rail refresh) but DISPLAYED in the
           conversation column: JS clones it into .its4you-ai-suggestion-slot,
           CSS hides this original *}
        <div class="its4you-crm-section its4you-ai-suggestion-section">
            <div class="its4you-crm-card its4you-ai-suggestion {if $TRIAGE}its4you-ai-suggestion-{$TRIAGE.state}{/if}">
                <div class="its4you-ai-suggestion-title">
                    <i class="fa-regular fa-lightbulb"></i>&nbsp;{vtranslate('LBL_SUGGESTION', $MODULE)}
                </div>
                {* An existing record that is the conversation's next step — the
                   same suggestion as in the panel, linked or refused here too *}
                {foreach item=LINK_STEP from=$LINK_STEPS}
                    <div class="its4you-ai-linkstep">
                        <span class="its4you-ai-linkstep-text">
                            {if $LINK_STEP.reason eq 'potential'}{vtranslate('LBL_SUGGEST_PROJECT_OF_POTENTIAL', $MODULE)}{else}{vtranslate('LBL_SUGGEST_TICKET_IN_SUBJECT', $MODULE)}{/if}:&nbsp;
                            <a href="index.php?module={$LINK_STEP.module}&view=Detail&record={$LINK_STEP.crmid}" target="_blank" rel="noopener">{$LINK_STEP.label|escape}</a>
                        </span>
                        <button type="button" class="btn btn-primary btn-sm its4you-crm-link" data-crmid="{$LINK_STEP.crmid}">{vtranslate('LBL_LINK', $MODULE)}</button>
                        <a href="javascript:void(0);" class="its4you-crm-dismiss" data-crmid="{$LINK_STEP.crmid}" title="{vtranslate('LBL_DISMISS', $MODULE)}">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    </div>
                {/foreach}
                {if $TRIAGE && $TRIAGE.state eq 'suggested'}
                <div class="its4you-ai-suggestion-text">
                        {if $THREAD.label_name}<b>{$THREAD.label_name|escape}</b>{/if}
                        {if $THREAD.priority}&nbsp;&middot;&nbsp;{vtranslate('LBL_PRIORITY_'|cat:strtoupper($THREAD.priority), $MODULE)}{/if}
                        &nbsp;&rarr;&nbsp;
                        {if $TRIAGE.action eq 'spam'}{vtranslate('LBL_SUGGEST_SPAM', $MODULE)}
                        {elseif $TRIAGE.action eq 'delete'}{vtranslate('LBL_SUGGEST_DELETE', $MODULE)}
                        {elseif $TRIAGE.action eq 'close_ticket'}
                            {vtranslate('LBL_SUGGEST_CLOSE_TICKET', $MODULE)}:&nbsp;
                            <a href="index.php?module=HelpDesk&view=Detail&record={$TRIAGE.crmid}" target="_blank" rel="noopener">{$TRIAGE.record_label|escape}</a>
                        {else}{vtranslate('LBL_SUGGEST_CREATE', $MODULE)|replace:'%s':$TRIAGE.module_label}{/if}
                        {if $TRIAGE.module && !$TRIAGE.known_sender}
                            <div class="its4you-crm-card-summary">
                                {vtranslate('LBL_TRIAGE_NEW_SENDER', $MODULE)}&nbsp;{$TRIAGE.from_email|escape}
                                &nbsp;&middot;&nbsp;{vtranslate('LBL_TRIAGE_NO_RECORD', $MODULE)}
                            </div>
                        {/if}
                </div>
                <div class="its4you-ai-suggestion-buttons">
                        {if $TRIAGE.action eq 'spam'}
                            <button type="button" class="btn btn-outline-secondary btn-sm its4you-triage-action" data-action="spam">
                                <i class="fa-solid fa-ban me-1"></i>{vtranslate('LBL_MOVE_TO_SPAM', $MODULE)}
                            </button>
                        {elseif $TRIAGE.action eq 'close_ticket'}
                            <button type="button" class="btn btn-primary btn-sm its4you-triage-close">
                                <i class="fa-solid fa-check me-1"></i>{vtranslate('LBL_CLOSE_TICKET_SAVE', $MODULE)}
                            </button>
                        {elseif $TRIAGE.action eq 'delete'}
                            <button type="button" class="btn btn-outline-secondary btn-sm its4you-triage-action" data-action="delete">
                                <i class="fa-solid fa-trash me-1"></i>{vtranslate('LBL_MOVE_TO_TRASH', $MODULE)}
                            </button>
                        {else}
                            <button type="button" class="btn btn-primary btn-sm its4you-crm-create"
                                    data-module="{$TRIAGE.module}" data-prefill="{$TRIAGE.prefill_json|escape}"
                                    data-prefill-labels="{$TRIAGE.prefill_labels_json|escape}">
                                {vtranslate('LBL_CREATE', $MODULE)}&nbsp;{$TRIAGE.module_label}{if $TRIAGE.record_hint}:&nbsp;{$TRIAGE.record_hint|escape}{/if}
                            </button>
                            {* the record may exist already — pick it instead of creating a duplicate *}
                            {if isset($PICKER_MODULES[$TRIAGE.module])}
                                <button type="button" class="btn btn-outline-secondary btn-sm its4you-crm-pick" data-module="{$TRIAGE.module}"
                                        data-filtered="{if $PICKER_MODULES[$TRIAGE.module].filtered}1{else}0{/if}">
                                    <i class="fa-solid fa-link me-1"></i>{vtranslate('LBL_LINK_EXISTING', $MODULE)}
                                </button>
                            {/if}
                        {/if}
                    {* a creation step skips just that step, the chain goes on *}
                    <a href="javascript:void(0);" class="its4you-triage-dismiss"
                       data-step="{if !$TRIAGE.action}{$TRIAGE.module}{/if}">{vtranslate('LBL_DISMISS', $MODULE)}</a>
                </div>
                {/if}
            </div>
        </div>
    {/if}

</div>
{/strip}
