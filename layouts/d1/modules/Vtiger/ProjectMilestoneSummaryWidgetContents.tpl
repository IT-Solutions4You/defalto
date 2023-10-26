{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	{foreach item=HEADER from=$RELATED_HEADERS}
		{if $HEADER->get('label') eq "Project Milestone Name"}
			{assign var=PROJECTMILESTONE_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
		{elseif $HEADER->get('label') eq "Milestone Date"}
			{assign var=PROJECTMILESTONE_DATE_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
		{/if}
	{/foreach}
	<div class="container-fluid">
		<div class="row my-3">
			<div class="col-lg-8">
				<strong>{$PROJECTMILESTONE_NAME_HEADER}</strong>
			</div>
			<div class="col-lg-4 text-end">
				<strong>{$PROJECTMILESTONE_DATE_HEADER}</strong>
			</div>
		</div>
		{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
			<div class="recentActivitiesContainer row my-3">
				<div class="col-lg-8 text-truncate">
					<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('projectmilestonename')}">{$RELATED_RECORD->getDisplayValue('projectmilestonename')}</a>
				</div>
				<div class="col-lg-4 horizontalLeftSpacingForSummaryWidgetContents text-end">
					<span>{$RELATED_RECORD->getDisplayValue('projectmilestonedate')}</span>
				</div>
			</div>
		{/foreach}
		{assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
		{if $NUMBER_OF_RECORDS eq 5}
			<div class="row my-3">
				<div class="col">
					<a class="moreRecentMilestones btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
				</div>
			</div>
		{/if}
	</div>
{/strip}