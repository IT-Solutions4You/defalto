{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="px-4 pb-4" id="customRecordNumbering">
		<div class="detailViewContainer bg-body rounded">
			<form id="EditView" method="POST">
				<div class="blockData">
					<div class="container-fluid px-3 pt-3 border-bottom">
						<div class="row align-items-center">
							<div class="col-lg pb-3">
								<h4 class="m-0">{vtranslate('LBL_CUSTOMIZE_RECORD_NUMBERING', $QUALIFIED_MODULE)}</h4>
							</div>
							<div class="col-lg-auto pb-3">
								<button type="button" class="btn btn-outline-secondary addButton ms-auto" name="updateRecordWithSequenceNumber">{vtranslate('LBL_UPDATE_MISSING_RECORD_SEQUENCE', $QUALIFIED_MODULE)}</button>
							</div>
						</div>
					</div>
					<div class="container-fluid p-3">
						{assign var=DEFAULT_MODULE_DATA value=$DEFAULT_MODULE_MODEL->getModuleCustomNumberingData()}
						{assign var=DEFAULT_MODULE_NAME value=$DEFAULT_MODULE_MODEL->getName()}
						{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
						<div class="row form-group py-2">
							<div class="col-lg-4 control-label fieldLabel">
								<label>{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-lg-4 fieldValue {$WIDTHTYPE}">
								<select class="select2 inputElement" name="sourceModule">
									{foreach key=index item=MODULE_MODEL from=$SUPPORTED_MODULES}
										{assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
										<option value={$MODULE_NAME} {if $MODULE_NAME eq $DEFAULT_MODULE_NAME} selected {/if}>
											{vtranslate($MODULE_NAME, $MODULE_NAME)}
										</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row form-group py-2">
							<div class="col-lg-4 control-label fieldLabel">
								<label>{vtranslate('LBL_USE_PREFIX', $QUALIFIED_MODULE)}</label>
							</div>
							<div class="col-lg-4 fieldValue {$WIDTHTYPE}">
								<input type="text" id="prefix" class="inputElement form-control" value="{$DEFAULT_MODULE_DATA['prefix']}" data-old-prefix="{$DEFAULT_MODULE_DATA['prefix']}" name="prefix"/>
							</div>
						</div>
						<div class="row form-group py-2">
							<div class="col-lg-4 control-label fieldLabel">
								<label>
									<b>{vtranslate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}</b>&nbsp;<span class="redColor">*</span>
								</label>
							</div>
							<div class="col-lg-4 fieldValue {$WIDTHTYPE}">
								<input type="text" value="{$DEFAULT_MODULE_DATA['sequenceNumber']}" class="inputElement form-control" id="sequence" data-old-sequence-number="{$DEFAULT_MODULE_DATA['sequenceNumber']}" data-rule-required="true" data-rule-positive="true" data-rule-wholeNumber="true" name="sequenceNumber"/>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer py-3 border-top">
					<div class="container-fluid">
						<div class="row">
							<div class="col text-end">
								<a class="btn btn-primary cancelLink" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
							</div>
							<div class="col">
								<button class="btn btn-primary active saveButton" type="submit" disabled="disabled">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
{/strip}