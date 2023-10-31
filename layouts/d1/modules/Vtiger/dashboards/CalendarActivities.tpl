{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="dashboardWidgetHeader p-2 text-secondary">
    {if $SHARED_USERS|@count gt 0 || $SHARED_GROUPS|@count gt 0}
        {assign var="usersList" value="1"}
    {/if}
    <div class="title">
        <div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}">
            <b>{if !$usersList}{vtranslate('LBL_MY',$MODULE_NAME)}&nbsp;{/if}{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b>
        </div>
    </div>
    {if $usersList}
        <div class="userList">
            <select class="select2 widgetFilter" name="type" data-close-on-select="true">
                <option value="{$CURRENTUSER->getId()}" selected>{vtranslate('LBL_MINE',$MODULE_NAME)}</option>
                {foreach key=USER_ID from=$SHARED_USERS item=USER_NAME}
                    <option value="{$USER_ID}">{$USER_NAME}</option>
                {/foreach}
                {foreach key=GROUP_ID from=$SHARED_GROUPS item=GROUP_NAME}
                    <option value="{$GROUP_ID}">{$GROUP_NAME}</option>
                {/foreach}
                <option value="all">{vtranslate('All', $MODULE_NAME)}</option>
            </select>
        </div>
    {/if}
</div>
<div name="history" class="dashboardWidgetContent overflow-auto">
    {include file="dashboards/CalendarActivitiesContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>
<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME}
    </div>
</div>