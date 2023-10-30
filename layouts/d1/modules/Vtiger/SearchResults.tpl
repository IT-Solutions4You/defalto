{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Vtiger/resources/List.js')}"></script>
	<script type="text/javascript" src="{vresource_url('layouts/d1/modules/Vtiger/resources/SearchList.js')}"></script>
	<div id="searchResults-container" class="container-fluid">
		<div class="searchHeader row py-2 border-bottom border-1">
			<div class="col"></div>
			<div class="col-auto ms-auto">
				<div class="overlay-close">
					<button type="button" class="btn btn-light border-light-subtle border-1 border" aria-label="Close" data-bs-dismiss="modal">
						<i class="fa fa-xmark"></i>
					</button>
				</div>
			</div>
		</div>
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
{/strip}