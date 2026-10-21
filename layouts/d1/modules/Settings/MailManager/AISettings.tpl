{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="listViewPageDiv detailViewContainer px-4 pb-4 its4you-module-settings its4you-ai-settings" data-agents="{$AI_AGENTS_JSON|escape}">
    <div class="bg-body rounded">
        <div class="p-3 border-bottom">
            <h4 class="m-0">
                <a href="index.php?module=MailManager&parent=Settings&view=Index">{vtranslate('MailManager', $QUALIFIED_MODULE)}</a>
                &nbsp;&gt;&nbsp;{vtranslate('LBL_AI_SETTINGS', $QUALIFIED_MODULE)}
            </h4>
        </div>
        <div class="contents tabbable pt-3">
            <ul class="nav nav-tabs massEditTabs border-bottom">
                <li class="nav-item"><a class="nav-link {if !$RADAR_OPEN}active{/if}" data-bs-toggle="tab" href="#mmAiTabGeneral">{vtranslate('LBL_TAB_GENERAL', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabAgents">{vtranslate('LBL_AI_AGENTS', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabFeatures">{vtranslate('LBL_AI_FEATURES', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabLabels">{vtranslate('LBL_EMAIL_LABELS', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabKnowledge">{vtranslate('LBL_KNOWLEDGE', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabUsage">{vtranslate('LBL_AI_USAGE', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link {if $RADAR_OPEN}active{/if}" data-bs-toggle="tab" href="#mmAiTabRadar">{vtranslate('LBL_RADAR', $QUALIFIED_MODULE)}</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#mmAiTabStatus">{vtranslate('LBL_AI_FAILOVER_TAB', $QUALIFIED_MODULE)}</a></li>
            </ul>
            <div class="tab-content layoutContent overflowVisible">
            <div class="tab-pane fade {if !$RADAR_OPEN}active show{/if}" id="mmAiTabGeneral">
            <div class="container-fluid pt-3 px-3">
                <div class="row mb-3 align-items-start">
                    <label class="col-sm-3 col-form-label fw-semibold">{vtranslate('LBL_AI_ENABLE', $QUALIFIED_MODULE)}</label>
                    <div class="col-sm-6">
                        <div class="form-check form-switch pt-2">
                            <input type="checkbox" class="form-check-input" name="ai_enabled" {if $CONFIG.ai_enabled eq '1'}checked{/if}>
                        </div>
                    </div>
                </div>
                {* providers, keys and models are set up per agent (the Agents tab) *}
                <div class="row mb-3">
                    <div class="col-sm-9 offset-sm-3 form-text">{vtranslate('LBL_AI_AGENTS_GENERAL_HINT', $QUALIFIED_MODULE)}</div>
                </div>
                {* what the assistant may and may not do with the mail content *}
                <div class="row mb-3">
                    <div class="col-sm-9 offset-sm-3"><div class="alert alert-info mb-0 small">{vtranslate('LBL_AI_SAFETY_HINT', $QUALIFIED_MODULE)}</div></div>
                </div>
                <div class="row mb-3 align-items-start">
                    <label class="col-sm-3 col-form-label fw-semibold">{vtranslate('LBL_LIST_PAGE_SIZE', $QUALIFIED_MODULE)}</label>
                    <div class="col-sm-6">
                        <select class="form-select inputElement its4you-ai-page-size" name="list_page_size">
                            {foreach item=SIZE_OPTION from=$PAGE_SIZES}
                                <option value="{$SIZE_OPTION}" {if $SIZE_OPTION eq $PAGE_SIZE}selected{/if}>{$SIZE_OPTION}</option>
                            {/foreach}
                        </select>
                        <div class="form-text">{vtranslate('LBL_LIST_PAGE_SIZE_HINT', $QUALIFIED_MODULE)}</div>
                    </div>
                </div>
            </div>
                <div class="container-fluid py-3 border-top">
                    <div class="row">
                        <div class="col text-end">
                            <a class="btn btn-primary cancelLink" href="index.php?module=MailManager&parent=Settings&view=Index">{vtranslate('LBL_BACK_TO_LIST', $QUALIFIED_MODULE)}</a>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-primary active saveButton its4you-save-ai">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
            {* AI agents (I13): a key per provider, the agents, which agent does each task *}
            <div class="tab-pane fade" id="mmAiTabAgents">
                <div class="container-fluid p-3 its4you-ai-agents">
                    <h5 class="mb-2">{vtranslate('LBL_AI_KEYS', $QUALIFIED_MODULE)}</h5>
                    <div class="form-text mb-2">{vtranslate('LBL_AI_KEYS_HINT', $QUALIFIED_MODULE)}</div>
                    <table class="table table-sm table-borderless border align-middle mb-4">
                        <tbody>
                            {foreach key=PROVIDER item=PROVIDER_INFO from=$AI_PROVIDERS}
                                <tr class="its4you-ai-key-row" data-provider="{$PROVIDER}">
                                    <td class="fw-semibold text-nowrap">{$PROVIDER_INFO.label|escape}</td>
                                    <td class="text-nowrap">
                                        {if $PROVIDER_INFO.has_key}
                                            <span class="text-success"><i class="fa-solid fa-check me-1"></i>{vtranslate('LBL_AI_KEY_STORED', $QUALIFIED_MODULE)}</span>
                                            {if $PROVIDER_INFO.verified_count}<span class="text-secondary small ms-1">({vtranslate('LBL_AI_MODELS_VERIFIED', $QUALIFIED_MODULE)|replace:'%s':$PROVIDER_INFO.verified_count})</span>{/if}
                                        {else}
                                            <span class="text-secondary">{vtranslate('LBL_AI_KEY_NONE', $QUALIFIED_MODULE)}</span>
                                        {/if}
                                    </td>
                                    <td class="w-50">
                                        <div class="input-group input-group-sm">
                                            {* the key is never shown back — empty keeps the stored one *}
                                            <input type="password" class="form-control inputElement its4you-ai-key" autocomplete="new-password" value=""
                                                   {if $PROVIDER_INFO.has_key}placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"{/if}>
                                            <button type="button" class="btn btn-outline-secondary its4you-ai-key-verify">{vtranslate('LBL_VERIFY_AND_SAVE', $QUALIFIED_MODULE)}</button>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-secondary its4you-ai-apply-recommended" {if !$PROVIDER_INFO.has_key}disabled{/if}
                                                title="{vtranslate('LBL_AI_APPLY_RECOMMENDED_HINT', $QUALIFIED_MODULE)|escape}">
                                            <i class="fa-regular fa-star me-1"></i>{vtranslate('LBL_AI_APPLY_RECOMMENDED', $QUALIFIED_MODULE)}
                                        </button>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {* the backup provider in short – set on its own tab (I17) *}
                    <div class="small mb-3 its4you-ai-failover-line">
                        {vtranslate('LBL_AI_FAILOVER_CURRENT', $QUALIFIED_MODULE)}&nbsp;<span class="fw-semibold">{if $AI_FAILOVER.provider && isset($AI_PROVIDERS[$AI_FAILOVER.provider])}{$AI_PROVIDERS[$AI_FAILOVER.provider].label|escape}{else}{vtranslate('LBL_AI_FAILOVER_OFF', $QUALIFIED_MODULE)}{/if}</span>
                        &nbsp;–&nbsp;<a href="#mmAiTabStatus" class="its4you-ai-open-failover">{vtranslate('LBL_AI_FAILOVER_SET', $QUALIFIED_MODULE)}</a>
                    </div>

                    <div class="d-flex align-items-center mb-2">
                        <h5 class="m-0">{vtranslate('LBL_AI_AGENTS', $QUALIFIED_MODULE)}</h5>
                        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto its4you-ai-agent-edit" data-agentid="0">
                            <i class="fa-solid fa-plus me-1"></i>{vtranslate('LBL_AI_AGENT_ADD', $QUALIFIED_MODULE)}
                        </button>
                    </div>
                    <table class="table table-sm table-borderless border align-middle mb-4">
                        <thead class="border-bottom">
                            <tr>
                                <th>{vtranslate('LBL_AI_AGENT_NAME', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_AI_AGENT_TIER', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_AI_PROVIDER', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_AI_MODEL', $QUALIFIED_MODULE)}</th>
                                <th>{vtranslate('LBL_AI_AGENT_EFFORT', $QUALIFIED_MODULE)}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=AGENT from=$AI_AGENTS}
                                <tr class="{if !$AGENT.active}text-secondary{/if}">
                                    <td class="fw-semibold">{$AGENT.name|escape}{if !$AGENT.active} <span class="badge text-bg-light">{vtranslate('LBL_AI_AGENT_INACTIVE', $QUALIFIED_MODULE)}</span>{/if}</td>
                                    <td>{$AGENT.tier_label|escape}</td>
                                    <td>{$AGENT.provider_label|escape}{if !$AGENT.has_key} <span class="badge text-bg-warning">{vtranslate('LBL_AI_KEY_NONE', $QUALIFIED_MODULE)}</span>{/if}</td>
                                    <td>{if $AGENT.model}{$AGENT.model|escape}{else}&mdash;{/if}{if $AGENT.fit_label} <span class="badge text-bg-light">{$AGENT.fit_label|escape}</span>{/if}</td>
                                    <td>{if $AGENT.effort}{vtranslate('LBL_AI_EFFORT_'|cat:strtoupper($AGENT.effort), $QUALIFIED_MODULE)}{else}&mdash;{/if}</td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary its4you-ai-agent-test" data-agentid="{$AGENT.agentid}" title="{vtranslate('LBL_AI_AGENT_TEST', $QUALIFIED_MODULE)|escape}"><i class="fa-solid fa-flask"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary its4you-ai-agent-edit" data-agentid="{$AGENT.agentid}" title="{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)|escape}"><i class="fa-solid fa-pencil"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary its4you-ai-agent-delete" data-agentid="{$AGENT.agentid}" title="{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)|escape}"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>

                    <h5 class="mb-2">{vtranslate('LBL_AI_TASKS', $QUALIFIED_MODULE)}</h5>
                    <div class="form-text mb-2">{vtranslate('LBL_AI_TASKS_HINT', $QUALIFIED_MODULE)}</div>
                    <table class="table table-sm table-borderless border align-middle its4you-ai-tasks">
                        <tbody>
                            {foreach item=AI_TASK from=$AI_TASKS}
                                <tr>
                                    <td class="w-50">{$AI_TASK.label|escape}</td>
                                    <td>
                                        <select class="form-select form-select-sm inputElement its4you-ai-task" data-task="{$AI_TASK.task}">
                                            {foreach item=AGENT from=$AI_AGENTS}
                                                <option value="{$AGENT.agentid}" {if $AGENT.agentid eq $AI_TASK.agentid}selected{/if}>{$AGENT.name|escape}</option>
                                            {/foreach}
                                        </select>
                                        {if $AI_TASK.task eq 'triage'}
                                            {* unsure sorting → the fallback agent sorts again *}
                                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2" title="{vtranslate('LBL_AI_TRIAGE_CASCADE_HINT', $QUALIFIED_MODULE)|escape}">
                                                <div class="form-check m-0">
                                                    <input type="checkbox" class="form-check-input" id="its4youAiCascade" name="ai_triage_cascade" {if $CONFIG.ai_triage_cascade eq '1'}checked{/if}>
                                                    <label class="form-check-label small" for="its4youAiCascade">{vtranslate('LBL_AI_TRIAGE_CASCADE', $QUALIFIED_MODULE)}</label>
                                                </div>
                                                <select class="form-select form-select-sm w-auto inputElement its4you-ai-fallback">
                                                    <option value="">{vtranslate('LBL_AI_FALLBACK_NONE', $QUALIFIED_MODULE)}</option>
                                                    {foreach item=AGENT from=$AI_AGENTS}
                                                        <option value="{$AGENT.agentid}" {if $AGENT.agentid eq $CONFIG.ai_triage_fallback}selected{/if}>{$AGENT.name|escape}</option>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    <div class="text-end"><button type="button" class="btn btn-primary active its4you-ai-tasks-save">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button></div>
                </div>
            </div>
            {* The feature toggles save through the same Save as the general tab *}
            <div class="tab-pane fade" id="mmAiTabFeatures">
            <div class="container-fluid pt-3 px-3">
                <div class="row mb-3">
                    <div class="col-lg-8">
                        <table class="table table-bordered its4you-ai-features">
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_SUMMARY', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_SUMMARY_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_summary" {if $CONFIG.ai_feature_summary eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_TRANSLATE', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_TRANSLATE_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_translate" {if $CONFIG.ai_feature_translate eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_DRAFT', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_DRAFT_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_draft" {if $CONFIG.ai_feature_draft eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_TRIAGE', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_TRIAGE_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_triage" {if $CONFIG.ai_feature_triage eq '1'}checked{/if}>
                                    <div class="mt-2">
                                        <select class="form-select form-select-sm inputElement its4you-ai-triage-mode" name="ai_triage_mode">
                                            <option value="suggest" {if $CONFIG.ai_triage_mode neq 'auto'}selected{/if}>{vtranslate('LBL_TRIAGE_SUGGEST', $QUALIFIED_MODULE)}</option>
                                            <option value="auto" {if $CONFIG.ai_triage_mode eq 'auto'}selected{/if}>{vtranslate('LBL_TRIAGE_AUTO', $QUALIFIED_MODULE)}</option>
                                        </select>
                                    </div>
                                    <div class="mt-2 d-flex align-items-center gap-2" title="{vtranslate('LBL_AUTOCREATE_LIMIT_DESC', $QUALIFIED_MODULE)|escape}">
                                        <input type="number" class="form-control form-control-sm inputElement its4you-ai-limit" name="ai_autocreate_limit" min="0" max="1000" value="{$CONFIG.ai_autocreate_limit|escape}">
                                        <span class="form-text m-0">{vtranslate('LBL_AUTOCREATE_LIMIT', $QUALIFIED_MODULE)}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_DRAFTCHECK', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_DRAFTCHECK_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_draftcheck" {if $CONFIG.ai_feature_draftcheck eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_RADAR', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_RADAR_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_radar" {if $CONFIG.ai_feature_radar eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_ANONYMIZE', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_ANONYMIZE_LEVEL_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    {* the level (I16): off · contact data · full (also names, companies, addresses) *}
                                    <select class="form-select form-select-sm" name="ai_anonymize">
                                        {foreach item=ANONYMIZE_LEVEL from=$AI_ANONYMIZE_LEVELS}
                                            <option value="{$ANONYMIZE_LEVEL}" {if $CONFIG.ai_anonymize eq $ANONYMIZE_LEVEL}selected{/if}>{vtranslate('LBL_ANONYMIZE_LEVEL_'|cat:$ANONYMIZE_LEVEL, $QUALIFIED_MODULE)}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_ENTITY', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_ENTITY_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_entity" {if $CONFIG.ai_feature_entity eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_SPAMLEARN', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_SPAMLEARN_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_spamlearn" {if $CONFIG.ai_feature_spamlearn eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_AUTOCOMMENT', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_AUTOCOMMENT_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_autocomment" {if $CONFIG.ai_feature_autocomment eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_EMAILFILING', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_EMAILFILING_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_emailfiling" {if $CONFIG.ai_feature_emailfiling eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_SIMILAR', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_SIMILAR_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_similar" {if $CONFIG.ai_feature_similar eq '1'}checked{/if}>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_KNOWLEDGE', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_KNOWLEDGE_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_knowledge" {if $CONFIG.ai_feature_knowledge eq '1'}checked{/if}>
                                    <div class="form-check mt-2">
                                        <input type="checkbox" class="form-check-input" id="mmAiKnowledgeFaq" name="ai_knowledge_faq" {if $CONFIG.ai_knowledge_faq eq '1'}checked{/if}>
                                        <label class="form-check-label form-text m-0" for="mmAiKnowledgeFaq">{vtranslate('LBL_KNOWLEDGE_FAQ', $QUALIFIED_MODULE)}</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="mmAiKnowledgeTickets" name="ai_knowledge_tickets" {if $CONFIG.ai_knowledge_tickets eq '1'}checked{/if}>
                                        <label class="form-check-label form-text m-0" for="mmAiKnowledgeTickets">{vtranslate('LBL_KNOWLEDGE_TICKETS', $QUALIFIED_MODULE)}</label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold">{vtranslate('LBL_FEATURE_RECORD_HISTORY', $QUALIFIED_MODULE)}</div>
                                    <div class="form-text">{vtranslate('LBL_FEATURE_RECORD_HISTORY_DESC', $QUALIFIED_MODULE)}</div>
                                </td>
                                <td class="its4you-feature-toggle">
                                    <input type="checkbox" class="form-check-input" name="ai_feature_recordhistory" {if $CONFIG.ai_feature_recordhistory eq '1'}checked{/if}>
                                    <label class="form-label form-text d-block mt-2 mb-1" for="mmAiRecordHistoryTokens">{vtranslate('LBL_RECORD_HISTORY_TOKENS', $QUALIFIED_MODULE)}</label>
                                    <input type="number" class="form-control form-control-sm inputElement" id="mmAiRecordHistoryTokens" name="ai_recordhistory_tokens" min="0" step="1000" value="{$CONFIG.ai_recordhistory_tokens|escape}" placeholder="{vtranslate('LBL_RECORD_HISTORY_TOKENS_PLACEHOLDER', $QUALIFIED_MODULE)|escape}">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                {* How the AI assesses the priority: the admin's own rules on top of the built-in ones *}
                <div class="row mb-3">
                    <div class="col-lg-8">
                        <label class="form-label fw-semibold" for="mmAiPriorityRules">{vtranslate('LBL_PRIORITY_RULES', $QUALIFIED_MODULE)}</label>
                        <div class="form-text mb-2">{vtranslate('LBL_PRIORITY_RULES_HINT', $QUALIFIED_MODULE)}</div>
                        <textarea class="form-control inputElement" id="mmAiPriorityRules" name="ai_priority_rules" rows="5"
                                  placeholder="{vtranslate('LBL_PRIORITY_RULES_PLACEHOLDER', $QUALIFIED_MODULE)|escape}">{$CONFIG.ai_priority_rules|escape}</textarea>
                    </div>
                </div>
            </div>
                <div class="container-fluid py-3 border-top">
                    <div class="row">
                        <div class="col text-end">
                            <a class="btn btn-primary cancelLink" href="index.php?module=MailManager&parent=Settings&view=Index">{vtranslate('LBL_BACK_TO_LIST', $QUALIFIED_MODULE)}</a>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-primary active saveButton its4you-save-ai">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                        </div>
                    </div>
                </div>
            </div>
            {* Global labels: every mailbox uses them; a user's own labels are in the detail of the personal mailbox *}
            <div class="tab-pane fade" id="mmAiTabLabels">
                <div class="container-fluid p-3 its4you-global-labels">
                    <div class="form-text mb-2">{vtranslate('LBL_GLOBAL_LABELS_HINT', $QUALIFIED_MODULE)}</div>
                    {include file=vtemplate_path('Pro/partials/LabelsTable.tpl', 'MailManager') EDITABLE=true}
                    <div class="form-text">{vtranslate('LBL_PRIORITY_HINT', $QUALIFIED_MODULE)}</div>
                </div>
            </div>
            {* The company knowledge the drafts answer from: the files; the FAQ and the solved tickets are switched on the features tab *}
            <div class="tab-pane fade" id="mmAiTabKnowledge">
                <div class="container-fluid p-3">
                    {include file=vtemplate_path('Pro/partials/KnowledgeFiles.tpl', 'MailManager') KNOWLEDGE_SCOPE='general'}
                </div>
            </div>
            {* the requests that keep coming — the ground for templates, FAQ and workflows *}
            <div class="tab-pane fade {if $RADAR_OPEN}active show{/if}" id="mmAiTabRadar">
                <div class="container-fluid p-3">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="form-text m-0">{vtranslate('LBL_RADAR_HINT', $QUALIFIED_MODULE)}</span>
                        <div class="btn-group btn-group-sm ms-auto">
                            {foreach item=PERIOD from=$RADAR_PERIODS}
                                <a class="btn btn-outline-secondary {if $PERIOD eq $RADAR_DAYS}active{/if}"
                                   href="index.php?module=MailManager&parent=Settings&view=AISettings&radar_days={$PERIOD}">{vtranslate('LBL_RADAR_DAYS', $QUALIFIED_MODULE)|replace:'%s':$PERIOD}</a>
                            {/foreach}
                        </div>
                    </div>
                    {if $RADAR}
                        <table class="table table-sm table-borderless border">
                            <thead class="border-bottom">
                                <tr>
                                    <th>{vtranslate('LBL_RADAR_REQUEST', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_RADAR_COUNT', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_RADAR_LAST', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_RADAR_EXAMPLES', $QUALIFIED_MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=RADAR_ROW from=$RADAR}
                                    <tr>
                                        <td class="fw-semibold">{$RADAR_ROW.request_type|escape}</td>
                                        <td class="text-end">{$RADAR_ROW.conversations}</td>
                                        <td class="text-nowrap">{$RADAR_ROW.last_at_display|escape}</td>
                                        <td>{foreach item=EXAMPLE from=$RADAR_ROW.examples}<div class="text-truncate small">{$EXAMPLE|escape}</div>{/foreach}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="text-secondary">{vtranslate('LBL_RADAR_EMPTY', $QUALIFIED_MODULE)}</div>
                    {/if}
                </div>
            </div>
            {* the backup provider and the outages of the providers (I17) *}
            <div class="tab-pane fade" id="mmAiTabStatus">
                <div class="container-fluid p-3 its4you-ai-status">
                    <h6>{vtranslate('LBL_AI_FAILOVER', $QUALIFIED_MODULE)}</h6>
                    <div class="form-text mb-2">{vtranslate('LBL_AI_FAILOVER_HINT', $QUALIFIED_MODULE)}</div>
                    <div class="row py-2">
                        <div class="col-sm-3 fieldLabel">{vtranslate('LBL_AI_FAILOVER_PROVIDER', $QUALIFIED_MODULE)}</div>
                        <div class="col-sm-5 fieldValue">
                            <select class="form-select inputElement" name="ai_failover_provider">
                                <option value="">{vtranslate('LBL_AI_FAILOVER_OFF', $QUALIFIED_MODULE)}</option>
                                {foreach key=FAILOVER_PROVIDER item=FAILOVER_INFO from=$AI_PROVIDERS}
                                    <option value="{$FAILOVER_PROVIDER}" {if $AI_FAILOVER.provider eq $FAILOVER_PROVIDER}selected{/if} {if !$FAILOVER_INFO.has_key}disabled{/if}>{$FAILOVER_INFO.label|escape}{if !$FAILOVER_INFO.has_key} ({vtranslate('LBL_KEY_MISSING', $QUALIFIED_MODULE)}){/if}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-sm-3 fieldLabel">{vtranslate('LBL_AI_FAILOVER_EMAIL', $QUALIFIED_MODULE)}</div>
                        <div class="col-sm-5 fieldValue">
                            <input type="text" class="form-control inputElement" name="ai_failover_email" value="{$AI_FAILOVER.email|escape}" placeholder="{vtranslate('LBL_AI_FAILOVER_EMAIL_ADMINS', $QUALIFIED_MODULE)|escape}">
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-sm-8 offset-sm-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="mmAiFailoverConfirm" name="ai_failover_confirmed" {if $AI_FAILOVER.confirmed}checked{/if}>
                                <label class="form-check-label" for="mmAiFailoverConfirm">{vtranslate('LBL_AI_FAILOVER_CONFIRM', $QUALIFIED_MODULE)}</label>
                            </div>
                            <button type="button" class="btn btn-primary active btn-sm mt-2 its4you-ai-failover-save">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                        </div>
                    </div>

                    <h6 class="mt-4">{vtranslate('LBL_AI_PROVIDER_STATUS', $QUALIFIED_MODULE)}</h6>
                    {if $AI_OUTAGE_STATUS}
                        <table class="table table-sm table-borderless border align-middle">
                            <thead class="border-bottom">
                                <tr>
                                    <th>{vtranslate('LBL_AI_PROVIDER', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_STATUS', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_OUTAGE_FAILURES', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_OUTAGE_ERROR', $QUALIFIED_MODULE)}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=STATUS from=$AI_OUTAGE_STATUS}
                                    <tr>
                                        <td class="fw-semibold">{$STATUS.label|escape}</td>
                                        <td>
                                            {if $STATUS.open}
                                                <span class="badge text-bg-danger">{vtranslate('LBL_AI_OUTAGE_DOWN', $QUALIFIED_MODULE)|replace:'%s':$STATUS.since}</span>
                                                {if $STATUS.backup_label}<div class="small text-secondary">{vtranslate('LBL_AI_OUTAGE_RUNS_ON', $QUALIFIED_MODULE)|replace:'%s':$STATUS.backup_label}</div>{/if}
                                            {else}
                                                <span class="badge text-bg-success">{vtranslate('LBL_AI_OUTAGE_OK', $QUALIFIED_MODULE)}</span>
                                            {/if}
                                            {if $STATUS.simulated}<span class="badge text-bg-warning ms-1">{vtranslate('LBL_AI_OUTAGE_SIMULATED', $QUALIFIED_MODULE)}</span>{/if}
                                        </td>
                                        <td class="text-end">{$STATUS.failures}</td>
                                        <td class="small">{if $STATUS.reason_label}{$STATUS.reason_label|escape}: {/if}{$STATUS.error|escape}</td>
                                        <td class="text-end text-nowrap">
                                            <button type="button" class="btn btn-outline-secondary btn-sm its4you-ai-outage-simulate" data-provider="{$STATUS.provider}" data-on="{if $STATUS.simulated}0{else}1{/if}"
                                                    {if !$STATUS.simulated && !$STATUS.can_simulate}disabled title="{vtranslate('LBL_AI_OUTAGE_SIMULATE_NEEDS_BACKUP', $QUALIFIED_MODULE)|escape}"{/if}>
                                                {if $STATUS.simulated}{vtranslate('LBL_AI_OUTAGE_SIMULATE_END', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_AI_OUTAGE_SIMULATE', $QUALIFIED_MODULE)}{/if}
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="text-secondary">{vtranslate('LBL_AI_PROVIDER_STATUS_EMPTY', $QUALIFIED_MODULE)}</div>
                    {/if}

                    <h6 class="mt-4">{vtranslate('LBL_AI_OUTAGE_HISTORY', $QUALIFIED_MODULE)}</h6>
                    {if $AI_OUTAGE_HISTORY}
                        <table class="table table-sm table-borderless border">
                            <thead class="border-bottom">
                                <tr>
                                    <th>{vtranslate('LBL_AI_PROVIDER', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_OUTAGE_START', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_OUTAGE_END', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_OUTAGE_DURATION', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_OUTAGE_ERROR', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_OUTAGE_BACKUP_CALLS', $QUALIFIED_MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=OUTAGE from=$AI_OUTAGE_HISTORY}
                                    <tr>
                                        <td>{$OUTAGE.provider_label|escape}{if $OUTAGE.backup_label} → {$OUTAGE.backup_label|escape}{/if}</td>
                                        <td class="text-nowrap">{$OUTAGE.started_display|escape}</td>
                                        <td class="text-nowrap">{if $OUTAGE.ended_display}{$OUTAGE.ended_display|escape}{else}<span class="badge text-bg-danger">{vtranslate('LBL_AI_OUTAGE_ONGOING', $QUALIFIED_MODULE)}</span>{/if}</td>
                                        <td class="text-nowrap">{$OUTAGE.duration_display|escape}</td>
                                        <td class="small">{$OUTAGE.reason_label|escape}{if $OUTAGE.last_error}: {$OUTAGE.last_error|escape}{/if}</td>
                                        <td class="text-end">{$OUTAGE.backup_calls}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="text-secondary">{vtranslate('LBL_AI_OUTAGE_HISTORY_EMPTY', $QUALIFIED_MODULE)}</div>
                    {/if}
                </div>
            </div>
            {* tokens per month, feature and model (df_mailmanager_ailog — counts only, never content) *}
            <div class="tab-pane fade" id="mmAiTabUsage">
                <div class="container-fluid p-3">
                    <div class="form-text mb-2">{vtranslate('LBL_AI_USAGE_HINT', $QUALIFIED_MODULE)}</div>
                    {if $AI_USAGE}
                        <table class="table table-sm table-borderless border">
                            <thead class="border-bottom">
                                <tr>
                                    <th>{vtranslate('LBL_AI_USAGE_MONTH', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_USAGE_FEATURE', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_AGENT_NAME', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_USAGE_MODEL', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_USAGE_CALLS', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_USAGE_BACKUP', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_USAGE_INPUT', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_USAGE_OUTPUT', $QUALIFIED_MODULE)}</th>
                                    <th class="text-end">{vtranslate('LBL_AI_USAGE_COST', $QUALIFIED_MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=USAGE from=$AI_USAGE}
                                    <tr>
                                        <td>{$USAGE.month|escape}</td>
                                        <td>{$USAGE.feature_label|escape}</td>
                                        <td>{$USAGE.agent|escape}</td>
                                        <td>{$USAGE.model|escape}</td>
                                        <td class="text-end">{$USAGE.calls}</td>
                                        <td class="text-end">{if $USAGE.backup_calls}{$USAGE.backup_calls}{/if}</td>
                                        <td class="text-end">{$USAGE.input_display}</td>
                                        <td class="text-end">{$USAGE.output_display}</td>
                                        <td class="text-end">{$USAGE.cost_display}</td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="text-secondary">{vtranslate('LBL_AI_USAGE_EMPTY', $QUALIFIED_MODULE)}</div>
                    {/if}
                    {* the admin's prices per 1M tokens — the cost column (any currency) *}
                    {if $AI_PRICE_MODELS}
                        <h6 class="mt-4">{vtranslate('LBL_AI_PRICES', $QUALIFIED_MODULE)}</h6>
                        <div class="form-text mb-2">{vtranslate('LBL_AI_PRICES_HINT', $QUALIFIED_MODULE)}</div>
                        <table class="table table-sm table-borderless border align-middle w-auto its4you-ai-prices">
                            <thead class="border-bottom">
                                <tr>
                                    <th>{vtranslate('LBL_AI_USAGE_MODEL', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_PRICE_IN', $QUALIFIED_MODULE)}</th>
                                    <th>{vtranslate('LBL_AI_PRICE_OUT', $QUALIFIED_MODULE)}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach item=PRICE_MODEL from=$AI_PRICE_MODELS}
                                    <tr data-model="{$PRICE_MODEL|escape}">
                                        <td>
                                            {$PRICE_MODEL|escape}
                                            {if isset($AI_PRICES[$PRICE_MODEL]) && $AI_PRICES[$PRICE_MODEL].source eq 'openrouter'}
                                                <div class="form-text m-0">{vtranslate('LBL_AI_PRICE_INDICATIVE', $QUALIFIED_MODULE)|replace:'%s':$AI_PRICES[$PRICE_MODEL].date}</div>
                                            {/if}
                                        </td>
                                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm inputElement its4you-ai-price-in" value="{if isset($AI_PRICES[$PRICE_MODEL])}{$AI_PRICES[$PRICE_MODEL].in}{/if}"></td>
                                        <td><input type="number" min="0" step="0.01" class="form-control form-control-sm inputElement its4you-ai-price-out" value="{if isset($AI_PRICES[$PRICE_MODEL])}{$AI_PRICES[$PRICE_MODEL].out}{/if}"></td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button" class="btn btn-primary active btn-sm its4you-ai-prices-save">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
                            {* indicative prices of the public OpenRouter catalog (USD) – the server reads it, nothing is sent *}
                            <button type="button" class="btn btn-outline-secondary btn-sm its4you-ai-prices-load">
                                <i class="fa-solid fa-cloud-arrow-down me-1"></i>{vtranslate('LBL_AI_PRICES_LOAD', $QUALIFIED_MODULE)}
                            </button>
                            <div class="form-check m-0">
                                <input type="checkbox" class="form-check-input" id="mmAiPricesOverwrite" name="ai_prices_overwrite">
                                <label class="form-check-label small" for="mmAiPricesOverwrite">{vtranslate('LBL_AI_PRICES_OVERWRITE', $QUALIFIED_MODULE)}</label>
                            </div>
                        </div>
                        <div class="form-text">{vtranslate('LBL_AI_PRICES_LOAD_HINT', $QUALIFIED_MODULE)}</div>
                    {/if}
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
{* Label modal: deliberately a sibling OUTSIDE the page container; handlers bind to the modal element *}
{include file=vtemplate_path('Pro/partials/LabelModal.tpl', 'MailManager')}
{/strip}
