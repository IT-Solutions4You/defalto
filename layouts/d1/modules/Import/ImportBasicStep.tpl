{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/Import.php *}

{strip}
	<form action="index.php" enctype="multipart/form-data" method="POST" name="importBasic" class='fc-overlay-modal modal-content'>
		{assign var=TITLE value="{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}"}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
		<div class="importview-content modal-body overflow-auto">
			<div action="index.php" enctype="multipart/form-data" method="POST" name="importBasic">
				<input type="hidden" name="module" value="{$FOR_MODULE}"/>
				<input type="hidden" name="view" value="Import"/>
				<input type="hidden" name="mode" value="uploadAndParse"/>
				<input type="hidden" id="auto_merge" name="auto_merge" value="0"/>
				<div class='modal-body' id="importContainer">
					{assign var=LABELS value=[]}
					{if $FORMAT eq 'vcf'}
						{$LABELS["step1"] = 'LBL_UPLOAD_VCF'}
					{else if $FORMAT eq 'ics'}
						{$LABELS["step1"] = 'LBL_UPLOAD_ICS'}
					{else}
						{$LABELS["step1"] = 'LBL_UPLOAD_CSV'}
					{/if}

					{if $FORMAT neq 'ics'}
						{if $DUPLICATE_HANDLING_NOT_SUPPORTED eq 'true'}
							{$LABELS["step3"] = 'LBL_FIELD_MAPPING'}
						{else}
							{$LABELS["step2"] = 'LBL_DUPLICATE_HANDLING'}
							{$LABELS["step3"] = 'LBL_FIELD_MAPPING'}
						{/if}
					{/if}
					{include file="BreadCrumbs.tpl"|vtemplate_path:$MODULE BREADCRUMB_ID='navigation_links' ACTIVESTEP=1 BREADCRUMB_LABELS=$LABELS MODULE=$MODULE}
					{include file='ImportStepOne.tpl'|@vtemplate_path:'Import'}

					{if $FORMAT neq 'ics'}
						{include file='ImportStepTwo.tpl'|@vtemplate_path:'Import'}
					{/if}
				</div>
			</div>
		</div>
		<div class="modal-overlay-footer modal-footer">
			<div class="container-fluid">
				{if $FORMAT eq 'ics'}
					<div class="row">
						<div class="col-6 text-end">
							<a class="cancelLink btn btn-primary" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
						<div class="col-6">
							<button type="submit" name="import" id="importButton" class="btn btn-primary active" type="button" onclick="return Calendar_Edit_Js.uploadAndParse();">{vtranslate('LBL_IMPORT_BUTTON_LABEL', $MODULE)}</button>
						</div>
					</div>
				{else}
					<div id="importStepOneButtonsDiv" class="row">
						<div class="col-6 text-end">
							<a class="cancelLink btn btn-primary" onclick="Vtiger_Import_Js.loadListRecords();" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
						<div class="col-6">
							{if $DUPLICATE_HANDLING_NOT_SUPPORTED eq 'true'}
								<button class="btn btn-primary active me-2" id="skipDuplicateMerge" type="button" onclick="Vtiger_Import_Js.uploadAndParse('0');">{vtranslate('LBL_NEXT_BUTTON_LABEL', $MODULE)}</button>
							{else}
								<button class="btn btn-primary active me-2" id="importStep2" type="button" onclick="Vtiger_Import_Js.importActionStep2();">{vtranslate('LBL_NEXT_BUTTON_LABEL', $MODULE)}</button>
							{/if}
						</div>
					</div>
					<div id="importStepTwoButtonsDiv" class="row hide">
						<div class="col-6 text-end">
							<a class='cancelLink btn btn-primary' data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
						</div>
						<div class="col-6">
							<button class="btn btn-primary active me-2" id="backToStep1" type="button" onclick="Vtiger_Import_Js.bactToStep1();">{vtranslate('LBL_BACK', $MODULE)}</button>
							<button class="btn btn-primary active me-2" id="uploadAndParse" type="button" onclick="Vtiger_Import_Js.uploadAndParse('1');" name="next">{vtranslate('LBL_NEXT_BUTTON_LABEL', $MODULE)}</button>
							<button class="btn btn-primary active me-2" id="skipDuplicateMerge" type="button" onclick="Vtiger_Import_Js.uploadAndParse('0');">{vtranslate('Skip this step', $MODULE)}</button>
						</div>
					</div>
				{/if}
			</div>
		</div>
	</form>
{/strip}