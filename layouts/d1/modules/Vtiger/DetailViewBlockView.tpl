{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
		{include file=vtemplate_path($BLOCK->getUITypeModel()->getDetailViewTemplateName(), $MODULE_NAME) BLOCK=$BLOCK USER_MODEL=$USER_MODEL MODULE_NAME=$MODULE_NAME RECORD=$RECORD}
	{/foreach}
{/strip}