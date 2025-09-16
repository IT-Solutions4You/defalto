{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Roles/views/Popup.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
<div class="modal-dialog modal-xl">
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
