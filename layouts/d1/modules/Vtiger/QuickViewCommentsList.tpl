{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
<div class = "summaryWidgetContainer">
    <div class="recentComments">
        <div class="commentsBody">
            {if !empty($COMMENTS)}
                <div class="recentCommentsBody">
                    {foreach key=index item=COMMENT from=$COMMENTS}
                        {assign var=CREATOR_NAME value={decode_html($COMMENT->getCommentedByName())}}
                        <div class="commentDetails">
                            <div class="singleComment">
                                {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
                                {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
                                <div class="container-fluid py-2">
                                    <div class="row">
                                        <div class="col-auto">
                                            <div class="recordImage commentInfoHeader rounded-circle bg-primary text-white overflow-hidden text-center" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto="{$COMMENT->get('related_to')}" style="height: 1.8rem; width: 1.8rem;">
                                                {assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
                                                {if !empty($IMAGE_PATH)}
                                                    <img src="{$IMAGE_PATH}" height="100%" width="100%">
                                                {else}
                                                    <strong class="py-2">{$CREATOR_NAME|substr:0:2}</strong>
                                                {/if}
                                            </div>
                                        </div>
                                        <div class="col-auto p-0">
                                            <span class="creatorName">{$CREATOR_NAME}</span>
                                        </div>
                                        <div class="col-auto commentActionsContainer">
                                            <span class="commentTime muted">
                                                <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateAndDateDiffInString($COMMENT->getCommentedTime())}</small>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ms-5">
                                        <div class="comment mt-1 px-4 py-2 bg-body-secondary rounded-end-5 rounded-bottom-5 d-inline-block">
                                            <div class="">
                                                <span class="commentInfoContent">
                                                    {nl2br($COMMENT->get('commentcontent'))}
                                                </span>
                                            </div>
                                            <div>
												{assign var="FILE_DETAILS" value=$COMMENT->getFileNameAndDownloadURL()}
                                                {foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
                                                    {assign var="FILE_NAME" value=$FILE_DETAIL['trimmedFileName']}
                                                    {if !empty($FILE_NAME)}
                                                        <a class="d-block mt-1 text-secondary" onclick="Vtiger_List_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile">
                                                            <i class="fa fa-paperclip me-2"></i>
                                                            <span title="{$FILE_DETAILS['rawFileName']}">{$FILE_NAME}</span>
                                                        </a>
                                                    {/if}
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    {/foreach}
                </div>
            {else}
                {include file="NoComments.tpl"|@vtemplate_path}
            {/if}
        </div>
    </div>
</div>
