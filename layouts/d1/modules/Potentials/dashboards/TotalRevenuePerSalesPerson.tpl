{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<script type="text/javascript">
	Vtiger_Pie_Widget_Js('Vtiger_TotalRevenuePerSalesPerson_Widget_Js',{},{});
</script>
<div class="dashboardWidgetHeader text-secondary p-2">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
</div>
<div class="dashboardWidgetContent overflow-auto h-100">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="filterContainer border-top border-bottom bg-body container-fluid">
        <div class="row py-2">
            <div class="col-lg-4">
                <span>
                    <strong>{vtranslate('Created Time', $MODULE_NAME)} &nbsp; {vtranslate('LBL_BETWEEN', $MODULE_NAME)}</strong>
                </span>
            </div>
            <div class="col-lg-8">
                <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="createdtime">
                    <input type="text" class="inputElement form-control" name="start" />
                    <span class="input-group-text">to</span>
                    <input type="text" class="inputElement form-control" name="end" />
                </div>
            </div>
        </div>
    </div>
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>