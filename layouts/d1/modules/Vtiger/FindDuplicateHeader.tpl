{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="container-fluid">
	<div class="row pt-3">
		<div class="col-12 clearfix">
			{assign var=HEADER_TITLE value={vtranslate('LBL_DUPLICATE')|cat:' '|cat:vtranslate($MODULE, $MODULE)}}
			<h3 class="p-0 mb-3">{$HEADER_TITLE}</h3>
			<div class="d-inline-block">
				<div class="alert alert-info">
					<span class="fa fa-info-circle icon"></span>
					<span class="message">{vJsTranslate('JS_ALLOWED_TO_SELECT_MAX_OF_THREE_RECORDS',$MODULE)}</span>
				</div>
			</div>
		</div>
	</div>
	<div class="row pb-3">
		<div class="col-lg-auto">
			{if $LISTVIEW_ENTRIES_COUNT > 0}
				{foreach item=LISTVIEW_BASICACTION from=$LISTVIEW_LINKS}
					<button id="{$MODULE}_listView_basicAction_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($LISTVIEW_BASICACTION->getLabel())}" class="btn btn-danger pull-left"
							{if stripos($LISTVIEW_BASICACTION->getUrl(), 'javascript:')===0} onclick='{$LISTVIEW_BASICACTION->getUrl()|substr:strlen("javascript:")};'{else} onclick='window.location.href="{$LISTVIEW_BASICACTION->getUrl()}"'{/if}>
						<strong>{vtranslate($LISTVIEW_BASICACTION->getLabel(), $MODULE)}</strong>
					</button>
				{/foreach}
			{/if}
		</div>
		<div class="col-lg">
			<div class="select-deselect-container">
				<div class="hide messageContainer">
					<div class="text-center">
						<a id="selectAllMsgDiv" href="#">{vtranslate('LBL_SELECT_ALL',$MODULE)}&nbsp;{vtranslate($MODULE ,$MODULE)}&nbsp;(<span id="totalRecordsCount" value=""></span>)</a>
					</div>
				</div>
				<div class="hide messageContainer">
					<div class="text-center">
						<a id="deSelectAllMsgDiv" href="#">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-auto">
			{assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
			{include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
		</div>
	</div>
</div>