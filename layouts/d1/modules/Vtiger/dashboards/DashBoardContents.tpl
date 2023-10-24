{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Vtiger/views/DashBoard.php *}

{strip}
    <div class="dashBoardContainer clearfix">
        <div class="tabContainer">
            <ul class="nav nav-pills tabs sortable container-fluid py-2 bg-body">
                {foreach key=index item=TAB_DATA from=$DASHBOARD_TABS}
                    <li class="dashboardTab ms-2 {if $TAB_DATA["id"] eq $SELECTED_TAB}active{/if}" data-tabid="{$TAB_DATA["id"]}" data-tabname="{$TAB_DATA["tabname"]}">
                        <a class="nav-link {if $TAB_DATA["id"] eq $SELECTED_TAB}active{/if}" data-bs-toggle="tab" href="#tab_{$TAB_DATA["id"]}">
                            <div class="d-flex align-items-center">
                                <span class="name text-truncate" value="{$TAB_DATA["tabname"]}">
                                    <strong>{$TAB_DATA["tabname"]}</strong>
                                </span>
                                <span class="editTabName hide">
                                    <input class="form-control d-inline" type="text" name="tabName"/>
                                </span>
                                {if $TAB_DATA["isdefault"] eq 0}
                                    <i class="fa fa-close deleteTab ms-2"></i>
                                {/if}
                                <i class="fa fa-bars moveTab ms-2 hide"></i>
                            </div>
                        </a>
                    </li>
                {/foreach}
                <div class="moreSettings ms-auto">
                    <div class="dropdown dashBoardDropDown">
                        <button class="btn btn-outline-secondary reArrangeTabs dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <span>{vtranslate('LBL_MORE',$MODULE)}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right moreDashBoards">
                            <li id="newDashBoardLi" {if php7_count($DASHBOARD_TABS) eq $DASHBOARD_TABS_LIMIT}class="disabled"{/if}>
                                <a class="dropdown-item addNewDashBoard" href="#">{vtranslate('LBL_ADD_NEW_DASHBOARD',$MODULE)}</a>
                            </li>
                            <li>
                                <a class="dropdown-item reArrangeTabs" href="#">{vtranslate('LBL_REARRANGE_DASHBOARD_TABS',$MODULE)}</a>
                            </li>
                        </ul>
                    </div>
                    <button class="btn btn-primary updateSequence hide">{vtranslate('LBL_SAVE_ORDER',$MODULE)}</button>
                </div>
            </ul>
            <div class="tab-content">
                {foreach key=index item=TAB_DATA from=$DASHBOARD_TABS}
                    <div id="tab_{$TAB_DATA["id"]}" data-tabid="{$TAB_DATA["id"]}" data-tabname="{$TAB_DATA["tabname"]}" class="tab-pane {if $TAB_DATA["id"] eq $SELECTED_TAB}active show{/if}">
                        {if $TAB_DATA["id"] eq $SELECTED_TAB}
                            {include file="dashboards/DashBoardTabContents.tpl"|vtemplate_path:$MODULE TABID=$TABID}
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{/strip}