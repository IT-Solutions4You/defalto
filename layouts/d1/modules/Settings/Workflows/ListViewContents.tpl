{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div>
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
        <div class="container-fluid p-3">
            <div class="row justify-content-between">
                <div class="col-lg-3">
                    <div class="foldersContainer">
                        <select class="select2 form-check" id="moduleFilter">
                            <option value="" data-count='{$MODULES_COUNT['All']}'>{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}&nbsp;{vtranslate('LBL_WORKFLOWS')}
                            </option>
                            {foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
                                <option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}" data-count='{if $MODULES_COUNT[$TAB_ID]}{$MODULES_COUNT[$TAB_ID]}{else}0{/if}'>
                                    {vtranslate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}&nbsp;{vtranslate('LBL_WORKFLOWS')}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="search-link input-group">
                        <div class="input-group-text">
                            <span aria-hidden="true" class="fa fa-search"></span>
                        </div>
                        <input class="searchWorkflows form-control" type="text" value="{decode_html($SEARCH_VALUE)|htmlentities}" placeholder="{vtranslate('LBL_WORKFLOW_SEARCH', $QUALIFIED_MODULE)}">
                    </div>
                </div>
                <div class="col-lg-auto">
                    {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
                    {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
                </div>
            </div>
        </div>
        <div class="list-content">
            <div class="">
                <div id="table-content" class="table-container">
                    <table id="listview-table" class="workflow-table table table-borderless">
                        {assign var="NAME_FIELDS" value=$MODULE_MODEL->getNameFields()}
                        {assign var=WIDTHTYPE value=$CURRENT_USER_MODEL->get('rowheight')}
                        <thead>
                            <tr class="listViewContentHeader bg-body-secondary text-secondary">
                                <th></th>
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    {assign var="HEADER_NAME" value=$LISTVIEW_HEADER->get('name')}
                                    {*Showing all columns except description column*}
                                    {if $HEADER_NAME neq 'summary' && $HEADER_NAME neq 'module_name'}
                                        <th nowrap>
                                            <a class="listViewHeaderValues text-secondary">
                                                <span class="me-2">{vtranslate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}</span>
                                            </a>
                                        </th>
                                    {elseif $HEADER_NAME eq 'module_name' && empty($SOURCE_MODULE)}
                                        <th nowrap>
                                            <a class="listViewHeaderValues text-secondary">
                                                <span class="me-2">{vtranslate($LISTVIEW_HEADER->get('label'), $QUALIFIED_MODULE)}</span>
                                            </a>
                                        </th>
                                    {else}
                                    {/if}
                                {/foreach}
                                <th nowrap class="text-secondary">{vtranslate('LBL_ACTIONS', $QUALIFIED_MODULE)}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES}
                            <tr class="listViewEntries border-top" data-id="{$LISTVIEW_ENTRY->getId()}"
                                data-recordurl="{$LISTVIEW_ENTRY->getEditViewUrl()}&mode=V7Edit">
                                <td>
                                    {include file="ListViewRecordActions.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                                </td>
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                                    {assign var=LAST_COLUMN value=$LISTVIEW_HEADER@last}
                                    {if $LISTVIEW_HEADERNAME neq 'summary' && $LISTVIEW_HEADERNAME neq 'module_name'}
                                        <td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
                                            {if $LISTVIEW_HEADERNAME eq 'test'}
                                                {assign var=WORKFLOW_CONDITION value=$LISTVIEW_ENTRY->getConditonDisplayValue()}
                                                {assign var=ALL_CONDITIONS value=$WORKFLOW_CONDITION['All']}
                                                {assign var=ANY_CONDITIONS value=$WORKFLOW_CONDITION['Any']}
                                                <span><strong>{vtranslate('All')}&nbsp;:&nbsp;&nbsp;&nbsp;</strong></span>
                                                {if is_array($ALL_CONDITIONS) && !empty($ALL_CONDITIONS)}
                                                    {foreach item=ALL_CONDITION from=$ALL_CONDITIONS name=allCounter}
                                                        {if $smarty.foreach.allCounter.iteration neq 1}
                                                            <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
                                                        {/if}
                                                        <span>{$ALL_CONDITION}</span>
                                                        <br>
                                                    {/foreach}
                                                {else}
                                                    {vtranslate('LBL_NA')}
                                                {/if}
                                                <br>
                                                <span><strong>{vtranslate('Any')}&nbsp;:&nbsp;</strong></span>
                                                {if is_array($ANY_CONDITIONS) && !empty($ANY_CONDITIONS)}
                                                    {foreach item=ANY_CONDITION from=$ANY_CONDITIONS name=anyCounter}
                                                        {if $smarty.foreach.anyCounter.iteration neq 1}
                                                            <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </span>
                                                        {/if}
                                                        <span>{$ANY_CONDITION}</span>
                                                        <br>
                                                    {/foreach}
                                                {else}
                                                    {vtranslate('LBL_NA')}
                                                {/if}
                                            {elseif $LISTVIEW_HEADERNAME eq 'execution_condition'}
                                                {$LISTVIEW_ENTRY->getDisplayValue('v7_execution_condition')}
                                            {else}
                                                {$LISTVIEW_ENTRY->getDisplayValue($LISTVIEW_HEADERNAME)}
                                            {/if}
                                        </td>
                                    {elseif $LISTVIEW_HEADERNAME eq 'module_name' && empty($SOURCE_MODULE)}
                                        <td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
                                            {assign var="MODULE_ICON_NAME" value="{strtolower($LISTVIEW_ENTRY->get('raw_module_name'))}"}
                                            {Vtiger_Module_Model::getModuleIconPath($LISTVIEW_ENTRY->get('raw_module_name'))}
                                        </td>
                                    {else}
                                    {/if}
                                {/foreach}
                                <td class="listViewEntryValue {$WIDTHTYPE}" width="{$WIDTH}%" nowrap>
                                    {assign var=ACTIONS value=$LISTVIEW_ENTRY->getActionsDisplayValue()}
                                    {if is_array($ACTIONS) && !empty($ACTIONS)}
                                        {foreach item=ACTION_COUNT key=ACTION_NAME from=$ACTIONS}
                                            {vtranslate("LBL_$ACTION_NAME", $QUALIFIED_MODULE)}&nbsp;({$ACTION_COUNT})
                                        {/foreach}
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                            <tr class="emptyRecordsDiv">
                                {assign var=COLSPAN_WIDTH value={php7_count($LISTVIEW_HEADERS)+1}}
                                <td colspan="{$COLSPAN_WIDTH}" style="vertical-align:inherit !important;">
                                    <center>{vtranslate('LBL_NO')} {vtranslate($MODULE, $QUALIFIED_MODULE)} {vtranslate('LBL_FOUND')}</center>
                                </td>
                            </tr>
                        {/if}
                        </tbody>
                    </table>
                </div>
                <div id="scroller_wrapper" class="bottom-fixed-scroll">
                    <div id="scroller" class="scroller-div"></div>
                </div>
            </div>
        </div>
    </div>
{/strip}