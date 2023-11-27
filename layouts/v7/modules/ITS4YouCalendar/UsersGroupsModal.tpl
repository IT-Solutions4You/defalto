<div id="modal_users_and_groups" class="modal-dialog">
    <div class="modal-content">
        {include file='ModalHeader.tpl'|vtemplate_path:$QUALIFIED_MODULE TITLE=vtranslate('LBL_USERS_GROUP_TITLE', $QUALIFIED_MODULE)}
        <div class="modal-body">
            <div class="usersGroupsContainer">
                <div>
                    <b>{vtranslate('LBL_USERS_GROUP_TITLE', $QUALIFIED_MODULE)}</b>
                </div>
                <select name="field_users_groups_modal" class="select2 inputElement" multiple="multiple">
                    {foreach from=$USERS_GROUPS_VALUES item=USERS_GROUPS_RECORDS key=USERS_GROUP_TYPE}
                        <optgroup label="{vtranslate($USERS_GROUP_TYPE, $QUALIFIED_MODULE)}">
                            {foreach from=$USERS_GROUPS_RECORDS item=USERS_GROUPS_VALUE key=USERS_GROUPS_KEY}
                                {assign var=USERS_GROUP_OPTION value=implode('::::', [$USERS_GROUP_TYPE, $USERS_GROUPS_KEY])}
                                <option value="{$USERS_GROUP_OPTION}" {if in_array($USERS_GROUP_OPTION, $USERS_GROUPS_SELECTED)}selected="selected"{/if}>{$USERS_GROUPS_VALUE}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="selectUsersGroups btn btn-success">{vtranslate('LBL_SELECT', $QUALIFIED_MODULE)}</button>
        </div>
    </div>
</div>