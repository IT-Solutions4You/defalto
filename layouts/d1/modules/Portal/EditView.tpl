{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<div class="modal-dialog">
		<div class="modal-content">
			<form class="form-horizontal" id="saveBookmark" method="POST" action="index.php">
				<input type="hidden" name="record" value="{$RECORD}" />
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="SaveAjax" />
				{if $RECORD}
					{assign var="TITLE" value= {vtranslate('LBL_EDIT_BOOKMARK', $MODULE)}}
				{else}
					{assign var="TITLE" value={vtranslate('LBL_ADD_NEW_BOOKMARK', $MODULE)}}
				{/if}
				{include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$TITLE}
				<div class="modal-body">
					<div class="block nameBlock row">
						<div class="col-lg-1"></div>
						<div class="col-lg-4">
							<label class="pull-right">{vtranslate('LBL_BOOKMARK_NAME', $MODULE)}&nbsp;<span class="redColor">*</span></label>
						</div>
						<div class="col-lg-5">
							<input type="text" name="bookmarkName" id="bookmarkName" class="inputElement form-control" {if $RECORD} value="{$BOOKMARK_NAME}" {/if} placeholder="{vtranslate('LBL_ENTER_BOOKMARK_NAME', $MODULE)}" data-rule-required="true"/>
						</div>
						<div class="col-lg-2"></div>
					</div>
					<br>
					<div class="block nameBlock row">
						<div class="col-lg-1"></div>
						<div class="col-lg-4">
							<label class="pull-right">{vtranslate('LBL_BOOKMARK_URL', $MODULE)}&nbsp;<span class="redColor">*</span></label>
						</div>
						<div class="col-lg-5">
							<input type="text" class="inputElement form-control" name="bookmarkUrl" id="bookmarkUrl" {if $RECORD} value="{$BOOKMARK_URL}" {/if} placeholder="{vtranslate('LBL_ENTER_URL', $MODULE)}" data-rule-required="true" data-rule-url="true"/>
						</div>
						<div class="col-lg-2"></div>
					</div>
				</div>
				<br>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>
	</div>
{/strip}
