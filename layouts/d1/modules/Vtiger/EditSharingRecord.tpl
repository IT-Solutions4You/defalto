{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="editViewPageDiv container-fluid h-main py-3">
    <div class="bg-body rounded">
        <div class="editViewContainer">
            <form name="EditSharingRecord" action="index.php" method="post" id="EditView" class="form-horizontal">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="action" value="SaveSharingRecord">
                <input type="hidden" name="record" value="{$RECORD_ID}">
                <div class="p-3 border-bottom">
                    <h4 class="m-0">
                        {vtranslate('LBL_EDITING', $MODULE)} {vtranslate('LBL_SHARING_RECORD', $QUALIFIED_MODULE)} - {$RECORD_NAME}
                    </h4>
                </div>
                <div class="editViewBody container-fluid p-3">
                    <div class="form-group row py-2">
                        <label class="col-lg-3 fieldLabel control-label">
                            {vtranslate('LBL_SHARING_VIEW_MEMBERS', $MODULE)}
                        </label>
                        <div class="fieldValue col-lg-6" id="ViewList" name="ViewList">
                            <style>
                                [title*="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}:"]
                                {
                                    background-color: rgba(var(--bs-danger-rgb),0.25) !important;
                                    border-color: rgba(var(--bs-danger-rgb),0.25) !important;
                                }
                                [title*="{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}:"]
                                {
                                    background-color: rgba(var(--bs-primary-rgb),0.25) !important;
                                    border-color: rgba(var(--bs-primary-rgb),0.25) !important;
                                }
                                [title*="{vtranslate('LBL_ROLES', $QUALIFIED_MODULE)}:"]
                                {
                                    background-color: rgba(var(--bs-warning-rgb),0.25) !important;
                                    border-color: rgba(var(--bs-warning-rgb),0.25) !important;
                                }
                                [title*="{vtranslate('RoleAndSubordinates', $QUALIFIED_MODULE)}:"]
                                {
                                    background-color: rgba(var(--bs-success-rgb),0.25) !important;
                                    border-color: rgba(var(--bs-success-rgb),0.25) !important;
                                }
                                [title*="{vtranslate('MultiCompany4you', $QUALIFIED_MODULE)}:"]
                                {
                                    background-color: rgba(var(--bs-info-rgb),0.25) !important;
                                    border-color: rgba(var(--bs-info-rgb),0.25) !important;
                                }
                            </style>
                            {assign var=GROUP_MEMBERS value=$RECORD_MODEL->getMembers($RECORD_ID)}
                            <select id="memberViewList" class="select2 inputElement" multiple="true" name="memberViewList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
                                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                    <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)} {$GROUP_LABEL}" class="{$GROUP_LABEL}">
                                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                            {if $MEMBER->getName() neq $RECORD_MODEL->getName()}
                                                <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[1][$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}: {trim($MEMBER->getName())}</option>
                                            {/if}
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="form-group row py-2">
                        <label class="col-lg-3 fieldLabel control-label">
                            {vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}
                        </label>
                        <div class="fieldValue col-lg-6" id="EditList" name="EditList">
                            {assign var=GROUP_MEMBERS value=$RECORD_MODEL->getMembers()}
                            <select id="memberEditList" class="select2 inputElement" multiple="true" name="memberEditList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}">
                                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                    <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
                                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                            {if $MEMBER->getName() neq $RECORD_MODEL->getName()}
                                                <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[2][$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}: {trim($MEMBER->getName())}</option>
                                            {/if}
                                        {/foreach}
                                    </optgroup>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="row py-2">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-6">
                            <div class="groupMembersColors">
                                <div class="liStyleNone">
                                    <span class="Users btn me-2">{vtranslate('LBL_USERS', $MODULE)}</span>
                                    <span class="Groups btn me-2">{vtranslate('LBL_GROUPS', $MODULE)}</span>
                                    <span class="Roles btn me-2">{vtranslate('LBL_ROLES', $MODULE)}</span>
                                    <span class="RoleAndSubordinates btn me-2">{vtranslate('LBL_ROLEANDSUBORDINATE', $MODULE)}</span>
                                    {if isset($MULTICOMPANY4YOU) && 1 eq $MULTICOMPANY4YOU}
                                        <span class="MultiCompany4you btn me-2">{vtranslate('MultiCompany4you', $QUALIFIED_MODULE)}</span>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid p-3 border-top">
                    <div class="row">
                        <div class="col-6 text-end">
                            <a class="btn btn-primary cancelLink" href="javascript:history.back()">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{/strip}
    