{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}

	<div name='editContent'>
		{if $DUPLICATE_RECORDS}
			<div class="fieldBlockContainer bg-white rounded mb-3 duplicationMessageContainer">
				<div class="duplicationMessageHeader"><b>{vtranslate('LBL_DUPLICATES_DETECTED', $MODULE)}</b></div>
				<div>{getDuplicatesPreventionMessage($MODULE, $DUPLICATE_RECORDS)}</div>
			</div>
		{/if}
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
			{if $BLOCK_FIELDS|php7_count gt 0}
				<div class="fieldBlockContainer bg-white rounded mb-3 {if 1 neq $smarty.foreach.blockIterator.iteration}{/if}" data-block="{$BLOCK_LABEL}">
					<h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
					<div class="container-fluid py-3">
						<div class="row">
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								<div class="py-2 {if $FIELD_MODEL->isTableFullWidth()}col-lg-12{else}col-lg-6{/if}">
									{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
									{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
									{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
									{assign var="refrenceListCount" value=php7_count($refrenceList)}
									{if $FIELD_MODEL->isEditable() eq true}
										<div class="container-fluid">
											<div class="row">
												<div class="fieldLabel {if $FIELD_MODEL->isTableFullWidth()}col-lg-2{else}col-lg-4{/if}">
													{if $MASS_EDITION_MODE}
														<input class="inputElement" id="include_in_mass_edit_{$FIELD_MODEL->getFieldName()}" data-update-field="{$FIELD_MODEL->getFieldName()}" type="checkbox">
													{/if}
													{if $isReferenceField eq "reference"}
														{if $refrenceListCount > 1}
															{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
															{assign var="REFERENCED_MODULE_STRUCTURE" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
															{if !empty($REFERENCED_MODULE_STRUCTURE)}
																{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCTURE->get('name')}
															{/if}
															<select class="select2 referenceModulesList">
																{foreach key=index item=value from=$refrenceList}
																	<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $value)}</option>
																{/foreach}
															</select>
														{else}
															{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
														{/if}
													{elseif $FIELD_MODEL->get('uitype') eq "83"}
														{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
													{else}
														{if $MODULE eq 'Documents' && $FIELD_MODEL->get('label') eq 'File Name'}
															{assign var=FILE_LOCATION_TYPE_FIELD value=$RECORD_STRUCTURE['LBL_FILE_INFORMATION']['filelocationtype']}
															{if $FILE_LOCATION_TYPE_FIELD}
																{if $FILE_LOCATION_TYPE_FIELD->get('fieldvalue') eq 'E'}
																	{vtranslate("LBL_FILE_URL", $MODULE)}&nbsp;
																	<span class="redColor">*</span>
																{else}
																	{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
																{/if}
															{else}
																{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
															{/if}
														{else}
															{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
														{/if}
													{/if}
													{if $FIELD_MODEL->isMandatory() eq true}<span class="redColor">*</span>{/if}
												</div>
												{if $FIELD_MODEL->get('uitype') neq '83'}
													<div class="fieldValue {if $FIELD_MODEL->isTableFullWidth()}col-lg-10{elseif $FIELD_MODEL->get('uitype') eq '56'}col-lg-8 checkBoxType{else}col-lg-8{/if}">
														{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
													</div>
												{/if}
											</div>
										</div>
									{/if}
								</div>
							{/foreach}
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}
