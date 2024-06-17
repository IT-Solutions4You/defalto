{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
		{if isset($BLOCK_LIST[$BLOCK_LABEL_KEY])}
		{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
		{else}
			{assign var=BLOCK value=''}
		{/if}
		{if $BLOCK eq null}{continue}{/if}
		{include file=vtemplate_path($RECORD_STRUCTURE_MODEL->blockData[$BLOCK_LABEL_KEY]['template_name'], $MODULE_NAME) BLOCK=$BLOCK USER_MODEL=$USER_MODEL MODULE_NAME=$MODULE_NAME RECORD=$RECORD}
	{/foreach}
{/strip}