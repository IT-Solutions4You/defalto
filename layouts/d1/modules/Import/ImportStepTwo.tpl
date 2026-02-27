{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="importBlockContainer hide" id="importStep2Conatiner">
    <div class="container-fluid" id="duplicates_merge_configuration">
        <div class="row py-2">
            <div class="col">
                <h4>{'LBL_DUPLICATE_RECORD_HANDLING'|@vtranslate:$MODULE}</h4>
                <hr>
            </div>
        </div>
        <div class="row py-2">
            <div class="col">
                <b>{'LBL_SPECIFY_MERGE_TYPE'|@vtranslate:$MODULE}</b>
                <select name="merge_type" id="merge_type" class="select select2 form-control">
                    {foreach key=_MERGE_TYPE item=_MERGE_TYPE_LABEL from=$AUTO_MERGE_TYPES}
                        <option value="{$_MERGE_TYPE}">{$_MERGE_TYPE_LABEL|@vtranslate:$MODULE}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-12">
                <b>{'LBL_SELECT_MERGE_FIELDS'|@vtranslate:$MODULE}</b>
            </div>
        </div>
        <div class="row py-2">
            <div class="col">
                <b>{'LBL_AVAILABLE_FIELDS'|@vtranslate:$MODULE}</b>
                <select id="available_fields" multiple size="10" name="available_fields" class="txtBox form-control">
                    {foreach key=_FIELD_NAME item=_FIELD_INFO from=$AVAILABLE_FIELDS}
                        {if $_FIELD_NAME eq 'tags'} {continue} {/if}
                        <option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-default btn-lg" onClick="return Vtiger_Import_Js.copySelectedOptions('#available_fields', '#selected_merge_fields')">
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button class="btn btn-default btn-lg" onClick="return Vtiger_Import_Js.removeSelectedOptions('#selected_merge_fields')">
                    <i class="fa-solid fa-arrow-left"></i>
                </button>
            </div>
            <div class="col">
                <b>{'LBL_SELECTED_FIELDS'|@vtranslate:$MODULE}</b>
                <input type="hidden" id="merge_fields" size="10" name="merge_fields" value=""/>
                <select id="selected_merge_fields" size="10" name="selected_merge_fields" multiple class="txtBox form-control">
                    {foreach key=_FIELD_NAME item=_FIELD_INFO from=$ENTITY_FIELDS}
                        <option value="{$_FIELD_NAME}">{$_FIELD_INFO->getFieldLabelKey()|@vtranslate:$FOR_MODULE}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
</div>

