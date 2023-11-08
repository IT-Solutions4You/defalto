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
        <div class="row h-main">
            <div class="col-lg-2 py-3 bg-body rounded-end rounded-bottom-0">
                <form method="post" id="CalendarFilter">
                    <div class="calendarUserGroupContainer">
                        {assign var=USERS_GROUPS_SELECTED value=$CURRENT_USER->getName()}
                        <input type="hidden" name="users_groups_selected" value="Users::::{$USERS_GROUPS_SELECTED}">
                        <select name="field_users_groups" class="fieldUsersGroups hide" multiple="multiple">
                            <option value="Users::::{$USERS_GROUPS_SELECTED}" selected="selected">Users::::{$USERS_GROUPS_SELECTED}</option>
                        </select>
                    </div>
                    <div class="container-fluid calendarTypeContainer">
                        <div>
                            <b>{vtranslate('LBL_TYPE_FIELD_TITLE', $QUALIFIED_MODULE)}</b>
                        </div>
                        <label class="row align-items-center p-2 px-3 my-3 rounded" style="background-color: #ddd; color: #000;">
                            <div class="col text-truncate">{vtranslate('LBL_MASS_SELECT', $QUALIFIED_MODULE)}</div>
                            <div class="col-auto">
                                <input type="checkbox" class="form-check-input m-0 va-middle massSelectCalendarType select2 inputElement">
                            </div>
                        </label>
                        {foreach from=$FIELD_TYPE_VALUES key=TYPE_KEY item=TYPE_VALUE}
                            <label class="row align-items-center p-2 px-3 my-3 rounded" style="{if !empty($TYPE_COLORS[$TYPE_KEY])}background-color:{$TYPE_COLORS[$TYPE_KEY]};color: {Settings_Picklist_Module_Model::getTextColor($TYPE_COLORS[$TYPE_KEY])};{/if}">
                                <div class="col text-truncate">{$TYPE_VALUE}</div>
                                <div class="col-auto">
                                    <input type="checkbox" checked="checked" value="{$TYPE_KEY}" name="field_calendar_type" class="form-check-input m-0 fieldCalendarType select2 inputElement">
                                </div>
                            </label>
                        {/foreach}
                        <br>
                    </div>
                    {if $IS_EVENT_TYPES_VISIBLE}
                        <div class="container-fluid eventTypeContainer">
                            <div>
                                <b>{vtranslate('LBL_MODULES_FIELD_TITLE', $QUALIFIED_MODULE)}</b>
                            </div>
                            <label class="row align-items-center p-2 px-3 my-3 rounded" style="background-color: #ddd; color: #000;">
                                <div class="col text-truncate">
                                    {vtranslate('LBL_MASS_SELECT', $QUALIFIED_MODULE)}
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="editEventType btn btn-link p-0 me-3" style="color: #000" value=""><i class="fa fa-plus"></i></button>
                                    <input type="checkbox" class="form-check-input m-0 va-middle massSelectEventType select2 inputElement">
                                </div>
                            </label>
                            <label class="row align-items-center p-2 px-3 my-3 eventType rounded eventTypeClone">
                                <div class="col text-truncate eventTypeName">NEW_EVENT_NAME</div>
                                <div class="col-auto">
                                    <button type="button" class="editEventType btn btn-link p-0 me-3 eventTypeId"><i class="fa fa-pencil"></i></button>
                                    <button type="button" class="deleteEventType btn btn-link p-0 me-3 eventTypeId"><i class="fa fa-trash"></i></button>
                                    <input type="checkbox" class="form-check-input m-0 va-middle fieldEventType select2 inputElement eventTypeId" checked="checked">
                                </div>
                            </label>
                            {foreach from=$EVENT_TYPES item=EVENT_TYPE}
                                <label class="row align-items-center p-2 px-3 my-3 eventType rounded" style="background-color: {$EVENT_TYPE->getBackgroundColor()}; color: {$EVENT_TYPE->getTextColor()};">
                                    <div class="col text-truncate eventTypeName">{$EVENT_TYPE->getName()}</div>
                                    <div class="col-auto">
                                        <button type="button" class="editEventType btn btn-link p-0 me-3 eventTypeId" style="color: {$EVENT_TYPE->getTextColor()};" value="{$EVENT_TYPE->getId()}"><i class="fa fa-pencil"></i></button>
                                        <button type="button" class="deleteEventType btn btn-link p-0 me-3 eventTypeId" style="color: {$EVENT_TYPE->getTextColor()};" value="{$EVENT_TYPE->getId()}"><i class="fa fa-trash"></i></button>
                                        <input type="checkbox" value="{$EVENT_TYPE->getId()}" class="form-check-input m-0 va-middle fieldEventType select2 inputElement eventTypeId" {if $EVENT_TYPE->isVisible()}checked="checked"{/if}>
                                    </div>
                                </label>
                            {/foreach}
                        </div>
                    {/if}
                </form>
            </div>
            <div class="col-lg overflow-auto px-4">
                <div id="CalendarContainer">
                    <textarea id="hide_days" class="hide" >{json_encode($HIDE_DAYS)}</textarea>
                    <input type="hidden" id="day_of_week" value="{ITS4YouCalendar_Events_Model::getDayOfWeekId($CURRENT_USER->get('dayoftheweek'))}">
                    <input type="hidden" id="calendar_view" value="{ITS4YouCalendar_Events_Model::getInitialView($CURRENT_USER->get('activity_view'))}">
                    <input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}">
                    <input type="hidden" id="timezone" value="{$CURRENT_USER->get('timezone')}">
                    <input type="hidden" id="hour_format" value="{$CURRENT_USER->get('hour_format')}">
                    <input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}">
                    <input type="hidden" id="slot_duration" value="{$CURRENT_USER->get('slot_duration')}">
                    <div id="users_groups_tabs" class="mb-3">
                        <div class="fc fc-direction-ltr">
                            <div class="fc-toolbar-chunk">
                                <div class="fc-button-group">
                                    <button type="button" class="select_user fc-button fc-button-primary fc-button-active">
                                        <i class="fa fa-user me-2"></i>
                                        <span>{vtranslate('LBL_CALENDAR_VIEW', $QUALIFIED_MODULE)}</span>
                                    </button>
                                    <button type="button" class="select_users fc-button fc-button-primary">
                                        <i class="fa fa-users me-2"></i>
                                        <span>{vtranslate('LBL_SHARED_CALENDAR', $QUALIFIED_MODULE)}</span>
                                    </button>
                                    {foreach from=$USERS_GROUPS_TABS item=USERS_GROUPS_TAB}
                                        <button type="button" class="select_group fc-button fc-button-primary" data-name="Groups::::{$USERS_GROUPS_TAB['name']}" data-values='{json_encode($USERS_GROUPS_TAB['values'])}'>
                                            <i class="fa fa-list me-2"></i>
                                            <span>{$USERS_GROUPS_TAB['name']}</span>
                                        </button>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="selected_user_and_groups_images">
                        <textarea id="users_and_groups_images" class="hide" >{json_encode($USERS_GROUPS_INFO)}</textarea>
                        <textarea id="users_groups_group_selected" class="hide">{json_encode($USERS_GROUPS_GROUP_SELECTED)}</textarea>
                        <textarea id="users_groups_user_selected" class="hide">{json_encode($USERS_GROUPS_USER_SELECTED)}</textarea>
                        <textarea id="users_groups_users_selected" class="hide">{json_encode($USERS_GROUPS_USERS_SELECTED)}</textarea>
                        <select id="field_users_groups" class="hide" multiple="multiple"></select>
                        <div class="selected_user_and_groups_visible"></div>
                        <div class="selected_user_and_groups_toggle">
                            <div class="selected_image selected_image_text"><span></span></div>
                        </div>
                        <div class="selected_user_and_groups_hidden hide"></div>
                        <div class="selected_user_and_groups_actions">
                            <div class="select_users_and_groups selected_image selected_image_fa" title="{vtranslate('LBL_ADD', $QUALIFIED_MODULE)}" style="border-color: #ddd;"><i class="fa fa-plus"></i></div>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
{/strip}