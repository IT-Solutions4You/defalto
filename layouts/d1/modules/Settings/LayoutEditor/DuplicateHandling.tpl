{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<div class="duplicateHandlingDiv padding20">
		<form class="duplicateHandlingForm">
			<input type="hidden" name="_source" value="{$SOURCE}" />
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" id="sourceModule" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="module" value="LayoutEditor" />
			<input type="hidden" name="action" value="Field" />
			<input type="hidden" name="mode" value="updateDuplicateHandling" />
			<div class="vt-default-callout vt-info-callout">
				<h4 class="vt-callout-header"><span class="fa fa-info-circle"></span>&nbsp; Info </h4>
				<div class="duplicationInfoMessage">{vtranslate('LBL_DUPLICATION_INFO_MESSAGE', $QUALIFIED_MODULE)}</div>
			</div>
			<div class="py-3">
				<input type="hidden" class="rule" name="rule" value="">
				<input type="checkbox" class="duplicateCheck" data-on-color="success" data-off-color="danger" data-current-rule="{$SOURCE_MODULE_MODEL->allowDuplicates}" {if !$SOURCE_MODULE_MODEL->isFieldsDuplicateCheckAllowed()}readonly="readonly"{/if} data-on-text="{vtranslate('LBL_YES', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_NO', $QUALIFIED_MODULE)}" />
				<span class="ms-3">{vtranslate('LBL_DUPLICATE_CHECK', $QUALIFIED_MODULE)}</span>
			</div>
			<div class="duplicateHandlingContainer show">
				<div class="fieldsBlock">
					<div class="py-3">
						<b>{vtranslate('LBL_SELECT_FIELDS_FOR_DUPLICATION', $QUALIFIED_MODULE)}</b>
					</div>
					<select class="select" id="fieldsList" multiple name="fieldIdsList[]" data-placeholder="{vtranslate('LBL_SELECT_FIELDS', $QUALIFIED_MODULE)}" data-rule-required="true" >
						{foreach key=BLOCK_LABEL item=FIELD_MODELS from=$FIELDS}
							<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
								{foreach key=KEY item=FIELD_MODEL from=$FIELD_MODELS}
									<option {if $FIELD_MODEL->isUniqueField()}selected=""{/if} value={$FIELD_MODEL->getId()}>
										{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
									</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<div class="py-3">
						<b>{vtranslate('LBL_MAX_3_FIELDS', $QUALIFIED_MODULE)}</b>
					</div>
				</div>
				{if $SOURCE_MODULE_MODEL->isSyncable}
					<div class="ruleBlock">
						<div class="py-3">
							<b class="me-2">{vtranslate('LBL_DUPLICATES_IN_SYNC_MESSAGE', $QUALIFIED_MODULE)}</b>
						</div>
						<div class="py-3 d-flex">
							<div class="w-50 me-3">
								<select class="select actionsList" name="syncActionId">
									{foreach key=ACTION_ID item=ACTION_NAME from=$ACTIONS}
										<option {if $SOURCE_MODULE_MODEL->syncActionForDuplicate eq $ACTION_ID}selected=""{/if} value="{$ACTION_ID}">{vtranslate($ACTION_NAME, $QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</div>
							<div class="input-info-addon syncMessage">
								<a class="fa fa-info-circle" data-toggle="tooltip" data-html="true" data-placement="right" title="{vtranslate('LBL_SYNC_TOOLTIP_MESSAGE', $QUALIFIED_MODULE)}"></a>
							</div>
						</div>
					</div>
				{/if}
				<div class="formFooter py-3 hide">
					<div class="container">
						<div class="row">
							<div class="col text-end">
								<a class="btn btn-primary cancelLink" type="reset" data-bs-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
							</div>
							<div class="col">
								<button class="btn btn-primary active" type="submit" name="saveButton"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
{/strip}