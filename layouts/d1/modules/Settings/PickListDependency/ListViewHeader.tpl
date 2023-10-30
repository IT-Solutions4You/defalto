{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/PickListDependency/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="listViewPageDiv" id="listViewContent">
    <div>
        <div id="listview-actions" class="listview-actions-container">
            <div class="px-4 pb-3">
                <div class="container-fluid bg-body p-3 rounded">
                    <div class="row">
                        <div class="col-md-6">
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="listViewActions">
                                <select class="select2 pickListSupportedModules" name="pickListSupportedModules" data-close-on-select="true">
                                    <option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
                                    {foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
                                        {assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
                                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FOR_MODULE} selected {/if}>
                                            {if $MODULE_MODEL->get('label') eq 'Calendar'}
                                                {vtranslate('LBL_TASK', $MODULE_MODEL->get('label'))}
                                            {else}
                                                {vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->get('label'))}
                                            {/if}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-content">