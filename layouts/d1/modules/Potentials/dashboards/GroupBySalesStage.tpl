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
	Vtiger_Funnel_Widget_Js('Vtiger_GroupedBySalesStage_Widget_Js',{},{});
</script>
{foreach key=index item=cssModel from=$STYLES}
	<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
{/foreach}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<div class="dashboardWidgetHeader text-secondary p-2">
    <div class="title">
        <div class="col-lg-4 dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
        <div class="userList col-lg-5">
            <div>
                <select class="widgetFilter select2 form-select" id="owner" name="owner" data-close-on-select="true" style="width:30%;">
                    <option value="{$CURRENTUSER->getId()}" >{vtranslate('LBL_MINE')}</option>
                    <option value="all">{vtranslate('LBL_ALL')}</option>
                    {assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
                    {if php7_count($ALL_ACTIVEUSER_LIST) gt 1}
                        <optgroup label="{vtranslate('LBL_USERS')}">
                            {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                {if $OWNER_ID neq {$CURRENTUSER->getId()}}
                                    <option value="{$OWNER_ID}">{$OWNER_NAME}</option>
                                {/if}
                            {/foreach}
                        </optgroup>
                    {/if}
                    {assign var=ALL_ACTIVEGROUP_LIST value=$CURRENTUSER->getAccessibleGroups()}
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
</div>
<div class="dashboardWidgetContent overflow-auto h-100">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="filterContainer border-top border-bottom bg-body container-fluid">
		<div class="row py-2">
			<div class="col-lg-4">
				<span class="me-2">{vtranslate('Expected Close Date', $MODULE_NAME)}</span>
                <span>{vtranslate('LBL_BETWEEN', $MODULE_NAME)}</span>
			</div>
			<div class="col-lg-8">
                <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="expectedclosedate">
                    <input type="text" class="inputElement form-control" name="start"/>
                    <span class="input-group-text">to</span>
                    <input type="text" class="inputElement form-control" name="end"/>
                </div>
			</div>
		</div>
	</div>
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>