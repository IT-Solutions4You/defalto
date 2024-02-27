{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<!--LIST VIEW RECORD ACTIONS-->
<div class="d-flex align-items-center workflow-actions">
    <a class="btn text-secondary deleteRecordButton" style="opacity: 0;">
        <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa fa-trash alignMiddle"></i>
    </a>
    <div class="form-check form-switch">
        <input {if $LISTVIEW_ENTRY->get('status')}checked="checked" data-value="on"{else}value="off"{/if} data-id="{$LISTVIEW_ENTRY->getId()}" value="1" class="form-check-input" type="checkbox" name="workflowstatus" id="workflowstatus">
    </div>
</div>
{/strip}