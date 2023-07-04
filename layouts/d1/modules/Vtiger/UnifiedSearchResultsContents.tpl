{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
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