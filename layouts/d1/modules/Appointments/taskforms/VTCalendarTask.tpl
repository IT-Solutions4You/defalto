{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{$TASK_OBJECT->setSourceModule($SOURCE_MODULE)}
<div class="row form-group">
    <div class="col-sm-6 col-xs-6">
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Subject', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
            <div class="col-sm-9 col-xs-9">
                <input name="subject" class="inputElement form-control" type="text" required="required" value="{$TASK_OBJECT->subject}">
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Description', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <textarea name="description" class="inputElement form-control" type="text">{$TASK_OBJECT->description}</textarea>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Status', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
            <div class="col-sm-9 col-xs-9">
                <select name="calendar_status" type="text" required="required" class="inputElement form-select select2">
                    {foreach from=$TASK_OBJECT->getStatusValues() key=$KEY item=$VALUE}
                        <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->calendar_status}selected{/if}>{$VALUE}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Type', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
            <div class="col-sm-9 col-xs-9">
                <select name="calendar_type" type="text" required="required" class="inputElement form-select select2">
                    {foreach from=$TASK_OBJECT->getTypeValues() key=$KEY item=$VALUE}
                        <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->calendar_type}selected{/if}>{$VALUE}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Assinged To', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
            <div class="col-sm-9 col-xs-9">
                <select name="assigned_to" type="text" required="required" class="inputElement form-select select2">
                    {foreach from=$TASK_OBJECT->getAssignedToValues() key=$OPTGROUP_LABEL item=$OPTGROUP_VALUES}
                        <optgroup label="{$OPTGROUP_LABEL}">
                            {foreach from=$OPTGROUP_VALUES key=$KEY item=$VALUE}
                                <option value="{$KEY}">{$VALUE}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2 calendarTimeField">
            <div class="col-sm-3 col-xs-3">{vtranslate('Start Time', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <div class="input-group time">
                    <input name="start_time" type="text" class="timepicker-default inputElement form-control ui-timepicker-input" value="{$TASK_OBJECT->start_time}">
                    <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                </div>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Start Date', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <div class="row">
                    <div class="col-lg-3">
                        <input name="start_days" type="number"  class="inputElement form-control" value="{$TASK_OBJECT->start_days}">
                    </div>
                    <div class="col-lg-2">
                        {vtranslate('days', $QUALIFIED_MODULE)}
                    </div>
                    <div class="col-lg-3">
                        <select name="start_direction" class="inputElement form-select select2">
                            {foreach from=$TASK_OBJECT->getDirectionValues() key=$KEY item=$VALUE}
                                <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->start_direction}selected{/if}>{$VALUE}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <select name="start_field" class="inputElement form-select select2">
                            {foreach from=$TASK_OBJECT->getDateTimeValues() key=$KEY item=$VALUE}
                                <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->start_field}selected{/if}>{$VALUE}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row py-2 calendarTimeField">
            <div class="col-sm-3 col-xs-3">{vtranslate('End Time', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <div class="input-group time">
                    <input name="end_time" type="text" class="timepicker-default inputElement form-control ui-timepicker-input" value="{$TASK_OBJECT->end_time}">
                    <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                </div>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('End Date', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <div class="row">
                    <div class="col-lg-3">
                        <input name="end_days" type="number"  class="inputElement form-control" value="{$TASK_OBJECT->end_days}">
                    </div>
                    <div class="col-lg-2">
                        {vtranslate('days', $QUALIFIED_MODULE)}
                    </div>
                    <div class="col-lg-3">
                        <select name="end_direction" class="inputElement form-select select2">
                            {foreach from=$TASK_OBJECT->getDirectionValues() key=$KEY item=$VALUE}
                                <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->end_direction}selected{/if}>{$VALUE}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <select name="end_field" class="inputElement form-select select2">
                            {foreach from=$TASK_OBJECT->getDateTimeValues() key=$KEY item=$VALUE}
                                <option value="{$KEY}" {if $KEY eq $TASK_OBJECT->end_field}selected{/if}>{$VALUE}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-sm-3 col-xs-3">{vtranslate('Is All Day', $QUALIFIED_MODULE)}</div>
            <div class="col-sm-9 col-xs-9">
                <input name="is_all_day" type="checkbox"  class="inputElement form-check-input" {if '1' eq $TASK_OBJECT->is_all_day}checked{/if} value="1">
            </div>
        </div>
    </div>
</div>
{foreach from=$TASK_OBJECT->getOtherFields() item=$FIELD_MODEL key=$FIELD_NAME}
    {assign var=FIELD_DATA_TYPE value=$FIELD_MODEL->getFieldDataType()}
    <div class="row form-group" data-field_date_type="{$FIELD_DATA_TYPE}">
        <div class="col-sm-6 col-xs-6">
            <div class="row">
                <div class="col-sm-3 col-xs-3">{vtranslate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}{if $FIELD_MODEL->isMandatory()}<span class="text-danger ms-2">*</span>{/if}</div>
                <div class="col-sm-9 col-xs-9">
                    {if 'picklist' eq $FIELD_DATA_TYPE}
                        <select name="{$FIELD_NAME}" class="inputElement form-select select2" {if $FIELD_MODEL->isMandatory()}required="required"{/if}>
                            <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                            {foreach from=$FIELD_MODEL->getPicklistValues() item=$PICKLIST_VALUE key=$PICKLIST_KEY}
                                <option {if $PICKLIST_KEY eq $TASK_OBJECT->get($FIELD_NAME)}selected{/if} value="{$PICKLIST_KEY}">{vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE)}</option>
                            {/foreach}
                        </select>
                    {elseif 'boolean' eq $FIELD_DATA_TYPE}
                        <input name="{$FIELD_NAME}" type="checkbox"  class="inputElement form-check-input" {if $FIELD_MODEL->isMandatory()}required="required"{/if} {if '1' eq $TASK_OBJECT->get($FIELD_NAME)}checked{/if} value="1">
                    {elseif 'double' eq $FIELD_DATA_TYPE or 'integer' eq $FIELD_DATA_TYPE or 'percentage' eq $FIELD_DATA_TYPE}
                        <input name="{$FIELD_NAME}" type="number"  class="inputElement form-select" {if $FIELD_MODEL->isMandatory()}required="required"{/if} value="{$TASK_OBJECT->get($FIELD_NAME)}">
                    {elseif 'date' eq $FIELD_DATA_TYPE}
                        <div class="input-group date">
                            <input name="{$FIELD_NAME}" type="text"  class="inputElement form-control dateField form-control" {if $FIELD_MODEL->isMandatory()}required="required"{/if} value="{$TASK_OBJECT->get($FIELD_NAME)}">
                            <span class="input-group-text"><i class="fa fa-calendar "></i></span>
                        </div>
                    {elseif 'email' eq $FIELD_DATA_TYPE}
                        <input name="{$FIELD_NAME}" type="email"  class="inputElement form-control" {if $FIELD_MODEL->isMandatory()}required="required"{/if} value="{$TASK_OBJECT->get($FIELD_NAME)}">
                    {elseif 'url' eq $FIELD_DATA_TYPE}
                        <input name="{$FIELD_NAME}" type="url"  class="inputElement form-control" {if $FIELD_MODEL->isMandatory()}required="required"{/if} value="{$TASK_OBJECT->get($FIELD_NAME)}">
                    {elseif 'text' eq $FIELD_DATA_TYPE}
                        <textarea name="{$FIELD_NAME}" cols="30" rows="10" class="inputElement form-control" {if $FIELD_MODEL->isMandatory()}required="required"{/if} >{$TASK_OBJECT->get($FIELD_NAME)}</textarea>
                    {elseif 'multipicklist' eq $FIELD_DATA_TYPE}
                        <select name="{$FIELD_NAME}" class="inputElement form-select select2" multiple {if $FIELD_MODEL->isMandatory()}required="required"{/if}>
                            <option value="">{vtranslate('LBL_NONE', $QUALIFIED_MODULE)}</option>
                            {foreach from=$FIELD_MODEL->getPicklistValues() item=$PICKLIST_VALUE key=$PICKLIST_KEY}
                                <option {if in_array($PICKLIST_KEY, $TASK_OBJECT->get($FIELD_NAME))}selected{/if} value="{$PICKLIST_KEY}">{vtranslate($PICKLIST_VALUE, $QUALIFIED_MODULE)}</option>
                            {/foreach}
                        </select>
                    {elseif 'time' eq $FIELD_DATA_TYPE}
                        <div class="input-group time">
                            <input name="{$FIELD_NAME}" type="text" class="timepicker-default inputElement form-control ui-timepicker-input" value="{$TASK_OBJECT->get($FIELD_NAME)}" />
                            <span class="input-group-text"><i class="fa fa-clock-o"></i></span>
                        </div>
                    {else}
                        <input name="{$FIELD_NAME}" type="text"  class="inputElement form-select" {if $FIELD_MODEL->isMandatory()}required="required"{/if} value="{$TASK_OBJECT->get($FIELD_NAME)}">
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/foreach}
<script src="layouts/d1/modules/Appointments/taskforms/VTCalendarTask.js"></script>
<br><br><br>

