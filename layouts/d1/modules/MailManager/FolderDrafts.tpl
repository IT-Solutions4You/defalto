{*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************}
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
        <div class="col-lg-12 mmEmailContainerDiv" id='emailListDiv'>
            {foreach item=MAIL from=$FOLDER->mails()}
                {assign var=IS_READ value=1}
                <div class="col-lg-12 cursorPointer mailEntry {if $IS_READ}mmReadEmail{/if}" data-read='{$IS_READ}'>
                    <span class="col-lg-1 ">
                        <input type='checkbox' class='mailCheckBox' class="pull-left">
                    </span>
                    <div class="col-lg-11 draftEmail ">
                        <input type="hidden" class="msgNo" value='{$MAIL.id}'>
                        <div class="col-lg-8  font13px stepText">
                            {strip_tags($MAIL.saved_toid)}<br>{strip_tags($MAIL.subject)}
                        </div>
                        <div class="col-lg-4 ">
                            <span class="pull-right">
                                <span class='mmDateTimeValue'>{{$MAIL.date_start}}</span>
                            </span>
                        </div>
                        <div class="col-lg-12 mmMailDesc text-truncate">
                            {assign var=MAIL_DESC value=str_replace("\n", " ", strip_tags($MAIL.description))}
                            {$MAIL_DESC}
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