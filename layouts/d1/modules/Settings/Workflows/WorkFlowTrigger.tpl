{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div class="container-fluid py-3 px-4">
        {assign var=EXECUTION_CONDITION value=$WORKFLOW_MODEL_OBJ->executionCondition}
        <input type="hidden" name="workflow_trigger" value="{$EXECUTION_CONDITION}"/>
        <div class="form-group row mb-3">
            <label for="name" class="col-sm-3 control-label">
                {vtranslate('LBL_TRIGGER_WORKFLOW_ON', $QUALIFIED_MODULE)}
            </label>
            <div class="col-sm-6 controls">
                {assign var=SINGLE_SELECTED_MODULE value="SINGLE_$SELECTED_MODULE"}
                <div>
                    <label>
                        <input type="radio" name="workflow_trigger" value="1" {if $EXECUTION_CONDITION eq '1'} checked="" {/if}>
                        <span class="ms-2" id="workflowTriggerCreate">{vtranslate($SINGLE_SELECTED_MODULE, $SELECTED_MODULE)} {vtranslate('LBL_CREATION', $QUALIFIED_MODULE)}</span>
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="workflow_trigger" value="3" {if $EXECUTION_CONDITION eq '3' or $EXECUTION_CONDITION eq '2'} checked="" {/if}>
                        <span class="ms-2" id="workflowTriggerUpdate">{vtranslate($SINGLE_SELECTED_MODULE, $SELECTED_MODULE)} {vtranslate('LBL_UPDATED', $QUALIFIED_MODULE)}</span>
                        <span class="ms-2">({vtranslate('LBL_INCLUDES_CREATION', $QUALIFIED_MODULE)})</span>
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="workflow_trigger" value="6" {if $EXECUTION_CONDITION eq '6'} checked="" {elseif $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS} disabled="disabled" {/if}>
                        <span class="mx-2">{vtranslate('LBL_TIME_INTERVAL', $QUALIFIED_MODULE)}</span>
                        {if $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS}
                            <span class="alert-info textAlignCenter">
                            <i class="fa fa-info-circle"></i>
                            <span class="ms-2">({vtranslate('LBL_MAX_SCHEDULED_WORKFLOWS_EXCEEDED', $QUALIFIED_MODULE, $MAX_ALLOWED_SCHEDULED_WORKFLOWS)})</span>
                        </span>
                        {/if}
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group row mb-3 workflowRecurrenceBlock {if !in_array($EXECUTION_CONDITION, array(2,3))} hide {/if}">
            <label for="name" class="col-sm-3 control-label">
                {vtranslate('LBL_RECURRENCE', $QUALIFIED_MODULE)}
            </label>
            <div class="col-sm-5 controls">
                <div>
                    <label>
                        <input type="radio" name="workflow_recurrence" value="2" {if $EXECUTION_CONDITION eq '2'} checked="" {/if}>
                        <span class="ms-2">{vtranslate('LBL_FIRST_TIME_CONDITION_MET', $QUALIFIED_MODULE)}</span>
                    </label>
                </div>
                <div>
                    <label>
                        <input type="radio" name="workflow_recurrence" value="3" {if $EXECUTION_CONDITION eq '3'} checked="" {/if}>
                        <span class="ms-2">{vtranslate('LBL_EVERY_TIME_CONDITION_MET', $QUALIFIED_MODULE)}</span>
                    </label>
                </div>
            </div>
        </div>
        {if $SCHEDULED_WORKFLOW_COUNT < $MAX_ALLOWED_SCHEDULED_WORKFLOWS}
            <div id="scheduleBox" class="contentsBackground {if $WORKFLOW_MODEL_OBJ->executionCondition neq 6} hide {/if}">
                <div class="row form-group">
                    <div class="col-sm-3 control-label">
                        {vtranslate('LBL_FREQUENCY', $QUALIFIED_MODULE)}
                    </div>
                    <div class="col-sm-6 controls">
                        <div class="well">
                            <div class="form-group py-3">
                                <label for="schtypeid" class="control-label">
                                    {vtranslate('LBL_RUN_WORKFLOW', $QUALIFIED_MODULE)}
                                </label>
                                <div class="controls">
                                    <select class='select2' id='schtypeid' name='schtypeid' style="min-width: 150px;">
                                        <option value="1" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 1}selected{/if}>{vtranslate('LBL_HOURLY', $QUALIFIED_MODULE)}</option>
                                        <option value="2" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 2}selected{/if}>{vtranslate('LBL_DAILY', $QUALIFIED_MODULE)}</option>
                                        <option value="3" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 3}selected{/if}>{vtranslate('LBL_WEEKLY', $QUALIFIED_MODULE)}</option>
                                        <option value="4" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 4}selected{/if}>{vtranslate('LBL_SPECIFIC_DATE', $QUALIFIED_MODULE)}</option>
                                        <option value="5" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 5}selected{/if}>{vtranslate('LBL_MONTHLY_BY_DATE', $QUALIFIED_MODULE)}</option>
                                        <!--option value="6" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 6}selected{/if}>{vtranslate('LBL_MONTHLY_BY_WEEKDAY', $QUALIFIED_MODULE)}</option-->
                                        <option value="7" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 7}selected{/if}>{vtranslate('LBL_YEARLY', $QUALIFIED_MODULE)}</option>
                                    </select>
                                </div>
                            </div>

                            {* show weekdays for weekly option *}
                            <div class="form-group py-3 {if $WORKFLOW_MODEL_OBJ->schtypeid neq 3}hide{/if}" id="scheduledWeekDay">
                                <label class="control-label">{vtranslate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}
                                    <span class="redColor">*</span>
                                </label>
                                <div class="controls">
                                    {assign var=dayOfWeek value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schdayofweek)}
                                    <div class="weekDaySelect py-2">
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("7",$dayOfWeek)}ui-selected{/if}" data-value="7"> {vtranslate('LBL_DAY0', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("1",$dayOfWeek)}ui-selected{/if}" data-value="1"> {vtranslate('LBL_DAY1', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("2",$dayOfWeek)}ui-selected{/if}" data-value="2"> {vtranslate('LBL_DAY2', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("3",$dayOfWeek)}ui-selected{/if}" data-value="3"> {vtranslate('LBL_DAY3', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("4",$dayOfWeek)}ui-selected{/if}" data-value="4"> {vtranslate('LBL_DAY4', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("5",$dayOfWeek)}ui-selected{/if}" data-value="5"> {vtranslate('LBL_DAY5', 'Calendar')} </span>
                                        <span class="ui-state-default border-0 p-2 rounded me-2 {if is_array($dayOfWeek) && in_array("6",$dayOfWeek)}ui-selected{/if}" data-value="6"> {vtranslate('LBL_DAY6', 'Calendar')} </span>
                                        <input type="hidden" data-rule-required="true" name='schdayofweek' id='schdayofweek' {if is_array($dayOfWeek)} value="{implode(',',$dayOfWeek)}" {else} value=""{/if}/>
                                    </div>
                                </div>
                            </div>

                            {* show month view by dates *}
                            <div class="form-group {if $WORKFLOW_MODEL_OBJ->schtypeid neq 5}hide{/if}" id="scheduleMonthByDates">
                                <label class="control-label">
                                    <span>{vtranslate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                                <div class="controls">
                                    {assign var=DAYS value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schdayofmonth)}
                                    <select multiple class="select2" data-rule-required="true" name='schdayofmonth[]' id='schdayofmonth'>
                                        {section name=foo loop=31}
                                            <option value={$smarty.section.foo.iteration} {if is_array($DAYS) && in_array($smarty.section.foo.iteration, $DAYS)}selected{/if}>{$smarty.section.foo.iteration}</option>
                                        {/section}
                                    </select>
                                </div>
                            </div>

                            {* show specific date *}
                            <div class="form-group py-3 {if $WORKFLOW_MODEL_OBJ->schtypeid neq 4}hide{/if}" id="scheduleByDate">
                                <label class="control-label">
                                    <span>{vtranslate('LBL_CHOOSE_DATE', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                                <div class="controls">
                                    <div class="input-group">
                                        {assign var=specificDate value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schannualdates)}
                                        {if $specificDate[0] neq ''}
                                            {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])}
                                        {/if}
                                        <input type="text" class="dateField form-control" name="schdate" value="{$specificDate1}" data-date-format="{$CURRENT_USER->date_format}" data-rule-required="true"/>
                                        <span class="input-group-text">
                                            <i class="fa fa-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {* show month view by anually *}
                            <div class='form-group py-3 {if $WORKFLOW_MODEL_OBJ->schtypeid neq 7}hide{/if}' id="scheduleAnually">
                                <label class='control-label'> {vtranslate('LBL_SELECT_MONTH_AND_DAY', $QUALIFIED_MODULE)} <span class="redColor">*</span> </label>
                                <div class='controls'>
                                    <div id='annualDatePicker'></div>
                                </div>
                                <div class='controls'>
                                    <label>{vtranslate('LBL_SELECTED_DATES', $QUALIFIED_MODULE)}</label>
                                    <div>
                                        <input type=hidden id=hiddenAnnualDates value='{$WORKFLOW_MODEL_OBJ->schannualdates}'/>
                                        <select multiple class="select2" id='annualDates' name='schannualdates[]' data-rule-required="true" style="min-width: 100px;">
                                            {foreach item=DATES from=$ANNUAL_DATES}
                                                <option value="{$DATES}" selected>{$DATES}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {* show time for all other than Hourly option*}
                            <div class="form-group py-3 {if $WORKFLOW_MODEL_OBJ->schtypeid < 2} hide {/if}" id="scheduledTime">
                                <label for="schtime" class="col-sm-2 control-label">
                                    <span>{vtranslate('LBL_AT_TIME', $QUALIFIED_MODULE)}</span>
                                    <span class="text-danger ms-2">*</span>
                                </label>
                                <div class="controls" id="schtime">
                                    <div class="input-group time">
                                        <input type='text' data-format='24' name='schtime' value="{$WORKFLOW_MODEL_OBJ->schtime}" data-rule-required="true" class="timepicker-default inputElement form-control"/>
                                        <span class="input-group-text">
                                          <i class="fa fa-clock-o"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            {if $WORKFLOW_MODEL_OBJ->nexttrigger_time}
                                <div class="form-group">
                                    <label class="control-label">{vtranslate('LBL_NEXT_TRIGGER_TIME', $QUALIFIED_MODULE)}</label>
                                    <div class="controls">
                                        {if $WORKFLOW_MODEL_OBJ->schtypeid neq 4}
                                            <span>{DateTimeField::convertToUserFormat($WORKFLOW_MODEL_OBJ->nexttrigger_time)}</span>
                                            <span class="ms-2">({$ACTIVE_ADMIN->time_zone})</span>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
{/strip}