{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
<input type="hidden" id="currentView" value="{$smarty.request.view}" />
<input type="hidden" id="start_day" value="{$CURRENT_USER->get('dayoftheweek')}" />
<input type="hidden" id="activity_view" value="{$CURRENT_USER->get('activity_view')}" />
<input type="hidden" id="time_format" value="{$CURRENT_USER->get('hour_format')}" />
<input type="hidden" id="start_hour" value="{$CURRENT_USER->get('start_hour')}" />
<input type="hidden" id="date_format" value="{$CURRENT_USER->get('date_format')}" />
<input type="hidden" id="hideCompletedEventTodo" value="{$CURRENT_USER->get('hidecompletedevents')}">
<input type="hidden" id="show_allhours" value="{$CURRENT_USER->get('showallhours')}" />
<div id="sharedcalendar" class="calendarview col-lg-12">
	{assign var=LEFTPANELHIDE value=$CURRENT_USER->get('leftpanelhide')}
	<div class="essentials-toggle" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
		<span class="essentials-toggle-marker fa {if $LEFTPANELHIDE eq '1'}fa-chevron-right{else}fa-chevron-left{/if} cursorPointer"></span>
	</div>
</div>
{/strip}
