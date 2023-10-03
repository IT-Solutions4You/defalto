{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{* modules/Users/views/EditAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div id="massEditContainer" class="modal-dialog">
		<form class="form-horizontal" id="changePassword" name="changePassword" method="post" action="index.php">
			<div class="modal-content">
				{assign var=HEADER_TITLE value=vtranslate('LBL_CHANGE_PASSWORD', $MODULE)}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="modal-body">
					<input type="hidden" name="module" value="{$MODULE}"/>
					<input type="hidden" name="userid" value="{$USERID}"/>
					<div name="massEditContent">
						<div>
							<div class="form-group my-3">
								{if !$CURRENT_USER_MODEL->isAdminUser()}
									<label class="control-label fieldLabel">
										<span class="me-2">{vtranslate('LBL_OLD_PASSWORD', $MODULE)}</span>
										<span class="text-danger">*</span>
									</label>
									<div class="controls col-sm-6">
										<input type="password" name="old_password" class="form-control inputElement" data-rule-required="true"/>
									</div>
								{/if}
							</div>

							<div class="form-group my-3">
								<label class="control-label fieldLabel">
									<span class="me-2">{vtranslate('LBL_NEW_PASSWORD', $MODULE)}</span>
									<span class="text-danger">*</span>
								</label>
								<div class="controls">
									<input type="password" class="form-control inputElement" name="new_password" data-rule-required="true" autofocus="autofocus"/>
								</div>
							</div>

							<div class="form-group my-3">
								<label class="control-label fieldLabel">
									<span class="me-2">{vtranslate('LBL_CONFIRM_PASSWORD', $MODULE)}</span>
									<span class="text-danger">*</span>
								</label>
								<div class="controls">
									<input type="password" class="form-control inputElement" name="confirm_password" data-rule-required="true"/>
								</div>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</form>
	</div>
{/strip}
