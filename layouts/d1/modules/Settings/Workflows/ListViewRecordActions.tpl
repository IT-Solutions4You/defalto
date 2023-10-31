{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<!--LIST VIEW RECORD ACTIONS-->
<div style="width:80px ;">
    <a class="deleteRecordButton" style=" opacity: 0; padding: 0 5px;">
        <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa fa-trash alignMiddle"></i>
    </a>
    <input style="opacity: 0;" {if $LISTVIEW_ENTRY->get('status')} checked value="on" {else} value="off"{/if} data-on-color="success"  data-id="{$LISTVIEW_ENTRY->getId()}" type="checkbox" name="workflowstatus" id="workflowstatus">
</div>
{/strip}