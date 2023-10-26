{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
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
    <ul class="nav nav-tabs massEditTabs pt-3 px-3 border-bottom">
        <li class="nav-item me-2">
            <a class="nav-link active" href="#allValuesLayout" data-bs-toggle="tab"><strong>{vtranslate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}</strong></a>
        </li>
        {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
            <li class="nav-item me-2" id="assignedToRoleTab">
                <a class="nav-link" href="#AssignedToRoleLayout" data-bs-toggle="tab"><strong>{vtranslate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}</strong></a>
            </li>
        {/if}
    </ul>
    <div class="tab-content layoutContent p-3 themeTableColor overflowVisible">
        <div class="tab-pane active show" id="allValuesLayout">
            <div class="col-lg-10">
                <div class="container-fluid">
                    <div class="row pickListValuesTableContainer">
                        <div class="col-lg-2">
                            <div>{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}&nbsp;{vtranslate('LBL_ITEMS',$QUALIFIED_MODULE)}</div>
                        </div>
                        <div class="col-lg-8">
                            <table id="pickListValuesTable" class="table" style="table-layout: fixed">
                                <thead>
                                    <tr class="listViewHeaders bg-body-secondary">
                                        <th class="text-end">
                                            <button class="btn btn-outline-secondary" id="addItem">
                                                <i class="fa fa-plus"></i>
                                                <span class="ms-2">{vtranslate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</span>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><!-- Placeholder role to allow drag-and-drop for last elements --></td>
                                </tr>
                                <input type="hidden" id="dragImagePath" value="{vimage_path('drag.png')}"/>
                                {assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
                                {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
                                    <tr class="pickListValue border-bottom" data-key-id="{$PICKLIST_KEY}" data-key="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}" data-deletable="{if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}true{else}false{/if}">
                                        <td class="text-truncate fieldPropertyContainer">
                                            <span class="pull-left"><img class="cursorDrag alignMiddle" src="{vimage_path('drag.png')}"/> &nbsp;&nbsp;
                                                <span class="picklist-color picklist-{$SELECTED_PICKLIST_FIELDMODEL->getId()}-{$PICKLIST_KEY}"> {vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)} </span>
                                            </span>
                                            <span class="pull-right picklistActions" style='margin-top:0px;'>
                                                <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="renameItem"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;&nbsp;
                                                {if !in_array($PICKLIST_VALUE, $NON_DELETABLE_VALUES)}
                                                    <a title="{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}" class="deleteItem"><i class="fa fa-trash-o"></i></a>
                                                {/if}
                                            </span>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <span class="picklistActionsTemplate hide">
                                    <a title="{vtranslate('LBL_EDIT',$QUALIFIED_MODULE)}" class="renameItem ms-2">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    <a title="{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}" class="deleteItem ms-2">
                                        <i class="fa fa-trash-o"></i>
                                    </a>
                                </span>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div id="createViewContents" style="display: none;">
                {include file="CreateView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
            </div>
        </div>
        {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
            <div class="tab-pane form-horizontal row" id="AssignedToRoleLayout">
                <div class="col-lg-10 col-md-10 col-sm-10">
                    <div class="container-fluid">
                        <div class="form-group row">
                            <label class="control-label col-lg-2">{vtranslate('LBL_ROLE_NAME',$QUALIFIED_MODULE)}</label>
                            <div class="controls col-lg-8">
                                <select id="rolesList" class="select2 inputElement" name="rolesSelected" data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
                                    {foreach from=$ROLES_LIST item=ROLE}
                                        <option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="pickListValeByRoleContainer">
                    </div>
                </div>
            </div>
        {/if}
    </div>	
{/strip}
