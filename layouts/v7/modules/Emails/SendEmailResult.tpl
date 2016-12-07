{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Emails/views/MassSaveAjax.php *}
    
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}

<div class="modal-dialog">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="Result"} 
        <div class="modal-body">
            {if $SUCCESS}
                <div class="mailSentSuccessfully" data-relatedload="{$RELATED_LOAD}">
                    Mail Sent Successfully
                </div>
            {else}
                <div class="failedToSend" data-relatedload="false">
                    Failed to Send
                    <br>
                    {$MESSAGE}
                </div>
            {/if}
        </div>
    </div>
</div>
