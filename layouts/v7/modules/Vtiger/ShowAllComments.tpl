{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
<form id="detailView" method="POST">    
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
<div class="commentContainer commentsRelatedContainer container-fluid">
    <div class="commentTitle row">
        {if $COMMENTS_MODULE_MODEL->isPermitted('EditView')}
            <div class="addCommentBlock">
				<div class="commentTextArea">
					<textarea name="commentcontent" class="commentcontent mention_listener"  placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
				</div>
				<div class="row">
					<div class="col-xs-8">
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
					</div>
					<div class="col-xs-4">
						<div class="pull-right">
							<button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
						</div>
					</div>
				</div>
			</div>
        {/if}
    </div>
    <div class="showcomments container-fluid row" style="margin-top:10px;">
        <h3>
            {vtranslate('LBL_COMMENTS',$MODULE)}
            {if $MODULE_NAME ne 'Leads'}
                <div class="pull-right fontSize10pt">
                    <div class="input-info-addon">
                    <a class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></a></div>
                    <input type="checkbox" class="bootstrap-switch" id="rollupcomments" hascomments="1" startindex="{$STARTINDEX}" data-view="relatedlist" rollupid="{$ROLLUPID}" 
                           rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$MODULE_RECORD}" checked data-on-color="success"/>
                </div>
            {/if}
        </h3>
        <br>
    <div class="commentsList commentsBody">
        {include file='CommentsList.tpl'|@vtemplate_path COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
    </div>
    
    <div class="hide basicAddCommentBlock container-fluid">
		<div class="commentTextArea row">
            <textarea name="commentcontent" class="commentcontent" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
        </div>
        <div class="pull-right row">
            <button class="btn btn-success saveComment" type="button" data-mode="add"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
            <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
	</div>
        
	<div class="hide basicEditCommentBlock container-fluid">
		<div class="row" style="padding-bottom: 10px;">
            <input style="width:100%;height:30px;" type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level"/>
		</div>
		<div class="row">
            <div class="commentTextArea">
                <textarea name="commentcontent" class="commentcontenthidden"  placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
            </div>
        </div>
        <input type="hidden" name="is_private">
        <div class="pull-right row">
            <button class="btn btn-success saveComment" type="button" data-mode="edit"><strong>{vtranslate('LBL_POST', $MODULE_NAME)}</strong></button>
            <a href="javascript:void(0);" class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
        </div>
	</div>
</div>
</div>
            </form>
{/strip}