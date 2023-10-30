{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="detailview-header-block p-3 bg-body rounded">
        <div class="detailview-header">
            {include file='DetailViewHeaderTitle.tpl'|vtemplate_path:$QUALIFIED_MODULE}
            {include file='DetailViewActions.tpl'|vtemplate_path:$QUALIFIED_MODULE}
        </div>
    </div>
{/strip}