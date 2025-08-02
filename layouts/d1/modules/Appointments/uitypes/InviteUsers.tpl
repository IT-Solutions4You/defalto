{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<select id="selectedUsers" class="select2 inputElement" multiple name="invite_users[]">
    {assign var=INVITED_USER_IDS value=$FIELD_MODEL->getUITypeModel()->getInvitedUsers($RECORD_ID)}
    {foreach key=USER_ID item=USER_NAME from=$FIELD_MODEL->getUITypeModel()->getAccessibleUsers()}
        <option value="{$USER_ID}" {if in_array($USER_ID,$INVITED_USER_IDS)}selected{/if}>
            {$USER_NAME}
        </option>
    {/foreach}
</select>