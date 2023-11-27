{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="HistoryContainer">
        <div class="historyButtons btn-group" role="group" aria-label="...">
            <button type="button" class="btn btn-default" onclick='Vtiger_Detail_Js.showUpdates(this);'>
                {vtranslate("LBL_UPDATES",$MODULE_NAME)}
            </button>
        </div>
        
        <div class='data-body'>
        </div>
    </div>
    
{/strip}