<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{vtranslate('LBL_CHARTS', 'Project')|escape:'html'}</title>
    {foreach item=STYLE from=$STYLES}
        <link rel="stylesheet" href="{vresource_url($STYLE->getHref())}">
    {/foreach}
    {foreach item=SCRIPT from=$SCRIPTS}
        <script src="{vresource_url($SCRIPT->getSrc())}"></script>
    {/foreach}
</head>
<body>
    <header class="project-chart-print-header">
        <h1>{$PROJECT_TITLE|escape:'html'}</h1>
        <p>{vtranslate('LBL_CHART_PERIOD', 'Project')}: {if $PRINT_VIEW['from']}{DateTimeField::convertToUserFormat($PRINT_VIEW['from'])|escape:'html'} – {DateTimeField::convertToUserFormat($PRINT_VIEW['to'])|escape:'html'}{/if}</p>
        <p>{vtranslate('LBL_CREATED_ON')}: {$PRINT_GENERATED|escape:'html'}</p>
        <p>{vtranslate('LBL_SEARCH')}: {$PRINT_VIEW['filter_labels']['search']|escape:'html'} · {vtranslate('Assigned To')}: {$PRINT_VIEW['filter_labels']['owner']|escape:'html'} · {vtranslate('LBL_STATUS')}: {$PRINT_VIEW['filter_labels']['status']|escape:'html'}</p>
    </header>
    {include file='ShowChart.tpl'|vtemplate_path:'Project'}
</body>
</html>
