{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<input type="hidden" id="pageNumber" value="{$CURRENT_PAGE}">
<input type="hidden" id="totalCount" value="{$PAGING_INFO['recordCount']}"/>
<input type="hidden" id="recordsCount" value="{$PAGING_INFO['recordCount']}"/>
<input type="hidden" id="selectedIds" name="selectedIds"/>
<input type="hidden" id="excludedIds" name="excludedIds"/>
<input type="hidden" id="alphabetValue" value="{$ALPHABET_VALUE}"/>
<input type="hidden" id="pageStartRange" value="{$PAGING_INFO['startSequence']}"/>
<input type="hidden" id="pageEndRange" value="{$PAGING_INFO['endSequence']}"/>
<input type="hidden" id="previousPageExist" {if $CURRENT_PAGE neq 1}value="1"{/if} />
<input type="hidden" id="nextPageExist" value="{$PAGING_INFO['nextPageExists']}"/>
<input type="hidden" id="pageLimit" value="{$PAGING_INFO['pageLimit']}"/>
<input type="hidden" id="noOfEntries" value="{$NO_OF_ENTRIES}"/>
<input type="hidden" value="{$COLUMN_NAME}" name="orderBy">
<input type="hidden" value="{$SORT_ORDER}" name="sortOrder">
<div id="listview-actions" class="listViewActionsJs listview-actions-container px-3">
    <div class="row">
        <div class="col-lg-auto listViewActionsContainer">
            <button type="button" class="btn btn-outline-secondary" id="{$MODULE}_listview_massAction" onclick="Portal_List_Js.massDeleteRecords()" disabled="disabled">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="col-lg">
            <div class="messageContainer hide">
                <div id="selectAllMsgDiv">
                    <div class="text-center">
                        <a href="#" class="text-primary fs-5">{vtranslate('LBL_SELECT_ALL',$MODULE)}
                            <span class="me-2">{vtranslate($MODULE ,$MODULE)}</span>
                            <span>(<span id="totalRecordsCount" value=""></span>)</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="messageContainer hide">
                <div id="deSelectAllMsgDiv">
                    <div class="text-center">
                        <a href="#" class="text-primary fs-5">{vtranslate('LBL_DESELECT_ALL_RECORDS',$MODULE)}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-auto">
            {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
        </div>
    </div>
</div>
<div id="table-content" class="table-container">
    {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
    <table id="listview-table" class="table table-borderless listview-table portal-table">
        <thead>
            <tr class="listViewContentHeader bg-body-secondary">
                <th style="width: 5rem;">
                    <div class="table-actions">
                        <span class="input form-check">
                            <input class="listViewEntriesMainCheckBox form-check-input" type="checkbox">
                        </span>
                    </div>
                </th>
                <th>
                    <a href="#" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq 'portalname'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="portalname">
                        {if $COLUMN_NAME eq 'portalname'}
                            <i class="fa {$FASORT_IMAGE}"></i>
                        {else}
                            <i class="fa fa-sort customsort"></i>
                        {/if}
                        <span class="mx-2">{vtranslate('LBL_BOOKMARK_NAME', $MODULE)}</span>
                    </a>
                    {if $COLUMN_NAME eq 'portalname'}
                        <a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
                    {/if}
                </th>
                <th>
                    <a href="#" class="listViewContentHeaderValues text-secondary text-nowrap"
                       data-nextsortorderval="{if $COLUMN_NAME eq 'portalurl'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="portalurl">
                        {if $COLUMN_NAME eq 'portalurl'}
                            <i class="fa {$FASORT_IMAGE}"></i>
                        {else}
                            <i class="fa fa-sort customsort"></i>
                        {/if}
                        <span class="mx-2">{vtranslate('LBL_BOOKMARK_URL', $MODULE)}</span>
                    </a>
                    {if $COLUMN_NAME eq 'portalurl'}
                        <a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
                    {/if}
                </th>
                <th>
                    <a href="#" class="listViewContentHeaderValues text-secondary text-nowrap"
                       data-nextsortorderval="{if $COLUMN_NAME eq 'createdtime'}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="createdtime">
                        {if $COLUMN_NAME eq 'createdtime'}
                            <i class="fa {$FASORT_IMAGE}"></i>
                        {else}
                            <i class="fa fa-sort customsort"></i>
                        {/if}
                        <span class="mx-2">{vtranslate('LBL_CREATED_ON', $MODULE)}</span>
                    </a>
                    {if $COLUMN_NAME eq 'createdtime'}
                        <a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
                    {/if}
                </th>
            </tr>
        </thead>
        <tbody class="overflow-y">
        {foreach item=LISTVIEW_ENTRY key=RECORD_ID from=$LISTVIEW_ENTRIES}
            <tr class="listViewEntries border-bottom" data-id="{$RECORD_ID}" data-recordurl="index.php?module=Portal&view=Detail&record={$RECORD_ID}">
                <td class="listViewRecordActions text-secondary">
                    <div class="table-actions d-flex align-items-center flex-nowrap">
                        <div class="input form-check">
                            <input type="checkbox" value="{$LISTVIEW_ENTRY->getId()}" class="listViewEntriesCheckBox form-check-input"/>
                        </div>
                        <div class="more dropdown action">
                            <div href="javascript:;" data-bs-toggle="dropdown" class="px-2">
                                <i class="fa fa-ellipsis-v icon"></i>
                            </div>
                            <ul class="dropdown-menu" data-id="{$RECORD_ID}">
                                <li>
                                    <a href="javascript:void(0);" class="editPortalRecord dropdown-item">{vtranslate('LBL_EDIT', $MODULE)}</a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="deleteRecordButton dropdown-item">{vtranslate('LBL_DELETE', $MODULE)}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
                <td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
                    <a class="fw-bold" href="index.php?module=Portal&view=Detail&record={$RECORD_ID}" sl-processed="1">{$LISTVIEW_ENTRY->get('portalname')}</a>
                </td>
                <td class="listViewEntryValue {$WIDTHTYPE}" nowrap>
                    <a class="urlField cursorPointer" href="{if substr($LISTVIEW_ENTRY->get('portalurl'), 0, 4) neq 'http'}//{/if}{$LISTVIEW_ENTRY->get('portalurl')}" target="_blank" sl-processed="1">{$LISTVIEW_ENTRY->get('portalurl')}</a>
                </td>
                <td class="listViewEntryValue {$WIDTHTYPE}" nowrap>{$LISTVIEW_ENTRY->get('createdtime')}</td>
            </tr>
        {/foreach}
        {if $PAGING_INFO['recordCount'] eq '0'}
            <tr class="emptyRecordsDiv">
                <td colspan="4">
                    <div class="emptyRecordsContent text-center fs-4">
                        <span>{vtranslate('LBL_NO')} {vtranslate('LBL_BOOKMARKS', $MODULE)} {vtranslate('LBL_FOUND')}.</span>
                    </div>
                </td>
            </tr>
        {/if}
        </tbody>
    </table>
</div>