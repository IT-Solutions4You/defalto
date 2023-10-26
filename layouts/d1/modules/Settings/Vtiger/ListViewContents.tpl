{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}

{strip}
	<input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
	<input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
	<input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
	<input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type="hidden" value="{$ORDER_BY}" id="orderBy">
	<input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
	<input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
	<input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
	<input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
	<input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">

	<div class="px-4 pb-4 listViewContents">
		<div id="listview-actions" class="listview-actions-container bg-body rounded py-3">
			{if $MODULE neq 'Currency' and $MODULE neq 'PickListDependency' and $MODULE neq 'CronTasks'}
				<div class="container-fluid">
					<div class="row p-3">
						<div class="col-md">
							{if $MODULE eq 'Tags'}
								<h4>{vtranslate('LBL_MY_TAGS', $QUALIFIED_MODULE)}</h4>
							{/if}
						</div>
						<div class="col-md-auto">
							{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
							{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
						</div>
					</div>
				</div>
			{/if}
			<div class="list-content">
				<div>
					<div id="table-content" class="table-container">
						<table id="listview-table" class="table table-borderless listview-table">
							{assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
							{assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
							<thead>
								<tr class="listViewContentHeader bg-body-secondary">
									{if $MODULE eq 'Profiles' or $MODULE eq 'Groups' or $MODULE eq 'Webforms' or $MODULE eq 'Currency' or $MODULE eq 'SMSNotifier'}
										<th class="text-secondary" style="width:25%">
											{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}
										</th>
									{elseif $MODULE neq 'Currency'}
										{if $SHOW_LISTVIEW_CHECKBOX eq true}
											<th class="text-secondary">
												<span class="input">
													<input class="listViewEntriesMainCheckBox" type="checkbox">
												</span>
											</th>
										{/if}
									{/if}
									{if $MODULE eq 'Tags' or $MODULE eq 'CronTasks' or $LISTVIEW_ACTIONS_ENABLED eq true}
										<th class="text-secondary">
											{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}
										</th>
									{/if}
									{foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
										<th class="text-secondary" nowrap>
											<a {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues cursorPointer" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>{vtranslate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}
												&nbsp;{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}<img class="{$SORT_IMAGE} icon-white">{/if}</a>&nbsp;
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
											<td class="listViewEntryValue text-truncate {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
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
