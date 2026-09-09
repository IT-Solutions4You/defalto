{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
	{include file='SummaryWidgetEmpty.tpl'|vtemplate_path:'Vtiger'
		EMPTY_STATE_LABEL='LBL_NO_COMMENTS'
		EMPTY_STATE_MODULE=$MODULE_NAME
		EMPTY_STATE_SUFFIX=''
		EMPTY_STATE_CLASS='mt-3'}
{/strip}
