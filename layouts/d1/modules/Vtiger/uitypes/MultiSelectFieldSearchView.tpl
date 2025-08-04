{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
	{assign var=PICKLIST_VALUES value=$FIELD_INFO['picklistvalues']}
	{assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_INFO))}
    {assign var=SEARCH_VALUES value=explode(',',$SEARCH_INFO['searchValue'])}
    <div class="select2_search_div">
        <input type="text" class="listSearchContributor inputElement select2_input_element"/>
        <select class="select2 listSearchContributor"  name="{$FIELD_MODEL->get('name')}" multiple data-fieldinfo='{$FIELD_INFO|escape}' style="display:none">
            {foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
                <option value="{$PICKLIST_KEY}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "")} selected{/if}>{$PICKLIST_LABEL}</option>
            {/foreach}
        </select>
    </div>
{/strip}