{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* Calendar Widget: Copy to vtiger Detail view*}
{strip}
    <div class="activityEntriesContainer">
        <input type="hidden" name="relatedField" value="{$PARENT_FIELD_NAME}">
        {if php7_count($ACTIVITIES) neq '0'}
            {assign var=FIELD_MODEL value=$ACTIVITIES_MODULE->getField('calendar_status')}
            <style>
                {foreach item=PICKLIST_COLOR key=PICKLIST_VALUE from=Settings_Picklist_Module_Model::getPicklistColorMap('calendar_status', true)}
                    {if $PICKLIST_COLOR eq '#ffffff'}
                        {assign var=PICKLIST_COLOR value='#5e81f4'}
                    {/if}
                    {assign var=RGB_COLOR value=Vtiger_Functions::hexToRGB($PICKLIST_COLOR)}
                    .activityStatus[data-calendar-status*="{$PICKLIST_VALUE}"] .select2-selection {
                        background-color: rgba({$RGB_COLOR}, 0.1) !important;
                        border: 0 solid #fff !important;
                    } .activityStatus[data-calendar-status*="{$PICKLIST_VALUE}"] * {
                        color: rgba({$RGB_COLOR}, 1) !important;
                        font-weight: bold !important;
                    } .activityStatus[data-calendar-status*="{$PICKLIST_VALUE}"] .select2-container--bootstrap-5 .select2-selection--single {
                        background-image: url("{Vtiger_Functions::getArrowDownSVG($PICKLIST_COLOR)}");
                    }
                {/foreach}
            </style>
            {foreach item=ACTIVITY_RECORD name=ACTIVITY from=$ACTIVITIES}
                {assign var=START_DATE_INFO value=$ACTIVITY_RECORD->getDateInfo('datetime_start')}
                {assign var=EDITVIEW_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'EditView', $ACTIVITY_RECORD->get('crmid'))}
                {assign var=DETAILVIEW_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'DetailView', $ACTIVITY_RECORD->get('crmid'))}
                {assign var=DELETE_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'Delete', $ACTIVITY_RECORD->get('crmid'))}
                <div class="activityEntries py-2 {if $smarty.foreach.ACTIVITY.index}border-top{/if}">
                    <input type="hidden" class="activityId" value="{$ACTIVITY_RECORD->getId()}"/>
                    <div class="media">
                        <div class="row align-items-center">
                            <div class="media-left module-icon col-auto text-center">
                                <div class="py-2">
                                    <div class="d-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary" style="width: 3em; height: 3em;">
                                        {$ACTIVITY_RECORD->getActivityTypeIcon()}
                                    </div>
                                </div>
                            </div>
                            <div class="media-body col overflow-hidden">
                                <div class="summaryViewEntries fw-bold">
                                    {if $DETAILVIEW_PERMITTED == 'yes'}
                                        <a href="{$ACTIVITY_RECORD->getDetailViewUrl()}" title="{$ACTIVITY_RECORD->get('subject')}">{$ACTIVITY_RECORD->get('subject')}</a>
                                    {else}
                                        <span>{$ACTIVITY_RECORD->get('subject')}</span>
                                    {/if}
                                    {if $EDITVIEW_PERMITTED == 'yes'}
                                        <a href="{$ACTIVITY_RECORD->getEditViewUrl()}&sourceModule={$RECORD->getModuleName()}&sourceRecord={$RECORD->getId()}&relationOperation=true" class="fieldValue ms-3 text-secondary">
                                            <i class="summaryViewEdit fa fa-pencil" title="{vtranslate('LBL_EDIT',$ACTIVITIES_MODULE_NAME)}"></i>
                                        </a>
                                    {/if}
                                </div>
                                <div class="text-secondary">
                                    {if $START_DATE_INFO['display_time']}
                                        <span>{$START_DATE_INFO['display_time']} - </span>
                                    {/if}
                                    <span>{$START_DATE_INFO['display_date']}</span>
                                </div>
                            </div>
                            {assign var=CALENDAR_STATUS value=$ACTIVITY_RECORD->get('calendar_status')}
                            {assign var=FIELD_VALUE value=$FIELD_MODEL->set('fieldvalue', $CALENDAR_STATUS)}
                            <div class="col-3 activityStatus" data-calendar-status="{$CALENDAR_STATUS}">
                                <input type="hidden" class="activityModule" value="{$ACTIVITIES_MODULE_NAME}"/>
                                <input type="hidden" class="activityType" value="{$ACTIVITY_RECORD->get('calendar_type')}"/>
                                <span class="edit">
                                    {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$ACTIVITIES_MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$ACTIVITIES_MODULE_NAME OCCUPY_COMPLETE_WIDTH='true'}
                                    {if $EDITVIEW_PERMITTED == 'yes'}
                                        <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}'/>
                                    {/if}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
        {else}
            <div class="summaryWidgetContainer noContent">
                <p class="textAlignCenter">{vtranslate('LBL_NO_PENDING_ACTIVITIES',$ACTIVITIES_MODULE_NAME)}</p>
            </div>
        {/if}
        {if $PAGING_MODEL->isNextPageExists()}
            <div class="row py-2">
                <div class="col text-center">
                    <a href="index.php?{$RELATION_LIST_URL}&tab_label={$ACTIVITIES_MODULE_NAME}" target="_blank" class="moreRecentActivities btn btn-primary">{vtranslate('LBL_SHOW_MORE',$ACTIVITIES_MODULE_NAME)}</a>
                </div>
            </div>
        {/if}
    </div>
{/strip}