{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{assign var=TOMORROW_DATE value=$FIELD_MODEL->getUITypeModel()->getTomorrowDate()}
	<div class="row">
		<div class="col-sm-1 py-2">
			<input type="hidden" class="recurringEditMode" name="recurringEditMode" value="">
			{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes' && !$REQUEST_INSTANCE.isDuplicate}
				<input type="hidden" class="recurringEdit" value="true" />
			{/if}
			<input type="checkbox" class="form-check-input" name="recurringcheck" data-field-id= '{$FIELD_MODEL->get('id')}' value="" {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}checked{/if} style="height: 1.3rem; width: 1.3rem;"/>
		</div>
		<div id="repeatUI" class="col-sm-11" style="visibility: {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}visible{else}collapse{/if};">
			<div class="row">
				<div class="col-lg-2 py-2">
					<span>{vtranslate('LBL_REPEATEVENT', $MODULE)}</span>
				</div>
				<div class="col-lg-2 py-2">
					<select class="select2 input-mini" name="repeat_frequency">
						{for $FREQUENCY = 1 to 14}
							<option value="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['repeat_frequency']}selected{/if}>{$FREQUENCY}</option>
						{/for}
					</select>
				</div>
				<div class="col-lg-2 py-2">
					<select class="select2 input-medium" style="width:85px;margin-left: 10px;" name="recurringtype" id="recurringType">
						<option value="Daily" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
						<option value="Weekly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
						<option value="Monthly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
						<option value="Yearly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
					</select>
				</div>
				<div class="col-lg-2 py-2">
					<span>{vtranslate('LBL_UNTIL', $MODULE)}</span>
				</div>
				<div class="col-lg-4 py-2">
					<div class="input-group date inputElement">
						<input type="text" id="calendar_repeat_limit_date" class="dateField input-small form-control" name="calendar_repeat_limit_date" data-date-format="{$USER_MODEL->get('date_format')}"
							   value="{if $RECURRING_INFORMATION['recurringcheck'] neq 'Yes'}{$TOMORROW_DATE}{elseif $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}{$RECURRING_INFORMATION['recurringenddate']}{/if}"
							   data-rule-date="true" data-rule-required="true" data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}/>
						<span class="input-group-text">
							<i class="fa fa-calendar"></i>
						</span>
					</div>
				</div>
			</div>
			<div class="{if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'}show{else}hide{/if}"  id="repeatWeekUI">
				<div class="row">
					<div class="col-lg-2 py-2">
						<span class="medium">{ucwords(vtranslate('LBL_ON', $MODULE))}</span>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="sun_flag" class="form-check-input" value="sunday" {$RECURRING_INFORMATION['week0']} type="checkbox"/>
							<span>{vtranslate('LBL_SM_SUN', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="mon_flag" class="form-check-input" value="monday" {$RECURRING_INFORMATION['week1']} type="checkbox">
							<span>{vtranslate('LBL_SM_MON', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="tue_flag" class="form-check-input" value="tuesday" {$RECURRING_INFORMATION['week2']} type="checkbox">
							<span>{vtranslate('LBL_SM_TUE', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="wed_flag" class="form-check-input" value="wednesday" {$RECURRING_INFORMATION['week3']} type="checkbox">
							<span>{vtranslate('LBL_SM_WED', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="thu_flag" class="form-check-input" value="thursday" {$RECURRING_INFORMATION['week4']} type="checkbox">
							<span>{vtranslate('LBL_SM_THU', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="fri_flag" class="form-check-input" value="friday" {$RECURRING_INFORMATION['week5']} type="checkbox">
							<span>{vtranslate('LBL_SM_FRI', $MODULE)}</span>
						</label>
					</div>
					<div class="col-lg-auto py-2">
						<label class="checkbox form-check">
							<input name="sat_flag" class="form-check-input" value="saturday" {$RECURRING_INFORMATION['week6']} type="checkbox">
							<span>{vtranslate('LBL_SM_SAT', $MODULE)}</span>
						</label>
					</div>
				</div>
			</div>
			<div class="{if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI">
				<div class="row">
					<div class="col-lg-2 py-2">
						<span class="form-check">
							<input class="form-check-input" type="radio" id="repeatDate" data-field-id= '{$FIELD_MODEL->get('id')}' name="repeatMonth" checked value="date" {if $RECURRING_INFORMATION['repeatMonth'] eq 'date'} checked {/if}/>
							<span class="form-check-label">{vtranslate('LBL_ON', $MODULE)}</span>
						</span>
					</div>
					<div class="col-lg py-2">
						<div class="input-group">
							<input type="text" id="repeatMonthDate" data-field-id= '{$FIELD_MODEL->get('id')}' class="form-control input-mini" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{if $RECURRING_INFORMATION['repeatMonth_date'] eq ''}2{else}{$RECURRING_INFORMATION['repeatMonth_date']}{/if}"/>
							<span class="input-group-text">{vtranslate('LBL_DAY_OF_THE_MONTH', $MODULE)}</span>
						</div>
					</div>
				</div>
				<div class="row" id="repeatMonthDayUI">
					<div class="col-lg-2 py-2">
						<span class="form-check">
							<input class="form-check-input" type="radio" id="repeatDay" data-field-id= '{$FIELD_MODEL->get('id')}' name="repeatMonth" value="day" {if $RECURRING_INFORMATION['repeatMonth'] eq 'day'} checked {/if}/>
							<span class="form-check-label">{vtranslate('LBL_ON', $MODULE)}</span>
						</span>
					</div>
					<div class="col-lg py-2">
						<select id="repeatMonthDayType" class="select2" name="repeatMonth_daytype">
							<option value="first" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $MODULE)}</option>
							<option value="last" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $MODULE)}</option>
						</select>
					</div>
					<div class="col-lg py-2">
						<select id="repeatMonthDay" class="select2" name="repeatMonth_day">
							<option value=0 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 0} selected {/if}>{vtranslate('LBL_DAY0', $MODULE)}</option>
							<option value=1 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 1} selected {/if}>{vtranslate('LBL_DAY1', $MODULE)}</option>
							<option value=2 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 2} selected {/if}>{vtranslate('LBL_DAY2', $MODULE)}</option>
							<option value=3 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 3} selected {/if}>{vtranslate('LBL_DAY3', $MODULE)}</option>
							<option value=4 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 4} selected {/if}>{vtranslate('LBL_DAY4', $MODULE)}</option>
							<option value=5 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 5} selected {/if}>{vtranslate('LBL_DAY5', $MODULE)}</option>
							<option value=6 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 6} selected {/if}>{vtranslate('LBL_DAY6', $MODULE)}</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
	{include file=vtemplate_path('RecurringEditView.tpl',$MODULE)}
{/strip}