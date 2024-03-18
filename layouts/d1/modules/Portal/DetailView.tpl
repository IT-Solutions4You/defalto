{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Portal/views/Detail.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
	<div class="listViewPageDiv">
		<div class="container-fluid">
			<div class="row align-items-center">
				<div class="col-lg-8 pb-3 text-end">
					<label>{vtranslate('LBL_BOOKMARKS_LIST', $MODULE)}</label>
				</div>
				<div class="col-lg-4 pb-3">
					<select class="inputElement select2" id="bookmarksDropdown" name="bookmarksList">
						{foreach item=RECORD from=$RECORDS_LIST}
							<option value="{$RECORD['id']}" {if $RECORD['id'] eq $RECORD_ID}selected{/if}>{$RECORD['portalname']}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="row">
				<div class="listViewLoadingImageBlock hide modal noprint" id="loadingListViewModal">
					<img class="listViewLoadingImage" src="{vimage_path('loading.gif')}" alt="no-image" title="{vtranslate('LBL_LOADING', $MODULE)}"/>
					<p class="listViewLoadingMsg">{vtranslate('LBL_LOADING_LISTVIEW_CONTENTS', $MODULE)}........</p>
				</div>
				{if substr($URL, 0, 8) neq 'https://'}
					<div id="portalDetailViewHttpError" class="">
						<div class="col-lg-12 pb-3">{vtranslate('HTTP_ERROR', $MODULE)}</div>
					</div>
				{/if}
			</div>
			<div class="row">
				<div class="col-lg-12">
					<iframe src="{if substr($URL, 0, 4) neq 'http'}//{/if}{$URL}" class="border rounded w-100" style="height: 70vh" sandbox=""></iframe>
				</div>
			</div>
		</div>
	</div>
{/strip}
