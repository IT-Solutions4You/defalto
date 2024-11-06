{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*}

<div class="listViewPageDiv detailViewContainer px-4 pb-4" id="listViewContent">
    <div class="bg-body rounded">
        <div class="p-3 border-bottom">
            <h4 class="m-0">{vtranslate('LBL_PICKLIST_EDITOR',$QUALIFIED_MODULE)}</h4>
        </div>
        <div class="detailViewInfo container-fluid pt-3 px-3">
            <div class="row form-group align-items-center">
                <div class="col-sm-3 control-label fieldLabel pb-3">
                    <label class="fieldLabel ">{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)}</label>
                </div>
                <div class="fieldValue col-sm-6 pb-3">
                    <select class="select2 inputElement" id="pickListModules" name="pickListModules">
                        <option value="0">{vtranslate('LBL_GENERAL_SETTING',$QUALIFIED_MODULE)}</option>
                        {foreach item=INVENTORY_MODULE_NAME key=INVENTORY_MODULE_ID from=$PICKLIST_MODULES}
                            <option {if $SELECTED_MODULE_NAME eq $INVENTORY_MODULE_NAME} selected="" {/if} value="{$INVENTORY_MODULE_ID}">{vtranslate($INVENTORY_MODULE_NAME, $INVENTORY_MODULE_NAME)}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div id="modulePickListContainer">
            {*include file="ModulePickListDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE*}
        </div>
        <div id="modulePickListValuesContainer">
            {if empty($NO_PICKLIST_FIELDS)}
                {*include file="PickListValueDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE*}
            {/if}
        </div>
    </div>
</div>