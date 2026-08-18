{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{assign var=LISTVIEW_ENTRIES_COUNT value=$LISTVIEW_ENTRIES|@count}
	<div class="listViewPageDiv">
		<div class="row py-2">
			<div class="col-lg">
				<h4 class="searchModuleHeader">{vtranslate($MODULE, $MODULE)}</h4>
				<input type="hidden" name="search_module" value="{$MODULE}"/>
				<input type="hidden" name="recordsCount" value="{$RECORDS_COUNT}">
			</div>
			<div class="col-lg-auto">
				{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
				{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=false SHOWTOTALCOUNT=false}
			</div>
		</div>
		<div class="row">
			{include file="ListViewContents.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
		</div>
	</div>
{/strip}
