{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Vtiger/resources/List.js')}"></script>
	<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Vtiger/resources/SearchList.js')}"></script>
	<div id="searchResults-container">
		<div class="modal-header">
			<h5 class="modal-title">{vtranslate('LBL_SEARCH', $QUALIFIED_MODULE)}</h5>
			<button type="button" class="btn btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
		</div>
		<div class="container-fluid">
			<div class="searchResults row">
				<input type="hidden" value="{$SEARCH_VALUE|escape:"html"}" id="searchValue">
				<div class="scrollableSearchContent p-0">
					<div class="container-fluid moduleResults-container py-3">
						<input type="hidden" name="groupStart" value="{$GROUP_START}" class="groupStart"/>
						{assign var=NORECORDS value=false}
						{foreach key=MODULE item=LISTVIEW_MODEL from=$MATCHING_RECORDS}
							{assign var=RECORDS_COUNT value=$LISTVIEW_MODEL->recordsCount}
							{assign var=PAGING_MODEL value=$LISTVIEW_MODEL->pagingModel}
							{assign var=LISTVIEW_HEADERS value=$LISTVIEW_MODEL->listViewHeaders}
							{assign var=LISTVIEW_ENTRIES value=$LISTVIEW_MODEL->listViewEntries}
							{assign var=MODULE_MODEL value=$LISTVIEW_MODEL->getModule()}
							{assign var=QUICK_PREVIEW_ENABLED value=$MODULE_MODEL->isQuickPreviewEnabled()}
							{include file="ModuleSearchResults.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
							<br>
						{/foreach}
						{if !$MATCHING_RECORDS}
							<div class="emptyRecordsDiv">
								<div class="emptyRecordsContent">
									{vtranslate("LBL_NO_RECORDS_FOUND")}
								</div>
							</div>
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}