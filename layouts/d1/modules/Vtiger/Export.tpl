{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<form id="exportForm" class="fc-overlay-modal modal-content form-horizontal" method="post" action="index.php">
		<input type="hidden" name="module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="action" value="ExportData" />
		<input type="hidden" name="viewname" value="{$VIEWID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
		<input type="hidden" id="page" name="page" value="{$PAGE}" />
		<input type="hidden" name="search_key" value= "{if isset($SEARCH_KEY)}{$SEARCH_KEY}{/if}" />
		<input type="hidden" name="operator" value="{$OPERATOR}" />
		<input type="hidden" name="search_value" value="{if isset($ALPHABET_VALUE)}{$ALPHABET_VALUE}{/if}" />
		<input type="hidden" name="search_params" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SEARCH_PARAMS))}' />
		<input type="hidden" name="orderby" value="{$ORDER_BY}" />
		<input type="hidden" name="sortorder" value="{$SORT_ORDER}" />
		<input type="hidden" name="tag_params" value='{Zend_JSON::encode($TAG_PARAMS)}' />
		{if $SOURCE_MODULE eq 'Documents'}
			<input type="hidden" name="folder_id" value="{$FOLDER_ID}"/>
			<input type="hidden" name="folder_value" value="{$FOLDER_VALUE}"/>
		{/if}
		{assign var=TITLE value=vtranslate('LBL_EXPORT_RECORDS',$MODULE)}
		{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
		<div class="modal-body bg-body-secondary">
			<div class="container-fluid rounded bg-body p-3">
				<div class="datacontent row">
					<div class="col-lg">
						<div class="well exportContents">
							<div>
								<h4>{vtranslate('LBL_EXPORT_DATA',$MODULE)}</h4>
							</div>
							<div class="py-2">
								<input type="radio" name="mode" value="ExportSelectedRecords" id="group1" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if} />
								<label class="ms-2" for="group1">{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}</label>
								{if empty($SELECTED_IDS)}
									<span class="text-danger ms-2">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>
								{/if}
								<input type="hidden" class="isSelectedRecords" value="{if $SELECTED_IDS}1{else}0{/if}" >
							</div>
							<div class="py-2">
								<input type="radio" name="mode" value="ExportCurrentPage" id="group2" />
								<label class="ms-2" for="group2">{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}</label>
							</div>
							<div class="py-2">
								<input type="radio" name="mode" value="ExportAllData" id="group3" {if empty($SELECTED_IDS)} checked="checked" {/if} />
								<label class="ms-2" for="group3">{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}</label>
							</div>
							{if isset($MULTI_CURRENCY)}
								<div class="py-2">
									<i class="icon-question-sign" data-bs-toggle="tooltip" title="{vtranslate('LBL_EXPORT_CURRENCY_TOOLTIP_TEXT',$MODULE)}"></i>
									<strong class="ms-2">{vtranslate('LBL_EXPORT_LINEITEM_CURRENCY',$MODULE)}:</strong>
								</div>
								<div class="py-2 form-check">
									<input class="form-check-input" type="radio" name="selected_currency" value="UserCurrency" checked="checked"/>
									<span class="ms-2">{vtranslate('LBL_EXPORT_USER_CURRENCY',$MODULE)}</span>
								</div>
								<div class="py-2 form-check">
									<input class="form-check-input" type="radio" name="selected_currency" value="RecordCurrency"/>
									<span class="ms-2">{vtranslate('LBL_EXPORT_RECORD_CURRENCY',$MODULE)}</span>
								</div>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal-overlay-footer modal-footer">
			<div class="container-fluid">
				<div class="row">
					<div class="col text-end">
						<a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="#">{vtranslate('LBL_CANCEL', $MODULE)}</a>
					</div>
					<div class="col">
						<button type="submit" class="btn btn-primary active">{vtranslate('LBL_EXPORT', 'Vtiger')}&nbsp;{vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}</button>
					</div>
				</div>
			</div>
		</div>
	</form>
{/strip}