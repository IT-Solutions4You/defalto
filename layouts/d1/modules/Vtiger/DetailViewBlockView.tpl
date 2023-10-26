{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{if $BLOCK eq null or $FIELD_MODEL_LIST|count lte 0}{continue}{/if}
		<div class="mt-3 bg-body rounded block block_{$BLOCK_LABEL_KEY}" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
			{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
			{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
			<input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}' />
			<div class="p-3">
				<div class="text-truncate d-flex align-items-center">
					<span class="btn btn-outline-secondary blockToggle {if !$IS_HIDDEN}hide{/if}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						<i class="fa fa-plus"></i>
					</span>
					<span class="btn btn-outline-secondary blockToggle {if $IS_HIDDEN}hide{/if}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
						<i class="fa fa-minus"></i>
					</span>
					<span class="ms-3 fs-4 fw-bold">{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}</span>
				</div>
			</div>
			<div class="blockData p-3 border-top border-light-subtle {if $IS_HIDDEN}hide{/if}">
				<div class="container-fluid detailview-table">
					<div class="row">
						{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
							{assign var=fieldDataType value=$FIELD_MODEL->getFieldDataType()}
							{if !$FIELD_MODEL->isViewableInDetailView()}
								{continue}
							{/if}
							{if $FIELD_MODEL->get('uitype') eq "83"}
								{foreach item=tax key=count from=$TAXCLASS_DETAILS}
									<div id="{$MODULE}_{$VIEW}_{$FIELD_MODEL->getName()}" class="py-2 col-lg-6">
										<div class="h-100">
											<div class="row py-2 border-bottom border-light-subtle h-100">
												<div class="col-4 fieldLabel {$WIDTHTYPE}">
													<span class='muted'>{vtranslate($tax.taxlabel, $MODULE)}(%)</span>
												</div>
												<div class="col-8 fieldValue fw-semibold {$WIDTHTYPE}">
													<span class="value text-truncate" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
														{if $tax.check_value eq 1}
															{$tax.percentage}
														{else}
															0
														{/if}
													</span>
												</div>
											</div>
										</div>
									</div>
								{/foreach}
							{elseif $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
								<div  id="{$MODULE}_{$VIEW}_{$FIELD_MODEL->getName()}" class="py-2 col-lg-12">
									<div class="h-100">
										<div class="row py-2 border-bottom border-light-subtle h-100">
											<div class="col-lg-2 fieldLabel {$WIDTHTYPE}">
												<span class="muted">{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}</span>
											</div>
											<div class="col-lg-10 fieldValue fw-semibold {$WIDTHTYPE}">
												{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
													{if !empty($IMAGE_INFO.url) && !empty({$IMAGE_INFO.orgname})}
														<div class="d-inline-block pb-2 pe-2">
															<img class="rounded" src="{$IMAGE_INFO.url}" title="{$IMAGE_INFO.orgname}" style="max-height: 15rem;" />
														</div>
													{/if}
												{/foreach}
											</div>
										</div>
									</div>
								</div>
							{else}
								<div id="{$MODULE}_{$VIEW}_field_{$FIELD_MODEL->getName()}" class="py-2 {if $FIELD_MODEL->isTableFullWidth()}col-lg-12{else}col-lg-6{/if}">
									<div class="h-100">
										<div class="row py-2 border-bottom border-light-subtle h-100">
											<div class="fieldLabel text-truncate {if $FIELD_MODEL->isTableFullWidth()}col-lg-2{else}col-lg-4{/if} {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
												<span class="muted">
													{if $MODULE_NAME eq 'Documents' && $FIELD_MODEL->get('label') eq "File Name" && $RECORD->get('filelocationtype') eq 'E'}
														{vtranslate("LBL_FILE_URL",{$MODULE_NAME})}
													{else}
														{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
													{/if}
													{if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
														({$BASE_CURRENCY_SYMBOL})
													{/if}
												</span>
											</div>
											<div class="fieldValue fw-semibold {if $FIELD_MODEL->isTableFullWidth()}col-lg-10{else}col-lg-8{/if} {$WIDTHTYPE}" id="{$MODULE_NAME}_detailView_fieldValue_{$FIELD_MODEL->getName()}">
												{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
												{if $fieldDataType eq 'multipicklist'}
													{assign var=FIELD_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
												{else}
													{assign var=FIELD_DISPLAY_VALUE value=Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}
												{/if}

												<span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
													{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
												</span>
												{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && $FIELD_MODEL->isAjaxEditable() eq 'true'}
													<span class="action pull-right"><a href="#" onclick="return false;" class="editAction fa fa-pencil"></a></span>
													<div class="hide edit">
														{if $fieldDataType eq 'multipicklist'}
															<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}[]' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
														{else}
															<input type="hidden" class="fieldBasicData" data-name='{$FIELD_MODEL->get('name')}' data-type="{$fieldDataType}" data-displayvalue='{$FIELD_DISPLAY_VALUE}' data-value="{$FIELD_VALUE}" />
														{/if}
													</div>
												{/if}
											</div>
										</div>
									</div>
								</div>
							{/if}
						{/foreach}
					</div>
				</div>
			</div>
		</div>
	{/foreach}
{/strip}