{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
	{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE LISTVIEW_HEADERS=$RELATED_HEADERS}
	<div class="relatedContainer container-fluid">
		<div class="rounded bg-body mt-3">
			{assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
			<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
			<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
			<input type="hidden" value="{$ORDER_BY}" id="orderBy">
			<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
			<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
			<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
			<input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
			<input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
			<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
			<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
			<input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>

			{include file="partials/RelatedListHeader.tpl"|vtemplate_path:$RELATED_MODULE_NAME}
			{if $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS}
				<div data-module="{$MODULE}" class="px-3 pb-3">
					{assign var=IS_VIEWABLE value=$PARENT_RECORD->isBundleViewable()}
					<input type="hidden" class="isShowBundles" value="{$IS_VIEWABLE}">
					<label class="showBundlesInInventory checkbox form-check">
						<input type="checkbox" class="form-check-input" {if $IS_VIEWABLE}checked{/if} value="{$IS_VIEWABLE}">
						<span class="form-check-label">{vtranslate('LBL_SHOW_BUNDLE_IN_INVENTORY', $MODULE)}</span>
					</label>
				</div>
			{/if}

			<div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
				<div class="bottomscroll-div">
					<table id="listview-table" class="table listview-table table-borderless">
						<thead>
							<tr class="listViewHeaders bg-body-secondary">
								<th></th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th class="nowrap">
										{if $HEADER_FIELD->get('column') eq "access_count" or $HEADER_FIELD->get('column') eq "idlists"}
											<a href="javascript:void(0);" class="noSorting text-secondary">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</a>
										{else}
											<a href="javascript:void(0);" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
												{if isset($FASORT_IMAGE) && $COLUMN_NAME eq $HEADER_FIELD->get('column')}
													<i class="fa {$FASORT_IMAGE}"></i>
												{else}
													<i class="fa fa-sort customsort"></i>
												{/if}
												<span class="mx-2">
													{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}
												</span>
												{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}
											</a>
											{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
											{/if}
										{/if}
									</th>
								{/foreach}
							</tr>
							<tr class="searchRow border-bottom">
								<th class="inline-search-btn">
									<button class="btn text-secondary" data-trigger="relatedListSearch" title="{vtranslate("LBL_SEARCH",$MODULE)}">
										<i class="fa fa-search"></i>
									</button>
								</th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th>
										{if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
										{else}
											{assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
											{assign var=SEARCH_DETAILS_FIELD_INFO value=['searchValue' => '', 'comparator' => '']}
											{if isset($SEARCH_DETAILS[$HEADER_FIELD->getName()])}
											{assign var=SEARCH_DETAILS_FIELD_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
											{/if}
											{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS_FIELD_INFO USER_MODEL=$USER_MODEL}
											<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS_FIELD_INFO['comparator']}">
										{/if}
									</th>
								{/foreach}
							</tr>
						</thead>
						{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
							<tr class="listViewEntries border-bottom" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
								<td class="related-list-actions text-secondary">
									<span class="actionImages btn-group">
                                        {if $RELATED_RECORD->isViewable()}
                                            <a class="btn btn-sm text-secondary js-reference-display-value" href="{$RELATED_RECORD->getDetailViewUrl()}" title="{vtranslate('LBL_VIEW', $MODULE)}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        {/if}
										{if $IS_EDITABLE && $RELATED_RECORD->isEditable()}
											{if $RELATED_MODULE_NAME eq 'PriceBooks'}
												{assign var=LISTPRICE value=Vtiger_Currency_UIType::transformEditViewDisplayValue($RELATED_RECORD->get('listprice'), null, true)}
												<a data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}" class="editListPrice cursorPointer btn btn-sm text-secondary" data-related-recordid='{$RELATED_RECORD->getId()}' data-list-price={$LISTPRICE}>
													<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $MODULE)}"></i>
												</a>
											{elseif $MODULE eq 'Products' && $RELATED_MODULE_NAME eq 'Products' && $TAB_LABEL === 'Product Bundles' && $RELATED_LIST_LINKS && $PARENT_RECORD->isBundle()}
												{assign var=quantity value=$RELATED_RECORD->get($RELATION_FIELD->getName())}
												<a class="quantityEdit btn btn-sm text-secondary" data-url="index.php?module=Products&view=SubProductQuantityUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentQty={$quantity}" onclick ="Products_Detail_Js.triggerEditQuantity('index.php?module=Products&view=SubProductQuantityUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentQty={$quantity}');if(event.stopPropagation){ldelim}event.stopPropagation();{rdelim}else{ldelim}event.cancelBubble=true;{rdelim}">
													<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $MODULE)}"></i>
												</a>
											{else}
												<a name="relationEdit" class="btn btn-sm text-secondary" data-url="{$RELATED_RECORD->getEditViewUrl()}">
													<i class="fa fa-pencil" title="{vtranslate('LBL_EDIT', $MODULE)}"></i>
												</a>
											{/if}
										{/if}
										{if $IS_DELETABLE}
                                            <button type="button" class="btn btn-sm text-secondary" onclick='app.controller().relationDeleteRecord("{$RELATED_RECORD->getDeleteUrl()}")'>
                                                <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="fa-solid fa-trash"></i>
                                            </button>
                                            {if isset($RELATION_MODEL) && $RELATION_MODEL->isUnlinkable()}
                                                <a class="btn btn-sm text-secondary relationDelete" data-message="LBL_UNLINK_CONFIRMATION">
                                                    <i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i>
                                                </a>
                                            {/if}
										{/if}
									</span>
								</td>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
									{assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
									<td class="relatedListEntryValues" title="{strip_tags((isset($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)))?$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME):"")}"
										data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
										<span class="value text-truncate">
											{if $RELATED_MODULE_NAME eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
												<div class="text-center">{$RELATED_RECORD->get($RELATED_HEADERNAME)}</div>
                                            {else}
                                                {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
													<a class="fw-bold" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
												{elseif $RELATED_HEADERNAME eq 'access_count'}
													{$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
												{elseif $HEADER_FIELD->getFieldDataType() eq 'currency'}
													{assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($RELATED_RECORD->getCurrencyId())}
													{CurrencyField::appendCurrencySymbol($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME), $CURRENCY_INFO['symbol'])}
												{elseif $HEADER_FIELD->getFieldDataType() eq 'picklist' && $HEADER_FIELD->isPicklistColorSupported()}
													{assign var=PICKLIST_FIELD_ID value={$HEADER_FIELD->getId()}}
													<span class="py-1 px-2 rounded picklist-color picklist-{$PICKLIST_FIELD_ID}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}"> {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} </span>
												{else}
													{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
													{* Documents list view special actions "view file" and "download file" *}
													{if $RELATED_MODULE_NAME eq 'Documents' && $RELATED_HEADERNAME eq 'filename' && isPermitted($RELATED_MODULE_NAME, 'DetailView', $RELATED_RECORD->getId()) eq 'yes'}
														<span class="actionImages btn-group">
															{assign var=RECORD_ID value=$RELATED_RECORD->getId()}
															{assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
															{if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
																<a name="viewfile" href="javascript:void(0)" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)"><i title="{vtranslate('LBL_VIEW_FILE', $RELATED_MODULE_NAME)}" class="icon-picture alignMiddle"></i></a>&nbsp;
                                                            {/if}
                                                            {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
																<a name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}"><i title="{vtranslate('LBL_DOWNLOAD_FILE', $RELATED_MODULE_NAME)}" class="icon-download-alt alignMiddle"></i></a>&nbsp;
                                                            {/if}
														</span>
													{/if}
												{/if}
											{/if}
										</span>
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</table>
				</div>
			</div>
			<script type="text/javascript">
				var related_uimeta = (function () {
					var fieldInfo = {$RELATED_FIELDS_INFO};
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
		</div>
	</div>
{/strip}