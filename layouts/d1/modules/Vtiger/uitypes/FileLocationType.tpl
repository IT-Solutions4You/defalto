{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
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