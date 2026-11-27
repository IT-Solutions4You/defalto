{strip}
<div class="container-fluid">
    <div class="project-chart bg-body rounded p-3"
         data-record="{$PARENT_ID}" data-print="{if $CHART_PRINT}true{else}false{/if}"
         data-locale="{$CHART_LOCALE|escape:'html'}" data-date-format="{$USER_DATE_FORMAT|escape:'html'}"
         data-range-warning="{vtranslate('LBL_OUTSIDE_PROJECT_RANGE', 'Project')|escape:'html'}"
         data-dependency-warning="{vtranslate('LBL_DEPENDENCY_VIOLATION', 'Project')|escape:'html'}"
         data-milestone-warning="{vtranslate('LBL_TASK_AFTER_MILESTONE', 'Project')|escape:'html'}"
         data-dependency-help="{vtranslate('LBL_DEPENDENCY_HELP', 'Project')|escape:'html'}"
         data-overdue-label="{vtranslate('LBL_CHART_OVERDUE', 'Project')|escape:'html'}"
         data-today-label="{vtranslate('LBL_TODAY', 'Core')|escape:'html'}"
         data-print-from="{if $CHART_PRINT}{$PRINT_VIEW['from']|escape:'html'}{/if}"
         data-print-to="{if $CHART_PRINT}{$PRINT_VIEW['to']|escape:'html'}{/if}"
         data-print-scale="{if $CHART_PRINT}{$PRINT_VIEW['scale']}{/if}"
         data-filter-search="{if $CHART_PRINT}{$CHART_FILTERS['search']|escape:'html'}{/if}"
         data-filter-owner="{if $CHART_PRINT}{$CHART_FILTERS['owner']|escape:'html'}{/if}"
         data-filter-status="{if $CHART_PRINT}{$CHART_FILTERS['status']|escape:'html'}{/if}"
         data-task-label="{vtranslate('SINGLE_ProjectTask', 'ProjectTask')|escape:'html'}"
         data-owner-label="{vtranslate('Assigned To')|escape:'html'}"
         data-start-label="{vtranslate('LBL_CHART_START', 'Project')|escape:'html'}"
         data-end-label="{vtranslate('LBL_CHART_END', 'Project')|escape:'html'}"
         data-warning-label="{vtranslate('LBL_WARNING', 'Core')|escape:'html'}"
         data-close-label="{vtranslate('LBL_CLOSE', 'Core')|escape:'html'}"
         data-range-remedy="{vtranslate('LBL_CHART_RANGE_REMEDY', 'Project')|escape:'html'}"
         data-dependency-remedy="{vtranslate('LBL_CHART_DEPENDENCY_REMEDY', 'Project')|escape:'html'}"
         data-milestone-remedy="{vtranslate('LBL_CHART_MILESTONE_REMEDY', 'Project')|escape:'html'}"
         data-overdue-remedy="{vtranslate('LBL_CHART_OVERDUE_REMEDY', 'Project')|escape:'html'}"
         data-days-label="{vtranslate('LBL_CHART_DAY_COUNT', 'Project')|escape:'html'}">
        <input type="hidden" class="project-chart-data" value="{Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($PROJECT_CHART))}">
        <div class="project-chart-warnings mb-2">
            {if !$PROJECT_CHART['dependencies_ready'] && !$CHART_PRINT}<p class="text-secondary">{vtranslate('LBL_DEPENDENCY_SETUP', 'Project')}</p>{/if}
            {if !$PROJECT_CHART['preferences_ready'] && !$CHART_PRINT}<p class="text-secondary">{vtranslate('LBL_CHART_SETTINGS_SETUP', 'Project')}</p>{/if}
        </div>
        {if !empty($PROJECT_CHART['data']) || !empty($PROJECT_CHART['unscheduled'])}
            <div class="project-chart-toolbar d-flex flex-wrap align-items-end gap-3 mb-3">
                {if !empty($PROJECT_CHART['has_records']) && !$CHART_PRINT}
                    <div class="project-chart-filters d-flex flex-wrap gap-3 me-auto">
                        <div class="input-group w-auto">
                            <span class="input-group-text"><i class="fa fa-search" aria-hidden="true"></i></span>
                            <input type="search" class="project-chart-filter-search form-control" placeholder="{vtranslate('LBL_SEARCH')|escape:'html'}" aria-label="{vtranslate('LBL_SEARCH')|escape:'html'}" />
                        </div>
                        {include file='AssigneeSelect.tpl'|vtemplate_path:'Core' ASSIGNEE_OPTIONS=$PROJECT_CHART['assignees'] ASSIGNEE_CLASS='project-chart-filter-owner'}
                        <div class="input-group w-auto">
                            <span class="input-group-text"><i class="fa fa-filter" aria-hidden="true"></i></span>
                            <select class="project-chart-filter-status form-select" aria-label="{vtranslate('LBL_STATUS')|escape:'html'}"><option value="">{vtranslate('LBL_STATUS')}: {vtranslate('LBL_ALL')}</option></select>
                        </div>
                    </div>
                {/if}
                <div class="d-flex gap-2 ms-auto">
                    {if !$CHART_PRINT}
                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{vtranslate('LBL_HELP', 'Core')|escape:'html'}" title="{vtranslate('LBL_HELP', 'Core')|escape:'html'}"><i class="fa fa-circle-info" aria-hidden="true"></i></button>
                            <div class="dropdown-menu dropdown-menu-end p-3 text-body project-chart-help">
                                <h6 class="fw-bold mb-2">{vtranslate('SINGLE_ProjectMilestone', 'ProjectMilestone')}</h6>
                                <p class="small mb-3">{vtranslate('LBL_MILESTONE_RANGE_HELP', 'Project')}</p>
                                <hr class="my-3" />
                                <h6 class="fw-bold mb-2">{vtranslate('LBL_HELP', 'Core')}</h6>
                                <p class="small mb-0">{vtranslate('LBL_DEPENDENCY_HELP', 'Project')}</p>
                            </div>
                        </div>
                    {/if}
                    {if !empty($PROJECT_CHART['data'])}
                        <button type="button" class="btn btn-outline-secondary project-chart-zoom-out" aria-label="−">−</button>
                        <button type="button" class="btn btn-outline-secondary project-chart-zoom-in" aria-label="+">+</button>
                    {/if}
                    {if $CHART_PRINT}
                        <button type="button" class="project-chart-print btn btn-outline-secondary">{vtranslate('LBL_PRINT')}</button>
                    {else}
                        {if !empty($PROJECT_CHART['data'])}
                            <button type="button" class="project-chart-grid-toggle btn btn-outline-secondary" aria-expanded="true" aria-label="{vtranslate('LBL_CHART_TABLE', 'Project')|escape:'html'}" title="{vtranslate('LBL_CHART_TABLE', 'Project')|escape:'html'}"><i class="fa fa-list" aria-hidden="true"></i></button>
                        {/if}
                        <a href="index.php?module=Project&amp;view=ExportChart&amp;record={$PARENT_ID}" target="_blank" rel="noopener" class="project-chart-print-link btn btn-outline-secondary">{vtranslate('LBL_PRINT')}</a>
                    {/if}
                </div>
            </div>
        {/if}
        {if !empty($PROJECT_CHART['data'])}
            <div class="project-chart-canvas"></div>
        {/if}
        {if empty($PROJECT_CHART['data']) || empty($PROJECT_CHART['has_records'])}
            <div class="text-center py-5">
                <p class="text-secondary">{vtranslate('LBL_EMPTY_PROJECT_CHART', 'Project')}</p>
                {if !$CHART_PRINT && empty($PROJECT_CHART['unscheduled']) && !empty($PROJECT_CHART['create_task_url'])}
                    <a class="btn btn-primary" href="{$PROJECT_CHART['create_task_url']|escape:'html'}">{vtranslate('LBL_ADD_RECORD', 'ProjectTask')}</a>
                {/if}
            </div>
        {/if}
        {if !empty($PROJECT_CHART['unscheduled'])}
            <section class="project-chart-unscheduled border rounded p-3 mt-3">
                <h5 class="mb-3">{vtranslate('LBL_UNSCHEDULED_TASKS', 'Project')} <span class="project-chart-unscheduled-count badge bg-secondary">{count($PROJECT_CHART['unscheduled'])}</span></h5>
                <div class="list-group list-group-flush">
                    {foreach item=TASK from=$PROJECT_CHART['unscheduled']}
                        <div class="project-chart-unscheduled-row list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2 px-0" data-task-id="{$TASK['id']|escape:'html'}">
                            <div>
                                {if $CHART_PRINT}
                                    <span>{$TASK['text']|escape:'html'}</span>
                                {else}
                                    <button type="button" class="project-chart-unscheduled-task btn btn-link p-0 text-start" data-task-id="{$TASK['id']|escape:'html'}">{$TASK['text']|escape:'html'}</button>
                                {/if}
                                {if !empty($TASK['owner'])}<div class="small text-secondary">{$TASK['owner']|escape:'html'}</div>{/if}
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                {if $TASK['missing_start']}<span class="badge text-bg-warning">{vtranslate('LBL_MISSING_START_DATE', 'Project')}</span>{/if}
                                {if $TASK['missing_end']}<span class="badge text-bg-warning">{vtranslate('LBL_MISSING_END_DATE', 'Project')}</span>{/if}
                                {if !$TASK['missing_start'] && !$TASK['missing_end']}<span class="badge text-bg-warning">{vtranslate('LBL_INVALID_DATE_RANGE', 'Project')}</span>{/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
            </section>
        {/if}
    </div>
</div>
{/strip}
