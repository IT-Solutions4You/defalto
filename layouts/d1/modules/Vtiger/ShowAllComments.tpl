{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<form id="detailView" method="POST" class="container-fluid mt-3">
	{assign var=COMMENT_TEXTAREA_DEFAULT_ROWS value="2"}
	{assign var=PRIVATE_COMMENT_MODULES value=Vtiger_Functions::getPrivateCommentModules()}
	{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
	{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}
	<div class="commentContainer commentsRelatedContainer bg-body rounded p-3">
		{include file='comments/Form.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='saveComment'}
		{include file='comments/Header.tpl'|vtemplate_path:$MODULE_NAME ROLLUP_VIEW='relatedlist'}
		<div class="commentsList commentsBody px-3 overflow-auto" style="height: 60vh;">
			{if !empty($PARENT_COMMENTS) && is_array($PARENT_COMMENTS)}
				{foreach key=Index item=COMMENT from=$PARENT_COMMENTS}
					{include file='comments/Comment.tpl'|@vtemplate_path:$MODULE_NAME}
				{/foreach}
			{else}

			{/if}
		</div>
		{if empty($PARENT_COMMENTS)}
			<div class="noCommentsMsgContainer p-3 my-3">
				<p class="text-center">{vtranslate('LBL_NO_COMMENTS',$MODULE_NAME)}</p>
			</div>
		{elseif !empty($ROLLUP_STATUS)}
			<div class="moreRelatedCommentsContainer text-center">
				<button type="button" class="btn btn-primary moreRelatedComments">{vtranslate('LBL_SHOW_MORE', $MODULE_NAME)}</button>
			</div>
		{/if}
		{include file='comments/FormAdd.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='saveComment'}
		{include file='comments/FormEdit.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='saveComment'}
	</div>
</form>
{/strip}