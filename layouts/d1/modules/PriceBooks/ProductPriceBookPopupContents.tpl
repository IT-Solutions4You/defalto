{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/PriceBooks/views/ProductPriceBookPopupAjax.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE}
    <div class="row">
        <div class="col-md-2 mb-3">
            {if !empty($LISTVIEW_ENTRIES)}<button class="select btn btn-outline-secondary"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>{/if}
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <input type='hidden' id='pageNumber' value="{$PAGE_NUMBER}">
            <input type='hidden' id='pageLimit' value="{$PAGING_MODEL->getPageLimit()}">
            <input type="hidden" id="noOfEntries" value="{$LISTVIEW_ENTRIES_COUNT}">
            <input type="hidden" id="pageStartRange" value="{$PAGING_MODEL->getRecordStartRange()}" />
            <input type="hidden" id="pageEndRange" value="{$PAGING_MODEL->getRecordEndRange()}" />
            <input type="hidden" id="previousPageExist" value="{$PAGING_MODEL->isPrevPageExists()}" />
            <input type="hidden" id="nextPageExist" value="{$PAGING_MODEL->isNextPageExists()}" />
            <input type="hidden" id="totalCount" value="{$LISTVIEW_COUNT}" />
            <div class="contents-topscroll">
                <div class="topscroll-div"></div>
            </div>
            <div class="popupEntriesDiv relatedContents">
                <input type="hidden" value="{$ORDER_BY}" id="orderBy">
                <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
                <input type="hidden" value="{$SOURCE_FIELD}" id="sourceField">
                <input type="hidden" value="{$SOURCE_RECORD}" id="sourceRecord">
                <input type="hidden" value="{$SOURCE_MODULE}" id="parentModule">
                <input type="hidden" value="Product_PriceBooks_Popup_Js" id="popUpClassName"/>
                <input type="hidden" value="{Vtiger_Util_Helper::toSafeHTML(Zend_JSON::encode($SEARCH_DETAILS))}" id="currentSearchParams" />
                {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                <div class="popupEntriesTableContainer">
                    <table class="listview-table table table-borderless listViewEntriesTable">
                        <thead>
                            <tr class="listViewHeaders bg-body-secondary">
                                <th class="{$WIDTHTYPE}">
                                    <input type="checkbox"  class="selectAllInCurrentPage form-check-input" />
                                </th>
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    <th class="{$WIDTHTYPE}">
                                        <a href="javascript:void(0);" class="listViewContentHeaderValues listViewHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-columnname="{$LISTVIEW_HEADER->get('column')}">
                                            {if $ORDER_BY eq $LISTVIEW_HEADER->get('column')}
                                                <i class="fa {$FASORT_IMAGE}"></i>
                                            {else}
                                                <i class="fa fa-sort customsort"></i>
                                            {/if}
                                            <span class="ms-2">{vtranslate($LISTVIEW_HEADER->get('label'), $MODULE_NAME)}</span>
                                        </a>
                                    </th>
                                {/foreach}
                                <th class="listViewHeaderValues noSorting text-secondary {$WIDTHTYPE}">{vtranslate('LBL_UNIT_PRICE',$MODULE_NAME)}</th>
                                <th class="listViewHeaderValues noSorting text-secondary {$WIDTHTYPE}">{vtranslate('LBL_LIST_PRICE',$MODULE_NAME)}</th>
                            </tr>
                        </thead>
                        {if $MODULE_MODEL && $MODULE_MODEL->isQuickSearchEnabled()}
                            <tr class="searchRow">
                                <td class="searchBtn">
                                    <button class="btn text-secondary" data-trigger="PopupListSearch" title="{vtranslate('LBL_SEARCH', $MODULE )}">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </td>
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    <td>
                                        {assign var=FIELD_UI_TYPE_MODEL value=$LISTVIEW_HEADER->getUITypeModel()}
                                        {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$MODULE_NAME) FIELD_MODEL= $LISTVIEW_HEADER SEARCH_INFO=$SEARCH_DETAILS[$LISTVIEW_HEADER->getName()] USER_MODEL=$USER_MODEL}
                                    </td>
                                {/foreach}
                                <td></td>
                                <td></td>
                            </tr>
                        {/if}

                        {foreach item=LISTVIEW_ENTRY from=$LISTVIEW_ENTRIES name=popupListView}
                            {assign var="RECORD_DATA" value="{$LISTVIEW_ENTRY->getRawData()}"}
                            <tr class="listViewEntries border-top" data-id="{$LISTVIEW_ENTRY->getId()}" data-name='{$LISTVIEW_ENTRY->getName()}' data-currency='{$LISTVIEW_ENTRY->get('currency_id')}'
                                {if $GETURL neq '' } data-url='{$LISTVIEW_ENTRY->$GETURL()}' {/if} id="{$MODULE}_popUpListView_row_{$smarty.foreach.popupListView.index+1}">
                                <td class="{$WIDTHTYPE}">
                                    <input class="entryCheckBox form-check-input" type="checkbox" />
                                </td>
                                {foreach item=LISTVIEW_HEADER from=$LISTVIEW_HEADERS}
                                    {assign var=LISTVIEW_HEADERNAME value=$LISTVIEW_HEADER->get('name')}
                                    <td class="listViewEntryValue text-truncate {$WIDTHTYPE}">
                                        {if $LISTVIEW_HEADER->isNameField() eq true or $LISTVIEW_HEADER->get('uitype') eq '4'}
                                            <a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
                                        {elseif $LISTVIEW_HEADER->getFieldDataType() eq 'currency'}
                                            {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($LISTVIEW_ENTRY->getCurrencyId())}
                                            {CurrencyField::appendCurrencySymbol($LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME), $CURRENCY_INFO['symbol'])}
                                        {else}
                                            <a>{$LISTVIEW_ENTRY->get($LISTVIEW_HEADERNAME)}</a>
                                        {/if}
                                    </td>
                                {/foreach}
                                <td class="listViewEntryValue {$WIDTHTYPE}">
                                    <a>{$LISTVIEW_ENTRY->get('unit_price')}</a>
                                </td>
                                <td class="listViewEntryValue {$WIDTHTYPE}">
                                    <input type="text" value="{$LISTVIEW_ENTRY->get('unit_price')}" name="unit_price" class="inputElement form-control zeroPaddingAndMargin invisible" data-rule-required="true" data-rule-currency="true"
                                               data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}'/>
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                </div>
                    
                <!--added this div for Temporarily -->
                {if $LISTVIEW_ENTRIES_COUNT eq '0'}
                    <div class="row">
                        <div class="emptyRecordsDiv">
                                {vtranslate('LBL_NO', $MODULE_NAME)} {vtranslate($MODULE_NAME, $MODULE_NAME)} {vtranslate('LBL_FOUND', $MODULE_NAME)}.
                        </div>
                    </div>
                {/if}
            </div>
            {if $FIELDS_INFO neq null}
                <script type="text/javascript">
                    var popup_uimeta = (function() {
                        var fieldInfo  = {$FIELDS_INFO};
                        return {
                            field: {
                                get: function(name, property) {
                                    if(name && property === undefined) {
                                        return fieldInfo[name];
                                    }
                                    if(name && property) {
                                        return fieldInfo[name][property]
                                    }
                                },
                                isMandatory : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].mandatory;
                                    }
                                    return false;
                                },
                                getType : function(name){
                                    if(fieldInfo[name]) {
                                        return fieldInfo[name].type
                                    }
                                    return false;
                                }
                            },
                        };
                    })();
                </script>
            {/if}
        </div>
    </div>
{/strip}
