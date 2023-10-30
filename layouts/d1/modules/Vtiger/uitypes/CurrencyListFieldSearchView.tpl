{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=CURRENCY_LIST value=$FIELD_MODEL->getCurrencyList()}
	<div class="select2_search_div">
        <input type="text" class="listSearchContributor inputElement select2_input_element"/>
		<select class="select2 listSearchContributor" name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{$FIELD_INFO|escape}' style="display:none">
			<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
			{foreach item=CURRENCY_NAME key=CURRENCY_ID from=$CURRENCY_LIST}
				<option value="{$CURRENCY_NAME}" {if ($CURRENCY_NAME eq $SEARCH_INFO['searchValue']) && ($CURRENCY_NAME neq "") } selected{/if}>{vtranslate($CURRENCY_NAME, $MODULE)}</option>
			{/foreach}
		</select>
	</div>
{/strip}