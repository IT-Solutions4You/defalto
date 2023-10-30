{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/Roles/views/Index.php *}
{strip}
    <div class="listViewPageDiv px-4 pb-4" id="listViewContent">
        <div class="p-3 bg-body rounded">
            <br>
            <div class="clearfix treeView">
                <ul>
                    <li data-role="{$ROOT_ROLE->getParentRoleString()}" data-roleid="{$ROOT_ROLE->getId()}">
                        <div class="toolbar-handle">
                            <a href="javascript:;" class="btn bg-primary text-white droppable">{$ROOT_ROLE->getName()}</a>
                            <div class="toolbar btn btn-outline-secondary ms-2" title="{vtranslate('LBL_ADD_RECORD', $QUALIFIED_MODULE)}">
                                <a href="{$ROOT_ROLE->getCreateChildUrl()}" data-url="{$ROOT_ROLE->getCreateChildUrl()}" data-action="modal">
                                    <i class="fa fa-plus"></i>
                                </a>
                            </div>
                        </div>
                        {assign var="ROLE" value=$ROOT_ROLE}
                        {include file=vtemplate_path("RoleTree.tpl", "Settings:Roles")}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
{/strip}