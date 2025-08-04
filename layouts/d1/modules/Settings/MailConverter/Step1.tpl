{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="block fieldBlockContainer">
		<form class="form-horizontal" id="mailBoxEditView" name="step1">
			{assign var=FIELDS value=$MODULE_MODEL->getFields()}
			{if empty($RECORD_ID)}
				{assign var=RECORD_EXISTS value=false}
			{else}
				{assign var=RECORD_EXISTS value=true}
			{/if}

			{if $RECORD_EXISTS}
				<input type="hidden" name="record" value="{$RECORD_MODEL->getId()}" />
				<input type="hidden" name="scannerOldName" value="{$RECORD_MODEL->getName()}" />
			{/if}
			<div class="addMailBoxStep">
				<div class="container-fluid px-3">
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$FIELDS}
						{if !$RECORD_MODEL->isFieldEditable($FIELD_MODEL)}
							{continue}
						{/if}
						<div class="row py-2 fieldContainer">
							<div class="col-lg-3 fieldLabel control-label">
								<label>{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}{if $FIELD_MODEL->isMandatory()}<span class="text-danger ms-2">*</span>{/if}</label>
							</div>
							<div class="col-lg-6">
								{assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
								{if $FIELD_DATA_TYPE eq 'password'}
									<input class="fieldValue inputElement form-control" type="password" name="{$FIELD_MODEL->getName()}" {if $RECORD_EXISTS} value="{$RECORD_MODEL->get($FIELD_NAME)}" {/if}
											{if $FIELD_MODEL->isMandatory()}data-validation-engine="validate[required]"{/if} />
								{elseif $FIELD_DATA_TYPE eq 'token'}
									<div class="input-group">
										<input class="fieldValue inputElement form-control" type="password" name="{$FIELD_MODEL->getName()}" {if $RECORD_EXISTS} value="{$RECORD_MODEL->get($FIELD_NAME)}" {/if}
												{if $FIELD_MODEL->isMandatory()}data-validation-engine="validate[required]"{/if} />
										<button class="input-group-text refreshToken" type="button"><i class="fa fa-download"></i></button>
										<button class="input-group-text retrieveToken" type="button"><i class="fa fa-search"></i></button>
									</div>
								{elseif $FIELD_DATA_TYPE eq 'boolean'}
									{assign var=RECORD_ID value=$RECORD_MODEL->getId()}
									<input type="hidden" name="{$FIELD_MODEL->getName()}" value="0" />
									<input type="checkbox" name="{$FIELD_MODEL->getName()}" {if ($RECORD_MODEL->get($FIELD_MODEL->getName()) eq '1') || (empty($RECORD_ID))}checked{/if} />
								{elseif $FIELD_DATA_TYPE eq 'picklist'}
									{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPickListValues()}
									{assign var=FIELD_VALUE value=$RECORD_MODEL->get($FIELD_NAME)}
									{if $FIELD_MODEL->getName() eq 'time_zone' && empty($FIELD_VALUE)}
										{assign var=FIELD_VALUE value=" "}
									{/if}
									<select name="{$FIELD_MODEL->getName()}" class="select2 fieldValue inputElement form-control" {if $FIELD_MODEL->getName() eq 'time_zone'}data-dropdownCssClass="select2-drop-above"{/if}>
										{if $FIELD_MODEL->getName() eq 'time_zone'}
											{* since in time zone its array of value and key, since there will mutiple areas with same time_zone *}
											{foreach item=PICKLIST_VALUE key=PICKLIST_KEY from=$PICKLIST_VALUES}
												<option value="{$PICKLIST_KEY}" {if $FIELD_VALUE eq $PICKLIST_KEY} selected {/if} >{vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE)}</option>
											{/foreach}
										{else}
											{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
												<option value="{$PICKLIST_KEY}" {if $RECORD_MODEL->get($FIELD_NAME) eq $PICKLIST_KEY} selected {/if} >{$PICKLIST_VALUE}</option>
											{/foreach}
										{/if}
									</select>
								{elseif $FIELD_DATA_TYPE eq 'radio'}
									{assign var=RADIO_OPTIONS value=$FIELD_MODEL->getRadioOptions()}
									{foreach key=RADIO_NAME item=RADIO_VALUE from=$RADIO_OPTIONS}
										<label class="radioOption inline form-check">
											<input class="radioOption form-check-input" type="radio" name="{$FIELD_MODEL->getName()}" value="{$RADIO_NAME}"
													{if $RECORD_EXISTS}
														{if $RECORD_MODEL->get($FIELD_NAME) eq $RADIO_NAME} checked {/if}
													{else}
														{if $RADIO_NAME eq 'imap4' || $RADIO_NAME eq 'ssl' || $RADIO_NAME eq 'novalidate-cert'} checked {/if}
													{/if} />
											<span class="ms-2">{$RADIO_VALUE}</span>
										</label>
									{/foreach}
								{else}
                                    <div class="input-group">
									    <input type="text" class="fieldValue inputElement form-control" name="{$FIELD_MODEL->getName()}" {if $FIELD_MODEL->isMandatory()}data-validation-engine="validate[required]"{/if} value="{$RECORD_MODEL->get($FIELD_NAME)}"/>
                                        {if 'scannername' eq $FIELD_NAME}
                                            <div class="input-group-text" data-bs-toggle="tooltip" title="{vtranslate('LBL_REQUERED_ALPHANUMERIC_SCANNER_NAME', $QUALIFIED_MODULE)}"><i class="fa-solid fa-info"></i></div>
                                        {elseif 'server' eq $FIELD_NAME}
                                            <div class="input-group-text" data-bs-toggle="tooltip" title="{vtranslate('LBL_GMAIL_SERVER', $QUALIFIED_MODULE)}: imap.gmail.com:993"><i class="fa-solid fa-info"></i></div>
                                        {/if}
                                    </div>
								{/if}
							</div>
						</div>
					{/foreach}
				</div>
			</div>
			<div class="modal-overlay-footer modal-footer py-3">
				<div class="container-fluid">
					<div class="row">
						<div class="col text-end">
							<a type="reset" class="btn btn-primary cancelLink cursorPointer" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL',$MODULE)}</a>
						</div>
						<div class="col">
							<button type="submit" class="btn btn-primary active nextStep" onclick="javascript:Settings_MailConverter_Edit_Js.firstStep()">{vtranslate('LBL_NEXT',$MODULE)}</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
</div>
{/strip}