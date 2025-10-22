{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}"/>
    <input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}"/>
    <input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}"/>
    <input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}"/>
    <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
    <input type="hidden" value="{$ORDER_BY}" id="orderBy">
    <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
    <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
    <input type='hidden' value="{$PAGE_NUMBER}" id='pageNumber'>
    <input type='hidden' value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
    <input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
    <div class="px-4 pb-4">
        <div id="listview-actions" class="listview-actions-container bg-body rounded">
            <div class="row pt-3 px-3 align-items-center">
                <div class="col-lg pb-3">
                    <h4 class="m-0">{vtranslate('LBL_LOGIN_HISTORY_DETAILS', $QUALIFIED_MODULE)}</h4>
                </div>
                <div class="col-lg pb-3 usersListDiv">
                    <select class="select2 col-md-4" id="usersFilter">
                        <option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
                        {foreach item=USERNAME key=USER from=$USERSLIST}
                            <option value="{$USER}" name="{$USERNAME}" {if $USERNAME eq $SELECTED_USER} selected {/if}>{$USERNAME}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg pb-3 text-end">
                    {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                    {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
                </div>
            </div>
            <div class="list-content row">
                <div class="col-sm-12 col-xs-12 ">
                    <div id="table-content" class="table-container" style="padding-top:0px !important;">
                        <table id="settings-listview-table" class="table listview-table table-borderless">
                            {assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
                            {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
                            <thead>
                            <tr class="listViewContentHeader bg-body-secondary">
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    <th nowrap>
                                        <a {if !($LISTVIEW_HEADER->has('sort'))} class="listViewHeaderValues text-secondary" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}" {/if}>
                                            <span class="me-2">{vtranslate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}</span>
                                        </a>
                                    </th>
                                {/foreach}
                            </tr>
                            </thead>
                            <tbody class="overflow-y">
                            {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
                                <tr class="listViewEntries border-bottom" data-id="{$LISTVIEW_ENTRY->getId()}"
                                    {if method_exists($LISTVIEW_ENTRY,'getDetailViewUrl')}data-recordurl="{$LISTVIEW_ENTRY->getDetailViewUrl()}"{/if}
                                        {if method_exists($LISTVIEW_ENTRY,'getRowInfo')}data-info="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::Encode($LISTVIEW_ENTRY->getRowInfo()))}"{/if}>
                                    {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                        {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                                        {assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
                                    <td class="listViewEntryValue {$WIDTHTYPE}" {if isset($WIDTH)}width="{$WIDTH}%"{/if} nowrap style='cursor:text;'>
                                        {$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
                                        {if $LAST_COLUMN && $LISTVIEW_ENTRY->getRecordLinks()}
                                            </td>
                                        {/if}
                                        </td>
                                    {/foreach}
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                        <!--added this div for Temporarily -->
                        {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                            <table class="emptyRecordsDiv">
                                <tbody>
                                <tr>
                                    <td class="p-3">
                                        {vtranslate('LBL_NO')} {vtranslate($MODULE, $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND')}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        {/if}
                    </div>
                    <div id="scroller_wrapper" class="bottom-fixed-scroll">
                        <div id="scroller" class="scroller-div"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/strip}
