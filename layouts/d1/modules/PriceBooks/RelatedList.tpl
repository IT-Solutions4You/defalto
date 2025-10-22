{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/PriceBooks/views/Detail.php *}

{strip}
	{assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
	{if $RELATED_MODULE_NAME neq 'Products' && $RELATED_MODULE_NAME neq 'Services'}
		{include file='RelatedList.tpl'|vtemplate_path:'Vtiger'}
	{else}
		<div class="relatedContainer container-fluid">
			<div class="mt-3 bg-body rounded">
				<input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}"/>
				<input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}"/>
				<input type="hidden" value="{$ORDER_BY}" id="orderBy">
				<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
				<input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
				<input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
				<input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
				<input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
				<input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
				<input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
				{include file="partials/RelatedListHeader.tpl"|vtemplate_path:$RELATED_MODULE_NAME}
				<div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
					<div class="bottomscroll-div">
						{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
						<table id="listview-table" class="table table-borderless listview-table">
							<thead>
							<tr class="listViewHeaders bg-body-secondary">
								<th></th>
								{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
									<th nowrap {if $HEADER_FIELD@last} {/if}>
										<a href="javascript:void(0);" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('name')}">
											{if isset($FASORT_IMAGE) && $COLUMN_NAME eq $HEADER_FIELD->get('column')}
												<i class="fa {$FASORT_IMAGE}"></i>
											{else}
												<i class="fa fa-sort customsort"></i>
											{/if}
											<span class="mx-2">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE_NAME)}</span>
											{if $COLUMN_NAME eq $HEADER_FIELD->get('name')}<img class="{$SORT_IMAGE}">{/if}
										</a>
										{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
											<a href="#" class="removeSorting"><i class="fa fa-remove"></i></a>
										{/if}
									</th>
								{/foreach}
							</tr>
							<tr class="searchRow">
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
							</tr>
							</thead>
							{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
								{assign var=BASE_CURRENCY_DETAILS value=$RELATED_RECORD->getBaseCurrencyDetails()}
								<tr class="listViewEntries border-top" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
									<td style="width:100px">
										<span class="actionImages btn-group">
											{assign var=LISTPRICE value=Vtiger_Currency_UIType::transformEditViewDisplayValue($RELATED_RECORD->get('listprice'), null, true)}
											<a href="javascript:void(0);" data-url="index.php?module=PriceBooks&view=ListPriceUpdate&record={$PARENT_RECORD->getId()}&relid={$RELATED_RECORD->getId()}&currentPrice={$LISTPRICE}" class="editListPrice btn text-secondary" data-related-recordid="{$RELATED_RECORD->getId()}" data-list-price="{$LISTPRICE}">
												<i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i>
											</a>
											<a class="relationDelete btn text-secondary">
												<i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i>
											</a>
										</span>
									</td>
									{foreach item=HEADER_FIELD from=$RELATED_HEADERS}
										{assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
										<td nowrap class="fieldName_{$RELATED_HEADERNAME} {$WIDTHTYPE} ">
											{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
												<a class="fw-bold" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
											{elseif $HEADER_FIELD->getFieldDataType() eq 'currency'}
												{assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($RELATED_RECORD->getCurrencyId())}
												{CurrencyField::appendCurrencySymbol($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME), $CURRENCY_INFO['symbol'])}
											{else}
												{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
											{/if}
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
							},
						};
					})();
				</script>
			</div>
		</div>
	{/if}
{/strip}
