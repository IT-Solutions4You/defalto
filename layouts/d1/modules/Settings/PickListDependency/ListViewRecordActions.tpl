{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="table-actions pickList-dependency-actions">
        {assign var=RECORD_SOURCE_MODULE value=$LISTVIEW_ENTRY->get('sourceModule')}
        {assign var=RECORD_SOURCE_FIELD value=$LISTVIEW_ENTRY->get('sourcefield')}
        {assign var=RECORD_TARGET_FIELD value=$LISTVIEW_ENTRY->get('targetfield')}
        <div class="btn text-secondary" onclick="javascript:Settings_PickListDependency_Js.triggerEdit(event, '{$RECORD_SOURCE_MODULE}', '{$RECORD_SOURCE_FIELD}', '{$RECORD_TARGET_FIELD}')" title="{vtranslate('LBL_EDIT',$MODULE)}">
            <span class="fa fa-pencil"></span>
        </div>
        <div class="btn text-secondary" onclick="javascript:Settings_PickListDependency_Js.triggerDelete(event, '{$RECORD_SOURCE_MODULE}', '{$RECORD_SOURCE_FIELD}', '{$RECORD_TARGET_FIELD}')" title="{vtranslate('LBL_DELETE',$MODULE)}">
            <span class="fa fa-trash-o"></span>
        </div>
    </div>
{/strip}