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
	<div class="containerHelpDeskSummaryWidgetContents">
		{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
			<div class="recentActivitiesContainer container-fluid py-3">
				<div class="row">
					<div class="col">
						<span>{vtranslate('Title', $MODULE)}</span>
						<span class="mx-2">:</span>
						<a href="{$RELATED_RECORD->getDetailViewUrl()}" title="{$RELATED_RECORD->getDisplayValue('ticket_title')}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
							<span>{$RELATED_RECORD->getDisplayValue('ticket_title')}</span>
						</a>
					</div>
					<div class="row">
						<div class="col">
							<span>{vtranslate('LBL_TICKET_PRIORITY',$MODULE)}</span>
							<span class="mx-2">:</span>
							<strong> {$RELATED_RECORD->getDisplayValue('ticketpriorities')}</strong>
						</div>
					</div>
					{assign var=DESCRIPTION value="{$RELATED_RECORD->getDescriptionValue()}"}
					{if !empty($DESCRIPTION)}
						<div class="row">
							<div class="col-lg-10">
								<div class="text-truncate w-100">
									<span>{vtranslate('LBL_DESCRIPTION',$MODULE)}</span>
									<span class="mx-2">:</span>
									<span>{$DESCRIPTION}</span>
								</div>
							</div>
							<div class="col-lg-2">
								<a href="{$RELATED_RECORD->getDetailViewUrl()}">{vtranslate('LBL_MORE',$MODULE)}</a>
							</div>
						</div>
					{/if}
				</div>
			</div>
		{/foreach}
		<div class="container-fluid">
			{assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
			{if $NUMBER_OF_RECORDS eq 5}
				<div class="row">
					<div class="col">
						<a class="moreRecentTickets btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
{/strip}