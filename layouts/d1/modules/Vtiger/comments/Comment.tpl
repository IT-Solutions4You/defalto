{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=PRIVATE_COMMENT_MODULES value=Vtiger_Functions::getPrivateCommentModules()}
    {assign var=COMMENT_CHILDS value=$COMMENT->getChildComments()}
    {assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
    {assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
    {assign var=CREATOR_NAME value={decode_html($COMMENT->getCommentedByName())}}
    <div class="commentDiv commentDetails mb-3 {if $COMMENT->get('is_private')}privateComment{/if}">
        <div class="singleComment container-fluid">
            <input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
            <div class="row">
                <div class="col-auto p-0 title" id="{$COMMENT->getId()}">
                    <div class="recordImage rounded-circle commentInfoHeader" style="width:1.8rem; height:1.8rem; font-size: 1rem;" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto="{$COMMENT->get('related_to')}">
                        {assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
                        {if !empty($IMAGE_PATH)}
                            <img class="rounded-circle w-100 h-100 vertical-top image" src="{$IMAGE_PATH}">
                        {else}
                            <div class="rounded-circle w-100 h-100 vertical-top name">
                                <span>
                                    <strong>{$CREATOR_NAME|mb_substr:0:2|escape:"html"}</strong>
                                </span>
                            </div>
                        {/if}
                    </div>
                </div>
                <div class="col">
                    <div class="comment media">
                        <div class="media-left title lh-lg vertical-middle">
                            <span class="creatorName me-1 fw-bold" data-related-to="{$COMMENT->isEmpty('related_to')}" data-parent-record="{$PARENT_RECORD}">
                                {$CREATOR_NAME}
                            </span>
                            {if !empty($PARENT_RECORD) and !$COMMENT->isEmpty('related_to') and $COMMENT->get('related_to') ne $PARENT_RECORD}
                                {assign var=SINGULAR_MODULE value='SINGLE_'|cat:$COMMENT->get('module')}
                                {assign var=ENTITY_NAME value=getEntityName($COMMENT->get('module'), array($COMMENT->get('related_to')))}
                                <span class="text-secondary wordbreak">
                                    <span class="me-1">{vtranslate('LBL_ON','Vtiger')}</span>
                                    <span class="me-1">{vtranslate($SINGULAR_MODULE, $COMMENT->get('module'))}</span>
                                    <a class="fw-bold me-1" href="index.php?module={$COMMENT->get('module')}&view=Detail&record={$COMMENT->get('related_to')}">
                                        {$ENTITY_NAME[$COMMENT->get('related_to')]}
                                    </a>
                                </span>
                            {/if}
                            <span class="commentTime text-secondary cursorDefault ms-3" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">
                                {Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}
                            </span>
                            {if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
                                <span class="text-secondary ms-3">
                                    {if $COMMENT->get('is_private') or !in_array($COMMENT->get('module'), $PRIVATE_COMMENT_MODULES)}
                                        <i class="fa fa-lock" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_INTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
                                    {else}
                                        <i class="fa fa-unlock" data-bs-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_EXTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
                                    {/if}
                                </span>
                            {/if}
                        </div>
                        <div class="comment media-body">
                            <div class="commentInfoContentBlock mt-1 px-4 py-2 bg-body-secondary rounded-end-5 rounded-bottom-5 d-inline-block">
                                {assign var=COMMENT_CONTENT value=nl2br($COMMENT->get('commentcontent'))}
                                {if $COMMENT_CONTENT}
                                    {if $MAX_LENGTH}
                                        {assign var=DISPLAYNAME value=decode_html($COMMENT_CONTENT)}
                                        <span class="commentInfoContent" data-maxlength="{$MAX_LENGTH}" data-fullComment="{$COMMENT_CONTENT|escape:"html"}" data-shortComment="{$DISPLAYNAME|mb_substr:0:$MAX_LENGTH|escape:"html"}..." data-more='{vtranslate('LBL_SHOW_MORE',$MODULE)}' data-less='{vtranslate('LBL_SHOW',$MODULE)} {vtranslate('LBL_LESS',$MODULE)}'>
                                        {if $DISPLAYNAME|count_characters:true gt $MAX_LENGTH}
                                            {mb_substr(trim($DISPLAYNAME),0,$MAX_LENGTH)}...
                                            <a class="pull-right toggleComment showMore text-secondary">
                                                <small>{vtranslate('LBL_SHOW_MORE',$MODULE)}</small>
                                            </a>
                                        {else}
                                            {$COMMENT_CONTENT}
                                        {/if}
                                    </span>
                                    {else}
                                        <span class="commentInfoContent">
                                        {$COMMENT_CONTENT}
                                    </span>
                                    {/if}
                                {/if}
                                {assign var=FILE_DETAILS value=$COMMENT->getFileNameAndDownloadURL()}
                                {foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
                                    {assign var=FILE_NAME value=$FILE_DETAIL['trimmedFileName']}
                                    {if !empty($FILE_NAME)}
                                        <div class="commentAttachmentName my-2">
                                            <div class="filePreview d-flex text-secondary">
                                                <a class="previewfile me-2" onclick="Vtiger_Detail_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile" title="{$FILE_DETAIL['rawFileName']}">
                                                    <i class="fa fa-paperclip me-2"></i>
                                                    <span>{$FILE_NAME}</span>
                                                </a>
                                                <a name="downloadfile" class="hide" title="{vtranslate('LBL_DOWNLOAD_FILE',$MODULE_NAME)}" href="{$FILE_DETAIL['url']}">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    {/if}
                                {/foreach}
                                {if $COMMENT->getCommentedTime() neq $COMMENT->getModifiedTime()}
                                    <div class="row commentEditStatus text-secondary" name="editStatus">
                                        {assign var=REASON_TO_EDIT value=$COMMENT->get('reasontoedit')}
                                        {if $REASON_TO_EDIT}
                                            <div class="col-lg text-nowrap">
                                                <small>{vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} : <span name="editReason" class="text-truncate">{nl2br($REASON_TO_EDIT)}</span></small>
                                            </div>
                                        {/if}
                                        <div {if $REASON_TO_EDIT}class="col-lg-auto text-nowrap"{/if}>
                                            <small class="me-2">{vtranslate('LBL_COMMENT',$MODULE_NAME)} {strtolower(vtranslate('LBL_MODIFIED',$MODULE_NAME))}</small>
                                            <small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getModifiedTime())}" class="commentModifiedTime">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getModifiedTime())}</small>
                                        </div>
                                    </div>
                                {/if}
                            </div>
                            <div class="commentActionsContainer ms-4 my-2">
                            <span class="commentActions">
                                <span class="commentActionBlock">
                                    {assign var=COMMENT_CHILDS_COUNT value=php7_count($COMMENT_CHILDS)}
                                    {if $COMMENT_CHILDS_COUNT && !empty($IS_SUMMARY_VIEW)}
                                        <a href="index.php?{$RELATION_LIST_URL}&tab_label=ModComments&commentid={$COMMENT->getId()}" class="cursorPointer detailViewThread text-secondary me-3">
                                            <i class="fa-solid fa-comments"></i>
                                            <span class="ms-2">{vtranslate('LBL_VIEW_THREAD',$MODULE_NAME)}</span>
                                        </a>
                                    {/if}
                                    {if $IS_CREATABLE}
                                        <a href="javascript:void(0);" class="cursorPointer replyComment feedback text-secondary me-3">
                                            <i class="fa-solid fa-reply"></i>
                                            <span class="ms-2">{vtranslate('LBL_REPLY',$MODULE_NAME)}</span>
                                        </a>
                                    {/if}
                                    {if $CURRENTUSER->getId() eq $COMMENT->get('userid') && $IS_EDITABLE}
                                        <a href="javascript:void(0);" class="cursorPointer editComment feedback text-secondary me-3">
                                            <i class="fa-solid fa-pencil"></i>
                                            <span class="ms-2">{vtranslate('LBL_EDIT',$MODULE_NAME)}</span>
                                        </a>
                                    {/if}
                                    {if $COMMENT_CHILDS_COUNT && empty($IS_SUMMARY_VIEW)}
                                        <a data-bs-toggle="collapse" href="#childComments{$COMMENT->getId()}" class="text-secondary me-3">
                                            <span>{$COMMENT_CHILDS_COUNT}</span>
                                            <span class="ms-2">{if $COMMENT_CHILDS_COUNT eq 1}{vtranslate('LBL_REPLY',$MODULE_NAME)}{else}{vtranslate('LBL_REPLIES',$MODULE_NAME)}{/if}</span>
                                        </a>
                                    {/if}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {if empty($IS_SUMMARY_VIEW)}
            {if isset($CURRENT_COMMENT) && !isset($SHOW_REPLIES) && $CURRENT_COMMENT->getId() eq $COMMENT->getId()}
                {assign var=SHOW_REPLIES value=true}
            {/if}
            <div class="childComments ms-5 collapse {if $SHOW_REPLIES}show{/if}" id="childComments{$COMMENT->getId()}">
                {foreach $COMMENT_CHILDS as $COMMENT}
                    {include file='comments/Comment.tpl'|@vtemplate_path COMMENT=$COMMENT COMMENT_MODULE_MODEL=$COMMENTS_MODULE_MODEL}
                {/foreach}
            </div>
        {/if}
    </div>
{/strip}