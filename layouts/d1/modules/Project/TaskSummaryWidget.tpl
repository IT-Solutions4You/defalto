{strip}
    <div class="summaryWidgetContainer project-task-summary bg-body rounded mb-3" data-record="{$RECORD->getId()}">
        <div class="widgetContainer_{$DETAIL_VIEW_WIDGET->getId()}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="LBL_TASKS" data-sequence="{$DETAIL_VIEW_WIDGET->get('sequence')}">
            <header class="widget_header project-task-summary-header border-bottom p-3">
                <input type="hidden" name="relatedModule" value="ProjectTask">
                <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
                    <h4 class="m-0">{vtranslate('LBL_TASKS', 'Project')|escape:'html'}</h4>
                </div>
                <div class="project-task-summary-actions">
                    {foreach item=ACTION key=ACTION_MODULE from=$DETAIL_VIEW_WIDGET->get('summary_actions')}
                        {if $ACTION['quick_url']}
                            <button type="button" class="btn btn-sm text-secondary fw-bold project-summary-add project-summary-create" data-module="{$ACTION_MODULE|escape:'html'}" data-url="{$ACTION['quick_url']|escape:'html'}"><i class="fa fa-plus" aria-hidden="true"></i><span>{$ACTION['label']|escape:'html'}</span></button>
                        {else}
                            <a class="btn btn-sm text-secondary fw-bold project-summary-add" href="{$ACTION['url']|escape:'html'}"><i class="fa fa-plus" aria-hidden="true"></i><span>{$ACTION['label']|escape:'html'}</span></a>
                        {/if}
                    {/foreach}
                </div>
            </header>
            <div class="widget_contents"></div>
        </div>
    </div>
{/strip}
