{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{$TASK_OBJECT->setSourceModule($SOURCE_MODULE)}
{assign var=MAPPABLE_FIELDS value=$TASK_OBJECT->getMappableFields()}
<div class="row form-group">
    <div class="col-sm-6 col-xs-6">
        <div class="conditionsContainer rawTextTemplateOnly">
            <div class="row conditionRow py-2">
                <div class="col-sm-3 col-xs-3">{vtranslate('Subject', $QUALIFIED_MODULE)}<span class="text-danger ms-2">*</span></div>
                <div class="fieldUiHolder col-sm-9 col-xs-9">
                    <input name="subject" class="getPopupUi inputElement form-control" type="text" readonly required="required" value="{$TASK_OBJECT->subject}">
                    <input type="hidden" name="valuetype" value="rawtext">
                </div>
            </div>
            <div class="row conditionRow py-2">
                <div class="col-sm-3 col-xs-3">{vtranslate('Description', $QUALIFIED_MODULE)}</div>
                <div class="fieldUiHolder col-sm-9 col-xs-9">
                    <textarea name="description" class="getPopupUi inputElement form-control" type="text" readonly>{$TASK_OBJECT->description}</textarea>
                    <input type="hidden" name="valuetype" value="rawtext">
                </div>
            </div>
            {include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE ENABLE_TEMPLATE_VARIABLES=true RAW_TEXT_ONLY=true}
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
<div class="row py-3 border-top">
    <div class="col-sm-9">
        <h5>{vtranslate('LBL_ADD_FIELDS', $QUALIFIED_MODULE)}</h5>
    </div>
    <div class="col-sm-3 text-end">
        <button type="button" class="btn btn-outline-secondary" id="addFieldBtn">
            <i class="fa-solid fa-plus"></i>
            <span class="ms-2">{vtranslate('LBL_ADD_FIELD', $QUALIFIED_MODULE)}</span>
        </button>
    </div>
</div>
<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{Vtiger_Util_Helper::toSafeHTML($TASK_OBJECT->field_value_mapping)}'>
<div class="conditionsContainer" id="save_fieldvaluemapping">
    {foreach from=$TASK_OBJECT->getFieldValueMapping() item=FIELD_MAP}
        {assign var=SELECTED_FIELD_MODEL value=$MAPPABLE_FIELDS[$FIELD_MAP['fieldname']]}
        {if empty($SELECTED_FIELD_MODEL)}
            {continue}
        {/if}
        <div class="row conditionRow my-3">
            <div class="cursorPointer col-auto text-center">
                <span class="btn btn-outline-secondary deleteCondition">
                    <i class="fa fa-trash"></i>
                </span>
            </div>
            <div class="col-4">
                <select name="fieldname" class="select2 form-select" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
                    <option></option>
                    {foreach from=$MAPPABLE_FIELDS item=FIELD_MODEL}
                        {assign var=FIELD_INFO value=$TASK_OBJECT->getFieldInfo($FIELD_MODEL)}
                        <option value="{$FIELD_MODEL->getName()}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->getName()}selected{/if} data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}'>
                            {vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="fieldUiHolder col-4">
                <input type="text" class="getPopupUi inputElement form-control" readonly name="fieldValue" data-display-value="{$FIELD_MAP['display-value']|escape:'html'}" value="{$FIELD_MAP['value']|escape:'html'}">
                <input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}">
            </div>
        </div>
    {/foreach}
    {include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE ENABLE_TEMPLATE_VARIABLES=true RAW_TEXT_ONLY=false}
</div>
<div class="row my-3 basicAddFieldContainer hide">
    <div class="cursorPointer col-auto text-center">
        <span class="btn btn-outline-secondary deleteCondition">
            <i class="fa fa-trash"></i>
        </span>
    </div>
    <div class="col-4">
        <select name="fieldname" data-placeholder="{vtranslate('LBL_SELECT_FIELD', $QUALIFIED_MODULE)}">
            <option></option>
            {foreach from=$MAPPABLE_FIELDS item=FIELD_MODEL}
                {assign var=FIELD_INFO value=$TASK_OBJECT->getFieldInfo($FIELD_MODEL)}
                <option value="{$FIELD_MODEL->getName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->getName()}" data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}'>
                    {vtranslate($FIELD_MODEL->getLabel(), $FIELD_MODEL->getModuleName())}
                </option>
            {/foreach}
        </select>
    </div>
    <div class="fieldUiHolder col-4">
        <input type="text" class="inputElement form-control" readonly name="fieldValue" value="">
        <input type="hidden" name="valuetype" value="rawtext">
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

