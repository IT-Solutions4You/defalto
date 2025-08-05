{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="listViewEntriesDiv contents-bottomscroll">
		<div class="bottomscroll-div">
			<div class="fieldBlockContainer mt-3 bg-body rounded">
				<div class="fieldBlockHeader p-3 border-bottom">
					<span class="ms-3 fs-4 fw-bold">{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)} {vtranslate('LBL_FIELD_INFORMATION', $QUALIFIED_MODULE)}</span>
				</div>
				<div class="p-3">
					<table class="table table-borderless">
						<thead>
							<tr>
								<td><b>{vtranslate('LBL_MANDATORY', $QUALIFIED_MODULE)}</b></td>
								<td><b>{vtranslate('LBL_HIDDEN', $QUALIFIED_MODULE)}</b></td>
								<td><b>{vtranslate('LBL_FIELD_NAME', $QUALIFIED_MODULE)}</b></td>
								<td><b>{vtranslate('LBL_OVERRIDE_VALUE', $QUALIFIED_MODULE)}</b></td>
								<td><b>{vtranslate('LBL_WEBFORM_REFERENCE_FIELD', $QUALIFIED_MODULE)}</b></td>
							</tr>
						</thead>
						<tbody>
							{foreach item=FIELD_MODEL key=FIELD_NAME from=$SELECTED_FIELD_MODELS_LIST}
								{assign var=FIELD_STATUS value="{$FIELD_MODEL->get('required')}"}
								{assign var=FIELD_HIDDEN_STATUS value="{$FIELD_MODEL->get('hidden')}"}
								<tr class="border-top">
									<td>
										{if ($FIELD_STATUS eq 1) or ($FIELD_MODEL->isMandatory(true))}
											{assign var=FIELD_VALUE value="LBL_YES"}
										{else}
											{assign var=FIELD_VALUE value="LBL_NO"}
										{/if}
										{vtranslate($FIELD_VALUE, $SOURCE_MODULE)}
									</td>
									<td>
										{if $FIELD_HIDDEN_STATUS eq 1}
											{assign var=FIELD_VALUE value="LBL_YES"}
										{else}
											{assign var=FIELD_VALUE value="LBL_NO"}
										{/if}
										{vtranslate($FIELD_VALUE, $SOURCE_MODULE)}
									</td>
									<td>
										{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
										{if $FIELD_MODEL->isMandatory()}
											<span class="redColor">*</span>
										{/if}
									</td>
									<td>
										{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
											{assign var=EXPLODED_FIELD_VALUE value = 'x'|explode:$FIELD_MODEL->get('defaultvalue')}
											{assign var=FIELD_VALUE value=$EXPLODED_FIELD_VALUE[1]}
											{if !isRecordExists($FIELD_VALUE)}
												{assign var=FIELD_VALUE value=0}
											{/if}
										{else}
											{assign var=FIELD_VALUE value=$FIELD_MODEL->get('defaultvalue')}
										{/if}
										{$FIELD_MODEL->getDisplayValue($FIELD_VALUE, $RECORD->getId(), $RECORD)}
									</td>
									<td>
										{if Settings_Webforms_Record_Model::isCustomField($FIELD_MODEL->get('name'))}
											{vtranslate('LBL_LABEL', $QUALIFIED_MODULE)} : {vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
										{else}
											{vtranslate({$FIELD_MODEL->get('neutralizedfield')}, $SOURCE_MODULE)}
										{/if}
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	{if Vtiger_Functions::isDocumentsRelated($SOURCE_MODULE) && count($DOCUMENT_FILE_FIELDS)}
		<div class="listViewEntriesDiv contents-bottomscroll">
			<div class="bottomscroll-div">
				<div class="fieldBlockContainer mt-3 bg-body rounded">
					<div class="fieldBlockHeader p-3 border-bottom">
						<span class="ms-3 fs-4 fw-bold">{vtranslate('LBL_UPLOAD_DOCUMENTS', $QUALIFIED_MODULE)}</span>
					</div>
					<div class="p-3">
						<div>
							<table class="table table-borderless">
								<tr>
									<td><b>{vtranslate('LBL_FIELD_LABEL', $QUALIFIED_MODULE)}</b></td>
									<td><b>{vtranslate('LBL_MANDATORY', $QUALIFIED_MODULE)}</b></td>
								</tr>
								{foreach from=$DOCUMENT_FILE_FIELDS item=DOCUMENT_FILE_FIELD}
									<tr class="border-top">
										<td>{$DOCUMENT_FILE_FIELD['fieldlabel']}</td>
										<td>{if $DOCUMENT_FILE_FIELD['required']}{vtranslate('LBL_YES', $QUALIFIED_MODULE)}{else}{vtranslate('LBL_NO', $QUALIFIED_MODULE)}{/if}</td>
									</tr>
								{/foreach}
							</table>
						</div>
						<div>
							<div class="vt-default-callout vt-info-callout" style="margin: 0;">
								<h4 class="vt-callout-header">
									<span class="fa fa-info-circle"></span>
									<span class="ms-2">{vtranslate('LBL_INFO', $QUALIFIED_MODULE)}</span>
								</h4>
								<div>
									{vtranslate('LBL_FILE_FIELD_INFO', $QUALIFIED_MODULE, vtranslate("SINGLE_$SOURCE_MODULE", $SOURCE_MODULE))}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>
{/strip}