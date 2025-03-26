{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Picklist/views/IndexAjax.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
    {assign var=PICKLIST_COLOR_MAP value=Settings_Picklist_Module_Model::getPicklistColorMap($SELECTED_PICKLIST_FIELDMODEL->getName())}
    <style type="text/css">
        {foreach item=PICKLIST_COLOR key=PICKLIST_KEY_ID from=$PICKLIST_COLOR_MAP}
            {assign var=PICKLIST_TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)}
            .picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY_ID} {
                background-color: {$PICKLIST_COLOR};
                color: {$PICKLIST_TEXT_COLOR}; 
            }
        {/foreach}
    </style>
    {assign var=NON_DELETABLE_VALUES value=$SELECTED_PICKLIST_FIELDMODEL->getNonEditablePicklistValues($SELECTED_PICKLIST_FIELDMODEL->getName())}
    <ul class="nav nav-tabs massEditTabs my-3 border-bottom">
        <li class="nav-item ms-3">
            <a class="nav-link active" href="#allValuesLayout" data-bs-toggle="tab"><strong>{vtranslate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}</strong></a>
        </li>
        {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
            <li class="nav-item ms-3" id="assignedToRoleTab">
                <a class="nav-link" href="#AssignedToRoleLayout" data-bs-toggle="tab"><strong>{vtranslate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}</strong></a>
            </li>
        {/if}
    </ul>
    <div class="tab-content layoutContent p-3 themeTableColor overflowVisible">
        <div class="tab-pane active show" id="allValuesLayout">
            <div class="row pickListValuesTableContainer">
                <div class="col-lg-3">
                    <div>{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}&nbsp;{vtranslate('LBL_ITEMS',$QUALIFIED_MODULE)}</div>
                </div>
                <div class="col-lg-6">
                    <div>
                        <button class="btn btn-outline-secondary" id="addItem">
                            <i class="fa fa-plus"></i>
                            <span class="ms-2">{vtranslate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</span>
                        </button>
                    </div>
                    <div id="pickListValuesTable" class="border rounded mt-3">
                        <div class="p-2 border-bottom"><!-- Placeholder role to allow drag-and-drop for last elements --></div>
                        {assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
                        {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
                            <div class="pickListValue border-bottom p-2" data-key-id="{$PICKLIST_KEY}" data-key="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" data-deletable="{if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}true{else}false{/if}">
                                <div class="text-truncate fieldPropertyContainer">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                                    <span class="cursorDrag btn text-secondary">
                                                        <i class="fa-solid fa-grip-vertical"></i>
                                                    </span>
                                        </div>
                                        <div class="col-auto picklistActions">
                                            <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="renameItem btn text-secondary">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            {if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}
                                                <a title="{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}" class="deleteItem btn text-secondary">
                                                    <i class="fa fa-trash-o"></i>
                                                </a>
                                            {/if}
                                        </div>
                                        <div class="col">
                                            <span class="py-1 px-2 rounded picklist-color picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY}">{vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        <div class="p-2 afterPicklistValues"><!-- Placeholder role to allow drag-and-drop for last elements --></div>
                        <span class="picklistActionsTemplate hide">
                                    <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="renameItem btn text-secondary">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a title="{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}" class="deleteItem btn text-secondary">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                </span>
                    </div>
                </div>
            </div>
            <div id="createViewContents" style="display: none;">
                {include file="CreateView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
            </div>
        </div>
        {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
            <div class="tab-pane form-horizontal" id="AssignedToRoleLayout">
                <div class="form-group row">
                    <label class="control-label col-lg-3">{vtranslate('LBL_ROLE_NAME',$QUALIFIED_MODULE)}</label>
                    <div class="controls col-lg-6">
                        <select id="rolesList" class="select2 inputElement" name="rolesSelected" data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
                            {foreach from=$ROLES_LIST item=ROLE}
                                <option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div id="pickListValeByRoleContainer">
                </div>
            </div>
        {/if}
    </div>	
{/strip}
