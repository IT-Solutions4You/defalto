{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{assign var=CURRENCY_LIST value=$FIELD_MODEL->getCurrencyList()}
	<select class="select2 inputElement" name="{$FIELD_MODEL->getFieldName()}">
		{foreach item=CURRENCY_NAME key=CURRENCY_ID from=$CURRENCY_LIST}
			<option value="{$CURRENCY_ID}" data-picklistvalue= '{$CURRENCY_ID}' {if $FIELD_MODEL->get('fieldvalue') eq $CURRENCY_ID} selected {/if}>{vtranslate($CURRENCY_NAME, $MODULE)}</option>
		{/foreach}
	</select>
{/strip}