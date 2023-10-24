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
	Vtiger_Barchat_Widget_Js('Vtiger_LeadsByIndustry_Widget_Js',{},{});
</script>

<div class="dashboardWidgetHeader text-secondary p-2">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
</div>
<div class="dashboardWidgetContent overflow-auto h-100">
	{include file="dashboards/DashBoardWidgetContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>

<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="filterContainer border-top border-bottom bg-body">
        <div class="row">
            <div class="col-lg-4">
                <span>
                    <strong>{vtranslate('Created Time', $MODULE_NAME)} &nbsp; {vtranslate('LBL_BETWEEN', $MODULE_NAME)}</strong>
                </span>
            </div>
            <div class="col-lg-8">
                <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="createdtime">
                    <input type="text" class="inputElement form-control" name="start"/>
                    <span class="input-group-text">to</span>
                    <input type="text" class="inputElement form-control" name="end"/>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-lg-4">
                <span>
                    <strong>{vtranslate('Assigned To', $MODULE_NAME)}</strong>
                </span>
            </div>
            <div class="col-lg-8">
                {assign var=CURRENT_USER_ID value=$CURRENTUSER->getId()}
                <select class="select2 col-sm-12 widgetFilter reloadOnChange" name="smownerid" data-close-on-select="true">
                    <option value="{$CURRENT_USER_ID}">{vtranslate('LBL_MINE')}</option>
                    <option value="">{vtranslate('LBL_ALL', $MODULE_NAME)}</option>
                    {assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
                    {if php7_count($ALL_ACTIVEUSER_LIST) gt 1}
                        <optgroup label="{vtranslate('LBL_USERS')}">
                            {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                {if $OWNER_ID neq $CURRENT_USER_ID}
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
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>