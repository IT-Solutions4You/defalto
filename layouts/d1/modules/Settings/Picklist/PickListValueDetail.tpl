{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
        <div class="tab-pane active show pickListValuesTableContainer" id="allValuesLayout">
            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <div class="container pb-3">
                        <button class="btn btn-primary active" id="addItem">
                            <i class="fa fa-plus"></i>
                            <span class="ms-2">{vtranslate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</span>
                        </button>
                    </div>
                    <div class="container border rounded">
                        <div class="row py-2 border-bottom bg-body-secondary align-items-center">
                            <div class="col-auto invisible">
                                <span class="btn">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </span>
                                <span class="btn">
                                    <i class="fa-solid fa-palette"></i>
                                </span>
                            </div>
                            <div class="col fw-bold">{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}</div>
                        </div>
                        <div id="pickListValuesTable" class="row">
                            <div class="p-2 border-bottom"><!-- Placeholder role to allow drag-and-drop for last elements --></div>
                            {assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
                            {foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
                                {include file="PickListValueRow.tpl"|vtemplate_path:$QUALIFIED_MODULE}
                            {/foreach}
                            <div class="p-2 afterPicklistValues"><!-- Placeholder role to allow drag-and-drop for last elements --></div>
                            <div class="pickListValueClone hide">
                                {include file="PickListValueRow.tpl"|vtemplate_path:$QUALIFIED_MODULE PICKLIST_KEY='clone_id' PICKLIST_VALUE='clone_value'}
                            </div>
                        </div>
                    </div>
                    <div id="createViewContents" style="display: none;">
                        {include file="CreateView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
                    </div>
                </div>
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
