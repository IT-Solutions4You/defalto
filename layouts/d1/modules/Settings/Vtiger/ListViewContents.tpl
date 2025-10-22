{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{if isset($ORDER_BY)}{$ORDER_BY}{/if}" id="orderBy">
	<input type="hidden" value="{if isset($SORT_ORDER)}{$SORT_ORDER}{/if}" id="sortOrder">
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{if isset($PAGE_NUMBER)}{$PAGE_NUMBER}{/if}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{if isset($LISTVIEW_ENTRIES_COUNT)}{$LISTVIEW_ENTRIES_COUNT}{/if}" id="noOfEntries">

	<div class="px-4 pb-4 listViewContents">
		<div id="listview-actions" class="listview-actions-container bg-body rounded py-3">
			{if $MODULE neq 'PickListDependency'}
				<div class="container-fluid pb-3 px-3">
					<div class="row align-items-center">
						<div class="col-md">
							{if $MODULE eq 'Tags'}
								<h4 class="m-0">{vtranslate('LBL_MY_TAGS', $QUALIFIED_MODULE)}</h4>
							{elseif $MODULE}
								<h4 class="m-0">{vtranslate($MODULE, $QUALIFIED_MODULE)}</h4>
							{/if}
						</div>
						{if $MODULE neq 'Currency' and $MODULE neq 'CronTasks'}
							<div class="col-md-auto">
								{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
								{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
							</div>
						{/if}
					</div>
				</div>
			{/if}
			<div class="list-content">
				<div>
					<div id="table-content" class="table-container">
						<table id="settings-listview-table" class="table table-borderless listview-table">
							{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
							{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
							<thead>
								<tr class="listViewContentHeader bg-body-secondary">
									{if $MODULE eq 'Profiles' or $MODULE eq 'Groups' or $MODULE eq 'Webforms' or $MODULE eq 'Currency' or $MODULE eq 'SMSNotifier'}
										<th class="text-secondary" style="width:25%">
											{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}
										</th>
									{elseif $MODULE neq 'Currency'}
										{if isset($SHOW_LISTVIEW_CHECKBOX) && $SHOW_LISTVIEW_CHECKBOX eq true}
											<th class="text-secondary">
												<span class="input">
													<input class="listViewEntriesMainCheckBox" type="checkbox">
												</span>
											</th>
										{/if}
									{/if}
									{if $MODULE eq 'Tags' or $MODULE eq 'CronTasks' or (isset($LISTVIEW_ACTIONS_ENABLED) && $LISTVIEW_ACTIONS_ENABLED eq true)}
										<th class="text-secondary">
											{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}
										</th>
									{/if}
									{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
										<th class="text-secondary" nowrap>
											<a {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues cursorPointer" data-nextsortorderval="{if isset($COLUMN_NAME) && $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>{vtranslate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}
												&nbsp;{if isset($COLUMN_NAME) && $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}<img class="{$SORT_IMAGE} icon-white">{/if}</a>&nbsp;
										</th>
									{/foreach}
								</tr>
							</thead>
							<tbody class="overflow-y">
								{foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
									<tr class="listViewEntries border-bottom" data-id="{$LISTVIEW_ENTRY->getId()}"
										{if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}"{/if}
										{if method_exists($LISTVIEW_ENTRY,'getRowInfo')}data-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::Encode($LISTVIEW_ENTRY->getRowInfo()))}"{/if}>
										<td>
											{include file="ListViewRecordActions.tpl"|vtemplate_path:$QUALIFIED_MODULE}
										</td>
										{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
											{assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
											{assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
											<td class="listViewEntryValue text-truncate {$WIDTHTYPE}" {if isset($WIDTH)}width="{$WIDTH}%"{/if} nowrap>
												{$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
												{if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
													</td>
												{/if}
											</td>
										{/foreach}
									</tr>
								{/foreach}
								{if $LISTVIEW_ENTRIES_COUNT eq '0'}
									<tr class="emptyRecordsDiv">
										{assign var=COLSPAN_WIDTH value={php7_count($LISTVIEW_HEADERS)+1}}
										<td colspan="{$COLSPAN_WIDTH}" style="vertical-align:inherit !important;">
											<center>{vtranslate('LBL_NO')} {vtranslate($MODULE, $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND')}</center>
										</td>
									</tr>
								{/if}
							</tbody>
						</table>
					</div>
					<div id="scroller_wrapper" class="bottom-fixed-scroll">
						<div id="scroller" class="scroller-div"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}
