{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if !empty($CUSTOM_VIEWS)}
		{include file=vtemplate_path('PicklistColorMap.tpl', $MODULE) LISTVIEW_HEADERS=$RELATED_HEADERS}
		<div class="relatedContainer container-fluid">
			<div class="mt-3 bg-body rounded">
				{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
				{assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
				<input type="hidden" name="emailEnabledModules" value=true />
				<input type="hidden" id="view" value="{$VIEW}" />
				<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
				<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}" />
				<input type="hidden" value="{$ORDER_BY}" id="orderBy">
				<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
				<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
				<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
				<input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
				<input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
				<input type="hidden" id="selectedIds" name="selectedIds" data-selected-ids={ZEND_JSON::encode($SELECTED_IDS)} />
				<input type="hidden" id="excludedIds" name="excludedIds" data-excluded-ids={ZEND_JSON::encode($EXCLUDED_IDS)} />
				<input type="hidden" id="recordsCount" name="recordsCount" />
				<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
				<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
				<input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>

				<div class="relatedHeader">
					<div class="container-fluid">
						<div class="btn-toolbar row py-3">
							<div class="col-lg-auto container-related-list-actions">
								{foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
									<div class="btn-group">
										{assign var=DROPDOWNS value=$RELATED_LINK->get('linkdropdowns')}
										{if !empty($DROPDOWNS) && (php7_count($DROPDOWNS) gt 0)}
											<a class="btn dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" data-hover="dropdown" data-delay="200" data-close-others="false" style="width:20px;height:18px;">
												<img title="{$RELATED_LINK->getLabel()}" alt="{$RELATED_LINK->getLabel()}" src="{vimage_path("{$RELATED_LINK->getIcon()}")}">
											</a>
											<ul class="dropdown-menu">
												{foreach item=DROPDOWN from=$DROPDOWNS}
													<li><a id="{$RELATED_MODULE_NAME}_relatedlistView_add_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($DROPDOWN['label'])}" class="{$RELATED_LINK->get('linkclass')}" href='javascript:void(0)' data-documentType="{$DROPDOWN['type']}" data-url="{$DROPDOWN['url']}" data-name="{$RELATED_MODULE_NAME}" data-firsttime="{$DROPDOWN['firsttime']}"><i class="icon-plus"></i>&nbsp;{vtranslate($DROPDOWN['label'], $RELATED_MODULE_NAME)}</a></li>
												{/foreach}
											</ul>
										{else}
											{assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
											{* setting button module attribute to Events or Calendar based on link label *}
											{assign var=LINK_LABEL value={$RELATED_LINK->get('linklabel')}}
											{if $RELATED_LINK->get('_linklabel') === '_add_event'}
												{assign var=RELATED_MODULE_NAME value='Events'}
											{elseif $RELATED_LINK->get('_linklabel') === '_add_task'}
												{assign var=RELATED_MODULE_NAME value='Calendar'}
											{/if}
											{if $IS_SELECT_BUTTON || $IS_CREATE_PERMITTED}
												<button type="button" module="{$RELATED_MODULE_NAME}" class="me-2 btn btn-outline-secondary
													{if $IS_SELECT_BUTTON eq true} selectRelation{else} addButton" name="addButton{/if}"
													{if $IS_SELECT_BUTTON eq true} data-moduleName="{$RELATED_LINK->get('_module')->get('name')}" {/if}
													{if ($RELATED_LINK->isPageLoadLink())}
														{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
														data-url="{$RELATED_LINK->getUrl()}{if $SELECTED_MENU_CATEGORY}&app={$SELECTED_MENU_CATEGORY}{/if}"
													{/if}
												>{if $IS_SELECT_BUTTON eq false}<i class="fa fa-plus me-2"></i>{/if}{$RELATED_LINK->getLabel()}</button>
											{/if}
										{/if}
									</div>
								{/foreach}
							</div>
							<div class="col-lg mb-2">
								<span class="customFilterMainSpan">
									{if php7_count($CUSTOM_VIEWS) gt 0}
										<select id="recordsFilter" class="select2 col-lg-8" data-placeholder="{vtranslate('LBL_SELECT_TO_LOAD_LIST', $RELATED_MODULE_NAME)}">
											<option></option>
											{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
												<optgroup label=' {if $GROUP_LABEL eq 'Mine'} &nbsp; {else} {vtranslate($GROUP_LABEL, $RELATED_MODULE_NAME)} {/if}' >
													{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
														<option id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" value="{$CUSTOM_VIEW->get('cvid')}" class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}" data-id="{$CUSTOM_VIEW->get('cvid')}">{if $CUSTOM_VIEW->get('viewname') eq 'All'}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)} {vtranslate($RELATED_MODULE_NAME, $RELATED_MODULE_NAME)}{else}{vtranslate($CUSTOM_VIEW->get('viewname'), $RELATED_MODULE_NAME)}{/if}{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ] {/if}</option>
													{/foreach}
												</optgroup>
											{/foreach}
										</select>
									{else}
										<input type="hidden" value="0" id="customFilter" />
									{/if}
								</span>
							</div>
							<div class="col-lg-auto text-end">
								{assign var=CLASS_VIEW_ACTION value='relatedViewActions'}
								{assign var=CLASS_VIEW_PAGING_INPUT value='relatedViewPagingInput'}
								{assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='relatedViewPagingInputSubmit'}
								{assign var=CLASS_VIEW_BASIC_ACTION value='relatedViewBasicAction'}
								{assign var=PAGING_MODEL value=$PAGING}
								{assign var=RECORD_COUNT value=$RELATED_RECORDS|@count}
								{assign var=PAGE_NUMBER value=$PAGING->get('page')}
								{include file=vtemplate_path('Pagination.tpl',$MODULE) SHOWPAGEJUMP=true}
							</div>
						</div>
					</div>
				</div>
				<div>
					{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
					<div class="hide messageContainer">
						<div class="text-center pb-3">
							<a id="selectAllMsgDiv" class="text-secondary" href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($RELATED_MODULE_NAME ,$RELATED_MODULE_NAME)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a>
						</div>
					</div>
					<div class="hide messageContainer pb-3">
						<div class="text-center">
							<a id="deSelectAllMsgDiv" class="text-secondary" href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a>
						</div>
					</div>
				</div>
				<div class="relatedContents table-container">
					<div class="bottomscroll-div">
						{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
						<table id="listview-table"  class="table table-borderless listview-table">
							<thead>
								<tr class="listViewHeaders bg-body-secondary">
									<th>
										<input type="checkbox" id="listViewEntriesMainCheckBox"/>
									</th>
									<th>
									</th>
									{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
										<th class="nowrap">
											<a href="javascript:void(0);" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
												{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
													<i class="fa {$FASORT_IMAGE}"></i>
												{else}
													<i class="fa fa-sort customsort"></i>
												{/if}
												<span class="mx-2">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</span>
												{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}
											</a>
											{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
											{/if}
										</th>
									{/foreach}
									<th class="nowrap">
										<a href="javascript:void(0);" class="listViewContentHeaderValues noSorting text-secondary text-nowrap">{vtranslate('Status', $RELATED_MODULE_NAME)}</a>
									</th>
								</tr>
								<tr class="searchRow">
									<th></th>
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
												{include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS[$HEADER_FIELD->getName()] USER_MODEL=$USER_MODEL}
												<input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$HEADER_FIELD->getName()]['comparator']}">
											{/if}
										</th>
									{/foreach}
									<th></th>
								</tr>
							</thead>
							{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
								<tr class="listViewEntries border-top" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
									<td class="{$WIDTHTYPE}">
										<input type="checkbox" value="{$RELATED_RECORD->getId()}" class="listViewEntriesCheckBox"/>
									</td>
									<td>
										<span class="actionImages btn-group">
											<a class="btn text-secondary" name="relationEdit" data-url="{$RELATED_RECORD->getEditViewUrl()}" href="javascript:void(0)">
												<i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i>
											</a>
											{if $IS_DELETABLE}
												<a class="btn text-secondary relationDelete">
													<i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i>
												</a>
											{/if}
										</span>
									</td>
									{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
										{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
										{assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
										<td class="{$WIDTHTYPE} relatedListEntryValues" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
											<span class="value text-truncate">
												{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
													<a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
												{elseif $HEADER_FIELD->get('uitype') eq '71' or $HEADER_FIELD->get('uitype') eq '72'}
													{assign var=CURRENCY_SYMBOL value=Vtiger_RelationListView_Model::getCurrencySymbol($RELATED_RECORD->get('id'), $HEADER_FIELD)}
													{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME))}
													{if $HEADER_FIELD->get('uitype') eq '72'}
														{assign var=CURRENCY_VALUE value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
													{/if}
													{if Users_Record_Model::getCurrentUserModel()->get('currency_symbol_placement') eq '$1.0'}
														{$CURRENCY_SYMBOL}{$CURRENCY_VALUE}
													{else}
														{$CURRENCY_VALUE}{$CURRENCY_SYMBOL}
													{/if}
												{elseif $HEADER_FIELD->getFieldDataType() eq 'picklist'}
													<span {if !empty($RELATED_LIST_VALUE)} class="picklist-color picklist-{$HEADER_FIELD->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}" {/if}> {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)} </span>
												{else}
													{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
												{/if}
											</span>
										</td>
									{/foreach}
									<td class="{$WIDTHTYPE}" nowrap>
										<div class="currentStatus more dropdown action">
											<div class="btn statusValue text-start" data-bs-toggle="dropdown">{vtranslate($RELATED_RECORD->get('status'),$MODULE)}</div>
											<a class="btn editRelatedStatus" data-bs-toggle="dropdown" title="{vtranslate('LBL_EDIT', $MODULE)}">
												<i class="fa-solid fa-chevron-down"></i>
											</a>
											<ul class="dropdown-menu dropdown-menu-end">
												{foreach key=STATUS_ID item=STATUS from=$STATUS_VALUES}
													<li id="{$STATUS_ID}" data-status="{vtranslate($STATUS, $MODULE)}">
														<a class="dropdown-item">{vtranslate($STATUS, $MODULE)}</a>
													</li>
												{/foreach}
											</ul>
										</div>
									</td>
								</tr>
							{/foreach}
						</table>
					</div>
				</div>
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
					},
				};
			})();
		</script>
	{else}
		{include file=vtemplate_path('RelatedList.tpl')}
	{/if}
{/strip}
