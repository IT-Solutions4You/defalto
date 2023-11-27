{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="dashboardWidgetHeader text-secondary p-2">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

<div class="dashboardWidgetContent overflow-auto h-100">
	{include file="dashboards/MiniListContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
</div>