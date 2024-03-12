{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
<div class="sharing-container">
    <div class="row">
        <h4 class="m-0 fw-bold py-3 border-bottom">{vtranslate('Share with', $MODULE)}</h4>
    </div>
    <div class="row py-2">
        <div class="col-lg-2">
            {vtranslate('LBL_SHARING_VIEW_MEMBERS', $MODULE)}
        </div>
        <div class="col-lg-6">
            {assign var=GROUP_MEMBERS value=$TASK_OBJECT->getSharingRecord()->getMembers($RECORD_ID)}
            <select id="memberViewList" class="select2 inputElement" multiple="true" name="memberViewList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}" >
                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                    <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)} {$GROUP_LABEL}" class="{$GROUP_LABEL}">
                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                            <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if $TASK_OBJECT->isSelectedList($MEMBER->getId())}selected="true"{/if}>{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}: {trim($MEMBER->getName())}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="row py-2">
        <div class="col-lg-2">
            {vtranslate('LBL_SHARING_EDIT_MEMBERS', $MODULE)}
        </div>
        <div class="col-lg-6">
            {assign var=GROUP_MEMBERS value=$TASK_OBJECT->getSharingRecord()->getMembers()}
            <select id="memberEditList" class="select2 inputElement" multiple="true" name="memberEditList[]" data-rule-required="" data-placeholder="{vtranslate('LBL_ADD_USERS_ROLES', $QUALIFIED_MODULE)}">
                {foreach from=$MEMBER_GROUPS key=GROUP_LABEL item=ALL_GROUP_MEMBERS}
                    <optgroup label="{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}" class="{$GROUP_LABEL}">
                        {foreach from=$ALL_GROUP_MEMBERS item=MEMBER}
                            <option value="{$MEMBER->getId()}" data-member-type="{$GROUP_LABEL}" {if $TASK_OBJECT->isSelectedEdit($MEMBER->getId())}selected="true"{/if}>{vtranslate($GROUP_LABEL, $QUALIFIED_MODULE)}: {trim($MEMBER->getName())}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
    </div>
</div>