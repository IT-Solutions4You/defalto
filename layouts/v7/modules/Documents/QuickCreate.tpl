{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{strip}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}

	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			{assign var=HEADER_TITLE value={vtranslate('LBL_QUICK_CREATE', $MODULE)}|cat:" "|cat:{vtranslate($SINGLE_MODULE, $MODULE)}}
			{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}

			<!-- Random number is used to make specific tab is opened -->
			{assign var="RAND_NUMBER" value=rand()}
			<div class="modal-body tabbable" style="padding:0px">
				<ul class="nav nav-pills" style="margin-bottom:0px;padding-left:5px">
					{foreach item=DOC_TYPE_DETAILS key=DOC_TYPE from=$DOC_TYPES}
						<li class="{if $SELECTED_DOC_TYPE eq $DOC_TYPE}active{/if}">
							<a class="tab" href="javascript:void(0);" data-target=".{$DOC_TYPE}QuickCreateContents_{$RAND_NUMBER}" data-toggle="tab" data-tab-name="{$DOC_TYPE_DETAILS.tabName}">{vtranslate({$DOC_TYPE_DETAILS.label},$MODULE)}</a>
						</li>
					{/foreach}
				</ul>
				<div class="tab-content overflowVisible fieldsContainer">
					{foreach item=TYPE_DETAILS key=DOCUMENT_TYPE from=$QUICK_CREATE_CONTENTS}
						<div class="{$DOCUMENT_TYPE}QuickCreateContents_{$RAND_NUMBER} tab-pane {if $DOCUMENT_TYPE eq $SELECTED_DOC_TYPE} active in {/if}fade">
							<form id="{$DOCUMENT_TYPE}_form" class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php" enctype="multipart/form-data">
								{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
									<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
								{/if}
								<div class="quickCreateContent" id="{$DOCUMENT_TYPE}QuickCreateContent">
									<input type="hidden" name="module" value="{$MODULE}">
									<input type="hidden" name="action" value="SaveAjax">
									<input type="hidden" name="document_source" value="Vtiger" />
									<input type="hidden" name="max_upload_limit" value="{$MAX_UPLOAD_LIMIT_BYTES}" />
									<input type="hidden" name="max_upload_limit_mb" value="{$MAX_UPLOAD_LIMIT_MB}" />
									{if $DOCUMENT_TYPE eq 'I' || $DOCUMENT_TYPE eq 'E'}
										<input type="hidden" name='filelocationtype' value="{if $DOCUMENT_TYPE eq 'E'}E{else}I{/if}">
									{/if}
									<div style='margin:18px;'>
										<table class="massEditTable table no-border">
											<tr>
												{assign var=COUNTER value=0}
												{foreach key=FIELD_NAME item=FIELD_MODEL from=$TYPE_DETAILS name=blockfields}
													{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
													{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
													{if $FIELD_MODEL->get('uitype') eq "19"}
														{if $COUNTER eq '1'}
															<td></td><td></td></tr><tr>
															{assign var=COUNTER value=0}
														{/if}
													{/if}
													{assign var="refrenceListCount" value=count($refrenceList)}
													{if $COUNTER eq 2}
													</tr><tr>
														{assign var=COUNTER value=1}
													{else}
														{assign var=COUNTER value=$COUNTER+1}
													{/if}
													{if $FIELD_MODEL->get('label') eq 'File Name' && $DOCUMENT_TYPE neq 'E'}
														{if $COUNTER eq 2}
															<td></td><td></td></tr><tr>
															{assign var=COUNTER value=1}
														{/if}
													{/if}
													<td class="fieldLabel alignMiddle">
														<label class="muted pull-right">
															{if {$isReferenceField} eq "reference"}
																{if $refrenceListCount > 1}
																	<select style="width: 150px;" class="chzn-select referenceModulesList">
																		<optgroup>
																			{foreach key=index item=value from=$refrenceList}
																				<option value="{$value}">{vtranslate($value, $value)}</option>
																			{/foreach}
																		</optgroup>
																	</select>
																{else}
																	{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
																{/if}
																{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
															{else}
																{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
															{/if}
															&nbsp;{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
														</label>
													</td>
													<td class="fieldValue" {if $FIELD_MODEL->get('uitype') eq '19'} {if $FIELD_MODEL->get('label') eq 'Note'}style="position:relative;max-height:300px;max-width: 300px;"{/if} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
														{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
														{if $FIELD_MODEL->get('label') eq 'File Name' && $DOCUMENT_TYPE neq 'E'}
															{if $COUNTER eq 2}
														</tr><tr>
															{assign var=COUNTER value=1}
														{else}
															{assign var=COUNTER value=$COUNTER+1}
														{/if}
														<td colspan="2">
															<div id="dragandrophandler" class="dragdrop-dotted"><strong>{vtranslate('LBL_DRAG_&_DROP_FILE_HERE', $MODULE)}</strong></div>
														</td>
													{/if}
													</td>
												{/foreach}
											</tr>
											{if $smarty.request.parent_id neq ''}
												<input type="hidden" name="parent_id" value="{$smarty.request.parent_id}" />
											{else if $smarty.request.contact_id neq ''}
												<input type="hidden" name="contact_id" value="{$smarty.request.contact_id}" />
											{/if}
										</table>
									</div>
								</div>
								{include file="ModalFooter.tpl"|vtemplate_path:$MODULE}
							</form>
						</div>
					{/foreach}
				</div>
			</div>
		</div>
		{if $FIELDS_INFO neq null}
			<script type="text/javascript">
				var quickcreate_uimeta = (function () {
					var fieldInfo = {$FIELDS_INFO};
					return {
						field: {
							get: function (name, property) {
								if (name && property === undefined) {
									return fieldInfo[name];
								}
								if (name && property) {
									return fieldInfo[name][property]
								}
							},
							isMandatory: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].mandatory;
								}
								return false;
							},
							getType: function (name) {
								if (fieldInfo[name]) {
									return fieldInfo[name].type
								}
								return false;
							}
						}
					};
				})();
			</script>
		{/if}
	</div>
{/strip}
