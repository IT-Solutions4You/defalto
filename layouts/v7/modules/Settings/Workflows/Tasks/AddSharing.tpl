{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<div class="editViewPageDiv">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="editViewContainer">
            <div class="editViewBody">
                <div class="form-group row">
                    <span class="col-lg-9 col-md-9 col-sm-9">
                        <div class="row-fluid">
                            <div class="form-group row">
                                <label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
                                    {vtranslate('LBL_SHARING_VIEW_MEMBERS', $MODULE)}
                                </label>
                                <div class="fieldValue col-lg-9 col-md-9 col-sm-9" id="ViewList" name="ViewList">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                            <select id="memberViewList" class="select2 inputElement" multiple="true" name="memberViewList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
                                                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                                    <optgroup label="{vtranslate({$GROUP_LABEL}, $QUALIFIED_MODULE)} {$GROUP_LABEL}" class="{$GROUP_LABEL}">
                                                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                            <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($memberViewList[$MEMBER->getId()])} selected="true"{/if}>{trim($MEMBER->getName())}</option>
                                                        {/foreach}
                                                    </optgroup>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                                <br/>&nbsp<br/>
                        <div class="row-fluid">
                            <span class="span2">
                                <div class="form-group row">
                                <label class="col-lg-3 col-md-3 col-sm-3 fieldLabel control-label">
                                    {vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}
                                </label>
                                <div class="fieldValue col-lg-9 col-md-9 col-sm-9" id="EditList" name="EditList">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6">
                                            <select id="memberEditList" class="select2 inputElement" multiple="true" name="memberEditList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
                                                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                                                    <optgroup label="{vtranslate({$GROUP_LABEL}, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
                                                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                                                            <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if isset($memberEditList[$MEMBER->getId()])} selected="true"{/if}>{trim($MEMBER->getName())}</option>
                                                        {/foreach}
                                                    </optgroup>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </span>
                        </div>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
