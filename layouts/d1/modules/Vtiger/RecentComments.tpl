{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{* Change to this also refer: AddCommentForm.tpl *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}
{assign var="PRIVATE_COMMENT_MODULES" value=Vtiger_Functions::getPrivateCommentModules()}
{assign var=IS_CREATABLE value=$COMMENTS_MODULE_MODEL->isPermitted('CreateView')}
{assign var=IS_EDITABLE value=$COMMENTS_MODULE_MODEL->isPermitted('EditView')}

<div class="commentContainer recentComments">
	<div class="commentTitle">
		{if $IS_CREATABLE}
			<div class="p-3 rounded bg-body-secondary addCommentBlock">
				<div class="row">
					<div class="col pe-0">
						<div class="commentTextArea">
							<textarea name="commentcontent" class="commentcontent form-control" placeholder="{vtranslate('LBL_POST_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
						</div>
					</div>
					<div class="col-auto">
						<button class="btn btn-primary active detailViewSaveComment px-5" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
					</div>
				</div>
				<div class="row mt-2">
					{if $FIELD_MODEL->getProfileReadWritePermission()}
						<div class="col-lg-6">
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) MODULE="ModComments"}
						</div>
					{/if}
					<div class="col-lg-6">
						<div class="text-end">
							{if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
								<label class="form-check form-switch form-check-reverse">
									<span class="form-check-label me-2">{vtranslate('LBL_INTERNAL_COMMENT')}</span>
									<i class="fa fa-question-circle cursorPointer" data-toggle="tooltip" data-placement="top" data-original-title="{vtranslate('LBL_INTERNAL_COMMENT_INFO')}"></i>
									<input class="form-check-input" type="checkbox" id="is_private" role="switch" checked="checked">
								</label>
							{/if}
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
	<div class="recentCommentsHeader row py-3">
		{if $MODULE_NAME ne 'Leads'}
			<div class="commentHeader text-end col-auto ms-auto px-3">
				<div class="form-check form-switch form-check-reverse">
					<label class="form-check-label" for="rollupcomments">
						<span class="me-2">{vtranslate('LBL_ROLL_UP',$QUALIFIED_MODULE)}</span>
						<span class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{vtranslate('LBL_ROLLUP_COMMENTS_INFO',$QUALIFIED_MODULE)}"></span>&nbsp;&nbsp;
					</label>
					<input type="checkbox" class="form-check-input" id="rollupcomments" role="switch" hascomments="1" startindex="{$STARTINDEX}" data-view="summary" rollupid="{$ROLLUPID}" rollup-status="{$ROLLUP_STATUS}" module="{$MODULE_NAME}" record="{$PARENT_RECORD}" {if 1 eq $ROLLUP_STATUS}checked="checked"{/if} data-on-color="success"/>
				</div>
			</div>
		{/if}
	</div>
	<div class="commentsBody">
		{if !empty($COMMENTS)}
			<div class="recentCommentsBody">
				{assign var=COMMENTS_COUNT value=php7_count($COMMENTS)}
				{foreach key=index item=COMMENT from=$COMMENTS}
					{assign var=CREATOR_NAME value={decode_html($COMMENT->getCommentedByName())}}
					<div class="commentDetails mb-3">
						<div class="singleComment container-fluid">
							<input type="hidden" name="is_private" value="{$COMMENT->get('is_private')}">
							{assign var=PARENT_COMMENT_MODEL value=$COMMENT->getParentCommentModel()}
							{assign var=CHILD_COMMENTS_MODEL value=$COMMENT->getChildComments()}
							<div class="row">
								<div class="col-auto p-0">
									<div class="recordImage commentInfoHeader lh-lg vertical-middle" data-commentid="{$COMMENT->getId()}" data-parentcommentid="{$COMMENT->get('parent_comments')}" data-relatedto="{$COMMENT->get('related_to')}">
										{assign var=IMAGE_PATH value=$COMMENT->getImagePath()}
										{if !empty($IMAGE_PATH)}
											<img class="rounded-circle" src="{$IMAGE_PATH}" style="width: 1.8rem; height: 1.8rem;">
										{else}
											<div class="name"><span><strong> {$CREATOR_NAME|mb_substr:0:2|escape:"html"} </strong></span></div>
										{/if}
									</div>
								</div>
								<div class="col">
									<div class="media">
										<div class="media-left title lh-lg vertical-middle">
											<span class="creatorName me-2 fw-bold">
												{$CREATOR_NAME}
											</span>
											{if $ROLLUP_STATUS and ($COMMENT->get('module') ne $MODULE_NAME or $COMMENT->get('related_to') ne $PARENT_RECORD)}
												{assign var=SINGULR_MODULE value='SINGLE_'|cat:$COMMENT->get('module')}
												{assign var=ENTITY_NAME value=getEntityName($COMMENT->get('module'), array($COMMENT->get('related_to')))}
												<span class="text-secondary wordbreak me-2">
													<span class="me-2">{vtranslate('LBL_ON','Vtiger')}</span>
													<span class="me-2">{vtranslate($SINGULR_MODULE,$COMMENT->get('module'))}</span>
													<a class="fw-bold" href="index.php?module={$COMMENT->get('module')}&view=Detail&record={$COMMENT->get('related_to')}">
														{$ENTITY_NAME[$COMMENT->get('related_to')]}
													</a>
												</span>
											{/if}
											<span class="commentTime text-secondary cursorDefault me-2">
												<span title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($COMMENT->getCommentedTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($COMMENT->getCommentedTime())}</span>
											</span>
											{if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
												<span class="text-secondary">
													{if $COMMENT->get('is_private')}
														<i class="fa fa-lock text-warning" title="{vtranslate('LBL_INTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
													{else}
														<i class="fa fa-unlock" title="{vtranslate('LBL_EXTERNAL_COMMENT_TOOTLTIP',$MODULE)}"></i>
													{/if}
												</span>
											{/if}
										</div>
										<div class="media-body">
											<div class="comment">
												<div class="commentInfoContentBlock mt-1 px-4 py-2 bg-body-secondary rounded-end-5 rounded-bottom-5 d-inline-block">
													{assign var=COMMENT_CONTENT value={nl2br($COMMENT->get('commentcontent'))}}
													{if $COMMENT_CONTENT}
														{assign var=DISPLAYNAME value={decode_html($COMMENT_CONTENT)}}
														{assign var=MAX_LENGTH value=200}
														<span class="commentInfoContent" data-maxlength="{$MAX_LENGTH}" data-fullComment="{$COMMENT_CONTENT|escape:"html"}" data-shortComment="{$DISPLAYNAME|mb_substr:0:200|escape:"html"}..." data-more='{vtranslate('LBL_SHOW_MORE',$MODULE)}' data-less='{vtranslate('LBL_SHOW',$MODULE)} {vtranslate('LBL_LESS',$MODULE)}'>
															{if $DISPLAYNAME|count_characters:true gt $MAX_LENGTH}
																{mb_substr(trim($DISPLAYNAME),0,$MAX_LENGTH)}...
																<a class="pull-right toggleComment showMore text-secondary"><small>{vtranslate('LBL_SHOW_MORE',$MODULE)}</small></a>
															{else}
																{$COMMENT_CONTENT}
															{/if}
														</span>
													{/if}
													{assign var="FILE_DETAILS" value=$COMMENT->getFileNameAndDownloadURL()}
													{foreach key=index item=FILE_DETAIL from=$FILE_DETAILS}
														{assign var="FILE_NAME" value=$FILE_DETAIL['trimmedFileName']}
														{if !empty($FILE_NAME)}
															<div class="commentAttachmentName my-2">
																<div class="filePreview d-flex text-secondary">
																	<a class="previewfile me-2" onclick="Vtiger_Detail_Js.previewFile(event,{$COMMENT->get('id')},{$FILE_DETAIL['attachmentId']});" data-filename="{$FILE_NAME}" href="javascript:void(0)" name="viewfile">
																		<i class="fa fa-paperclip me-2"></i>
																		<span title="{$FILE_DETAIL['rawFileName']}">{$FILE_NAME}</span>&nbsp
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
															{assign var="REASON_TO_EDIT" value=$COMMENT->get('reasontoedit')}
															{if $REASON_TO_EDIT}
																<span class="text-secondary col-lg-6 text-nowrap">
																	<small>{vtranslate('LBL_EDIT_REASON',$MODULE_NAME)} : <span name="editReason" class="text-truncate">{nl2br($REASON_TO_EDIT)}</span></small>
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
													<span>
														{if $PARENT_COMMENT_MODEL neq false or $CHILD_COMMENTS_MODEL neq null}
															<a href="javascript:void(0);" class="cursorPointer detailViewThread text-secondary me-3">
																<i class="fa-solid fa-comments"></i>
																<span class="ms-2">{vtranslate('LBL_VIEW_THREAD',$MODULE_NAME)}</span>
															</a>
														{/if}
													</span>
													<span class="summarycommemntActionblock" >
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
													</span>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
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
	<div class="container-fluid bg-body-secondary p-3 rounded hide basicAddCommentBlock">
		<div class="row">
			<div class="col pe-0">
				<div class="commentTextArea">
					<textarea name="commentcontent" class="commentcontent form-control" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
				</div>
			</div>
			<div class="col-auto pe-0">
				<button class="btn btn-primary active detailViewSaveComment" type="button" data-mode="add">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
			</div>
			<div class="col-auto">
				<a href="javascript:void(0);" class="btn btn-danger closeCommentBlock" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
			</div>
		</div>
		<div class="row">
			<div class="col">
				{if in_array($MODULE_NAME, $PRIVATE_COMMENT_MODULES)}
					<div class="checkbox mt-2">
						<label class="form-check form-switch form-check-reverse mb-0">
							<input type="checkbox" class="form-check-input" id="is_private" checked="checked">
							<span class="form-check-label">{vtranslate('LBL_INTERNAL_COMMENT')}</span>
						</label>
					</div>
				{/if}
			</div>
		</div>
	</div>
	<div class="container-fluid bg-body-secondary p-3 rounded hide basicEditCommentBlock">
		<div class="row">
			<div class="col pe-0">
				<div class="commentArea" >
					<input type="text" name="reasonToEdit" placeholder="{vtranslate('LBL_REASON_FOR_CHANGING_COMMENT', $MODULE_NAME)}" class="input-block-level form-control mb-2"/>
				</div>
				<div class="commentTextArea">
					<textarea name="commentcontent" class="commentcontenthidden form-control" placeholder="{vtranslate('LBL_ADD_YOUR_COMMENT_HERE', $MODULE_NAME)}" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}"></textarea>
				</div>
				<input type="hidden" name="is_private">
			</div>
			<div class="col-auto pe-0">
				<button class="btn btn-primary active detailViewSaveComment" type="button" data-mode="edit">{vtranslate('LBL_POST', $MODULE_NAME)}</button>
			</div>
			<div class="col-auto">
				<a href="javascript:void(0);" class="btn btn-danger cursorPointer closeCommentBlock" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
			</div>
		</div>
	</div>
</div>
{/strip}
