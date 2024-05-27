{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
{if isset($REMINDER_VALUES) && !$REMINDER_VALUES}
	{assign var=REMINDER_VALUES value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{/if}
{if isset($REMINDER_VALUES) && $REMINDER_VALUES eq ''}
    {assign var=DAYS value=0}
	{assign var=HOURS value=0}
	{assign var=MINUTES value=1}
{else}
    {assign var=DAY value=(isset($REMINDER_VALUES[0])) ? $REMINDER_VALUES[0] : ""}
	{assign var=HOUR value=(isset($REMINDER_VALUES[1])) ? $REMINDER_VALUES[1] : ""}
	{assign var=MINUTE value=(isset($REMINDER_VALUES[2])) ? $REMINDER_VALUES[2] : ""}
{/if}

<div id="js-reminder-controls">
	<div style="float:left;margin-top: 1%;">
		<input type=hidden name=set_reminder value=0 />
		<input type=checkbox name=set_reminder {if isset($REMINDER_VALUES) && $REMINDER_VALUES neq ''}checked{/if} value=1 />&nbsp;&nbsp;
	</div>
	<div id="js-reminder-selections" style="float:left;visibility:{if isset($REMINDER_VALUES) && $REMINDER_VALUES neq ''}visible{else}collapse{/if};">
		<div style="float:left">
			<div style="float:left">
				<select class="select2" name="remdays">
					{for $DAYS = 0 to 31}
						<option value="{$DAYS}" {if $DAYS eq $DAY}selected{/if}>{$DAYS}</option>
					{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_DAYS', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
		<div style="float:left">
			<div style="float:left">
				<select class="select2" name="remhrs">
					{for $HOURS = 0 to 23}
						<option value="{$HOURS}" {if $HOURS eq $HOUR}selected{/if}>{$HOURS}</option>
					{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_HOURS', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
		<div style="float:left">
			<div style="float:left">
				<select class="select2" name="remmin">
				{for $MINUTES = 1 to 59}
					<option value="{$MINUTES}" {if $MINUTES eq $MINUTE}selected{/if}>{$MINUTES}</option>
				{/for}
				</select>
			</div>
			<div style="float:left;margin-top:5px">
				&nbsp;{vtranslate('LBL_MINUTES', $MODULE)}&nbsp;&nbsp;
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	<div class="clearfix"></div>
</div>
{/strip}