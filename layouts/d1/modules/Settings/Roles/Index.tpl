{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}
{* modules/Settings/Roles/views/Index.php *}

{strip}
    <div class="listViewPageDiv px-4 pb-4" id="listViewContent">
        <div class="p-3 bg-white rounded">
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
{/strip}