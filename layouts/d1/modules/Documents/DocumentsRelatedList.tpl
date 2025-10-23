{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {include file="PicklistColorMap.tpl"|vtemplate_path:$MODULE LISTVIEW_HEADERS=$RELATED_HEADERS}
    <div class="relatedContainer container-fluid">
        <div class="rounded bg-body mt-3">
            {assign var=RELATED_MODULE_NAME value=$RELATED_MODULE->get('name')}
            {assign var=IS_RELATION_FIELD_ACTIVE value="{if $RELATION_FIELD}{$RELATION_FIELD->isActiveField()}{else}false{/if}"}
            <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}"/>
            <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE_NAME}"/>
            <input type="hidden" value="{$ORDER_BY}" id="orderBy">
            <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
            <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
            <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
            <input type='hidden' value="{$PAGING->get('page')}" id='pageNumber'>
            <input type="hidden" value="{$PAGING->isNextPageExists()}" id="nextPageExist"/>
            <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
            <input type='hidden' value="{$TAB_LABEL}" id='tab_label' name='tab_label'>
            <input type='hidden' value="{$IS_RELATION_FIELD_ACTIVE}" id='isRelationFieldActive'>
            <div class="relatedHeader p-3 row">
                <div class="col-lg">
                    <div class="btn-toolbar">
                        {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                            {if $RELATED_LINK->get('linkmodule') eq 'Documents'}
                                {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                                {assign var=LINK_LABEL value={$RELATED_LINK->get('linklabel')}}
                                <button type="button" module="{$RELATED_MODULE_NAME}" class="btn btn-outline-secondary me-2 addButton {if $IS_SELECT_BUTTON eq true}selectRelation{/if}"
                                    {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                                    {if ($RELATED_LINK->isPageLoadLink())}
                                        {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                                        data-url="{$RELATED_LINK->getUrl()}"
                                    {/if}
                                    {if $IS_SELECT_BUTTON neq true} name="addButton" {/if}>
                                    {if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
                                    <span class="ms-2">{$RELATED_LINK->getLabel()}</span>
                                </button>
                            {/if}
                            {if $RELATED_LINK->getLabel() eq 'Vtiger'}
                                {if $IS_CREATE_PERMITTED}
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-outline-secondary me-2 dropdown-toggle" data-bs-toggle="dropdown">
                                            <span class="fa fa-plus" title="{vtranslate('LBL_NEW_DOCUMENT', $MODULE)}"></span>&nbsp;&nbsp;{vtranslate('LBL_NEW_DOCUMENT', $RELATED_MODULE_NAME)}&nbsp; <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li class="dropdown-header"><i class="fa fa-upload"></i> {vtranslate('LBL_FILE_UPLOAD', $RELATED_MODULE_NAME)}</li>
                                            <li id="VtigerAction">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.uploadTo('Vtiger',{$PARENT_ID},'{$MODULE}')">
                                                    <i class="fa-solid fa-house"></i>
                                                    <span class="ms-2">{vtranslate('LBL_TO_SERVICE', $RELATED_MODULE_NAME, {vtranslate('LBL_CRM', $RELATED_MODULE_NAME)})}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li class="dropdown-header">
                                                <i class="fa fa-link"></i>
                                                <span class="ms-2">{vtranslate('LBL_LINK_EXTERNAL_DOCUMENT', $RELATED_MODULE_NAME)}</span>
                                            </li>
                                            <li id="shareDocument">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('E',{$PARENT_ID},'{$MODULE}')">
                                                    <i class="fa fa-external-link"></i>
                                                    <span class="ms-2">{vtranslate('LBL_FROM_SERVICE', $RELATED_MODULE_NAME, {vtranslate('LBL_FILE_URL', $RELATED_MODULE_NAME)})}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li id="createDocument">
                                                <a class="dropdown-item" href="javascript:Documents_Index_Js.createDocument('W',{$PARENT_ID},'{$MODULE}')">
                                                    <i class="fa fa-file-text"></i>
                                                    <span class="ms-2">{vtranslate('LBL_CREATE_NEW', $RELATED_MODULE_NAME, {vtranslate('SINGLE_Documents', $RELATED_MODULE_NAME)})}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                {/if}
                            {/if}
                        {/foreach}
                        {assign var=CLASS_VIEW_ACTION value='relatedViewActions'}
                        {assign var=CLASS_VIEW_PAGING_INPUT value='relatedViewPagingInput'}
                        {assign var=CLASS_VIEW_PAGING_INPUT_SUBMIT value='relatedViewPagingInputSubmit'}
                        {assign var=CLASS_VIEW_BASIC_ACTION value='relatedViewBasicAction'}
                        {assign var=PAGING_MODEL value=$PAGING}
                        {assign var=RECORD_COUNT value=$RELATED_RECORDS|@count}
                        {assign var=PAGE_NUMBER value=$PAGING->get('page')}
                    </div>
                </div>
                <div class="col-lg-auto text-end">
                    {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
                </div>
            </div>
            <div class="relatedContents col-lg-12 col-md-12 col-sm-12 table-container">
                <div class="bottomscroll-div">
                    {assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
                    <table id="listview-table" class="table listview-table table-borderless">
                        <thead>
                            <tr class="listViewHeaders bg-body-secondary">
                                <th></th>
                                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                    <th class="nowrap">
                                        {if $HEADER_FIELD->get('column') eq "access_count" or $HEADER_FIELD->get('column') eq "idlists"}
                                            <a href="javascript:void(0);" class="noSorting text-secondary">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
                                        {else}
                                            <a href="javascript:void(0);" class="listViewContentHeaderValues text-secondary text-nowrap" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">
                                                {if isset($FASORT_IMAGE) && $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                                    <i class="fa {$FASORT_IMAGE}"></i>
                                                {else}
                                                    <i class="fa fa-sort customsort"></i>
                                                {/if}
                                                <span class="mx-2">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</span>
                                                {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE}">{/if}
                                            </a>
                                            {if $COLUMN_NAME eq $HEADER_FIELD->get('column')}
                                                <a href="#" class="removeSorting text-secondary"><i class="fa fa-remove"></i></a>
                                            {/if}
                                        {/if}
                                    </th>
                                {/foreach}
                            </tr>
                            <tr class="searchRow">
                                <th class="inline-search-btn">
                                    <button class="btn text-secondary btn-sm" data-trigger="relatedListSearch" title="{vtranslate("LBL_SEARCH",$MODULE)}"><i class="fa fa-search"></i></button>
                                </th>
                                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                    <th>
                                        {if $HEADER_FIELD->get('column') eq 'time_start' or $HEADER_FIELD->get('column') eq 'time_end' or $HEADER_FIELD->get('column') eq 'folderid' or $HEADER_FIELD->getFieldDataType() eq 'reference'}
                                        {else}
                                            {assign var=FIELD_UI_TYPE_MODEL value=$HEADER_FIELD->getUITypeModel()}
                                            {assign var=SEARCH_DETAILS_FIELD_INFO value=['searchValue' => '', 'comparator' => '']}
                                            {if isset($SEARCH_DETAILS[$HEADER_FIELD->getName()])}
                                                {assign var=SEARCH_DETAILS_FIELD_INFO value=$SEARCH_DETAILS[$HEADER_FIELD->getName()]}
                                            {/if}
                                            {include file=vtemplate_path($FIELD_UI_TYPE_MODEL->getListSearchTemplateName(),$RELATED_MODULE_NAME) FIELD_MODEL= $HEADER_FIELD SEARCH_INFO=$SEARCH_DETAILS_FIELD_INFO USER_MODEL=$USER_MODEL}
                                            <input type="hidden" class="operatorValue" value="{$SEARCH_DETAILS_FIELD_INFO['comparator']}">
                                        {/if}
                                    </th>
                                {/foreach}
                            </tr>
                        </thead>
                        {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                            <tr class="listViewEntries border-top" data-id="{$RELATED_RECORD->getId()}" data-recordUrl="{$RELATED_RECORD->getDetailViewUrl()}">
                                <td>
                                    <span class="actionImages btn-group">
                                        <a class="btn btn-sm text-secondary" name="relationEdit" data-url="{$RELATED_RECORD->getEditViewUrl()}">
                                            <i title="{vtranslate('LBL_EDIT', $MODULE)}" class="fa fa-pencil"></i>
                                        </a>
                                        {if $IS_DELETABLE}
                                            <a class="btn btn-sm text-secondary relationDelete">
                                                <i title="{vtranslate('LBL_UNLINK', $MODULE)}" class="vicon-linkopen"></i>
                                            </a>
                                        {/if}
                                        {assign var=RECORD_ID value=$RELATED_RECORD->getId()}
                                        {assign var="DOCUMENT_RECORD_MODEL" value=Vtiger_Record_Model::getInstanceById($RECORD_ID)}
                                        {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus')}
                                            <a class="btn btn-sm text-secondary" name="viewfile" href="" data-filelocationtype="{$DOCUMENT_RECORD_MODEL->get('filelocationtype')}" data-filename="{$DOCUMENT_RECORD_MODEL->get('filename')}" onclick="Vtiger_Header_Js.previewFile(event)">
                                                <i title="{vtranslate('LBL_VIEW_FILE', $RELATED_MODULE_NAME)}" class="fa fa-picture-o alignMiddle"></i>
                                            </a>
                                        {/if}
                                        {if $DOCUMENT_RECORD_MODEL->get('filename') && $DOCUMENT_RECORD_MODEL->get('filestatus') && $DOCUMENT_RECORD_MODEL->get('filelocationtype') eq 'I'}
                                            <a class="btn btn-sm text-secondary" name="downloadfile" href="{$DOCUMENT_RECORD_MODEL->getDownloadFileURL()}" onclick="event.stopImmediatePropagation();">
                                                <i title="{vtranslate('LBL_DOWNLOAD_FILE', $RELATED_MODULE_NAME)}" class="fa fa-download alignMiddle"></i>
                                            </a>
                                        {/if}
                                    </span>
                                </td>
                                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                                    {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
                                    {assign var=RELATED_LIST_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
                                    {assign var=IS_DOCUMENT_SOURCE_FIELD value=0}
                                    {if $RELATED_MODULE->get('name') eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
                                        {if $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Vtiger' || $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Google Drive' || $RELATED_RECORD->get($RELATED_HEADERNAME) eq 'Dropbox'}
                                            {assign var=IS_DOCUMENT_SOURCE_FIELD value=1}
                                        {/if}
                                    {/if}
                                    <td class="relatedListEntryValues nowrap {$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" style="width:inherit;">
                                        {if $RELATED_MODULE->get('name') eq 'Documents' && $RELATED_HEADERNAME eq 'document_source'}
                                            <div style="text-align: center;">{$RELATED_RECORD->get($RELATED_HEADERNAME)}</div>
                                        {else}
                                            <span class="value text-truncate">
                                                {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                                                    <a class="fw-bold" href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                                                {elseif $RELATED_HEADERNAME eq 'access_count'}
                                                    {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                                                {elseif $RELATED_HEADERNAME eq 'time_start' or $RELATED_HEADERNAME eq 'time_end'}
                                                {elseif $RELATED_MODULE_NAME eq 'PriceBooks' AND ($RELATED_HEADERNAME eq 'listprice' || $RELATED_HEADERNAME eq 'unit_price')}
                                                    {if $RELATED_HEADERNAME eq 'listprice'}
                                                        {assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                                    {/if}
                                                    {CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                                {elseif $HEADER_FIELD->getFieldDataType() eq 'currency'}
                                                    {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($RELATED_RECORD->getCurrencyId())}
                                                    {CurrencyField::appendCurrencySymbol($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME), $CURRENCY_INFO['symbol'])}
                                                    {if $RELATED_HEADERNAME eq 'listprice'}
                                                        {assign var="LISTPRICE" value=CurrencyField::convertToUserFormat($RELATED_RECORD->get($RELATED_HEADERNAME), null, true)}
                                                    {/if}
                                                {elseif $HEADER_FIELD->getFieldDataType() eq 'picklist' and $FIELD_MODEL->isPicklistColorSupported()}
                                                    <span class="py-1 px-2 rounded picklist-color picklist-{$HEADER_FIELD->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($RELATED_LIST_VALUE)}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</span>
                                                {else}
                                                    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                                                {/if}
                                            </span>
                                        {/if}
                                    </td>
                                {/foreach}
                            </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <script type="text/javascript">
                var related_uimeta = (function () {
                    var fieldInfo = {$RELATED_FIELDS_INFO};
                    return {
                        field: {
                            get: function (name, property) {
                                if (name && property === undefined) {
                                    return fieldInfo[name];
                                }
                                if (name && property) {
                                    return fieldInfo[name][property]
                                }
                            },
                            isMandatory: function (name) {
                                if (fieldInfo[name]) {
                                    return fieldInfo[name].mandatory;
                                }
                                return false;
                            },
                            getType: function (name) {
                                if (fieldInfo[name]) {
                                    return fieldInfo[name].type
                                }
                                return false;
                            }
                        },
                    };
                })();
            </script>
        </div>
    </div>
{/strip}
{literal}
    <script type="text/javascript">
        jQuery(function () {
            if (typeof Documents_Index_Js !== 'function') {
                jQuery("body").append('<script type="text/javascript" src="layouts/d1/modules/Documents/resources/Documents.js"><\/script>');
            }
        });
    </script>
{/literal}