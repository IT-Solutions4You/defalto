{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-1">
                <input type='checkbox' id='mainCheckBox' class="form-check-input">
            </div>
            <div class="col-lg-6">
                <span class="btn btn-outline-secondary mmActionIcon" id="mmDeleteMail" title="{vtranslate('LBL_Delete', $MODULE)}">
                    <i class="fa fa-trash-o fa-stack-lg"></i>
                </span>
            </div>
            <div class="col-lg-5 text-end">
                    {if $FOLDER->mails()}<span class="me-2">{$FOLDER->pageInfo()}</span>{/if}
                    <button type="button" id="PreviousPageButton" class="btn btn-outline-secondary me-2" {if $FOLDER->hasPrevPage()}data-page='{$FOLDER->pageCurrent(-1)}' {else}disabled="disabled"{/if}>
                        <i class="fa fa-caret-left"></i>
                    </button>
                    <button type="button" id="NextPageButton" class="btn btn-outline-secondary" {if $FOLDER->hasNextPage()} data-page="{$FOLDER->pageCurrent(1)}" {else}disabled="disabled"{/if}>
                        <i class="fa fa-caret-right"></i>
                    </button>
            </div>
        </div>
    </div>

    <div class="mmSearchContainer py-3 container-fluid">
        <div class="row">
            <div class="col">
                <input type="text" class="form-control" id="mailManagerSearchbox" aria-describedby="basic-addon2" value="{$QUERY}" data-foldername='{$FOLDER->name()}' placeholder="{vtranslate('LBL_TYPE_TO_SEARCH', $MODULE)}">
            </div>
            <div class="col-3 mmSearchDropDown">
                <select id="searchType" class="form-select">
                    {foreach item=label key=value from=$SEARCHOPTIONS}
                        <option value="{$value}" {if $value eq $TYPE}selected{/if}>{vtranslate($label, $MODULE)}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-auto text-end" id="mmSearchButtonContainer">
                <button id='mm_searchButton' class="btn btn-outline-secondary">{vtranslate('LBL_Search', $MODULE)}</button>
            </div>
        </div>
    </div>

    {if $FOLDER->mails()}
        <div class="mmEmailContainerDiv" id="emailListDiv">
            {foreach item=MAIL from=$FOLDER->mails()}
                {assign var=IS_READ value=1}
                <div class="container-fluid py-3 border-bottom mailEntry">
                    <div class="row cursorPointer {if $IS_READ}mmReadEmail{/if}" data-read='{$IS_READ}'>
                        <span class="col-1">
                            <input type="checkbox" class="mailCheckBox form-check-input">
                        </span>
                        <div class="col-11">
                            <div class="row">
                                <div class="col-8 draftEmail">
                                    <input type="hidden" class="msgNo" value="{$MAIL['id']}">
                                    <div class="col-lg-8  font13px stepText">
                                        <strong>
                                            <span>{strip_tags($MAIL['to_email'])}</span>
                                            <br>
                                            <span>{strip_tags($MAIL['subject'])}</span>
                                        </strong>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <span class="mmDateTimeValue" title="{Vtiger_Util_Helper::formatDateTimeIntoDayString(date('Y-m-d H:i:s', strtotime($MAIL['createdtime'])))}">{Vtiger_Util_Helper::formatDateDiffInStrings(date('Y-m-d H:i:s', strtotime($MAIL['createdtime'])))}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <div class="noMailsDiv">
            <center><strong>{vtranslate('LBL_No_Mails_Found',$MODULE)}</strong></center>
        </div>
    {/if}
{/strip}