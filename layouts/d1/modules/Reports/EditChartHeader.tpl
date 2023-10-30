{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="editContainer" style="padding-left: 2%;padding-right: 2%">
        <div class="row">
            {assign var=LABELS value = ["step1" => "LBL_REPORT_DETAILS", "step2" => "LBL_FILTERS", "step3" => "LBL_SELECT_CHART"]}
            {include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE ACTIVESTEP=1 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
        </div>
        <div class="clearfix"></div>
{/strip}