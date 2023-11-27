{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="detailViewContainer px-4 pb-4">
        <div class="col-sm-12">
            {include file="DetailViewHeader.tpl"|vtemplate_path:Vtiger MODULE_NAME=$MODULE_NAME}
            {include file='DetailViewBlockView.tpl'|@vtemplate_path:Vtiger RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
            {include file='FieldsDetailView.tpl'|@vtemplate_path:$MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE_NAME=$MODULE_NAME}
        </div>
    </div>
    </div>
    </div>
{/strip}