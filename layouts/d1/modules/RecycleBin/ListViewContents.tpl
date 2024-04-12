{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}

{strip}
    {include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}
    <div class="col-sm-12 col-xs-12">
        <input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}"/>
        <input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}"/>
        <input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}"/>
        <input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}"/>
        <input type="hidden" id="numberOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}"/>
        <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}"/>
        <input type="hidden" id="sourceModule" value="{$SOURCE_MODULE}"/>
        <input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
        <input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
        <input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
        <input type="hidden" id="isRecordsDeleted" value="{$IS_RECORDS_DELETED}">
        <input type="hidden" value="{$ORDER_BY}" name="orderBy" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" name="sortOrder" id="sortOrder">
        <input type="hidden" value="{$LISTVIEW_ENTRIES_COUNT}" id="recordsCount">
        {include file="ListViewActions.tpl"|vtemplate_path:$MODULE}
        <div id="table-content" class="table-container">
            <form name="list" id="listedit" action="" onsubmit="return false;">
                <table id="listview-table" class="table table-borderless {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords{else}listview-table{/if}">
                    <thead>
                    <tr class="listViewContentHeader bg-body-secondary">
                        <th>
                            {if !$SEARCH_MODE_RESULTS}
                                <div class="table-actions">
                                    <span class="input form-check">
                                        <input class="listViewEntriesMainCheckBox form-check-input" type="checkbox">
                                    </span>
                                </div>
                            {else}
                                {vtranslate('LBL_ACTIONS',$MODULE)}
                            {/if}
                        </th>
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <th>
                                <a href="#" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}">
                                    {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
                                        <i class="fa {$FASORT_IMAGE}"></i>
                                    {else}
                                        <i class="fa fa-sort customsort"></i>
                                    {/if}
                                    <span class="mx-2">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE)}</span>
                                </a>
                                {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
                                    <a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
                                {/if}
                            </th>
                        {/foreach}
                    </tr>

                    {if $MODULE_MODEL->isQuickSearchEnabled() && !$SEARCH_MODE_RESULTS}
                        <tr class="searchRow listViewSearchContainer border-bottom">
                            <th class="inline-search-btn">
                                <div class="table-actions">
                                    <button class="btn text-secondary {if php7_count($SEARCH_DETAILS) gt 0}hide{/if}" data-trigger="listSearch" title="{vtranslate("LBL_SEARCH",$MODULE)}">
                                        <i class="fa fa-search"></i>
                                    </button>
                                    <button class="searchAndClearButton btn text-secondary {if php7_count($SEARCH_DETAILS) eq 0}hide{/if}" data-trigger="clearListSearch" title="{vtranslate("LBL_CLEAR",$MODULE)}">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </div>
                            </th>
                            {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                <th>
                                    {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                                    {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$SOURCE_MODULE) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$CURRENT_USER_MODEL}
                                    <input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]['comparator']}">
                                </th>
                            {/foreach}
                        </tr>
                    {/if}

                    <tbody class="overflow-y">
                    {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
                        <tr class="listViewEntries border-bottom" data-id='{$LISTVIEW_ENTRY->getId()}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
                            <td class="listViewRecordActions">
                                {include file="ListViewRecordActions.tpl"|vtemplate_path:$MODULE}
                            </td>
                            {if $SOURCE_MODULE eq 'Documents' && $LISTVIEW_ENTRY->get('document_source')}
                                <input type="hidden" name="document_source_type" value="{$LISTVIEW_ENTRY->get('document_source')}">
                            {/if}
                            {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                                {assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADER->get('column'))}
                                {assign var=LISTVIEW_ENTRY_VALUE value=$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                                <td class="listViewEntryValue" data-name="{$LISTVIEW_HEADER->get('name')}" data-rawvalue="{$LISTVIEW_ENTRY_RAWVALUE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}">
                                        <span class="fieldValue">
                                            <span class="value">
                                                {if $LISTVIEW_HEADER->getFieldDataType() eq 'currency'}
                                                    {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($LISTVIEW_ENTRY->getCurrencyId())}
                                                    {CurrencyField::appendCurrencySymbol($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), $CURRENCY_INFO['symbol'])}
                                                {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'picklist'}
                                                    <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="py-1 px-2 rounded picklist-color picklist-{$LISTVIEW_HEADER->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADERNAME))}" {/if}> {$LISTVIEW_ENTRY_VALUE} </span>
                                                {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist'}
                                                    {assign var=MULTI_RAW_PICKLIST_VALUES value=explode('|##|',$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADERNAME))}
                                                    {assign var=MULTI_PICKLIST_VALUES value=explode(',',$LISTVIEW_ENTRY_VALUE)}
                                                    {foreach item=MULTI_PICKLIST_VALUE key=MULTI_PICKLIST_INDEX from=$MULTI_RAW_PICKLIST_VALUES}
                                                        <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="py-1 px-2 rounded picklist-color picklist-{$LISTVIEW_HEADER->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen(trim($MULTI_PICKLIST_VALUE))}" {/if}> {trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])} </span>
                                                    {/foreach}
                                                {else}
                                                    {$LISTVIEW_ENTRY_VALUE}
                                                {/if}
                                            </span>
                                        </span>
                                    {if $LISTVIEW_HEADER->isEditable() eq 'true' && $LISTVIEW_HEADER->isAjaxEditable() eq 'true'}
                                        <span class="hide edit">
                                            </span>
                                    {/if}
                                </td>
                            {/foreach}
                        </tr>
                    {/foreach}
                    {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                        <tr class="emptyRecordsDiv">
                            {assign var=COLSPAN_WIDTH value={php7_count($LISTVIEW_HEADERS)}+1}
                            <td colspan="{$COLSPAN_WIDTH}">
                                <div class="emptyRecordsContent fs-5 text-center py-5">
                                    {vtranslate('LBL_NO_RECORDS_FOUND', $MODULE)} {vtranslate($SOURCE_MODULE, $SOURCE_MODULE)}.
                                </div>
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                    </thead>
                </table>
            </form>
        </div>
        <div id="scroller_wrapper" class="bottom-fixed-scroll">
            <div id="scroller" class="scroller-div"></div>
        </div>
    </div>
{/strip}