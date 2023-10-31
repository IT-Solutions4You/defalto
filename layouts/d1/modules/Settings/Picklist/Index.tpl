{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Picklist/views/Index.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="listViewPageDiv detailViewContainer px-4 pb-4" id="listViewContent">
    <div class="bg-body rounded">
        <div class="detailViewInfo container-fluid py-3">
            <div class="row form-group">
                <div class="col-sm-3 control-label fieldLabel text-end">
                    <label class="fieldLabel ">{vtranslate('LBL_SELECT_MODULE',$QUALIFIED_MODULE)}</label>
                </div>
                <div class="fieldValue col-sm-3">
                    <select class="select2 inputElement" id="pickListModules" name="pickListModules">
                        <option value="">{vtranslate('LBL_SELECT_OPTION',$QUALIFIED_MODULE)}</option>
                        {foreach item=PICKLIST_MODULE from=$PICKLIST_MODULES}
                            <option {if $SELECTED_MODULE_NAME eq $PICKLIST_MODULE->get('name')} selected="" {/if} value="{$PICKLIST_MODULE->get('name')}">{vtranslate($PICKLIST_MODULE->get('name'),$PICKLIST_MODULE->get('name'))}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div id="modulePickListContainer">
            {include file="ModulePickListDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
        </div>
        <br>
        <div id="modulePickListValuesContainer">
            {if empty($NO_PICKLIST_FIELDS)}
                {include file="PickListValueDetail.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
            {/if}
        </div>

    </div>
</div>