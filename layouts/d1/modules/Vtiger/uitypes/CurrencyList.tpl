{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{assign var=CURRENCY_LIST value=$FIELD_MODEL->getCurrencyList()}
	<select class="select2 inputElement" name="{$FIELD_MODEL->getFieldName()}">
		{foreach item=CURRENCY_NAME key=CURRENCY_ID from=$CURRENCY_LIST}
			<option value="{$CURRENCY_ID}" data-picklistvalue= '{$CURRENCY_ID}' {if $FIELD_MODEL->get('fieldvalue') eq $CURRENCY_ID} selected {/if}>{vtranslate($CURRENCY_NAME, $MODULE)}</option>
		{/foreach}
	</select>
{/strip}