{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	<input type="hidden" name="page" value="{$PAGING_MODEL->get('page')}" />
	<input type="hidden" name="pageLimit" value="{$PAGING_MODEL->get('limit')}" />
	<input type="hidden" name="relatedModule" value="{$RELATED_MODULE}" />
	{if $RELATED_MODULE && $RELATED_RECORDS}
		{assign var=FILENAME value=$RELATED_MODULE|cat:"SummaryWidgetContents.tpl"}
		{include file=$FILENAME|vtemplate_path:$MODULE RELATED_RECORDS=$RELATED_RECORDS}
    {else}
		{include file='SummaryWidgetEmpty.tpl'|vtemplate_path:'Vtiger'
			EMPTY_STATE_LABEL='LBL_NO_RELATED'
			EMPTY_STATE_MODULE=$MODULE
			EMPTY_STATE_SUFFIX=$RELATED_MODULE}
	{/if}
{/strip}
