{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="modalContents">
    <div class="modal-dialog modal-lg basicCreateView">
        <div class='modal-content'>
            <form name="addItemForm" class="form-horizontal" method="post" action="index.php">
                <input type="hidden" name="module" value="{$MODULE}" />
                <input type="hidden" name="parent" value="Settings" />
                <input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}" />
                <input type="hidden" name="action" value="SaveAjax" />
                <input type="hidden" name="mode" value="add" />
                <input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}" />
                <input type="hidden" name="pickListValues" value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($SELECTED_PICKLISTFIELD_ALL_VALUES))}' />
                {assign var=HEADER_TITLE value={vtranslate('LBL_ADD_ITEM_TO', $QUALIFIED_MODULE)}|cat:" "|cat:{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}}
                {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE=$HEADER_TITLE}
                <div class="modal-body container-fluid">
                    <div class="form-group row my-3">
                        <div class="control-label col-sm-4">{vtranslate('LBL_ITEM_VALUE',$QUALIFIED_MODULE)}&nbsp;<span class="redColor">*</span></div>
                        <div class="controls col-sm-5">
                            <select name="newValue" class="form-control" data-tags="true" data-token-separators="," data-rule-required="true" multiple="multiple"></select>
                        </div>
                    </div>
                    {if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
                        <div class="form-group row my-3">
                            <div class="control-label col-sm-4">
                                <div class="d-flex">
                                    <span>{vtranslate('LBL_ASSIGN_TO_ROLE',$QUALIFIED_MODULE)}</span>
                                </div>
                            </div>
                            <div class="controls col-sm-5">
                                <select class="rolesList form-control" name="rolesSelected[]" multiple="multiple" data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
                                    <option value="all" selected>{vtranslate('LBL_ALL_ROLES',$QUALIFIED_MODULE)}</option>
                                    {foreach from=$ROLES_LIST item=ROLE}
                                        <option value="{$ROLE->get('roleid')}">{$ROLE->get('rolename')}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class="input-info-addon cursorPointer ms-2 col-sm-1">
                                <i class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{vtranslate('LBL_ASSIGN_TO_ROLE_INFO',$QUALIFIED_MODULE)}"></i>
                            </div>
                        </div>
                    {/if}
                    <div class="form-group row my-3">
                        <div class="control-label col-sm-4">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</div>
                        <div class="controls col-sm-5">
                            <input type="hidden" name="selectedColor" />
                            <div class="colorPicker">
                            </div>
                        </div>
                    </div>
                </div>
                {include file='ModalFooter.tpl'|@vtemplate_path:$qualifiedName}
            </form>
        </div>
    </div>
</div>
{/strip}