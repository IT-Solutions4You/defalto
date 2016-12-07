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
<div class="commentDiv cursorPointer">
    <div class="singleComment">
        <input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
        <div class="commentInfoHeader"  data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
            {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
            {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
            <div class="col-lg-12">
                <div class="media">
                    <div class="media-left title" id="{$COMMENT->getId()}">
                        {assign var=CREATOR_NAME value=$COMMENT->getCommentedByName()}
                        <div class="col-lg-1 recordImage commentInfoHeader" style="width:50px;height:50px;" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}">
                            <div class="name">
                                <span style="font-size:30px">
                                    <strong> {$CREATOR_NAME|substr:0:2} </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="media-body">
                        <div class="comment" style="line-height:1;">                       
                            <span class="creatorName" style="color:blue">
                                {$CREATOR_NAME}
                            </span>&nbsp;&nbsp;
                            <div class="">
                                <span class="commentInfoContent">
                                    {nl2br($COMMENT->get('commentcontent'))}
                                </span>
                            </div>
                            <br>
                            <div class="commentActionsContainer">
                                <span class="commentActions">
                                    {if $CHILDS_ROOT_PARENT_MODEL}
                                        {assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
                                    {/if}
                                    
                                    {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
                                        {if $CHILDS_ROOT_PARENT_MODEL}
                                            {assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
                                        {/if}
                                        <a href="javascript:void(0);" class="cursorPointer replyComment feedback">
                                            {vtranslate('LBL_REPLY',$MODULE_NAME)}
                                        </a>
                                        {if $CURRENTUSER->getId() eq $COMMENT->get('userid')}
                                            &nbsp;<span>|</span>&nbsp;
                                            <a href="javascript:void(0);" class="cursorPointer editComment feedback">
                                                {vtranslate('LBL_EDIT',$MODULE_NAME)}
                                            </a>
                                        {/if}
                                    {/if}
                                    
                                    {assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
                                    {if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
                                        {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}&nbsp;<span style="color:black">|</span>&nbsp;{/if}
                                        <span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
                                            <a href="javascript:void(0)" class="cursorPointer viewThread">
                                                <span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
                                            </a>
                                        </span>
                                        <span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
                                            <a href="javascript:void(0)" class="cursorPointer hideThread">
                                                <span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
                                            </a>
                                        </span>
                                    {elseif $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
                                        {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}&nbsp;<span style="color:black">|</span>&nbsp;{/if}
                                        <span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
                                            <a href="javascript:void(0)" class="cursorPointer viewThread">
                                                <span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
                                            </a>
                                        </span>
                                        <span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
                                            <a href="javascript:void(0)" class="cursorPointer hideThread">
                                                <span class="childCommentsCount">{$CHILD_COMMENTS_COUNT}</span>&nbsp;{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}&nbsp;
                                            </a>
                                        </span>
                                    {/if}
                                </span>
                                
                                <span class="commentTime" style="padding:20px;">
                                    <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateAndDateDiffInString($COMMENT->getCommentedTime())}</small>
                                </span>
                            </div>
                    </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>
    </div>
</div>
