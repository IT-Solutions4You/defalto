{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<script type="text/javascript">
	Vtiger_Funnel_Widget_Js('Vtiger_GroupedBySalesStage_Widget_Js',{},{
        labelField: 'last_name',
        datasetLabelField: 'sales_stage',
        datasetNumberField: 'count',
    });
</script>
{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<div class="dashboardWidgetHeader text-secondary p-2">
    {include file="dashboards/WidgetHeader.tpl"|vtemplate_path:$MODULE_NAME}
</div>
<div class="dashboardWidgetContent overflow-auto h-100">
	{include file="dashboards/DashBoardWidgetContents.tpl"|vtemplate_path:$MODULE_NAME}
</div>
<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="filterContainer border-top border-bottom bg-body container-fluid">
		<div class="row py-2 align-items-center">
			<div class="col-lg-4">
				<span class="fw-bold">{vtranslate('Expected Close Date', $MODULE_NAME)} {vtranslate('LBL_BETWEEN', $MODULE_NAME)}</span>
			</div>
			<div class="col-lg-8">
                <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="expectedclosedate">
                    <input type="text" class="inputElement form-control" name="start"/>
                    <span class="input-group-text">to</span>
                    <input type="text" class="inputElement form-control" name="end"/>
                </div>
			</div>
		</div>
        <div class="row py-2 align-items-center">
            <div class="col-lg-4">
                <span class="fw-bold">{vtranslate('Assigned To', $MODULE_NAME)}</span>
            </div>
            <div class="col-lg-8 userList">
                {assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
                {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENTUSER->getAccessibleGroups()}
                <select class="widgetFilter select2 form-select" id="owner" name="owner" data-close-on-select="true">
                    <option value="{$CURRENTUSER->getId()}" selected="selected">{vtranslate('LBL_MINE')}</option>
                    <option value="all">{vtranslate('LBL_ALL')}</option>
                    {if !empty($ALL_ACTIVEUSER_LIST) gt 1}
                        <optgroup label="{vtranslate('LBL_USERS')}">
                            {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                {if $OWNER_ID neq {$CURRENTUSER->getId()}}
                                    <option value="{$OWNER_ID}">{$OWNER_NAME}</option>
                                {/if}
                            {/foreach}
                        </optgroup>
                    {/if}
                    {if !empty($ALL_ACTIVEGROUP_LIST)}
                        <optgroup label="{vtranslate('LBL_GROUPS')}">
                            {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
                                <option value="{$OWNER_ID}">{$OWNER_NAME}</option>
                            {/foreach}
                        </optgroup>
                    {/if}
                </select>
            </div>
        </div>
	</div>
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>
{/strip}