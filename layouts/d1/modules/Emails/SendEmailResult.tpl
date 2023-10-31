{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="modal-dialog">
	<div class="modal-content">
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE="Result"} 
		<div class="modal-body">
			{if $SUCCESS}
				<div class="mailSentSuccessfully" data-relatedload="{$RELATED_LOAD}">
                                    {if $FLAG eq 'SENT'}
                                        {vtranslate('LBL_MAIL_SENT_SUCCESSFULLY')}
                                    {else}
                                        {vtranslate('LBL_MAIL_SAVED_SUCCESSFULLY')}
                                    {/if}
				</div>
				{if $FLAG}
					<input type="hidden" id="flag" value="{$FLAG}">
				{/if}
			{else}
				<div class="failedToSend" data-relatedload="false">
					{vtranslate('LBL_FAILED_TO_SEND')}
					<br>
					{$MESSAGE}
				</div>
			{/if}
		</div>
	</div>
</div>
