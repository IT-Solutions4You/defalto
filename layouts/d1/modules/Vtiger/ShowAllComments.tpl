{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
<form id="detailView" method="POST" class="container-fluid mt-3">
	{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
	{assign var="PRIVATE_COMMENT_MODULES" value=Vtiger_Functions::getPrivateCommentModules()}
	{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
	{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

	<div class="commentContainer commentsRelatedContainer bg-white rounded p-3">
		{if $IS_CREATABLE}
			<div class="commentTitle">
				<div class="p-3 rounded bg-body-secondary addCommentBlock">
					<div class="row mb-3">
						<div class="col pe-0">
							<div class="commentTextArea">
								<textarea name="commentcontent" class="commentcontent form-control"  placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
							</div>
						</div>
						<div class="col-auto">
							<button class="btn btn-primary active px-5 saveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
						</div>
					</div>
					<div class="row">
						{if $FIELD_MODEL->getProfileReadWritePermission()}
							<div class="col">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
							</div>
						{/if}
						<div class="col-auto ms-auto">
							{if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
								<input type="checkbox" id="is_private" checked>&nbsp;&nbsp;{vtranslate('LBL_INTERNAL_COMMENT')}&nbsp;
								<i class="fa fa-question-circle cursorPointer" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>&nbsp;&nbsp;
							{/if}
						</div>
					</div>
				</div>
			</div>
		{/if}
		<div class="showcomments container-fluid">
			<div class="recentCommentsHeader row">
				{if $MODULE_NAME ne 'Leads'}
					<div class="commentHeader text-end col-auto ms-auto p-3">
						<div class="form-check form-switch form-check-reverse">
							<label for="rollupcomments" class="form-check-label">
								<span class="me-2">{vtranslate('LBL_ROLL_UP',$QUALIFIED_MODULE)} &nbsp;</span>
								<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>&nbsp;&nbsp;
							</label>
							<input type="checkbox" class="form-check-input bootstrap-switch" id="rollupcomments" hascomments="1" startindex="{$STARTINDEX}" data-view="relatedlist" rollupid="{$ROLLUPID}" rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$MODULE_RECORD}" checked data-on-color="success"/>
						</div>
					</div>
				{/if}
			</div>
			<div class="commentsList commentsBody marginBottom15">
				{include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL IS_CREATABLE=$IS_CREATABLE IS_EDITABLE=$IS_EDITABLE}
			</div>

			<div class="hide basicAddCommentBlock container-fluid rounded bg-body-secondary p-3 mb-3">
				<div class="row">
					<div class="col pe-0 commentTextArea">
						<textarea name="commentcontent" class="commentcontent form-control" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
					</div>
					<div class="col-auto pe-0">
						<button class="btn btn-primary active saveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
					</div>
					<div class="col-auto">
						<a href="javascript:void(0);" class="btn btn-danger closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
					</div>
				</div>
				<div class="row">
					{if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
						<input type="checkbox" id="is_private" checked>&nbsp;&nbsp;{vtranslate('LBL_INTERNAL_COMMENT')}&nbsp;&nbsp;
					{/if}
				</div>
			</div>

			<div class="hide basicEditCommentBlock container-fluid rounded bg-body-secondary p-3 mb-3">
				<div class="row">
					<div class="commentTextArea col pe-0">
						<input type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control mb-2"/>
						<textarea name="commentcontent" class="commentcontenthidden form-control"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
						<input type="hidden" name="is_private">
					</div>
					<div class="col-auto pe-0">
						<button class="btn btn-primary active saveComment" type="button" data-mode="edit">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
					</div>
					<div class="col-auto">
						<a href="javascript:void(0);" class="btn btn-danger closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</form>
{/strip}