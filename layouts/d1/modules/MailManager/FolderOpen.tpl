{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
{strip}
    <div class="mmActionsContainer container-fluid">
        <div class="row">
            <div class="col-lg-1">
                <input type='checkbox' id='mainCheckBox' class="form-check-input">
            </div>
            <div class="col-lg-5">
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmMarkAsRead" data-folder="{$FOLDER->name()}" title="{vtranslate('LBL_MARK_AS_READ', $MODULE)}">
                    <i class="fa-regular fa-envelope-open"></i>
                </div>
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmMarkAsUnread" data-folder="{$FOLDER->name()}" title="{vtranslate('LBL_Mark_As_Unread', $MODULE)}">
                    <i class="fa-regular fa-envelope"></i>
                </div>
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmDeleteMail" data-folder="{$FOLDER->name()}" title="{vtranslate('LBL_Delete', $MODULE)}">
                    <i class="fa fa-trash-o fa-stack-lg"></i>
                </div>
                <div class="btn btn-outline-secondary moveToFolderDropDown more dropdown action" title="{vtranslate('LBL_MOVE_TO', $MODULE)}">
                    <div data-bs-toggle="dropdown">
                        <i class="fa fa-folder me-2 mmMoveDropdownFolder"></i>
                        <i class="fa fa-arrow-right mmMoveDropdownArrow"></i>
                    </div>
                    <ul class="dropdown-menu" id="mmMoveToFolder">
                        {foreach item=folder from=$FOLDERLIST}
                            <li data-folder="{$FOLDER->name()}" data-movefolder='{$folder}'>
                                <a class="dropdown-item">
                                    {if mb_strlen($folder,'UTF-8')>20}
                                        {mb_substr($folder,0,20,'UTF-8')}...
                                    {else}
                                        {$folder}
                                    {/if}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 text-end">
                {if $FOLDER->mails()}
                    <span class="pageInfo me-2">{$FOLDER->pageInfo()}</span>
                    <span class="pageInfoData me-2" data-start="{$FOLDER->getStartCount()}" data-end="{$FOLDER->getEndCount()}" data-total="{$FOLDER->count()}" data-label-of="{vtranslate('LBL_OF')}"></span>
                {/if}
                <button type="button" id="PreviousPageButton" class="btn btn-outline-secondary me-2" {if $FOLDER->hasPrevPage()}data-folder="{$FOLDER->name()}" data-page="{$FOLDER->pageCurrent(-1)}" {else}disabled="disabled"{/if}>
                    <i class="fa fa-caret-left"></i>
                </button>
                <button type="button" id="NextPageButton" class="btn btn-outline-secondary" {if $FOLDER->hasNextPage()}data-folder="{$FOLDER->name()}" data-page="{$FOLDER->pageCurrent(1)}" {else}disabled="disabled"{/if}>
                    <i class="fa fa-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="mmSearchContainerOther py-3 container-fluid">
        <div class="row">
            <div class="col">
                <input type="text" class="form-control" id="mailManagerSearchbox" aria-describedby="basic-addon2" value="{$QUERY}" data-foldername='{$FOLDER->name()}' placeholder="{vtranslate('LBL_TYPE_TO_SEARCH', $MODULE)}">
            </div>
            <div class="col">
                <select class="form-select" id="searchType">
                    {foreach item=arr key=option from=$SEARCHOPTIONS}
                        <option value="{$arr}" {if $arr eq $TYPE}selected{/if}>{vtranslate($option, $MODULE)}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-auto">
                <button id="mm_searchButton" class="btn btn-outline-secondary">{vtranslate('LBL_Search', $MODULE)}</button>
            </div>
        </div>
    </div>
   {if $FOLDER->mails()}
        <div class="mmEmailContainerDiv container-fluid px-0" id="emailListDiv">
            {assign var=IS_SENT_FOLDER value=$FOLDER->isSentFolder()}
            <input type="hidden" name="folderMailIds" value="{','|implode:$FOLDER->mailIds()}"/>
            {foreach item=MAIL from=$FOLDER->mails()}
                {if $MAIL->isRead()}
                    {assign var=IS_READ value=1}
                {else}
                    {assign var=IS_READ value=0}
                {/if}
                <div class="container-fluid py-3 border-bottom cursorPointer mailEntry {if $IS_READ}mmReadEmail{/if}" id='mmMailEntry_{$MAIL->msgNo()}' data-folder="{$FOLDER->name()}" data-read='{$IS_READ}'>
                    <div class="row">
                        <span class="col-lg-1">
                            <input type="checkbox" class="mailCheckBox form-check-input">
                        </span>
                        <div class="col-lg-11 mmfolderMails" title="{$MAIL->subject()}">
                            <input type="hidden" class="msgNo" value='{$MAIL->msgNo()}'>
                            <input type="hidden" class='mm_foldername' value='{$FOLDER->name()}'>
                            <div class="row">
                                <div class="col-lg-8 nameSubjectHolder stepText text-truncate">
                                    {assign var=DISPLAY_NAME value=$MAIL->from(33)}
                                    {if $IS_SENT_FOLDER}
                                        {assign var=DISPLAY_NAME value=$MAIL->to(33)}
                                    {/if}
                                    {assign var=SUBJECT value=$MAIL->subject()}
                                    {if $IS_READ}
                                        {strip_tags($DISPLAY_NAME)}<br>{strip_tags($SUBJECT)}
                                    {else}
                                        <strong>{strip_tags($DISPLAY_NAME)}<br>{strip_tags($SUBJECT)}</strong>
                                    {/if}
                                </div>
                                <div class="col-lg-4 text-end text-muted fs-small">
                                    {assign var=ATTACHMENT value=$MAIL->attachments()}
                                    {assign var=INLINE_ATTCH value=$MAIL->inlineAttachments()}
                                    {assign var=ATTCHMENT_COUNT value=(php7_count($ATTACHMENT) - php7_count($INLINE_ATTCH))}
                                    {if $ATTCHMENT_COUNT}
                                        <i class="fa fa-paperclip me-2"></i>
                                    {/if}
                                    <span class="mmDateTimeValue" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString(date('Y-m-d H:i:s', strtotime($MAIL->_date)))}">{Vtiger_Util_Helper::formatDateDiffInStrings(date('Y-m-d H:i:s', strtotime($MAIL->_date)))}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <div class="noMailsDiv"><center><strong>{vtranslate('LBL_No_Mails_Found',$MODULE)}</strong></center></div>
    {/if}
{/strip}