{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
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
        <div class="container-fluid">
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                {assign var=PERMISSIONS value=Users_Privileges_Model::isPermitted($RELATED_MODULE, 'EditView', $RELATED_RECORD->get('id'))}
                <div class="recentActivitiesContainer py-2">
                    <div class="row">
                        <div class="col">
                            <a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" class="btn-link" title="{$RELATED_RECORD->getDisplayValue('projecttaskname')}">
                                <span class="fw-bold">{$RELATED_RECORD->getDisplayValue('projecttaskname')}</span>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        {assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance('ProjectTask')}
                        {assign var=FIELD_MODEL value=$RELATED_MODULE_MODEL->getField('projecttaskprogress')}
                        {if $FIELD_MODEL->isViewableInDetailView()}
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-auto">
                                        <span class="lh-lg">{$TASK_PROGRESS_HEADER}:</span>
                                    </div>
                                    {if $PERMISSIONS && $FIELD_MODEL->isEditable()}
                                        <span class="col text-end">
                                        <div class="dropdown">
                                            <a href="#" data-bs-toggle="dropdown" class="btn text-nowrap">
                                                <span class="fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskprogress')}</span>
                                                <i class="ms-2 text-secondary fa-solid fa-caret-down"></i>
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
                                    <div class="col-auto">
                                        <span class="lh-lg">{$TASK_STATUS_HEADER}:</span>
                                    </div>
                                    {if $PERMISSIONS && $FIELD_MODEL->isEditable()}
                                        <div class="col text-end">
                                            <div class="dropdown">
                                                <a href="#" data-bs-toggle="dropdown" class="btn text-nowrap">
                                                    <span class="fieldValue">{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}</span>
                                                    <i class="ms-2 text-secondary fa-solid fa-caret-down"></i>
                                                </a>
                                                <ul class="dropdown-menu widgetsList" data-recordid="{$RELATED_RECORD->getId()}" data-fieldname="projecttaskstatus" data-old-value="{$RELATED_RECORD->getDisplayValue('projecttaskstatus')}" data-mandatory="{$FIELD_MODEL->isMandatory()}">
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
                <div class="row py-2">
                    <div class="col text-center">
                        <a target="_blank" href="index.php?{$RELATION_LIST_URL}&tab_label=Project Tasks" class="moreRecentTasks btn btn-primary">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
                    </div>
                </div>
            {/if}
        </div>
    </div>
{/strip}