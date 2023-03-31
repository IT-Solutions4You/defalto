{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{strip}
    <div class="container-fluid">
        <br>
        <div class="row">
            <div class="col-lg-2">
                <form method="post" id="CalendarFilter">
                    <div class="usersGroupsContainer">
                        <br>
                        <div>
                            <b>{vtranslate('LBL_USERS_GROUP_TITLE', $QUALIFIED_MODULE)}</b>
                        </div>
                        {assign var=USERS_GROUPS_SELECTED value=$CURRENT_USER->getName()}
                        <select name="field_users_groups" class="fieldUsersGroups select2 inputElement" multiple="multiple">
                            <optgroup label="">
                                <option value=""></option>
                            </optgroup>
                            {foreach from=$USERS_GROUPS_VALUES item=USERS_GROUPS_RECORDS key=USERS_GROUP_TYPE}
                                <optgroup label="{vtranslate($USERS_GROUP_TYPE, $QUALIFIED_MODULE)}">
                                    {foreach from=$USERS_GROUPS_RECORDS item=USERS_GROUPS_VALUE key=USERS_GROUPS_KEY}
                                        <option value="{$USERS_GROUP_TYPE}::::{$USERS_GROUPS_KEY}" {if $USERS_GROUPS_KEY eq $USERS_GROUPS_SELECTED}selected="selected"{/if}>{$USERS_GROUPS_VALUE}</option>
                                    {/foreach}
                                </optgroup>
                            {/foreach}
                        </select>
                    </div>
                    <div class="calendarTypeContainer">
                        <br>
                        <div>
                            <b>{vtranslate('LBL_TYPE_FIELD_TITLE', $QUALIFIED_MODULE)}</b>
                        </div>
                        {foreach from=$FIELD_TYPE_VALUES key=TYPE_KEY item=TYPE_VALUE}
                            <label class="d-block clearfix lh-lg p-2 px-3 my-3" style="{if !empty($TYPE_COLORS[$TYPE_KEY])}background-color:{$TYPE_COLORS[$TYPE_KEY]};color: {Settings_Picklist_Module_Model::getTextColor($TYPE_COLORS[$TYPE_KEY])};{/if}">
                                {$TYPE_VALUE}
                                <div class="pull-right">
                                    <input type="checkbox" checked="checked" value="{$TYPE_KEY}" name="field_calendar_type" class="fieldCalendarType select2 inputElement">
                                </div>
                            </label>
                        {/foreach}
                    </div>
                    <div class="eventTypeContainer">
                        <br>
                        <div class="clearfix">
                            <b>{vtranslate('LBL_MODULES_FIELD_TITLE', $QUALIFIED_MODULE)}</b>
                        </div>
                        <label class="d-block clearfix lh-lg p-2 px-3 my-3" style="background-color: #ddd; color: #000;">
                            {vtranslate('LBL_MASS_SELECT', $QUALIFIED_MODULE)}
                            <div class="pull-right">
                                <button type="button" class="editEventType btn btn-link p-0 me-3" style="color: #000" value=""><i class="fa fa-plus"></i></button>
                                <input type="checkbox" class="va-middle massSelectEventType select2 inputElement">
                            </div>
                        </label>
                        <label class="clearfix d-block lh-lg p-2 px-3 my-3 eventType eventTypeClone">
                            <div class="pull-left eventTypeName">NEW_EVENT_NAME</div>
                            <div class="pull-right">
                                <button type="button" class="editEventType btn btn-link p-0 me-3 eventTypeId"><i class="fa fa-pencil"></i></button>
                                <button type="button" class="deleteEventType btn btn-link p-0 me-3 eventTypeId"><i class="fa fa-trash"></i></button>
                                <input type="checkbox" class="va-middle fieldEventType select2 inputElement eventTypeId" checked="checked">
                            </div>
                        </label>
                        {foreach from=$EVENT_TYPES item=EVENT_TYPE}
                            <label class="clearfix d-block lh-lg p-2 px-3 my-3 eventType" style="background-color: {$EVENT_TYPE->getBackgroundColor()}; color: {$EVENT_TYPE->getTextColor()};">
                                <div class="pull-left eventTypeName">{$EVENT_TYPE->getName()}</div>
                                <div class="pull-right">
                                    <button type="button" class="editEventType btn btn-link p-0 me-3 eventTypeId" style="color: {$EVENT_TYPE->getTextColor()};" value="{$EVENT_TYPE->getId()}"><i class="fa fa-pencil"></i></button>
                                    <button type="button" class="deleteEventType btn btn-link p-0 me-3 eventTypeId" style="color: {$EVENT_TYPE->getTextColor()};" value="{$EVENT_TYPE->getId()}"><i class="fa fa-trash"></i></button>
                                    <input type="checkbox" value="{$EVENT_TYPE->getId()}" class="va-middle fieldEventType select2 inputElement eventTypeId" {if $EVENT_TYPE->isVisible()}checked="checked"{/if}>
                                </div>
                            </label>
                        {/foreach}
                    </div>
                </form>
            </div>
            <div class="col-lg-10">
                <input type="hidden" id="day_of_week" value="{ITS4YouCalendar_Events_Model::getDayOfWeekId($CURRENT_USER->get('dayoftheweek'))}">
                <input type="hidden" id="calendar_view" value="{ITS4YouCalendar_Events_Model::getInitialView($CURRENT_USER->get('activity_view'))}">
                <input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}">
                <input type="hidden" id="timezone" value="{$CURRENT_USER->get('timezone')}">
                <input type="hidden" id="hour_format" value="{$CURRENT_USER->get('hour_format')}">
                <input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}">
                <div id="calendar"></div>
            </div>
        </div>
        <br>
    </div>
{/strip}