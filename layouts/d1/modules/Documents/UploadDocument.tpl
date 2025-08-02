{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="modal-dialog modelContainer">
		{assign var=HEADER_TITLE value={vtranslate('LBL_UPLOAD_TO_VTIGER', $MODULE)}}
		<div class="modal-content" style="width:675px;">
			<form class="form-horizontal recordEditView" name="upload" method="post" action="index.php">
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
				<div class="modal-body">
					<div class="uploadview-content container-fluid">
						<div class="uploadcontrols row">
							<div id="upload" data-filelocationtype="I">
								{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
									<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
								{/if}
								<input type="hidden" name="module" value="{$MODULE}" />
								<input type="hidden" name="action" value="SaveAjax" />
								<input type="hidden" name="document_source" value="Vtiger" />
								{if $RELATION_OPERATOR eq 'true'}
									<input type="hidden" name="relationOperation" value="{$RELATION_OPERATOR}" />
									<input type="hidden" name="sourceModule" value="{$PARENT_MODULE}" />
									<input type="hidden" name="sourceRecord" value="{$PARENT_ID}" />
									{if isset($RELATION_FIELD_NAME)}
										<input type="hidden" name="{$RELATION_FIELD_NAME}" value="{$PARENT_ID}" /> 
									{/if}
								{/if}

								<input type="hidden" name="max_upload_limit" value="{$MAX_UPLOAD_LIMIT_BYTES}" />
								<input type="hidden" name="max_upload_limit_mb" value="{$MAX_UPLOAD_LIMIT_MB}" />

								<div id="dragandrophandler" class="dragdrop-dotted">
									<div class="fs-3">
										<span class="fa fa-upload"></span>
										<span class="ms-2">{vtranslate('LBL_DRAG_&_DROP_FILE_HERE', $MODULE)}</span>
									</div>
									<div class="mt-1 mb-2 text-uppercase">
										{vtranslate('LBL_OR', $MODULE)}
									</div>
									<div>
										<div class="fileUploadBtn btn btn-primary">
											<span><i class="fa fa-laptop"></i> {vtranslate('LBL_SELECT_FILE_FROM_COMPUTER', $MODULE)}</span>
											{assign var=FIELD_MODEL value=$FIELD_MODELS['filename']}
											<input type="file" name="{$FIELD_MODEL->getFieldName()}" value="{$FIELD_VALUE}" data-rule-required="true" />
										</div>
										<i class="fa fa-info-circle cursorPointer ms-2" data-bs-toggle="tooltip" title="{vtranslate('LBL_MAX_UPLOAD_SIZE', $MODULE)} {$MAX_UPLOAD_LIMIT_MB}{vtranslate('MB', $MODULE)}"></i>
									</div>
									<div class="fileDetails"></div>
								</div>
								<div class="container-fluid">
									<div class="row py-2 align-items-center">
										{assign var=FIELD_MODEL value=$FIELD_MODELS['notes_title']}
										<div class="fieldLabel col-lg-3 text-secondary text-end">
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
										</div>
										<div class="fieldValue col-lg-9">
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</div>
									</div>
									<div class="row py-2 align-items-center">
										{assign var=FIELD_MODEL value=$FIELD_MODELS['assigned_user_id']}
										<div class="fieldLabel col-lg-3 text-secondary text-end">
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
											{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
										</div>
										<div class="fieldValue col-lg-9">
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
										</div>
									</div>
									<div class="row py-2 align-items-center">
										{assign var=FIELD_MODEL value=$FIELD_MODELS['folderid']}
										{if $FIELD_MODELS['folderid']}
											<div class="fieldLabel col-lg-3 text-secondary text-end">
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
											</div>
											<div class="fieldValue col-lg-9">
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</div>
										{/if}
									</div>
									<div class="row py-2 align-items-center">
										{assign var=FIELD_MODEL value=$FIELD_MODELS['notecontent']}
										{if $FIELD_MODELS['notecontent']}
											<div class="fieldLabel col-lg-3 text-secondary text-end">
												{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
												{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
											</div>
											<div class="fieldValue col-lg-9">
												{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
											</div>
										{/if}
									</div>
									<div class="row py-2 align-items-center">
										{assign var=HARDCODED_FIELDS value=','|explode:"filename,assigned_user_id,folderid,notecontent,notes_title"}
										{assign var=COUNTER value=0}
										{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELD_MODELS} 
											{if !in_array($FIELD_NAME,$HARDCODED_FIELDS) && $FIELD_MODEL->isQuickCreateEnabled()}
												<div class="fieldLabel col-lg-3 text-secondary text-end">
													{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
													{if $FIELD_MODEL->isMandatory() eq true}<span class="text-danger ms-2">*</span>{/if}
												</div>
												<div class="fieldValue col-lg-9">
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
												</div>
											{/if}
										{/foreach}
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				{assign var=BUTTON_NAME value={vtranslate('LBL_UPLOAD', $MODULE)}}
				{assign var=BUTTON_ID value="js-upload-document"}
				{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}
