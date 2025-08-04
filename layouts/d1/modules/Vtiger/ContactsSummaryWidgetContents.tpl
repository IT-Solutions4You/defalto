{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
<div class="relatedContacts container-fluid">
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="recentActivitiesContainer row flex-nowrap py-2">
			<div class="col-6 text-truncate">
				<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{Vtiger_Util_Helper::getRecordName($RELATED_RECORD->get('id'))}">
					{Vtiger_Util_Helper::getRecordName($RELATED_RECORD->get('id'))}
				</a>
			</div>
			<div class="col-3 text-truncate" title="{strip_tags($RELATED_RECORD->getDisplayValue('email'))}">{$RELATED_RECORD->getDisplayValue('email')}</div>
			<div class="col-3 text-truncate" title="{$RELATED_RECORD->getDisplayValue('phone')}">{$RELATED_RECORD->getDisplayValue('phone')}</div>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row py-2">
			<div class="col-12 text-end">
				<a href="javascript:void(0)" class="btn btn-primary moreRecentContacts">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
</div>
{/strip}
