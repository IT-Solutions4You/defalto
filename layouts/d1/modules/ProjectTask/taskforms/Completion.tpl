{strip}
<details class="border-bottom mb-4 pb-3">
    <summary class="fw-semibold">{vtranslate('LBL_PROJECT_COMPLETION_TITLE', 'Settings:Workflows')|escape:'html'}</summary>
    <div class="mt-3">{Core_Utils_Helper::markdownToHtml(vtranslate('LBL_PROJECT_COMPLETION_HELP', 'Settings:Workflows'))}</div>
</details>
<div class="row mb-3">
    <div class="col-sm-5">
        <label class="col-form-label" for="completion_task_statuses">{vtranslate('LBL_COMPLETION_TASK_STATUSES', 'Settings:Workflows')|escape:'html'}</label>
    </div>
    <div class="col-sm-7">
        <select class="select2 form-select" id="completion_task_statuses" name="completedStatuses[]" multiple required>
            {foreach from=$TASK_OBJECT->getTaskStatusOptions() item=STATUS}
                <option value="{$STATUS|escape:'html'}" {if in_array($STATUS, (array)$TASK_OBJECT->completedStatuses)}selected{/if}>{vtranslate($STATUS, 'ProjectTask')|escape:'html'}</option>
            {/foreach}
        </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-sm-5">
        <label class="col-form-label" for="completion_project_status">{vtranslate('LBL_COMPLETION_PROJECT_STATUS', 'Settings:Workflows')|escape:'html'}</label>
    </div>
    <div class="col-sm-7">
        <select class="select2 form-select" id="completion_project_status" name="projectStatus" required>
            {foreach from=$TASK_OBJECT->getProjectStatusOptions() item=STATUS}
                <option value="{$STATUS|escape:'html'}" {if $STATUS eq $TASK_OBJECT->projectStatus}selected{/if}>{vtranslate($STATUS, 'Project')|escape:'html'}</option>
            {/foreach}
        </select>
    </div>
</div>
{/strip}
