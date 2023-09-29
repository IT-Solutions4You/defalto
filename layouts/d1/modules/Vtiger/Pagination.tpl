{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    {if !$CLASS_VIEW_ACTION}
        {assign var=CLASS_VIEW_ACTION value='listViewActions'}
        {assign var=CLASS_VIEW_PAGING_INPUT value='listViewPagingInput'}
        {assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='listViewPagingInputSubmit'}
        {assign var=CLASS_VIEW_BASIC_ACTION value='listViewBasicAction'}
    {/if}
    <div class="pagination-container {$CLASS_VIEW_ACTION}">
        <div class="pagination-arrows">
            <button id="PageNumbers" class="pageNumbers showTotalCountIcon btn">
                <span class="pageNumbersText">
                    {if $RECORD_COUNT}
                        {$PAGING_MODEL->getRecordStartRange()}&nbsp;{vtranslate('LBL_to', $MODULE)}&nbsp;{$PAGING_MODEL->getRecordEndRange()}
                    {/if}
                </span>
                <span class="totalNumberOfRecords cursorPointer {if !$RECORD_COUNT}hide{/if}" title="{vtranslate('LBL_SHOW_TOTAL_NUMBER_OF_RECORDS', $MODULE)}">
                    &nbsp;{vtranslate('LBL_OF', $MODULE)}&nbsp;?
                </span>
            </button>
            <button type="button" id="PreviousPageButton" class="btn btn-outline-secondary me-1" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if}>
                <i class="fa-solid fa-angle-left"></i>
                <span class="ms-2">{vtranslate('LBL_PREV_LIST', $QUALIFIED_MODULE)}</span>
            </button>
            {if $SHOWPAGEJUMP}
                <button type="button" id="PageJump" data-bs-toggle="dropdown" class="btn btn-outline-secondary me-1">
                    <i class="fa fa-ellipsis-h icon" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$QUALIFIED_MODULE)}"></i>
                </button>
                <ul class="{$CLASS_VIEW_BASIC_ACTION} dropdown-menu dropdown-menu-end" id="PageJumpDropDown">
                    <li>
                        <div class="listview-pagenum px-2 mb-2 text-center">
                            <span class="me-2">{vtranslate('LBL_PAGE',$moduleName)}</span>
                            <strong class="me-2">
                                <span>{$PAGE_NUMBER}</span>
                            </strong>
                            <span class="me-2">{vtranslate('LBL_OF',$moduleName)}</span>
                            <strong>
                                <span id="totalPageCount"></span>
                            </strong>
                        </div>
                        <div class="listview-pagejump input-group p-0 px-2">
                            <input type="text" id="pageToJump" placeholder="{vtranslate('LBL_LISTVIEW_JUMP_TO',$moduleName)}" class="form-control text-center {$CLASS_VIEW_PAGING_INPUT}"/>
                            <button type="button" id="pageToJumpSubmit" class="btn btn-success text-center {$CLASS_VIEW_PAGING_INPUT_SUBMIT}">{'GO'}</button>
                        </div>
                    </li>
                </ul>
            {/if}
            <button type="button" id="NextPageButton" class="btn btn-outline-secondary" {if !$PAGING_MODEL->isNextPageExists()}disabled{/if}>
                <span class="me-2">{vtranslate('LBL_NEXT_LIST', $QUALIFIED_MODULE)}</span>
                <i class="fa-solid fa-angle-right"></i>
            </button>
        </div>

    </div>
{/strip}