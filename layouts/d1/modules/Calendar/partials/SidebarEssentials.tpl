{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}{if $smarty.get.view eq 'Calendar' OR $smarty.get.view eq 'SharedCalendar'}
<div class="sidebar-menu">
    <div class="module-filters" id="module-filters">
        <div class="sidebar-container lists-menu-container">
            {foreach item=SIDEBARWIDGET from=$QUICK_LINKS['SIDEBARWIDGET']}
            {if $SIDEBARWIDGET->get('linklabel') eq 'LBL_ACTIVITY_TYPES' || $SIDEBARWIDGET->get('linklabel') eq 'LBL_ADDED_CALENDARS'}
            <div class="calendar-sidebar-tabs sidebar-widget" id="{$SIDEBARWIDGET->get('linklabel')}-accordion" role="tablist" aria-multiselectable="true" data-widget-name="{$SIDEBARWIDGET->get('linklabel')}">
                <div class="calendar-sidebar-tab">
                    <div class="sidebar-widget-header" role="tab" data-url="{$SIDEBARWIDGET->getUrl()}">
                        <div class="sidebar-header clearfix">
                            {*<i class="fa fa-chevron-right widget-state-indicator"></i>*}
                            <h5 class="pull-left">{vtranslate($SIDEBARWIDGET->get('linklabel'),$MODULE)}</h5>
                            <button class="btn btn-default pull-right sidebar-btn add-calendar-feed">
                                <div class="fa fa-plus" aria-hidden="true"></div>
                            </button> 
                        </div>
                    </div>
                    <hr style="margin: 5px 0;">
                    <div class="list-menu-content">
                        <div id="{$SIDEBARWIDGET->get('linklabel')}" class="sidebar-widget-body activitytypes">
                            <div style="text-align:center;"><img src="layouts/d1/skins/images/loading.gif"></div>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            {/foreach}    
        </div>
    </div>
</div>
{else}
    {include file="partials/SidebarEssentials.tpl"|vtemplate_path:'Vtiger'}
{/if}