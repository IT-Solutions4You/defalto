{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{* Change to this also refer: AddCommentForm.tpl *}
{assign var=COMMENT_TEXTAREA_DEFAULT_ROWS value="2"}
{assign var=PRIVATE_COMMENT_MODULES value=Vtiger_Functions::getPrivateCommentModules()}
{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}
{assign var=IS_SUMMARY_VIEW value=true}
<div class="commentContainer recentComments">
	{include file='comments/Form.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='detailViewSaveComment'}
	{include file='comments/Header.tpl'|vtemplate_path:$MODULE_NAME ROLLUP_VIEW='summary'}
	<div class="commentsList commentsBody px-3">
		{if !empty($COMMENTS)}
			<div class="recentCommentsBody">
				{assign var=COMMENTS_COUNT value=php7_count($COMMENTS)}
				{foreach key=index item=COMMENT from=$COMMENTS}
                    {include file='comments/Comment.tpl'|@vtemplate_path COMMENT=$COMMENT COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL MAX_LENGTH=200}
				{/foreach}
			</div>
		{else}
			{include file="NoComments.tpl"|@vtemplate_path}
		{/if}
		{if $PAGING_MODEL->isNextPageExists()}
			<div class="row py-2">
				<div class="col text-center">
					<a target="_blank" href="index.php?{$RELATION_LIST_URL}&tab_label=ModComments" class="moreRecentComments btn btn-primary">{vtranslate('LBL_SHOW_MORE',$MODULE_NAME)}</a>
				</div>
			</div>
		{/if}
	</div>
	{include file='comments/FormAdd.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='detailViewSaveComment'}
	{include file='comments/FormEdit.tpl'|vtemplate_path:$MODULE_NAME SAVE_BUTTON_CLASS='detailViewSaveComment'}
</div>
{/strip}
