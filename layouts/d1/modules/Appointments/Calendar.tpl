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
        <div class="row">
            <div class="col-lg-2 py-3 bg-body rounded-end rounded-bottom-0 overflow-auto h-main h-main-max">
                <form method="post" id="CalendarFilter">
                    <div class="container-fluid calendarsContainer">
                        <div class="row align-items-center py-2 mb-2 rounded border">
                            <label class="col text-truncate fw-bold" for="massSelectCalendars">
                                {vtranslate('LBL_CALENDARS', $QUALIFIED_MODULE)}
                            </label>
                            <div class="col-auto p-0">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-sm text-inherit" data-bs-toggle="dropdown">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button type="button" class="dropdown-item text-inherit editEventType" value="">
                                                <i class="fa fa-plus"></i>
                                                <span class="ms-2">{vtranslate('LBL_ADD_CALENDAR', $QUALIFIED_MODULE)}</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-auto">
                                <input id="massSelectCalendars" type="checkbox" class="text-inherit form-check-input m-0 va-middle massSelectCalendars select2 inputElement">
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid calendarTypeContainer">
                        {foreach from=$FIELD_TYPE_VALUES key=TYPE_KEY item=TYPE_VALUE}
                            <label class="row align-items-center py-2 mb-2 rounded" style="{if !empty($TYPE_COLORS[$TYPE_KEY])}background-color:{$TYPE_COLORS[$TYPE_KEY]};color: {Settings_Picklist_Module_Model::getTextColor($TYPE_COLORS[$TYPE_KEY])};{/if}">
                                <div class="col text-truncate">{$TYPE_VALUE}</div>
                                <div class="col-auto">
                                    <input type="checkbox" checked="checked" value="{$TYPE_KEY}" name="field_calendar_type" class="text-inherit form-check-input m-0 fieldCalendarType fieldCalendarsType select2 inputElement">
                                </div>
                            </label>
                        {/foreach}
                    </div>
                    {if $IS_EVENT_TYPES_VISIBLE}
                        <div class="container-fluid eventTypeContainer">
                            <div class="row align-items-center py-2 mb-2 eventType rounded eventTypeClone">
                                <div class="col text-truncate eventTypeName">NEW_EVENT_NAME</div>
                                <div class="col-auto p-0">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm text-inherit" data-bs-toggle="dropdown">
                                            <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" class="dropdown-item editEventType text-inherit eventTypeId">
                                                    <i class="fa fa-pencil"></i>
                                                    <span class="ms-2">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item deleteEventType text-inherit eventTypeId">
                                                    <i class="fa fa-trash"></i>
                                                    <span class="ms-2">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}</span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <input type="checkbox" class="form-check-input m-0 va-middle fieldEventType select2 inputElement eventTypeId" checked="checked">
                                </div>
                            </div>
                            {foreach from=$EVENT_TYPES item=EVENT_TYPE}
                                <div class="row align-items-center py-2 mb-2 eventType rounded" style="background-color: {$EVENT_TYPE->getBackgroundColor()}; color: {$EVENT_TYPE->getTextColor()};">
                                    <div class="col text-truncate eventTypeName">
                                        <label for="fieldEventType{$EVENT_TYPE->getId()}">{$EVENT_TYPE->getName()}</label>
                                    </div>
                                    <div class="col-auto p-0">
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm text-inherit" data-bs-toggle="dropdown">
                                                <i class="bi bi-chevron-down"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button type="button" class="dropdown-item editEventType text-inherit eventTypeId" value="{$EVENT_TYPE->getId()}">
                                                        <i class="fa fa-pencil"></i>
                                                        <span class="ms-2">{vtranslate('LBL_EDIT', $QUALIFIED_MODULE)}</span>
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item deleteEventType text-inherit eventTypeId" value="{$EVENT_TYPE->getId()}">
                                                        <i class="fa fa-trash"></i>
                                                        <span class="ms-2">{vtranslate('LBL_DELETE', $QUALIFIED_MODULE)}</span>
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <input type="checkbox" id="fieldEventType{$EVENT_TYPE->getId()}" value="{$EVENT_TYPE->getId()}" class="form-check-input m-0 va-middle fieldEventType fieldCalendarsType select2 inputElement eventTypeId" {if $EVENT_TYPE->isVisible()}checked="checked"{/if}>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                    <div id="users_groups_tabs">
                        <div class="btn-group w-100 mb-2">
                            <button type="button" class="select_user btn-select btn btn-primary active">
                                <div>
                                    <i class="fa fa-user"></i>
                                </div>
                                <span>{vtranslate('LBL_CALENDAR_VIEW', $QUALIFIED_MODULE)}</span>
                            </button>
                            <button type="button" class="select_users btn-select btn btn-primary">
                                <div>
                                    <i class="fa fa-users"></i>
                                </div>
                                <span>{vtranslate('LBL_SHARED_CALENDAR', $QUALIFIED_MODULE)}</span>
                            </button>
                            <button type="button" class="select_groups btn-select btn btn-primary">
                                <div>
                                    <i class="fa-solid fa-people-group"></i>
                                </div>
                                <span>{vtranslate('LBL_TEAM_CALENDAR', $QUALIFIED_MODULE)}</span>
                            </button>
                        </div>
                        <div class="mb-2 select_groups_actions hide">
                            <select class="form-select select_group">
                                {foreach from=$USERS_GROUPS_TABS item=USERS_GROUPS_TAB}
                                    <option class="select_group" value="Groups::::{$USERS_GROUPS_TAB['name']}" data-name="Groups::::{$USERS_GROUPS_TAB['name']}" data-values='{json_encode($USERS_GROUPS_TAB['values'])}'>
                                        {$USERS_GROUPS_TAB['name']}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="mb-2 select_users_actions hide">
                            <div class="select_users_and_groups btn btn-outline-secondary w-100">
                                <i class="fa fa-search"></i>
                                <span class="ms-2">{vtranslate('LBL_SEARCH', $QUALIFIED_MODULE)}</span>
                            </div>
                        </div>
                    </div>
                    <div id="selected_user_and_groups_images">
                        <textarea id="users_and_groups_images" class="hide">{json_encode($USERS_GROUPS_INFO)}</textarea>
                        <textarea id="users_groups_group_selected" class="hide">{json_encode($USERS_GROUPS_GROUP_SELECTED)}</textarea>
                        <textarea id="users_groups_user_selected" class="hide">{json_encode($USERS_GROUPS_USER_SELECTED)}</textarea>
                        <textarea id="users_groups_users_selected" class="hide">{json_encode($USERS_GROUPS_USERS_SELECTED)}</textarea>
                        <select id="field_users_groups" class="hide" multiple="multiple"></select>
                        <div class="selected_user_and_groups_visible container-fluid"></div>
                        <div class="selected_user_and_groups_hidden container-fluid hide"></div>
                        <div class="selected_user_and_groups_toggle">
                            <div class="selected_image_text btn border mb-2 w-100">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg overflow-auto px-4 pb-4">
                <div id="CalendarContainer" class="d-flex flex-column w-100 h-100">
                    <style>
                        {Appointments_Events_Model::getEventTypeStyles()}
                        {Appointments_Events_Model::getUserStyles()}
                    </style>
                    <textarea id="hide_days" class="hide">{json_encode($HIDE_DAYS)}</textarea>
                    <input type="hidden" id="day_of_week" value="{Appointments_Events_Model::getDayOfWeekId($CURRENT_USER->get('dayoftheweek'))}">
                    <input type="hidden" id="calendar_view" value="{Appointments_Events_Model::getInitialView($CURRENT_USER->get('activity_view'))}">
                    <input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}">
                    <input type="hidden" id="timezone" value="{$CURRENT_USER->get('timezone')}">
                    <input type="hidden" id="hour_format" value="{$CURRENT_USER->get('hour_format')}">
                    <input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}">
                    <input type="hidden" id="slot_duration" value="{$CURRENT_USER->get('slot_duration')}">
                    <div id="calendar" class="mt-auto overflow-auto h-100"></div>
                </div>
            </div>
        </div>
    </div>
{/strip}