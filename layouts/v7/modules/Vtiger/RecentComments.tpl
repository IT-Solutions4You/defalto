{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{strip}
{* Change to this also refer: AddCommentForm.tpl *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}

<div class="commentContainer recentComments">
    <div class="commentTitle">
        {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
            <div class="addCommentBlock">
                <div class="row">
                    <div class=" col-lg-12">
                    <div class="commentTextArea ">
                            <textarea name="commentcontent" class="commentcontent form-control mention_listener"  placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
                        </div>
                    </div>
                </div>
                <div class='row'>
                    <div class="col-xs-6">
                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
                    </div>
                    <div class='col-xs-6'>
                        <div class="pull-right">
                            <button class="btn btn-success btn-sm detailViewSaveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
                        </div>
                    </div>
                </div>
            </div>
		{/if}
	</div>

	<hr>
            <div class="recentCommentsHeader row">
        <h4 class="display-inline-block col-lg-7">
                        {"Recent Comments"}
                    </h4>
                {if $MODULE_NAME ne 'Leads'}
            <div class="col-lg-5 pull-right" style="margin-top:5px;text-align:right;padding-right:20px;">
                        <div class="display-inline-block">                                
                            <span class="">{vtranslate('LBL_ROLL_UP',$QUALIFIED_MODULE)} &nbsp;</span>
                            <span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>&nbsp;&nbsp;
                        </div>  
                        <input type="checkbox" class="bootstrap-switch pull-right" id="rollupcomments" hascomments="1" startindex="{$STARTINDEX}" data-view="summary" rollupid="{$ROLLUPID}" 
                               rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$PARENT_RECORD}" checked data-on-color="success"/> 
                    </div> 
                {/if}
                </div>
	<div class="commentsBody container-fluid">
		{if !empty($COMMENTS)}
            <div class="recentCommentsBody">
                {assign var=COMMENTS_COUNT value=count($COMMENTS)}
                {foreach key=index item=COMMENT from=$COMMENTS}
                    {assign var=CREATOR_NAME value=$COMMENT->getCommentedByName()}
                    <div class="commentDetails">
                        <div class="singleComment">
                            {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
                            {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-lg-2 recordImage commentInfoHeader" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto = "{$COMMENT->get('related_to')}">
                                            {assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
                                                {if !empty($IMAGE_PATH)}
                                                    <img src="{$IMAGE_PATH}" width="100%" height="100%" align="left">
                                                {else}
                                                    <div class="name"><span><strong> {$CREATOR_NAME|substr:0:2} </strong></span></div>
                                                {/if}
                                    </div>
                                    <div class="comment col-lg-10" style="line-height:1;">
                                        <span class="creatorName">
                                            {$CREATOR_NAME}
                                        </span>&nbsp;&nbsp;
                                        {if $ROLLUP_STATUS and $COMMENT->get('module') ne $MODULE_NAME}
                                            {assign var=SINGULR_MODULE value='SINGLE_'|cat:$COMMENT->get('module')}
                                            {assign var=ENTITY_NAME value=getEntityName($COMMENT->get('module'), array($COMMENT->get('related_to')))}
                                            <span class="text-muted textOverflowEllipsis">
                                                {vtranslate('LBL_ON','Vtiger')}&nbsp;
                                                {vtranslate($SINGULR_MODULE)}&nbsp;
                                                <a href="index.php?module={$COMMENT->get('module')}&view=Detail&record={$COMMENT->get('related_to')}">
                                                    {$ENTITY_NAME[$COMMENT->get('related_to')]}
                                                </a>
                                            </span>&nbsp;&nbsp;
                                        {/if}
                                        <div class="">
                                            <span class="commentInfoContent">
                                                {nl2br($COMMENT->get('commentcontent'))}
                                            </span>
                                        </div>
                                        <br>
                                    <div class="commentActionsContainer">
                                            {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
                                                <span>
                                                    <a href="javascript:void(0);" class="cursorPointer replyComment feedback" style="color: blue;">
                                                        {vtranslate('LBL_REPLY',$MODULE_NAME)}
                                                    </a>
                                                    {if $CURRENTUSER->getId() eq $COMMENT->get('userid')}
                                                        &nbsp;&nbsp;&nbsp;
                                                        <a href="javascript:void(0);" class="cursorPointer editComment feedback" style="color: blue;">
                                                            {vtranslate('LBL_EDIT',$MODULE_NAME)}
                                                        </a>
                                                    {/if}
                                                </span>
                                            {/if}
                                            <span>
                                                {if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="javascript:void(0);" class="cursorPointer detailViewThread">{vtranslate('LBL_VIEW_THREAD',$MODULE_NAME)}</a>
                                                {/if}
                                            </span>

                                            <span class="commentTime pull-right">
                                                <p class="text-muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</small></p>
                                            </span>
                                        </div>
                                            <br>
                                            <div class="row commentEditStatus"  name="editStatus">
                                            {assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
                                                <span class="col-lg-5{if empty($REASON_TO_EDIT)} hide{/if}">
                                                    <p class="text-muted">
                                                        <small>
                                                        [ {vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} ] :
                                                        <span  name="editReason" class="textOverflowEllipsis">{nl2br($REASON_TO_EDIT)}</span>
                                                        </small>
                                                    </p>
                                                </span>
                                                {if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
                                                    <span class="{if empty($REASON_TO_EDIT)}row{else} col-lg-7{/if}">
											<p class="text-muted pull-right">
                                                            <small><em>{vtranslate('LBL_MODIFIED',$MODULE_NAME)}</em></small>&nbsp;
												<small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
											</p>
                                                    </span>
                                                {/if}
                                            </div>   
											<div style="margin-top:5px;">
											{assign var="FILE_DETAILS" value=$COMMENT->getFileNameAndDownloadURL()}
											{foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
												{assign var="FILE_NAME" value=$FILE_DETAIL['trimmedFileName']}
												{if !empty($FILE_NAME)}
													<div class="row-fluid">
														<div class="commentAttachmentName">
															<div class="filePreview">
																<a onclick="Vtiger_Detail_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile">
																	<span title="{$FILE_DETAIL['rawFileName']}" style="line-height:1.5em;">{$FILE_NAME}</span>&nbsp
																</a>&nbsp;
																<a name="downloadfile" href="{$FILE_DETAIL['url']}">
																	<i title="{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}" class="pull-left hide fa fa-download alignMiddle"></i>
																</a>
															</div>
														</div>
													</div>
												{/if}
											{/foreach}
											&nbsp;
											</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {if $index+1 neq $COMMENTS_COUNT}
                        <hr>
                    {/if}
                {/foreach}
            </div>
		{else}
			{include file="NoComments.tpl"|@vtemplate_path}
		{/if}
        {if $PAGING_MODEL->isNextPageExists()}
            <div class="row">
                <div class="pull-right">
                    <a href="javascript:void(0)" class="moreRecentComments">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
                </div>
            </div>
        {/if}
	</div>

	<div class="hide basicAddCommentBlock container-fluid">
		<div class="commentTextArea row" style="padding-top: 10px;padding-bottom: 10px;">
            <textarea name="commentcontent" class="commentcontent"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
        </div>
        <div class="pull-right row">
            <button class="btn btn-success btn-sm detailViewSaveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
            <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
	</div>

	<div class="hide basicEditCommentBlock container-fluid" style="min-height: 150px;">
		<div class="row" style="padding-top: 10px;padding-bottom: 10px;">
            <input style="width:100%;height:30px;" type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level"/>
		</div>
		<div class="row" style="padding-bottom: 10px;">
			<div class="commentTextArea">
                <textarea name="commentcontent" class="commentcontenthidden"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
            </div>
        </div>
        <input type="hidden" name="is_private">
        <div class="pull-right row">
            <button class="btn btn-success btn-sm detailViewSaveComment" type="button" data-mode="edit">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
            <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
	</div>
</div>
{/strip}
