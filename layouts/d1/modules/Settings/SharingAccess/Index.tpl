{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* modules/Settings/SharingAccess/views/Index.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
{strip}
<div class="listViewPageDiv " id="sharingAccessContainer">
    <div class="px-4 pb-4">
        <form name="EditSharingAccess" action="index.php" method="post" class="form-horizontal py-3 bg-body rounded" id="EditSharingAccess">
            <input type="hidden" name="module" value="SharingAccess" />
            <input type="hidden" name="action" value="SaveAjax" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" class="dependentModules" value='{ZEND_JSON::encode($DEPENDENT_MODULES)}' />
            <div class="container-fluid pb-3">
                <div class="row">
                    <div class="col">
                        <h4 class="m-0">{vtranslate('LBL_SHARING_ACCESS', $QUALIFIED_MODULE)}</h4>
                    </div>
                </div>
            </div>
            <div class="contents">
                <table class="table table-borderless table-condensed sharingAccessDetails">
                    <colgroup>
                        <col width="20%">
                        <col width="15%">
                        <col width="15%">
                        <col width="20%">
                        <col width="10%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                        <tr class="">
                            <th class="text-secondary bg-body-secondary">
                                {vtranslate('LBL_MODULE', $QUALIFIED_MODULE)}
                            </th>
                            {foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
                                <th class="text-secondary bg-body-secondary">
                                    {$ACTION_MODEL->getName()|vtranslate:$QUALIFIED_MODULE}
                                </th>
                            {/foreach}
                            <th class="text-secondary bg-body-secondary" nowrap="nowrap">{'LBL_ADVANCED_SHARING_RULES'|vtranslate:$QUALIFIED_MODULE}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                            <tr data-module-name="{$MODULE_MODEL->get('name')}" class="border-bottom">
                                <td>
                                    {$MODULE_MODEL->get('label')|vtranslate:$MODULE_MODEL->getName()}
                                </td>
                                {foreach from=$ALL_ACTIONS key=ACTION_ID item=ACTION_MODEL}
                                <td class="">
                                    {if $ACTION_MODEL->isModuleEnabled($MODULE_MODEL)}
                                    <div class="text-center">
                                        <input type="radio" name="permissions[{$TABID}]" data-action-state="{$ACTION_MODEL->getName()}" value="{$ACTION_ID}"{if $MODULE_MODEL->getPermissionValue() eq $ACTION_ID}checked="true"{/if}>
                                    </div>
                                    {/if}
                                </td>
                                {/foreach}
                                <td class="triggerCustomSharingAccess">
                                    <div class="row">
                                        <span class="col-sm-4">&nbsp;</span>
                                        <span class="col-sm-4">
                                            <button type="button" class="btn btn-sm btn-default vtButton" data-handlerfor="fields" data-togglehandler="{$TABID}-rules" style="padding-right: 20px; padding-left: 20px;">
                                                <i class="fa fa-chevron-down"></i>
                                            </button>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="modal-overlay-footer container-fluid saveSharingAccess hide">
                <div class="row">
                    <div class="col text-center">
                        <button class="btn btn-primary active saveButton" name="saveButton" type="submit">{vtranslate('LBL_APPLY_NEW_SHARING_RULES', $QUALIFIED_MODULE)}</button>&nbsp;&nbsp;
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
{/strip}
