{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Vtiger/views/List.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}

<div>
    <input type="hidden" name="view" id="view" value="{$VIEW}"/>
    <input type="hidden" name="cvid" value="{$VIEWID}"/>
    <input type="hidden" name="pageStartRange" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}"/>
    <input type="hidden" name="pageEndRange" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}"/>
    <input type="hidden" name="previousPageExist" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}"/>
    <input type="hidden" name="nextPageExist" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}"/>
    <input type="hidden" name="alphabetSearchKey" id="alphabetSearchKey" value="{$MODULE_MODEL->getAlphabetSearchField()}"/>
    <input type="hidden" name="Operator" id="Operator" value="{$OPERATOR}"/>
    <input type="hidden" name="totalCount" id="totalCount" value="{$LISTVIEW_COUNT}"/>
    <input type='hidden' name="pageNumber" value="{$PAGE_NUMBER}" id='pageNumber'>
    <input type='hidden' name="pageLimit" value="{$PAGING_MODEL->getPageLimit()}" id='pageLimit'>
    <input type="hidden" name="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}" id="noOfEntries">
    <input type="hidden" name="currentSearchParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams"/>
    <input type="hidden" name="currentTagParams" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($TAG_DETAILS))}" id="currentTagParams"/>
    <input type="hidden" name="noFilterCache" value="{$NO_SEARCH_PARAMS_CACHE}" id="noFilterCache">
    <input type="hidden" name="orderBy" value="{$ORDER_BY}" id="orderBy">
    <input type="hidden" name="sortOrder" value="{$SORT_ORDER}" id="sortOrder">
    <input type="hidden" name="list_headers" value='{$LIST_HEADER_FIELDS}'/>
    <input type="hidden" name="tag" value="{$CURRENT_TAG}"/>
    <input type="hidden" name="folder_id" value="{$FOLDER_ID}"/>
    <input type="hidden" name="folder_value" value="{$FOLDER_VALUE}"/>
    <input type="hidden" name="viewType" value="{$VIEWTYPE}"/>
    <input type="hidden" name="app" id="appName" value="{$SELECTED_MENU_CATEGORY}">
    <input type="hidden" id="isExcelEditSupported" value="{if $MODULE_MODEL->isExcelEditAllowed()}yes{else}no{/if}"/>
    {if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
        <input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}'/>
    {/if}
    {if !$SEARCH_MODE_RESULTS}
        {include file="ListViewActions.tpl"|vtemplate_path:$MODULE}
    {/if}

    <div id="table-content" class="table-container">
        <form name='list' id='listedit' action='' onsubmit="return false;">
            <table id="listview-table" class="table listview-table {if $LISTVIEW_ENTRIES_COUNT eq '0'}listview-table-norecords{/if}">
                <thead>
                <tr class="listViewContentHeader bg-body-secondary text-secondary border-transparent">
                    <th class="ps-3 text-secondary">
                        {if !$SEARCH_MODE_RESULTS}
                            <div class="table-actions d-flex align-items-center">
                                <div class="table-actions-dropdown">
									<span class="input table-actions-toggle form-check" title="{vtranslate('LBL_CLICK_HERE_TO_SELECT_ALL_RECORDS',$MODULE)}">
										<input class="listViewEntriesMainCheckBox form-check-input" type="checkbox">
									</span>
                                </div>
                                {if $MODULE_MODEL->isFilterColumnEnabled()}
                                    <div id="listColumnFilterContainer">
                                        {if $CURRENT_CV_MODEL}
                                            <div class="listColumnFilter btn btn-sm text-secondary {if !$CURRENT_CV_MODEL->isCvEditable()}disabled{/if}"
                                                    {if $CURRENT_CV_MODEL->isCvEditable()}
                                                        title="{vtranslate('LBL_CLICK_HERE_TO_MANAGE_LIST_COLUMNS',$MODULE)}"
                                                    {else}
                                                        {if $CURRENT_CV_MODEL->get('viewname') eq 'All' and !$CURRENT_USER_MODEL->isAdminUser()}
                                                            title="{vtranslate('LBL_SHARED_LIST_NON_ADMIN_MESSAGE',$MODULE)}"
                                                        {elseif !$CURRENT_CV_MODEL->isMine()}
                                                            {assign var=CURRENT_CV_USER_ID value=$CURRENT_CV_MODEL->get('userid')}
                                                            {if !Vtiger_Functions::isUserExist($CURRENT_CV_USER_ID)}
                                                                {assign var=CURRENT_CV_USER_ID value=Users::getActiveAdminId()}
                                                            {/if}
                                                            title="{vtranslate('LBL_SHARED_LIST_OWNER_MESSAGE',$MODULE, getUserFullName($CURRENT_CV_USER_ID))}"
                                                        {/if}
                                                    {/if}
                                                 data-bs-toggle="tooltip" data-placement="bottom" data-container="body">
                                                <i class="fa fa-th-large"></i>
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        {elseif $SEARCH_MODE_RESULTS}
                            {vtranslate('LBL_ACTIONS',$MODULE)}
                        {/if}
                    </th>
                    {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                        {if $SEARCH_MODE_RESULTS || ($LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist')}
                            {assign var=NO_SORTING value=1}
                        {else}
                            {assign var=NO_SORTING value=0}
                        {/if}
                        <th {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')} nowrap="nowrap" {/if}>
                            <a href="#" class="text-secondary text-nowrap {if $NO_SORTING}noSorting{else}listViewContentHeaderValues{/if}" {if !$NO_SORTING}data-nextsortorderval="{if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('name')}"{/if} data-field-id='{$LISTVIEW_HEADER->getId()}'>
                                {if !$NO_SORTING}
                                    {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
                                        <i class="fa {$FASORT_IMAGE}"></i>
                                    {else}
                                        <i class="fa fa-sort customsort"></i>
                                    {/if}
                                {/if}
                                &nbsp;{vtranslate($LISTVIEW_HEADER->get('label'), $LISTVIEW_HEADER->getModuleName())}&nbsp;
                            </a>
                            {if $COLUMN_NAME eq $LISTVIEW_HEADER->get('name')}
                                <a href="#" class="removeSorting text-secondary">
                                    <i class="fa fa-remove"></i>
                                </a>
                            {/if}
                        </th>
                    {/foreach}
                </tr>

                {if $MODULE_MODEL->isQuickSearchEnabled() && !$SEARCH_MODE_RESULTS}
                    <tr class="searchRow listViewSearchContainer">
                        <th class="inline-search-btn">
                            <div class="table-actions">
                                <button class="btn text-secondary {if php7_count($SEARCH_DETAILS) gt 0}hide{/if}" data-trigger="listSearch">
                                    <i class="fa fa-search"></i>
                                    <span class="s2-btn-text d-none">{vtranslate("LBL_SEARCH",$MODULE)}</span>
                                </button>
                                <button class="searchAndClearButton btn text-secondary {if php7_count($SEARCH_DETAILS) eq 0}hide{/if}" data-trigger="clearListSearch">
                                    <i class="fa fa-close"></i>
                                    <span class="d-none">{vtranslate("LBL_CLEAR",$MODULE)}</span>
                                </button>
                            </div>
                        </th>
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            <th>
                                {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                                {assign var=SEARCH_INFO_DETAILS_FOR_FIELD value=['searchValue' => '', 'comparator' => '']}
                                {if isset($SEARCH_DETAILS[$LISTVIEW_HEADER->getName()])}
                                    {assign var=SEARCH_INFO_DETAILS_FOR_FIELD value=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()]}
                                {/if}
                                {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_INFO_DETAILS_FOR_FIELD USER_MODEL=$CURRENT_USER_MODEL}
                                <input type="hidden" class="operatorValue" value="{$SEARCH_INFO_DETAILS_FOR_FIELD['comparator']}">
                            </th>
                        {/foreach}
                    </tr>
                {/if}
                </thead>
                <tbody class="overflow-y">
                {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=listview}
                    {assign var=DATA_ID value=$LISTVIEW_ENTRY->getId()}
                    {assign var=DATA_URL value=$LISTVIEW_ENTRY->getDetailViewUrl()}
                    {if $SEARCH_MODE_RESULTS && $LISTVIEW_ENTRY->getModuleName() == "ModComments"}
                        {assign var=RELATED_TO value=$LISTVIEW_ENTRY->get('related_to_model')}
                        {assign var=DATA_ID value=$RELATED_TO->getId()}
                        {assign var=DATA_URL value=$RELATED_TO->getDetailViewUrl()}
                    {/if}
                    <tr class="listViewEntries" data-id='{$DATA_ID}' data-recordUrl='{$DATA_URL}&app={$SELECTED_MENU_CATEGORY}' id="{$MODULE}_listView_row_{$smarty.foreach.listview.index+1}">
                        <td class="listViewRecordActions ps-3 text-secondary">
                            {include file="ListViewRecordActions.tpl"|vtemplate_path:$MODULE}
                        </td>
                        {if ($LISTVIEW_ENTRY->get('document_source') eq 'Google Drive' && $IS_GOOGLE_DRIVE_ENABLED) || ($LISTVIEW_ENTRY->get('document_source') eq 'Dropbox' && $IS_DROPBOX_ENABLED)}
                            <input type="hidden" name="document_source_type" value="{$LISTVIEW_ENTRY->get('document_source')}">
                        {/if}
                        {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                            {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                            {assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADER->get('column'))}
                            {if $LISTVIEW_HEADER->getFieldDataType() eq 'text'}
                                {assign var=LISTVIEW_ENTRY_RAWVALUE value=$LISTVIEW_ENTRY->getTitle($LISTVIEW_HEADER)}
                            {/if}
                            {assign var=LISTVIEW_ENTRY_VALUE value=$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                            <td class="listViewEntryValue" data-name="{$LISTVIEW_HEADER->get('name')}" title="{$LISTVIEW_ENTRY->getTitle($LISTVIEW_HEADER)}" data-rawvalue="{$LISTVIEW_ENTRY_RAWVALUE}" data-field-type="{$LISTVIEW_HEADER->getFieldDataType()}">
                                <span class="fieldValue">
                                    <span class="value text-truncate">
                                        {if ($LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4') and $MODULE_MODEL->isListViewNameFieldNavigationEnabled() eq true }
                                            <a class="fw-bold" href="{$LISTVIEW_ENTRY->getDetailViewUrl()}&app={$SELECTED_MENU_CATEGORY}">{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
                                            {if $MODULE eq 'Products' && $LISTVIEW_ENTRY->isBundle()}
                                                <span class="mx-2">-</span><i class="mute">{vtranslate('LBL_PRODUCT_BUNDLE', $MODULE)}</i>
                                            {/if}
                                        {elseif $MODULE_MODEL->getName() eq 'Documents' && $LISTVIEW_HEADERNAME eq 'document_source'}
                                            {$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}
                                        {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'currency'}
                                            {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($LISTVIEW_ENTRY->getCurrencyId())}
                                            <span class="currencyValue" data-currency-symbol="{$CURRENCY_INFO['symbol']}">
                                                {CurrencyField::appendCurrencySymbol($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), $CURRENCY_INFO['symbol'])}
                                            </span>
                                        {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'picklist'}
                                            {assign var=PICKLIST_FIELD_ID value={$LISTVIEW_HEADER->getId()}}
                                            <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="py-1 px-2 rounded picklist-color picklist-{$PICKLIST_FIELD_ID}-{Vtiger_Util_Helper::convertSpaceToHyphen($LISTVIEW_ENTRY_RAWVALUE)}" {/if}> {$LISTVIEW_ENTRY_VALUE}</span>
                                        {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'multipicklist'}
                                                {assign var=MULTI_RAW_PICKLIST_VALUES value=explode('|##|',$LISTVIEW_ENTRY->getRaw($LISTVIEW_HEADERNAME))}
                                                {assign var=MULTI_PICKLIST_VALUES value=explode(',',$LISTVIEW_ENTRY_VALUE)}
                                                {assign var=ALL_MULTI_PICKLIST_VALUES value=array_flip($LISTVIEW_HEADER->getPicklistValues())}
                                                {foreach item=MULTI_PICKLIST_VALUE key=MULTI_PICKLIST_INDEX from=$MULTI_PICKLIST_VALUES}
                                                <span {if !empty($LISTVIEW_ENTRY_VALUE)} class="py-1 px-2 rounded picklist-color picklist-{$LISTVIEW_HEADER->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen(trim($ALL_MULTI_PICKLIST_VALUES[trim($MULTI_PICKLIST_VALUE)]))}"{/if} >
                                                        {if trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX]) eq vtranslate('LBL_NOT_ACCESSIBLE', $MODULE)}
                                                            <font color="red">
                                                            {trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])}
                                                            </font>
                                                        {else}
                                                            {trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])}
                                                        {/if}
                                                    {if !empty($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX + 1])},{/if}
                                                </span>
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
                        <td colspan="{$COLSPAN_WIDTH}" class="fs-3 border-bottom-0">
                            <div class="emptyRecordsContent text-center h-50">
                                {vtranslate('LBL_RECORDS_NOT_FOUND', $MODULE)}.
                                {if $IS_CREATE_PERMITTED}
                                    {vtranslate('LBL_CREATE_OR_IMPORT_RECORDS', $MODULE)}
                                {/if}
                            </div>
                        </td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </form>
    </div>
    <div id="scroller_wrapper" class="bottom-fixed-scroll">
        <div id="scroller" class="scroller-div"></div>
    </div>
</div>
