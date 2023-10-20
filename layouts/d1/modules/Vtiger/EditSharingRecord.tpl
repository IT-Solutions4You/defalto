{*
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 *}
{strip}
<div class="editViewPageDiv container-fluid h-main py-3">
    <div class="bg-body rounded p-3">
        <div class="editViewContainer">
            <form name="EditSharingRecord" action="index.php" method="post" id="EditView" class="form-horizontal">
                <input type="hidden" name="module" value="{$MODULE}">
                <input type="hidden" name="action" value="SaveSharingRecord">
                <input type="hidden" name="record" value="{$RECORD_ID}">
                <h4 class="mb-3 pb-3 border-bottom">
                    {vtranslate('LBL_EDITING', $MODULE)} {vtranslate('LBL_SHARING_RECORD', $QUALIFIED_MODULE)} - {$RECORD_NAME}
                </h4>
                <div class="editViewBody container-fluid">
                    <div class="form-group row">
                        <div class="col-lg-9 col-md-9 col-sm-9">
                            <div class="container-fluid mb-3">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
                                        {vtranslate('LBL_SHARING_VIEW_MEMBERS', $MODULE)}
                                    </label>
                                    <div class="fieldValue col-lg-9 col-md-9 col-sm-9" id="ViewList" name="ViewList">
                                        <div class="row">
                                            {assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers($RECORD_ID)}
                                            <div class="col-lg-6 col-md-6 col-sm-6">
                                                <select id="memberViewList" class="select2 inputElement" multiple="true" name="memberViewList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
                                                    {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                                        <optgroup label="{vtranslate({$GROUP_LABEL}, $QUALIFIED_MODULE)} {$GROUP_LABEL}" class="{$GROUP_LABEL}">
                                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                                {if $MEMBER->getName() neq $RECORD_MODEL->getName()}
                                                                    <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[1][$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{trim($MEMBER->getName())}</option>
                                                                {/if}
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="container-fluid mb-3">
                                <div class="form-group row">
                                    <label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
                                        {vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}
                                    </label>
                                    <div class="fieldValue col-lg-9 col-md-9 col-sm-9" id="EditList" name="EditList">
                                        <div class="row">
                                            {assign var="GROUP_MEMBERS" value=$RECORD_MODEL->getMembers()}
                                            <div class="col-lg-6 col-md-6 col-sm-6">
                                                <select id="memberEditList" class="select2 inputElement" multiple="true" name="memberEditList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}">
                                                    {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                                        <optgroup label="{vtranslate({$GROUP_LABEL}, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
                                                            {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                                {if $MEMBER->getName() neq $RECORD_MODEL->getName()}
                                                                    <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($GROUP_MEMBERS[2][$GROUP_LABEL][$MEMBER->getId()])} selected="true"{/if}>{trim($MEMBER->getName())}</option>
                                                                {/if}
                                                            {/foreach}
                                                        </optgroup>
                                                    {/foreach}
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="container-fluid">
                                <div class="form-group row">
                                    <div class="col-lg-3">
                                    </div>
                                    <div class="col-lg-9">
                                        <div class="row">
                                            <div class="col-6 d-flex justify-content-between">
                                                <a class="btn btn-primary cancelLink" data-bs-dismiss="modal" href="javascript:history.back()" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                                                <button type="submit" class="btn btn-primary active saveButton">{vtranslate('LBL_SAVE', $MODULE)}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-2">
                            <span class="pull-right groupMembersColors">
                                <ul class="liStyleNone">
                                    <li class="Users padding5per textAlignCenter"><strong>{vtranslate('LBL_USERS', $MODULE)}</strong></li>
                                    <li class="Groups padding5per textAlignCenter"><strong>{vtranslate('LBL_GROUPS', $MODULE)}</strong></li>
                                    <li class="Roles padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLES', $MODULE)}</strong></li>
                                    <li class="RoleAndSubordinates padding5per textAlignCenter"><strong>{vtranslate('LBL_ROLEANDSUBORDINATE', $MODULE)}</strong></li>
                                    {if 1 eq $MULTICOMPANY4YOU}
                                        <li class="MultiCompany4you padding5per textAlignCenter"><strong>{vtranslate('MultiCompany4you', 'MultiCompany4you')}</strong></li>
                                    {/if}
                                </ul>
                            </span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{/strip}
    