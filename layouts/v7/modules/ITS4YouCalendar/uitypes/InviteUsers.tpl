<select id="selectedUsers" class="select2 inputElement" multiple name="invite_users[]">
    {assign var=INVITED_USER_IDS value=$FIELD_MODEL->getUITypeModel()->getInvitedUsers($RECORD_ID)}
    {foreach key=USER_ID item=USER_NAME from=$FIELD_MODEL->getUITypeModel()->getAccessibleUsers()}
        <option value="{$USER_ID}" {if in_array($USER_ID,$INVITED_USER_IDS)}selected{/if}>
            {$USER_NAME}
        </option>
    {/foreach}
</select>