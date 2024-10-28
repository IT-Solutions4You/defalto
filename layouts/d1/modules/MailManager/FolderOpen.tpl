{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="mmActionsContainer container-fluid">
        <div class="row">
            <div class="col-1">
                <input type='checkbox' id='mainCheckBox' class="form-check-input">
            </div>
            <div class="col">
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmMarkAsRead" data-folder="{$FOLDER->getName()}" title="{vtranslate('LBL_MARK_AS_READ', $MODULE)}">
                    <i class="fa-regular fa-envelope-open"></i>
                </div>
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmMarkAsUnread" data-folder="{$FOLDER->getName()}" title="{vtranslate('LBL_Mark_As_Unread', $MODULE)}">
                    <i class="fa-regular fa-envelope"></i>
                </div>
                <div class="btn btn-outline-secondary me-2 mmActionIcon" id="mmDeleteMail" data-folder="{$FOLDER->getName()}" title="{vtranslate('LBL_Delete', $MODULE)}">
                    <i class="fa fa-trash-o fa-stack-lg"></i>
                </div>
                <div class="btn btn-outline-secondary moveToFolderDropDown more dropdown action" title="{vtranslate('LBL_MOVE_TO', $MODULE)}">
                    <div data-bs-toggle="dropdown">
                        <i class="fa fa-folder me-2 mmMoveDropdownFolder"></i>
                        <i class="fa fa-arrow-right mmMoveDropdownArrow"></i>
                    </div>
                    <ul class="dropdown-menu" id="mmMoveToFolder">
                        {foreach item=FOLDER_LIST_NAME from=$FOLDER_LIST}
                            <li data-folder="{$FOLDER->getName()}" data-movefolder='{$FOLDER_LIST_NAME}'>
                                <a class="dropdown-item text-truncate">{$FOLDER_LIST_NAME}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
            <div class="col-auto text-end">
                {if $FOLDER->getMails()}
                    <span class="pageInfo me-2">{$FOLDER->pageInfo()}</span>
                    <span class="pageInfoData me-2" data-start="{$FOLDER->getStartCount()}" data-end="{$FOLDER->getEndCount()}" data-total="{$FOLDER->count()}" data-label-of="{vtranslate('LBL_OF')}"></span>
                {/if}
                <button type="button" id="PreviousPageButton" class="btn btn-outline-secondary me-2" {if $FOLDER->hasPrevPage()}data-folder="{$FOLDER->getName()}" data-page="{$FOLDER->pageCurrent(-1)}" {else}disabled="disabled"{/if}>
                    <i class="fa fa-caret-left"></i>
                </button>
                <button type="button" id="NextPageButton" class="btn btn-outline-secondary" {if $FOLDER->hasNextPage()}data-folder="{$FOLDER->getName()}" data-page="{$FOLDER->pageCurrent(1)}" {else}disabled="disabled"{/if}>
                    <i class="fa fa-caret-right"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="mmSearchContainerOther py-3 container-fluid">
        <div class="row">
            <div class="col">
                <div class="input-group">
                    <input type="text" class="form-control" id="mailManagerSearchbox" aria-describedby="basic-addon2" value="{$QUERY}" data-foldername='{$FOLDER->getName()}' placeholder="{vtranslate('LBL_TYPE_TO_SEARCH', $MODULE)}">
                </div>
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
   {if $FOLDER->getMails()}
        <div class="mmEmailContainerDiv container-fluid px-0" id="emailListDiv">
            {assign var=IS_SENT_FOLDER value=$FOLDER->isSentFolder()}
            <input type="hidden" name="folderMailIds" value="{','|implode:$FOLDER->getMailIds()}"/>
            {foreach item=MAIL from=$FOLDER->getMails()}
                {if $MAIL->isRead()}
                    {assign var=IS_READ value=1}
                {else}
                    {assign var=IS_READ value=0}
                {/if}
                <div class="container-fluid py-3 border-bottom cursorPointer mailEntry {if $IS_READ}mmReadEmail{else}fw-bold{/if}" id='mmMailEntry_{$MAIL->getUid()}' data-folder="{$FOLDER->getName()}" data-read='{$IS_READ}'>
                    <div class="row">
                        <div class="col-sm-auto">
                            <div class="min-w-5rem">
                                <input type="checkbox" class="mailCheckBox form-check-input">
                                {if $MAIL->hasRelations()}
                                    <i class="bi bi-check ms-2"></i>
                                {/if}
                                {if $MAIL->hasAttachments()}
                                    <i class="bi bi-paperclip ms-2"></i>
                                {/if}
                            </div>
                        </div>
                        <div class="col-sm overflow-auto mmfolderMails" title="{$MAIL->getSubject()}">
                            <input type="hidden" class="mm_uid" value='{$MAIL->getUid()}'>
                            <input type="hidden" class='mm_foldername' value='{$FOLDER->getName()}'>
                            <div class="row">
                                <div class="col-lg-8 nameSubjectHolder stepText text-truncate">
                                    {assign var=DISPLAY_NAME value=$MAIL->getFromName(33)}
                                    {if $IS_SENT_FOLDER}
                                        {assign var=DISPLAY_NAME value=$MAIL->getToName(33)}
                                    {/if}
                                    {assign var=SUBJECT value=$MAIL->getSubject()}
                                    {strip_tags($DISPLAY_NAME)}<br>{strip_tags($SUBJECT)}
                                </div>
                                <div class="col-sm-4 text-end text-muted fs-small">
                                    <span class="mmDateTimeValue" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString(date('Y-m-d H:i:s', strtotime($MAIL->_date)))}">{Vtiger_Util_Helper::formatDateDiffInStrings(date('Y-m-d H:i:s', $MAIL->_date))}</span>
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
