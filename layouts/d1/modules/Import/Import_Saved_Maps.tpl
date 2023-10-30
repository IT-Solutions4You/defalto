{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}

<div class="row" style = "margin-bottom: 10px">
    <div class = "form-group">
        <div class = "col-lg-2" style="margin-top:8px">
            <label class ="control-label" for="saved_maps">{'LBL_USE_SAVED_MAPS'|@vtranslate:$MODULE}</label>
        </div>
        <div class="col-lg-4">
            <select name="saved_maps" id="saved_maps" class="select2 form-control" onchange="Vtiger_Import_Js.loadSavedMap();">
                <option id="-1" value="" selected>--{'LBL_SELECT_SAVED_MAPPING'|@vtranslate:$MODULE}--</option>
                {foreach key=_MAP_ID item=_MAP from=$SAVED_MAPS}
                    <option id="{$_MAP_ID}" value="{$_MAP->getStringifiedContent()}">{$_MAP->getValue('name')}</option>
                {/foreach}
            </select>
        </div>
        <div id="delete_map_container" class ="col-lg-1" style="display:none; margin-top: 10px">
            <a class="glyphicon glyphicon-trash cursorPointer" onclick="Vtiger_Import_Js.deleteMap('{$FOR_MODULE}');" alt="{'LBL_DELETE'|@vtranslate:$FOR_MODULE}"></a>
        </div>
    </div>
</div>


