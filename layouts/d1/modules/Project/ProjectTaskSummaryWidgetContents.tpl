{strip}
    <div class="project-task-summary-content" data-summary-page-size="{$TASK_SUMMARY['page_size']}">
        <input type="hidden" name="relatedModule" value="ProjectTask">
        {if $TASK_SUMMARY['count'] && $TASK_SUMMARY['columns']}
            <div class="d-flex align-items-end border-bottom project-summary-toolbar">
            <div class="nav nav-tabs px-3 pt-2 project-summary-status-tabs flex-grow-1" role="tablist" aria-label="{vtranslate('Status', 'ProjectTask')|escape:'html'}">
                <button type="button" id="tab_project_summary_all" class="nav-link d-flex align-items-center gap-2 active" role="tab" aria-selected="true" tabindex="0" aria-controls="project-summary-task-panel" data-summary-group="all"><span>{vtranslate('LBL_ALL', 'Core')|escape:'html'}</span><span class="badge bg-body-secondary text-body">{$TASK_SUMMARY['count']}</span></button>
                {foreach item=GROUP from=$TASK_SUMMARY['groups'] name=statusTabs}
                    <button type="button" id="tab_{$GROUP['id']}" class="nav-link d-flex align-items-center gap-2" role="tab" aria-selected="false" tabindex="-1" aria-controls="project-summary-task-panel" data-summary-group="{$GROUP['id']}" data-summary-status="{$GROUP['status']|escape:'html'}">
                        <span class="project-summary-status-marker" style="background-color: {$GROUP['color']|escape:'html'}" aria-hidden="true"></span>
                        <span>{$GROUP['label']|escape:'html'}</span><span class="badge bg-body-secondary text-body">{count($GROUP['rows'])}</span>
                    </button>
                {/foreach}
            </div>
            {if $TASK_SUMMARY['filters']}
                <div class="dropdown project-summary-filter-dropdown p-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary project-summary-filter-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><i class="fa fa-filter me-2" aria-hidden="true"></i>{vtranslate('LBL_SUMMARY_FILTER', 'Project')|escape:'html'}<span class="badge text-bg-secondary ms-2 project-summary-filter-count" hidden>0</span></button>
                    <div class="dropdown-menu dropdown-menu-end project-summary-filter-menu p-2">
                        <div data-filter-panel="main">
                            {foreach key=KEY item=FILTER from=$TASK_SUMMARY['filters']}<button type="button" class="dropdown-item d-flex justify-content-between align-items-center py-2" data-filter-open="{$KEY}" aria-expanded="false"><span>{$FILTER['label']|escape:'html'}</span><i class="fa fa-chevron-right" aria-hidden="true"></i></button>{/foreach}
                        </div>
                        {foreach key=KEY item=FILTER from=$TASK_SUMMARY['filters']}
                            <div data-filter-panel="{$KEY}" data-filter-label="{$FILTER['label']|escape:'html'}" hidden>
                                <button type="button" class="btn btn-sm text-secondary mb-2" data-filter-open="main"><i class="fa fa-chevron-left me-2" aria-hidden="true"></i><span class="visually-hidden">{vtranslate('LBL_BACK', 'Core')|escape:'html'}: </span>{$FILTER['label']|escape:'html'}</button>
                                {if $KEY eq 'dates'}
                                    <label class="form-label d-block px-2">{vtranslate('LBL_SUMMARY_DATE_FROM', 'Project')|escape:'html'}<span class="input-group inputElement"><input type="text" class="dateField form-control form-control-sm" data-fieldtype="date" data-rule-date="true" autocomplete="off" data-filter-date="from"><span class="input-group-addon input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span></span></label>
                                    <label class="form-label d-block px-2">{vtranslate('LBL_SUMMARY_DATE_TO', 'Project')|escape:'html'}<span class="input-group inputElement"><input type="text" class="dateField form-control form-control-sm" data-fieldtype="date" data-rule-date="true" autocomplete="off" data-filter-date="to"><span class="input-group-addon input-group-text"><i class="fa fa-calendar" aria-hidden="true"></i></span></span></label>
                                {else}
                                    <div class="project-summary-filter-options">
                                        {foreach key=VALUE item=LABEL from=$FILTER['options']}<label class="dropdown-item d-flex align-items-center gap-2 py-2"><input type="checkbox" class="form-check-input m-0" data-filter-key="{$KEY}" value="{$VALUE|escape:'html'}"><span>{$LABEL|escape:'html'}</span></label>{/foreach}
                                    </div>
                                {/if}
                                <div class="text-end border-top mt-2 pt-2"><button type="button" class="btn btn-sm btn-primary" data-filter-done>{vtranslate('LBL_DONE', 'Core')|escape:'html'}</button></div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            {/if}
            </div>
            <div class="project-summary-filter-chips d-flex flex-wrap align-items-center gap-2 px-3 py-2 border-bottom" hidden><div class="project-summary-filter-values d-flex flex-wrap gap-2"></div><button type="button" class="btn btn-link btn-sm p-0" data-filter-clear>{vtranslate('LBL_SUMMARY_CLEAR_FILTERS', 'Project')|escape:'html'}</button></div>
            <div class="table-responsive" id="project-summary-task-panel" role="tabpanel">
                <table class="table table-sm table-hover align-middle mb-0 project-summary-table">
                    <colgroup>
                        {foreach key=NAME item=HEADER from=$TASK_SUMMARY['columns']}<col class="project-summary-column-{$NAME}" {if $NAME eq 'projecttaskstatus'}data-summary-all-only{/if}>{/foreach}
                        <col class="project-summary-column-actions">
                    </colgroup>
                    <thead><tr>
                        {foreach item=HEADER key=NAME from=$TASK_SUMMARY['columns']}
                            <th scope="col" class="project-summary-column-{$NAME}" {if $NAME eq 'projecttaskstatus'}data-summary-all-only{/if} {if $NAME eq 'projecttaskname'}aria-label="{vtranslate('SINGLE_ProjectTask', 'ProjectTask')|escape:'html'}"{/if}>
                                {if $NAME eq 'dates'}{vtranslate('LBL_SUMMARY_DATE_RANGE', 'Project')|escape:'html'}
                                {elseif $NAME eq 'projecttaskname'}
                                {elseif $NAME eq 'assigned_user_id'}{vtranslate('LBL_SUMMARY_ASSIGNEE', 'Project')|escape:'html'}
                                {elseif $NAME eq 'projecttaskprogress'}{vtranslate('Progress', 'Project')|escape:'html'}
                                {else}{vtranslate($HEADER->get('label'), 'ProjectTask')|escape:'html'}{/if}
                            </th>
                        {/foreach}
                        <th scope="col" class="project-summary-column-actions" aria-label="{vtranslate('Status', 'ProjectTask')|escape:'html'}"></th>
                    </tr></thead>
                    {foreach item=GROUP from=$TASK_SUMMARY['groups']}
                        <tbody id="{$GROUP['id']}" class="project-summary-group-rows {if $GROUP['closed']}project-summary-closed{/if}">
                            {foreach item=ROW from=$GROUP['rows']}
                                <tr data-summary-filter-row data-filter-status="{$GROUP['id']}" data-filter-owner="{$ROW['owner_key']|escape:'html'}" data-filter-milestone="{$ROW['milestone_key']|escape:'html'}" data-filter-priority="{$ROW['priority_key']|escape:'html'}" data-filter-start="{$ROW['start_date']|escape:'html'}" data-filter-end="{$ROW['end_date']|escape:'html'}">
                                    {foreach item=HEADER key=NAME from=$TASK_SUMMARY['columns']}
                                        <td class="project-summary-column-{$NAME}" {if $NAME eq 'projecttaskstatus'}data-summary-all-only{/if}>
                                            {if $NAME eq 'projecttaskname'}
                                                <div class="project-summary-task-label">
                                                    <span class="project-summary-priority text-{if $ROW['priority_type'] eq 'high'}danger{else}secondary{/if}" {if $ROW['priority']}title="{$ROW['priority']|escape:'html'}" role="img" aria-label="{$ROW['priority']|escape:'html'}"{else}aria-hidden="true"{/if}>
                                                        {if $ROW['priority'] && $ROW['priority_type'] neq 'normal'}<svg width="14" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 22V3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 3C9 0 14 6 20 3V15C14 18 9 12 4 15Z" fill="{if $ROW['priority_type'] eq 'high'}currentColor{else}none{/if}" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>{/if}
                                                    </span>
                                                    <a class="project-summary-task-name" data-record="{$ROW['id']}" data-module="ProjectTask" href="{$ROW['url']|escape:'html'}">{$ROW['name']|escape:'html'}</a>
                                                </div>
                                            {elseif $NAME eq 'projecttaskstatus'}
                                                <span class="d-inline-flex align-items-center gap-2"><span class="project-summary-status-marker" style="background-color: {$GROUP['color']|escape:'html'}" aria-hidden="true"></span><span>{$GROUP['label']|escape:'html'}</span></span>
                                            {elseif $NAME eq 'projecttaskprogress'}
                                                {if $ROW['progress_set']}<div class="project-summary-task-progress" data-bs-toggle="tooltip" tabindex="0" title="{$ROW['progress']}%"><progress max="100" value="{$ROW['progress']}" aria-label="{vtranslate('Progress', 'ProjectTask')|escape:'html'}">{$ROW['progress']}%</progress></div>{else}<span class="text-secondary">—</span>{/if}
                                            {elseif $NAME eq 'milestoneid'}
                                                {if $ROW['milestone']}<a class="project-summary-milestone" data-record="{$ROW['milestone_key']|escape:'html'}" data-module="ProjectMilestone" href="{$ROW['milestone']['url']|escape:'html'}"><span aria-hidden="true">◆</span><span>{$ROW['milestone']['name']|escape:'html'}</span></a>{else}<span class="text-secondary">{vtranslate('LBL_SUMMARY_NO_MILESTONE', 'Project')|escape:'html'}</span>{/if}
                                            {elseif $NAME eq 'assigned_user_id'}
                                                {if $ROW['owner']}<span class="project-summary-avatar" data-owner-label="{$ROW['owner']|escape:'html'}" role="img" aria-label="{$ROW['owner']|escape:'html'}" title="{$ROW['owner']|escape:'html'}"></span>{else}<span class="text-secondary">{vtranslate('LBL_SUMMARY_UNASSIGNED', 'Project')|escape:'html'}</span>{/if}
                                            {elseif $NAME eq 'dates'}
                                                <div class="{if $ROW['overdue_days']}text-danger{/if}">
                                                    {if $ROW['dates']}<span class="text-nowrap" title="{$ROW['dates_title']|escape:'html'}">{$ROW['dates']|escape:'html'}</span>{else}<span class="text-secondary">{vtranslate('LBL_SUMMARY_NO_DATES', 'Project')|escape:'html'}</span>{/if}
                                                    {if $ROW['overdue_days']}<small class="d-block">{sprintf(vtranslate('LBL_SUMMARY_DAYS_OVERDUE', 'Project'), $ROW['overdue_days'])|escape:'html'}</small>{/if}
                                                </div>
                                            {/if}
                                        </td>
                                    {/foreach}
                                    <td class="project-summary-column-actions text-end">
                                                    {if $ROW['can_edit_status']}
                                                        {assign var=FIELD_MODEL value=$ROW['status_field']}
                                                        <div class="dropdown project-summary-status-edit">
                                                            <button type="button" class="btn btn-sm text-secondary" data-bs-toggle="dropdown" aria-label="{vtranslate('Status', 'ProjectTask')|escape:'html'}"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></button>
                                                            <ul class="dropdown-menu dropdown-menu-end widgetsList" data-recordid="{$ROW['id']}" data-fieldname="projecttaskstatus" data-old-value="{$ROW['status']|escape:'html'}" data-mandatory="{$FIELD_MODEL->isMandatory()}">
                                                                {if !$FIELD_MODEL->isMandatory()}<li class="editTaskDetails emptyOption" data-value=""><button type="button" class="dropdown-item">{vtranslate('LBL_SELECT_OPTION', 'Project')|escape:'html'}</button></li>{/if}
                                                                {assign var=DENIED_VALUES value=$FIELD_MODEL->get('record_action_denied_values')}
                                                                {foreach item=LABEL key=VALUE from=$FIELD_MODEL->getEditablePicklistValues()}
                                                                    {if !isset($DENIED_VALUES[$VALUE])}<li class="editTaskDetails" data-value="{$VALUE|escape:'html'}"><button type="button" class="dropdown-item">{$LABEL|escape:'html'}</button></li>{/if}
                                                                {/foreach}
                                                            </ul>
                                                        </div>
                                                    {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    {/foreach}
                    <tbody class="project-summary-filter-empty" hidden><tr><td colspan="{count($TASK_SUMMARY['columns']) + 1}" class="text-center text-secondary py-4" role="status">{vtranslate('LBL_NO_RECORDS_FOUND', 'Core')|escape:'html'}</td></tr></tbody>
                </table>
            </div>
            <div class="p-3 text-center border-top" data-summary-load-more-container hidden>
                <button type="button" class="btn btn-sm btn-outline-primary" data-summary-load-more>{vtranslate('LBL_SHOW_MORE', 'Core')|escape:'html'}</button>
            </div>
        {else}
            <div class="p-4 text-secondary">{vtranslate('LBL_NO_RELATED', 'Project')|escape:'html'}</div>
        {/if}
    </div>
{/strip}
