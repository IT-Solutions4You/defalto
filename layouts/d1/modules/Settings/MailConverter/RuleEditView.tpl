{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="modelContainer modal-dialog modal-lg">
		<div class="modal-content">
			<form class="form-horizontal" id="ruleSave" method="post" action="index.php">
				{if $RECORD_ID}
					{assign var=TITLE value={vtranslate('LBL_EDIT_RULE', $QUALIFIED_MODULE)}}
				{else}
					{assign var=TITLE value={vtranslate('LBL_ADD_RULE', $QUALIFIED_MODULE)}}
				{/if}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="SaveRule" />
				<input type="hidden" name="scannerId" value="{$SCANNER_ID}" />
				<input type="hidden" name="record" value="{$RECORD_ID}" />
				<div class="addMailBoxStep modal-body">
					{assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
					<div class="container-fluid py-3">
						{assign var=FIELDS value=$MODULE_MODEL->getSetupRuleFields()}
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
							<div class="row py-2">
								<div class="col-lg-3 control-label">
									<label class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}</label>
								</div>
								<div class="col-lg-7">
									{assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
									{if $FIELD_DATA_TYPE eq 'picklist'}
										{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPickListValues()}
										{if $FIELD_NAME eq 'subject'}
											<select name="subjectop" class="select2 fieldValue inputElement form-select">
												<option value="">{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
												{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
													<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('subjectop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
												{/foreach}
											</select>
										{elseif $FIELD_NAME eq 'body'}
											<select name="bodyop" class="select2 fieldValue inputElement form-select">
												<option value="" {if $RECORD_MODEL->get('bodyop') eq ""}selected{/if}>{vtranslate('LBL_SELECT_OPTION', $QUALIFIED_MODULE)}</option>
												{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
													<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get('bodyop') eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
												{/foreach}
											</select>
											<textarea name="{$FIELD_MODEL->getName()}" class="form-control mt-2">{$RECORD_MODEL->get($FIELD_NAME)}</textarea>
										{else}
											<select id="actions" name="action1" class="select2 fieldValue inputElement form-select">
												{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
													<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get($FIELD_NAME) eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
												{/foreach}
											</select>
										{/if}
									{elseif $FIELD_DATA_TYPE eq 'radio'}
										{assign var=RADIO_OPTIONS value=$FIELD_MODEL->getRadioOptions()}
										{foreach key=RADIO_NAME item=RADIO_VALUE from=$RADIO_OPTIONS}
											<label class="radioOption inline form-check">
												<input class="radioOption form-check-input" type="radio" name="{$FIELD_MODEL->getName()}" value="{$RADIO_NAME}" {if $RECORD_MODEL->get($FIELD_NAME) eq $RADIO_NAME} checked {/if} />
												{$RADIO_VALUE}
											</label>
										{/foreach}
									{elseif $FIELD_DATA_TYPE eq 'email'}
										<input type="text" class="fieldValue inputElement form-control" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" data-validation-engine="validate[funcCall[Vtiger_Email_Validator_Js.invokeValidation]]"/>
									{else}
										<input type="text" class="fieldValue inputElement form-control" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
									{/if}
									{if $FIELD_NAME eq 'subject'}
										<input type="text" class="fieldValue inputElement form-control mt-2" name="{$FIELD_MODEL->getName()}" value="{$RECORD_MODEL->get($FIELD_NAME)}" />
									{/if}
								</div>
							</div>
						{/foreach}
						<div class="row" id="assignedToBlock">
							<div class="col-lg-3 control-label">
								<label class="fieldLabel">{vtranslate('Assigned To')}</label>
							</div>
							<div class="col-lg-7">
								<select class="select2 fieldValue inputElement form-select" id="assignedTo" name="assignedTo">
									<optgroup label="{vtranslate('LBL_USERS')}">
										{assign var=USERS value=$USER_MODEL->getAccessibleUsersForModule($MODULE_NAME)}
										{foreach key=OWNER_ID item=OWNER_NAME from=$USERS}
											<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
												{$OWNER_NAME}
											</option>
										{/foreach}
									</optgroup>
									<optgroup label="{vtranslate('LBL_GROUPS')}">
										{assign var=GROUPS value=$USER_MODEL->getAccessibleGroups()}
										{foreach key=OWNER_ID item=OWNER_NAME from=$GROUPS}
											<option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $ASSIGNED_USER eq $OWNER_ID} selected {/if}>
												{$OWNER_NAME}
											</option>
										{/foreach}
									</optgroup>
								</select>
							</div>
						</div>
					</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$QUALIFIED_MODULE}
			</form>
		</div>
	</div>
{/strip}
