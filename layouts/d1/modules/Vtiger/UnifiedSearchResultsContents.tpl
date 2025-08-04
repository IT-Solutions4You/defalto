{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="container-fluid listViewPageDiv moduleSearchResults">
    <div class="row py-2">
        <div class="col-lg-8">
            <h4 class="searchModuleHeader">{vtranslate($MODULE,$MODULE)}</h4>
        </div>
        <div class="col-lg-auto ms-auto">
            {assign var=RECORD_COUNT value=$LISTVIEW_ENTRIES_COUNT}
            {include file="Pagination.tpl"|vtemplate_path:$MODULE SHOWPAGEJUMP=true}
        </div>
    </div>
    <div class="row py-2">
        {include file="ListViewContents.tpl"|vtemplate_path:$MODULE SEARCH_MODE_RESULTS=true}
    </div>
</div>