{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{if !empty($CHILD_COMMENTS_MODEL)}
	<ul class="unstyled">
		{foreach item=COMMENT from=$CHILD_COMMENTS_MODEL}
			<li class="commentDetails">
				{include file='CommentThreadList.tpl'|@vtemplate_path COMMENT=$COMMENT}
				{assign var=CHILD_COMMENTS value=$COMMENT->getChildComments()}
				{if !empty($CHILD_COMMENTS)}
					{include file='CommentsListIteration.tpl'|@vtemplate_path CHILD_COMMENTS_MODEL=$COMMENT->getChildComments()}
				{/if}
			</li>
		{/foreach}
	</ul>
{/if}
{/strip}