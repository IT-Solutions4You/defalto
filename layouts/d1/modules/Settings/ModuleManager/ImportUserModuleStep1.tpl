{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="detailViewContainer px-4 pb-4" id="importModules">
		<div class="rounded bg-body">
			<div class="p-3 border-bottom">
				<h4>{vtranslate('LBL_IMPORT_MODULE_FROM_ZIP', $QUALIFIED_MODULE)}</h4>
			</div>
			<form class="form-horizontal" id="importUserModule" name="importUserModule" action='index.php' method="POST" enctype="multipart/form-data">
				<input type="hidden" name="module" value="ModuleManager" />
				<input type="hidden" name="moduleAction" value="Import"/>
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="view" value="ModuleImport" />
				<input type="hidden" name="mode" value="importUserModuleStep2" />
				<div class="contents p-3">
					<div class="alert alert-danger my-3">
						{vtranslate('LBL_DISCLAIMER_FOR_IMPORT_FROM_ZIP', $QUALIFIED_MODULE)}
					</div>
					<div class="my-3">
						<input type="checkbox" class="form-check-input" name="acceptDisclaimer" /> &nbsp;&nbsp;<b>{vtranslate('LBL_ACCEPT_WITH_THE_DISCLAIMER', $QUALIFIED_MODULE)}</b>
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
				<div class="modal-overlay-footer modal-footer border-top">
					<div class="container-fluid p-3">
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
