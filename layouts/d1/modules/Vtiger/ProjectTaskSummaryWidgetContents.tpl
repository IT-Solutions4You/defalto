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
	<div class="containerProjectTaskSummaryWidgetContents">
		{foreach item=HEADER from=$RELATED_HEADERS}
			{if $HEADER->get('label') eq "Project Task Name"}
				{assign var=TASK_NAME_HEADER value={vtranslate($HEADER->get('label'),$MODULE_NAME)}}
			{elseif $HEADER->get('label') eq "Progress"}
				{assign var=TASK_PROGRESS_HEADER value=vtranslate($HEADER->get('label'),$MODULE_NAME)}
			{elseif $HEADER->get('label') eq "Status"}
				{assign var=TASK_STATUS_HEADER value=vtranslate($HEADER->get('label'),$MODULE_NAME)}
			{/if}
		{/foreach}
		{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
			{assign var=PERMISSIONS value=Users_Privileges_Model::isPermitted($RELATED_MODULE, 'EditView', $RELATED_RECORD->get('id'))}
			<div class="recentActivitiesContainer container-fluid py-3">
				<div class="row">
					<div class="col">
						<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('projecttaskname')}">
							<strong>{$RELATED_RECORD->getDisplayValue('projecttaskname')}</strong>
						</a>
					</div>
				</div>
				<div class="row">
					{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ProjectTask')}
					{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskprogress')}
					{if $FIELD_MODEL->isViewableInDetailView()}
					<div class="col-lg-6">
						<div class="row">
							<div class="col-lg-6">
								<span class="lh-lg">{$TASK_PROGRESS_HEADER}:</span>
							</div>
							{if $PERMISSIONS && $FIELD_MODEL->isEditable()}
								<span class="col-lg-6 text-end">
									<div class="dropdown">
										<a href="#" data-bs-toggle="dropdown">
											<span class="btn fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</span>
											<span class="btn">
												<i class="fa-solid fa-caret-down"></i>
											</span>
										</a>
										<ul class="dropdown-menu widgetsList" data-recordid="{$RELATED_RECORD->getId()}" data-fieldname="projecttaskprogress" data-old-value="{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}" data-mandatory="{$FIELD_MODEL->isMandatory()}">
											{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
											<li class="editTaskDetails emptyOption">
												<a class="dropdown-item">{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}</a>
											</li>
											{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
												<li class="editTaskDetails">
													<a class="dropdown-item">{$PICKLIST_VALUE}</a>
												</li>
											{/foreach}
										</ul>
									</div>
								</span>
							{else}
								<span class="col-lg-7"><strong>{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</strong></span>
							{/if}
						</div>
					</div>
					{/if}
					{assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskstatus')}
					{if $FIELD_MODEL->isViewableInDetailView()}
						<div class="col-lg-6">
							<div class="row">
								<div class="col-lg-6">
									<span class="lh-lg">{$TASK_STATUS_HEADER}:</span>
								</div>
								{if $PERMISSIONS && $FIELD_MODEL->isEditable()}
									<div class="col-lg-6 text-end">
										<div class="dropdown">
											<a href="#" data-bs-toggle="dropdown">
												<span class="btn fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}</span>
												<i class="btn fa-solid fa-caret-down"></i>
											</a>
											<ul class="dropdown-menu widgetsList pull-right" data-recordid="{$RELATED_RECORD->getId()}" data-fieldname="projecttaskstatus" data-old-value="{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}" data-mandatory="{$FIELD_MODEL->isMandatory()}">
												{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
												<li class="editTaskDetails emptyOption" value="">
													<a class="dropdown-item">{vtranslate('LBL_SELECT_OPTION',$MODULE_NAME)}</a>
												</li>
												{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
													<li class="editTaskDetails" value="{$PICKLIST_VALUE}">
														<a class="dropdown-item">{$PICKLIST_VALUE}</a>
													</li>
												{/foreach}
											</ul>
										</div>
									</div>
								{else}
									<div class="col-lg-7">
										<strong>{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}</strong>
									</div>
								{/if}
							</div>
						</div>
					{/if}
				</div>
			</div>
		{/foreach}
		{assign var=NUMBER_OF_RECORDS value=php7_count($RELATED_RECORDS)}
		{if $NUMBER_OF_RECORDS eq 5}
			<div class="container-fluid">
				<div class="row">
					<div class="col">
						<a class="moreRecentTasks btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}