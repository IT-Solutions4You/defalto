{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/PickListDependency/views/List.php *}

{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<div class="listViewPageDiv" id="listViewContent">
    <div>
        <div id="listview-actions" class="listview-actions-container">
            <div class="px-4 pb-3">
                <div class="container-fluid bg-body pt-3 px-3 rounded">
                    <div class="row">
                        <div class="col-md-6 pb-3">
                            <h4 class="m-0">{vtranslate('PickListDependency', $QUALIFIED_MODULE)}</h4>
                        </div>
                        <div class="col-md-6 text-end pb-3">
                            <div class="listViewActions">
                                <select class="select2 pickListSupportedModules" name="pickListSupportedModules" data-close-on-select="true">
                                    <option value="">{vtranslate('LBL_ALL', $QUALIFIED_MODULE)}</option>
                                    {foreach item=MODULE_MODEL from=$PICKLIST_MODULES_LIST}
                                        {assign var=MODULE_NAME value=$MODULE_MODEL->get('name')}
                                        <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $FOR_MODULE} selected {/if}>
                                            {vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->get('label'))}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="list-content">