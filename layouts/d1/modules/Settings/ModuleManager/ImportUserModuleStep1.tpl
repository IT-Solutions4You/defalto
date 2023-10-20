{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<div class="detailViewContainer px-4 pb-4" id="importModules">
		<div class="rounded bg-body p-3">
			<div class="widget_header row col-lg-12 col-md-12 col-sm-12">
				<h4>{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}</h4>
			</div>
			<form class="form-horizontal" id="importUserModule" name="importUserModule" action='index.php' method="POST" enctype="multipart/form-data">
				<input type="hidden" name="module" value="ModuleManager" />
				<input type="hidden" name="moduleAction" value="Import"/>
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="view" value="ModuleImport" />
				<input type="hidden" name="mode" value="importUserModuleStep2" />
				<div class="contents">
					<div class="alert alert-danger my-3">
						{vtranslate('LBL_DISCLAIMER_FOR_IMPORT_FROM_ZIP', $QUALIFIED_MODULE)}
					</div>
					<div class="my-3">
						<input type="checkbox" name="acceptDisclaimer" /> &nbsp;&nbsp;<b>{vtranslate('LBL_ACCEPT_WITH_THE_DISCLAIMER', $QUALIFIED_MODULE)}</b>
					</div>
					<div class="my-3">
						<span name="proceedInstallation" class="fileUploadBtn btn btn-primary">
							<span><i class="fa fa-laptop"></i> {vtranslate('Select from My Computer', $MODULE)}</span>
							<input type="file" name="moduleZip" id="moduleZip" size="80px" data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
									data-validator={Zend_Json::encode([['name'=>'UploadModuleZip']])} />
						</span>
						<span id="moduleFileDetails" style="margin-left: 15px;"></span>
					</div>
				</div>
				<div class="modal-overlay-footer modal-footer">
					<div class="container-fluid">
						<div class="row">
							<div class="col-6 text-end">
								<a class="btn btn-primary cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<div class="col-6">
								<button class=" btn btn-primary active saveButton" disabled="disabled" type="submit" name="importFromZip"><strong>{vtranslate('LBL_IMPORT', $MODULE)}</strong></button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}
