{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
{assign var=ROLES value=$FIELD_MODEL->getAllRoles()}
{if !isset($SEARCH_INFO['searchValue'])}
	{$SEARCH_INFO['searchValue'] = ''}
{/if}
{assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
<div class="select2_search_div">
	<input type="text" class="listSearchContributor inputElement select2_input_element"/>
	<select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" multiple data-fieldinfo='{$FIELD_INFO|escape}' style="display:none;">
		{foreach item=ROLE_ID key=ROLE_NAME from=$ROLES}
			<option value="{$ROLE_NAME}" {if in_array($ROLE_NAME,$SEARCH_VALUES) && ($ROLE_NAME neq "") } selected{/if}>{$ROLE_NAME}</option>
		{/foreach}
	</select>
</div>
{/strip}