<div class="container-fluid p-3">
    <div class="bg-body rounded p-3">
        <h4>{vtranslate('LBL_CLOSED_TASK_STATUSES', 'Project')}</h4>
        <p>{vtranslate('LBL_CLOSED_TASK_STATUSES_HELP', 'Project')}</p>
        {if !$SETTINGS_READY}<p>{vtranslate('LBL_CHART_SETTINGS_SETUP', 'Project')}</p>{else}
        <form class="project-task-chart-settings" method="post" action="index.php?module=ProjectTask&amp;action=ChartSettings">
            <input type="hidden" name="module" value="ProjectTask">
            {foreach key=STATUS_ID item=OPTION from=$STATUS_OPTIONS}
                <label class="d-block py-1"><input type="checkbox" name="closed_statuses[]" value="{$STATUS_ID}" {if $OPTION['closed']}checked{/if}> {vtranslate($OPTION['value'], 'ProjectTask')|escape:'html'}</label>
            {/foreach}
            <button type="submit" class="btn btn-primary mt-3">{vtranslate('LBL_SAVE')}</button>
        </form>
        {/if}
    </div>
</div>
