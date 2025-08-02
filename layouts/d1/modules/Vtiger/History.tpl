{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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