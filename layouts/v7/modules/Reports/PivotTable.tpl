{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
    <script type="text/javascript" src="libraries/jquery/pivot/js/jbPivot.min.js"></script>
    <input type="hidden" name="reportdata" id="reportdata" value='{$DATA}'></input>
    <div class="contents-topscroll">
        <div class="topscroll-div container-fluid">
            &nbsp;
        </div>
    </div>
    <div id="reportDetails" class="contents-bottomscroll filterConditionContainer">
        <center>
            <div id="pivot1" class="contactAdd"></div>
        </center>
        <div class="bottomscroll-div">
        </div>
    </div>
    {if $REPORT_MODEL->isInventoryModuleSelected()}
        <br>
        <div class="alert alert-info">
            {assign var=BASE_CURRENCY_INFO value=Vtiger_Util_Helper::getUserCurrencyInfo()}
            <i class="icon-info-sign" style="margin-top: 1px;"></i>&nbsp;&nbsp;
            {vtranslate('LBL_CALCULATION_CONVERSION_MESSAGE', $MODULE)} - {$BASE_CURRENCY_INFO['currency_name']} ({$BASE_CURRENCY_INFO['currency_code']})
        </div>
    {/if}
</div>
</div>
</div>
{/strip}

