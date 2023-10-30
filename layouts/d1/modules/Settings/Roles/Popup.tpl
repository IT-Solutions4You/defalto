{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Roles/views/Popup.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        {include file="ModalHeader.tpl"|vtemplate_path:$MODULE TITLE={vtranslate('LBL_ASSIGN_ROLE',"Settings:Roles")}}
        <div class="modal-body">
            <div id="popupPageContainer" class="contentsDiv padding30px">
                <div class="clearfix treeView">
                    <ul>
                        <li data-role="{$ROOT_ROLE->getParentRoleString()}" data-roleid="{$ROOT_ROLE->getId()}">
                            <div class="toolbar-handle">
                                <a href="javascript:;" class="btn btn-primary">{$ROOT_ROLE->getName()}</a>
                            </div>
                            {assign var="ROLE" value=$ROOT_ROLE}
                            {include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
                        </li>
                    </ul>
            </div>
            </div>
        </div>
    </div>
</div>    
{/strip}
