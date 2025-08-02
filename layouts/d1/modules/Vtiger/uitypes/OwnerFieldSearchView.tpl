{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()|vtlib_array}
    <div class="select2_search_div">
    {assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
    {assign var=ALL_ACTIVEUSER_LIST value=$FIELD_INFO['picklistvalues'][vtranslate('LBL_USERS')]}
    {assign var=SEARCH_VALUES value=explode(',',(isset($SEARCH_INFO['searchValue'])) ? $SEARCH_INFO['searchValue'] : ',')}
    {assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUES)}

    {if $FIELD_MODEL->get('uitype') eq '52' || $FIELD_MODEL->get('uitype') eq '77'}
		{assign var=ALL_ACTIVEGROUP_LIST value=array()}
    {else}
        {assign var=ALL_ACTIVEGROUP_LIST value=$FIELD_INFO['picklistvalues'][vtranslate('LBL_GROUPS')]}
    {/if}

	{assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule($MODULE)}
	{assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}

    <input type="text" class="listSearchContributor inputElement select2_input_element"/>
	<select class="select2 listSearchContributor {$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}" multiple data-fieldinfo='{Zend_Json::encode($FIELD_INFO)|escape}' style="display:none">
		<optgroup label="{vtranslate('LBL_USERS')}">
			{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                    <option value="{$OWNER_NAME}" data-picklistvalue= '{$OWNER_NAME}' {if in_array(trim(decode_html($OWNER_NAME)),$SEARCH_VALUES)} selected {/if}
						{if array_key_exists($OWNER_ID, $ACCESSIBLE_USER_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if}
						data-userId="{$CURRENT_USER_ID}">
                    {$OWNER_NAME}
                    </option>
			{/foreach}
		</optgroup>
        {if php7_count($ALL_ACTIVEGROUP_LIST) gt 0}
		<optgroup label="{vtranslate('LBL_GROUPS')}">
			{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
				<option value="{$OWNER_NAME}" data-picklistvalue= '{$OWNER_NAME}' {if in_array(trim(decode_html($OWNER_NAME)),$SEARCH_VALUES)} selected {/if}
					{if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
				{$OWNER_NAME}
				</option>
			{/foreach}
		</optgroup>
        {/if}
	</select>
    </div>
{/strip}