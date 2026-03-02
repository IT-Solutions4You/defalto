{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="row py-2 align-items-center justify-content-end">
        <div class="col-lg-2 text-secondary">
            <label for="saved_maps">{'LBL_USE_SAVED_MAPS'|@vtranslate:$MODULE}</label>
        </div>
        <div class="col-lg-4">
            <div class="input-group">
                <select name="saved_maps" id="saved_maps" class="select2 form-control" onchange="Vtiger_Import_Js.loadSavedMap();">
                    <option id="-1" value="" selected>--{'LBL_SELECT_SAVED_MAPPING'|@vtranslate:$MODULE}--</option>
                    {foreach key=_MAP_ID item=_MAP from=$SAVED_MAPS}
                        <option id="{$_MAP_ID}" value="{$_MAP->getStringifiedContent()}">{$_MAP->getValue('name')}</option>
                    {/foreach}
                </select>
                <button type="button" class="btn btn-outline-secondary" id="delete_map_container" onclick="Vtiger_Import_Js.deleteMap('{$FOR_MODULE}');" title="{'LBL_DELETE'|vtranslate:$FOR_MODULE}">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
{/strip}