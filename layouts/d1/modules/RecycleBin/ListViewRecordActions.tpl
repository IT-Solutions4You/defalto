{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}{strip}
<!--LIST VIEW RECORD ACTIONS-->
<div class="table-actions d-flex align-items-center text-secondary">
    {if !$SEARCH_MODE_RESULTS}
        <span class="input form-check">
            <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input"/>
        </span>
    {/if}
    <span class="restoreRecordButton px-2">
        <i title="{vtranslate('LBL_RESTORE', $MODULE)}" class="fa fa-refresh alignMiddle"></i>
    </span>
    <span class="deleteRecordButton px-2">
        <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa fa-trash alignMiddle"></i>
    </span>
</div>
{/strip}