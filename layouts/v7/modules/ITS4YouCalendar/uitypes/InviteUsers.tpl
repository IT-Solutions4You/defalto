{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
<select id="selectedUsers" class="select2 inputElement" multiple name="invite_users[]">
    {assign var=INVITED_USER_IDS value=$FIELD_MODEL->getUITypeModel()->getInvitedUsers($RECORD_ID)}
    {foreach key=USER_ID item=USER_NAME from=$FIELD_MODEL->getUITypeModel()->getAccessibleUsers()}
        <option value="{$USER_ID}" {if in_array($USER_ID,$INVITED_USER_IDS)}selected{/if}>
            {$USER_NAME}
        </option>
    {/foreach}
</select>