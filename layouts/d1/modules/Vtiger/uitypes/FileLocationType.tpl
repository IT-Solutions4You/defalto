{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
{assign var=FIELD_VALUES value=$FIELD_MODEL->getFileLocationType()}
<select class="select2" name="{$FIELD_MODEL->getFieldName()}">
{foreach item=TYPE key=KEY from=$FIELD_VALUES}
	{if $FILE_LOCATION_TYPE eq 'I'}
		{assign var=SELECTED value='I'}
	{elseif $FILE_LOCATION_TYPE eq 'E'}
		{assign var=SELECTED value='E'}
	{else}
		{assign var=SELECTED value=$FIELD_MODEL->get('fieldvalue')}
	{/if}
	<option value="{$KEY}" {if $SELECTED eq $KEY} selected {/if}>{vtranslate($TYPE, $MODULE)}</option>
{/foreach}
</select>
{/strip}