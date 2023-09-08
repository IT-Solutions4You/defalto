{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}
<div class="commentDiv cursorPointer mb-3">
	<div class="singleComment">
		<input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
		<div class="commentInfoHeader" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
			{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
			{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
			<div class="row media" {if $COMMENT->get('is_private')}style="background: #fff9ea;"{/if}>
				<div class="col-auto p-0 media-left title" id="{$COMMENT->getId()}">
					{assign var=CREATOR_NAME value=$COMMENT->getCommentedByName()}
					<div class="col-lg-2 recordImage rounded-circle commentInfoHeader" style="width: 1.8rem; height: 1.8rem;" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto = "{$COMMENT->get('related_to')}">
						{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
						{if !empty($IMAGE_PATH)}
							<img class="rounded-circle" src="{$IMAGE_PATH}" style="width: 1.8rem; height: 1.8rem;">
						{else}
							<div class="rounded-circle name" style="width: 1.8rem; height: 1.8rem; font-size: 30px;"><span><strong> {$CREATOR_NAME|mb_substr:0:2|escape:"html"} </strong></span></div>
						{/if}
					</div>
				</div>
				<div class="col media-body">
					<div class="comment">
						<span class="creatorName fw-bold" >
							{$CREATOR_NAME}
						</span>&nbsp;
						<span class="commentTime text-secondary cursorDefault">
							<span title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</span>
						</span>
						<div class="commentInfoContentBlock mt-1 px-4 py-2 bg-body-secondary rounded-end-5 rounded-bottom-5">
							<span class="commentInfoContent">
								{nl2br($COMMENT->get('commentcontent'))}
							</span>
						</div>
						<div class="commemntActionsubblock ms-4 my-2">
							<div class="commentActionsContainer">
								<span class="commentActions">
									{if $CHILDS_ROOT_PARENT_MODEL}
										{assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
									{/if}
									{assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
									{if $IS_CREATABLE}
										<a href="javascript:void(0);" class="cursorPointer replyComment feedback me-3 text-secondary">
											<i class="fa-solid fa-reply"></i>
											<span class="ms-2">{vtranslate('LBL_REPLY',$MODULE_NAME)}</span>
										</a>
									{/if}
									{if $CURRENTUSER->getId() eq $COMMENT->get('userid') && $IS_EDITABLE}
										<a href="javascript:void(0);" class="cursorPointer editComment feedback me-3 text-secondary">
											<i class="fa-solid fa-pencil"></i>
											<span class="ms-2">{vtranslate('LBL_EDIT',$MODULE_NAME)}</span>
										</a>
									{/if}
									{if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
										<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a href="javascript:void(0)" class="cursorPointer viewThread text-secondary">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>
												<span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
											</a>
										</span>
										<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
											<a href="javascript:void(0)" class="cursorPointer hideThread text-secondary">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>
												<span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
											</a>
										</span>
									{elseif $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
										<span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
											<a href="javascript:void(0)" class="cursorPointer viewThread text-secondary">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>
												<span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
											</a>
										</span>
										<span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
											<a href="javascript:void(0)" class="cursorPointer hideThread text-secondary">
												<span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>
												<span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
											</a>
										</span>
									{/if}
								</span>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
