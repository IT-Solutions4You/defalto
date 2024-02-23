{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<div class="listViewPageDiv" id="listViewContent">
    <div class="px-4 pb-4">
        <input type="hidden" name="pwd_regex" value= {ZEND_json::encode($PWD_REGEX)}/>
        <div id="listview-actions" class="listview-actions-container container-fluid p-0 bg-body rounded">
            <div class="row p-3">
                <div class="col-lg">
                    <h4 class="m-0">{vtranslate('LBL_USERS', $MODULE)}</h4>
                </div>
                <div class="col-lg text-center">
                    <div class="btn-group userFilter">
                        <button class="btn btn-default btn-primary" id="activeUsers" data-searchvalue="Active">
                            {vtranslate('LBL_ACTIVE_USERS', $MODULE)}
                        </button>
                        <button class="btn btn-default" id="inactiveUsers" data-searchvalue="Inactive">
                            {vtranslate('LBL_INACTIVE_USERS', $MODULE)}
                        </button>
                    </div>
                </div>
                <div class="col-lg text-end">
                    {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                    {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
                </div>
            </div>
            <div class="list-content">
{/strip}