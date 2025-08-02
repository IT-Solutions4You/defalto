{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="listViewPageDiv">
		<div class="row py-2">
			<div class="col-lg">
				<h4 class="searchModuleHeader">{vtranslate($MODULE, $MODULE)}</h4>
				<input type="hidden" name="search_module" value="{$MODULE}"/>
			</div>
			<div class="col-lg-auto">
				<div class="btn-group border-light-subtle border border-1" >
					<input type="hidden" name="pageNumber" value="{$PAGE_NUMBER}">
					<input type="hidden" name="recordsCount" value="{$RECORDS_COUNT}">
					<div class="pageNumbersText btn btn-light disabled">
						{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_to', $MODULE)} {$PAGING_MODEL->getRecordEndRange()} {vtranslate('LBL_OF',$MODULE)} {$RECORDS_COUNT}
					</div>
					<a href="#" class="previousPageButton navigationButton verticalAlignMiddle btn btn-light" data-start='{$PAGING_MODEL->getRecordStartRange()-$PAGING_MODEL->getPageLimit()}' {if !$PAGING_MODEL->isPrevPageExists()}disabled=""{/if}>
						<i class="fa fa-caret-left"></i>&nbsp;&nbsp;
					</a>
					<a href="#" class="nextPageButton navigationButton verticalAlignMiddle btn btn-light" data-start='{$PAGING_MODEL->getRecordEndRange()}' {if !$PAGING_MODEL->isNextPageExists()} disabled=""{/if}>
						<i class="fa fa-caret-right"></i>
					</a>
				</div>
			</div>
		</div>
		<div class="row">
			{include file="ListViewContents.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
		</div>
	</div>
{/strip}