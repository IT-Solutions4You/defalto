{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<script type="text/javascript">
	Vtiger_Pie_Widget_Js('Vtiger_TotalRevenuePerSalesPerson_Widget_Js',{},{
        labelField: 'last_name',
        datasetNumberField: 'amount',
    });
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