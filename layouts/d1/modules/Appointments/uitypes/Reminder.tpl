{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

{strip}
{if !$REMINDER_VALUES}
	{assign var=REMINDER_VALUES value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{/if}
{if $REMINDER_VALUES eq ''}
    {assign var=DAYS value=0}
	{assign var=HOURS value=0}
	{assign var=MINUTES value=0}
{else}
    {assign var=DAY value=$REMINDER_VALUES[0]}
	{assign var=HOUR value=$REMINDER_VALUES[1]}
	{assign var=MINUTE value=$REMINDER_VALUES[2]}
{/if}

<div id="js-reminder-controls">
	<div class="row">
		<div class="col-sm-1 py-2">
			<input type="hidden" id="js-reminder-value" name="{$FIELD_NAME}" value="{$FIELD_MODEL->get('fieldvalue')}" />
			<input type="checkbox" id="js-reminder-checkbox" name="{$FIELD_NAME}_checkbox" {if $REMINDER_VALUES neq ''}checked{/if} value="1" class="form-check-input" style="height: 1.3rem; width: 1.3rem" />
		</div>
		<div class="col-sm">
			<div id="js-reminder-selections" style="visibility:{if $REMINDER_VALUES neq ''}visible{else}collapse{/if};">
				<div class="row">
					<div class="col-lg-2 py-2">
						<select class="select2" name="reminder_days" id="js-reminder-days" data-rule-reminder_required="true">
							{for $DAYS = 0 to 31}
								<option value="{$DAYS}" {if $DAYS eq $DAY}selected{/if}>{$DAYS}</option>
							{/for}
						</select>
					</div>
					<div class="col-lg-2 py-2">
						{vtranslate('LBL_DAYS', $MODULE)}
					</div>
					<div class="col-lg-2 py-2">
						<select class="select2" name="reminder_hours" id="js-reminder-hours">
							{for $HOURS = 0 to 23}
								<option value="{$HOURS}" {if $HOURS eq $HOUR}selected{/if}>{$HOURS}</option>
							{/for}
						</select>
					</div>
					<div class="col-lg-2 py-2">
						{vtranslate('LBL_HOURS', $MODULE)}
					</div>
					<div class="col-lg-2 py-2">
						<select class="select2" name="reminder_minutes" id="js-reminder-minutes">
							{foreach from=[0,15,30,45] item=$MINUTES}
								<option value="{$MINUTES}" {if $MINUTES eq $MINUTE}selected{/if}>{$MINUTES}</option>
							{/foreach}
						</select>
					</div>
					<div class="col-lg-2 py-2">
						{vtranslate('LBL_MINUTES', $MODULE)}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
{/strip}