{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="dashboardWidgetHeader p-2 text-secondary">
    <div class="title">
        <div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}">
            <b>{vtranslate($WIDGET->getTitle())}</b>
        </div>
    </div>
    <div class="userList">
        {assign var=CURRENT_USER_ID value=$CURRENT_USER->getId()}
        {if $ACCESSIBLE_USERS|@count gt 1}
            <select class="select2 widgetFilter col-lg-3 reloadOnChange" name="type" data-close-on-select="true">
                <option value="all"  selected>{vtranslate('All', $MODULE_NAME)}</option>
                {foreach key=USER_ID from=$ACCESSIBLE_USERS item=USER_NAME}
                    <option value="{$USER_ID}">
                    {if $USER_ID eq $CURRENT_USER_ID} 
                        {vtranslate('LBL_MINE',$MODULE_NAME)}
                    {else}
                        {$USER_NAME}
                    {/if}
                    </option>
                {/foreach}
            </select>
            {else}
                <center>{vtranslate('LBL_MY',$MODULE_NAME)} {vtranslate('History',$MODULE_NAME)}</center>
        {/if}
    </div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/HistoryContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<div class="dashBoardWidgetFooter widgeticons bg-body mt-auto">
    <div class="filterContainer boxSizingBorderBox bg-body border-top border-bottom container-fluid">
        <div class="row py-2">
            <div class="col-lg-4">
                <span>
                    <strong>{vtranslate('LBL_SHOW', $MODULE_NAME)}</strong>
                </span>
            </div>
            <div class="col-lg-7">
                {if $COMMENTS_MODULE_MODEL->isPermitted('DetailView')}
                    <label class="radio-group cursorPointer form-check">
                        <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer form-check-input" value="comments" />
                        <span class="ms-2">{vtranslate('LBL_COMMENTS', $MODULE_NAME)}</span>
                    </label>
                {/if}
                <label class="radio-group cursorPointer form-check">
                    <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer form-check-input" value="updates" />
                    <span class="ms-2">{vtranslate('LBL_UPDATES', $MODULE_NAME)}</span>
                </label>
                <label class="radio-group cursorPointer form-check">
                    <input type="radio" name="historyType" class="widgetFilter reloadOnChange cursorPointer form-check-input" value="all" checked="" />
                    <span class="ms-2">{vtranslate('LBL_BOTH', $MODULE_NAME)}</span>
                </label>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-lg-4">
                <span>
                    <strong>{vtranslate('LBL_SELECT_DATE_RANGE', $MODULE_NAME)}</strong>
                </span>
            </div>
            <span class="col-lg-8">
                <div class="input-daterange input-group dateRange widgetFilter" id="datepicker" name="modifiedtime">
                    <input type="text" class="inputElement form-control" name="start"/>
                    <span class="input-group-text">to</span>
                    <input type="text" class="inputElement form-control" name="end"/>
                </div>
            </span>
        </div>
    </div>
    <div class="footerIcons p-2">
        {include file="dashboards/DashboardFooterIcons.tpl"|@vtemplate_path:$MODULE_NAME SETTING_EXIST=true}
    </div>
</div>