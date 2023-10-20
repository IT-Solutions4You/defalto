{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}

{strip}
	{if $IMAP_ERROR || $CONNECTION_ERROR}
		<div class="block">
			<strong>
				{if $IMAP_ERROR}
					{$IMAP_ERROR}
				{else if $CONNECTION_ERROR}
					{vtranslate('LBL_CONNECTION_ERROR', $QUALIFIED_MODULE)}
				{/if}
			</strong>
		</div>
		<br>
	{/if}
	<div class="addMailBoxBlock">
		<div class="container-fluid p-3">
			<div class="row col-lg-12">
				<div id="mailConverterDragIcon">
					<i class="fa fa-info"></i>
					<span class="ms-2">{vtranslate('TO_CHANGE_THE_FOLDER_SELECTION_DESELECT_ANY_OF_THE_SELECTED_FOLDERS', $QUALIFIED_MODULE)}</span>
				</div>
			</div>
		</div>
		<form class="form-horizontal" id="mailBoxEditView" name="step2">
			<div class="block">
				<div class="addMailBoxStep container-fluid p-3">
					{foreach key=FOLDER item=SELECTED from=$FOLDERS}
						<div class="row py-2">
							<div class="col-lg-12">
								<label>
									<input type="checkbox" name="folders" value="{$FOLDER}" {if $SELECTED eq 'checked'}checked{/if}>
									<span class="ms-2">{$FOLDER}</span>
								</label>
							</div>
						</div>
					{/foreach}
				</div>
				<div class="modal-overlay-footer modal-footer py-3">
					<div class="container-fluid">
						<div class="row">
							<div class="col text-end">
								<a class="btn btn-primary cancelLink" type="reset" onclick="javascript:window.history.go(-2);">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
							</div>
							<div class="col-auto">
								<button class="btn btn-danger backStep" type="button" onclick="javascript:window.history.back();"><strong>{vtranslate('LBL_BACK', $QUALIFIED_MODULE)}</strong></button>
							</div>
							<div class="col">
								<button class="btn btn-primary active" onclick="javascript:Settings_MailConverter_Edit_Js.secondStep()">
									<strong>
										{if $CREATE eq 'new'}{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_FINISH', $QUALIFIED_MODULE)}{/if}
									</strong>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
</div>
{/strip}