{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

{if !empty($PARENT_COMMENTS)}
	<ul class="unstyled">
		{if isset($CURRENT_COMMENT) && $CURRENT_COMMENT}
			{assign var=CHILDS_ROOT_PARENT_MODEL value=$CURRENT_COMMENT}
			{assign var=CURRENT_COMMENT_PARENT_MODEL value=$CURRENT_COMMENT->getParentCommentModel()}
			{while $CURRENT_COMMENT_PARENT_MODEL neq false}
				{assign var=TEMP_COMMENT value=$CURRENT_COMMENT_PARENT_MODEL}
				{assign var=CURRENT_COMMENT_PARENT_MODEL value=$CURRENT_COMMENT_PARENT_MODEL->getParentCommentModel()}
				{if $CURRENT_COMMENT_PARENT_MODEL eq false}
					{assign var=CHILDS_ROOT_PARENT_MODEL value=$TEMP_COMMENT}
				{/if}	
			{/while}
		{/if}
		{if is_array($PARENT_COMMENTS)}
			{foreach key=Index item=COMMENT from=$PARENT_COMMENTS}
				{assign var=PARENT_COMMENT_ID value=$COMMENT->getId()}
				<li class="commentDetails">
					{include file='Comment.tpl'|@vtemplate_path COMMENT=$COMMENT COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}

					{if isset($CHILDS_ROOT_PARENT_MODEL) && $CHILDS_ROOT_PARENT_MODEL}
						{if $CHILDS_ROOT_PARENT_MODEL->getId() eq $PARENT_COMMENT_ID}		
							{assign var=CHILD_COMMENTS_MODEL value=$CHILDS_ROOT_PARENT_MODEL->getChildComments()}
							{include file='CommentsListIteration.tpl'|@vtemplate_path CHILD_COMMENTS_MODEL=$CHILD_COMMENTS_MODEL}
						{/if}
					{/if}
				</li>	
			{/foreach}
		{else}
			{include file='Comment.tpl'|@vtemplate_path COMMENT=$PARENT_COMMENTS}
		{/if}
	</ul>
{else}
	<div class="noCommentsMsgContainer p-3 my-3">
		<p class="text-center">{vtranslate('LBL_NO_COMMENTS',$MODULE_NAME)}</p>
	</div>
{/if}
{/strip}