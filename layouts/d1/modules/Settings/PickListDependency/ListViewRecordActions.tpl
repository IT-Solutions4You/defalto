{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    <div class="table-actions">
        {assign var=RECORD_SOURCE_MODULE value=$LISTVIEW_ENTRY->get('sourceModule')}
        {assign var=RECORD_SOURCE_FIELD value=$LISTVIEW_ENTRY->get('sourcefield')}
        {assign var=RECORD_TARGET_FIELD value=$LISTVIEW_ENTRY->get('targetfield')}
        <div class="btn" onclick="javascript:Settings_PickListDependency_Js.triggerEdit(event, '{$RECORD_SOURCE_MODULE}', '{$RECORD_SOURCE_FIELD}', '{$RECORD_TARGET_FIELD}')" title="{vtranslate('LBL_EDIT',$MODULE)}">
            <span class="fa fa-pencil"></span>
        </div>
        <div class="btn" onclick="javascript:Settings_PickListDependency_Js.triggerDelete(event, '{$RECORD_SOURCE_MODULE}', '{$RECORD_SOURCE_FIELD}', '{$RECORD_TARGET_FIELD}')" title="{vtranslate('LBL_DELETE',$MODULE)}">
            <span class="fa fa-trash-o"></span>
        </div>
    </div>
{/strip}