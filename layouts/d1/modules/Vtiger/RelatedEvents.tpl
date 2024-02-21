{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{* Calendar Widget: Copy to vtiger Detail view*}
{strip}
    <div class="widget_contents">
        {if php7_count($ACTIVITIES) neq '0'}
            {foreach item=ACTIVITY_RECORD from=$ACTIVITIES}
                {assign var=START_DATE value=$ACTIVITY_RECORD->get('datetime_start')}
                {assign var=EDITVIEW_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'EditView', $ACTIVITY_RECORD->get('crmid'))}
                {assign var=DETAILVIEW_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'DetailView', $ACTIVITY_RECORD->get('crmid'))}
                {assign var=DELETE_PERMITTED value=isPermitted($ACTIVITIES_MODULE_NAME, 'Delete', $ACTIVITY_RECORD->get('crmid'))}
                <div class="activityEntries border-bottom py-2">
                    <input type="hidden" class="activityId" value="{$ACTIVITY_RECORD->getId()}"/>
                    <div class="media">
                        <div class="row">
                            <div class="media-left module-icon col-lg-1 col-md-1 col-sm-1 textAlignCenter">
                                <span class='{$ACTIVITY_RECORD->getActivityTypeIcon()}'></span>
                            </div>
                            <div class="media-body col-lg-7 col-md-7 col-sm-7">
                                <div class="summaryViewEntries">
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
                                <span><strong title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($START_DATE)}">{Vtiger_Util_Helper::formatDateIntoStrings($START_DATE)}</strong></span>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-4 activityStatus">
                                <input type="hidden" class="activityModule" value="{$ACTIVITIES_MODULE_NAME}"/>
                                <input type="hidden" class="activityType" value="{$ACTIVITY_RECORD->get('calendar_type')}"/>
                                {assign var=FIELD_MODEL value=$ACTIVITY_RECORD->getModule()->getField('calendar_status')}
                                <style>
                                    {assign var=PICKLIST_COLOR_MAP value=Settings_Picklist_Module_Model::getPicklistColorMap('calendar_status', true)}
                                    {foreach item=PICKLIST_COLOR key=PICKLIST_VALUE from=$PICKLIST_COLOR_MAP}
                                    {assign var=PICKLIST_TEXT_COLOR value=Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)}
                                    {assign var=CONVERTED_PICKLIST_VALUE value=Vtiger_Util_Helper::convertSpaceToHyphen($PICKLIST_VALUE)}
                                    .picklist-{$FIELD_MODEL->getId()}-{Vtiger_Util_Helper::escapeCssSpecialCharacters($CONVERTED_PICKLIST_VALUE)} {
                                        background-color: {$PICKLIST_COLOR};
                                        color: {$PICKLIST_TEXT_COLOR};
                                    }

                                    {/foreach}
                                </style>
                                <span class="py-1 px-2 rounded value picklist-color picklist-{$FIELD_MODEL->getId()}-{Vtiger_Util_Helper::convertSpaceToHyphen($ACTIVITY_RECORD->get('calendar_status'))}">{vtranslate($ACTIVITY_RECORD->get('calendar_status'),$ACTIVITIES_MODULE_NAME)}</span>
                                {if $EDITVIEW_PERMITTED == 'yes'}
                                    <span class="ms-2 editStatus cursorPointer text-secondary">
                                        <i class="fa fa-pencil" title="{vtranslate('LBL_EDIT',$ACTIVITIES_MODULE_NAME)}"></i>
                                    </span>
                                    <span class="edit hide">
                                        {assign var=FIELD_VALUE value=$FIELD_MODEL->set('fieldvalue', $ACTIVITY_RECORD->get('calendar_status'))}
                                        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$ACTIVITIES_MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$ACTIVITIES_MODULE_NAME OCCUPY_COMPLETE_WIDTH='true'}
                                        {if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
                                            <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}'/>
                                        {else}
                                            <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}'/>
                                        {/if}
                                    </span>
                                {/if}
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