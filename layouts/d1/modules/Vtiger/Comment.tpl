{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    {assign var="PRIVATE_COMMENT_MODULES" value=Vtiger_Functions::getPrivateCommentModules()}

<div class="commentDiv {if $COMMENT->get('is_private')}privateComment{/if}">
    <div class="singleComment">
        <input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
        <div class="commentInfoHeader container-fluid mb-3" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto="{$COMMENT->get('related_to')}">
            {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
            {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
            <div class="row">
                <div class="col-auto p-0 title" id="{$COMMENT->getId()}">
                    {assign var=CREATOR_NAME value={decode_html($COMMENT->getCommentedByName())}}
                    <div class="col-lg-auto rounded-circle recordImage commentInfoHeader" style="width:1.8rem; height:1.8rem; font-size: 1rem;" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto="{$COMMENT->get('related_to')}">
                        {assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
                        {if !empty($IMAGE_PATH)}
                            <img class="rounded-circle" src="{$IMAGE_PATH}" style="width:1.8rem; height:1.8rem;">
                        {else}
                            <div class="rounded-circle name"><span><strong> {$CREATOR_NAME|substr:0:2} </strong></span></div>
                        {/if}
                    </div>
                </div>
                <div class="col">
                    <div class="comment">
                        <div>
                            <span class="creatorName fw-bold me-2">
                                {$CREATOR_NAME}
                            </span>
                            {if $ROLLUP_STATUS and $COMMENT->get('module') ne $MODULE_NAME}
                                {assign var=SINGULR_MODULE value='SINGLE_'|cat:$COMMENT->get('module')}
                                {assign var=ENTITY_NAME value=getEntityName($COMMENT->get('module'), array($COMMENT->get('related_to')))}
                                <span class="text-secondary">
                                    <span class="me-2">{vtranslate('LBL_ON','Vtiger')}</span>
                                    <span class="me-2">{vtranslate($SINGULR_MODULE, $COMMENT->get('module'))}</span>
                                    <a class="me-2" href="index.php?module={$COMMENT->get('module')}&view=Detail&record={$COMMENT->get('related_to')}">
                                        {$ENTITY_NAME[$COMMENT->get('related_to')]}
                                    </a>
                                </span>
                            {/if}
                            <span class="commentTime text-secondary cursorDefault">
                                <span class="me-2" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</span>
                            </span>
                            {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                                <span class="text-secondary">
                                    {if $COMMENT->get('is_private')}
                                        <i class="fa fa-lock" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_INTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
                                    {else}
                                        <i class="fa fa-unlock" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_EXTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
                                    {/if}
                                </span>
                            {/if}
                        </div>
                        <div class="commentInfoContentBlock mt-1 px-4 py-2 bg-body-secondary rounded-end-5 rounded-bottom-5 d-inline-block">
                            <span class="commentInfoContent">
                                {nl2br($COMMENT->get('commentcontent'))}
                            </span>
                            {assign var="FILE_DETAILS" value=$COMMENT->getFileNameAndDownloadURL()}
                            {foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
                                {assign var="FILE_NAME" value=$FILE_DETAIL['trimmedFileName']}
                                {if !empty($FILE_NAME)}
                                    <div class="row-fluid text-secondary my-2">
                                        <div class="span11 commentAttachmentName">
                                            <span class="filePreview d-flex text-secondary">
                                                <a onclick="Vtiger_Detail_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile">
                                                    <i class="fa fa-paperclip me-2"></i>
                                                    <span title="{$FILE_DETAIL['rawFileName']}" style="line-height:1.5em;">{$FILE_NAME}</span>&nbsp
                                                </a>&nbsp;
                                                <a name="downloadfile" class="hide" href="{$FILE_DETAIL['url']}">
                                                    <i title="{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}" class="fa fa-download alignMiddle"></i>
                                                </a>
                                            </span>
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                            {assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
                            {if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
                                <div class="row commentEditStatus text-secondary" name="editStatus">
                                    {assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
                                    {if $REASON_TO_EDIT}
                                        <span class="text-secondary col-lg-6 text-nowrap">
                                            <small>{vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} : <span name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span></small>
                                        </span>
                                    {/if}
                                    <span {if $REASON_TO_EDIT}class="text-end col-6 text-nowrap"{/if}>
                                        <small class="me-2">{vtranslate('LBL_COMMENT',$MODULE_NAME)} {strtolower(vtranslate('LBL_MODIFIED',$MODULE_NAME))}</small>
                                        <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
                                    </span>
                                </div>
                            {/if}
                        </div>
                        <div class="commentActionsContainer ms-4 my-2">
                            <span class="commentActions">
                                {if $CHILDS_ROOT_PARENT_MODEL}
                                    {assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
                                {/if}
                                {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
                                    {if $CHILDS_ROOT_PARENT_MODEL}
                                        {assign var=CHILDS_ROOT_PARENT_ID value=$CHILDS_ROOT_PARENT_MODEL->getId()}
                                    {/if}
                                    <a href="javascript:void(0);" class="cursorPointer replyComment feedback text-secondary me-3">
                                        <i class="fa-solid fa-reply"></i>
                                        <span class="ms-2">{vtranslate('LBL_REPLY',$MODULE_NAME)}</span>
                                    </a>
                                    {if $CURRENTUSER->getId() eq $COMMENT->get('userid')}
                                        <a href="javascript:void(0);" class="cursorPointer editComment feedback text-secondary me-3">
                                            <i class="fa-solid fa-pencil"></i>
                                            <span class="ms-2">{vtranslate('LBL_EDIT',$MODULE_NAME)}</span>
                                        </a>
                                    {/if}
                                {/if}
                                {assign var=CHILD_COMMENTS_COUNT value=$COMMENT->getChildCommentsCount()}
                                {if $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID neq $PARENT_COMMENT_ID)}
                                    <span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
                                        <a href="javascript:void(0)" class="cursorPointer viewThread text-secondary">
                                            <span class="childCommentsCount fw-bold">{$CHILD_COMMENTS_COUNT}</span>
                                            <span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
                                        </a>
                                    </span>
                                    <span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
                                        <a href="javascript:void(0)" class="cursorPointer hideThread text-secondary">
                                            <span class="childCommentsCount fw-bold">{$CHILD_COMMENTS_COUNT}</span>
                                            <span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
                                        </a>
                                    </span>
                                {elseif $CHILD_COMMENTS_MODEL neq null and ($CHILDS_ROOT_PARENT_ID eq $PARENT_COMMENT_ID)}
                                    <span class="viewThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}" style="display:none;">
                                        <a href="javascript:void(0)" class="cursorPointer viewThread text-secondary">
                                            <span class="childCommentsCount fw-bold">{$CHILD_COMMENTS_COUNT}</span>
                                            <span class="ms-2">{if $CHILD_COMMENTS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
                                        </a>
                                    </span>
                                    <span class="hideThreadBlock" data-child-comments-count="{$CHILD_COMMENTS_COUNT}">
                                        <a href="javascript:void(0)" class="cursorPointer hideThread text-secondary">
                                            <span class="childCommentsCount fw-bold">{$CHILD_COMMENTS_COUNT}</span>
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
{/strip}