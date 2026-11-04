{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="record-action-permission-task">
        <div class="row py-2">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTIONS', 'Core')}<span class="text-danger ms-1">*</span></div>
            <div class="col-lg-6">
                <select class="select2 inputElement" multiple="true" name="actions[]" data-rule-required="true" data-placeholder="{vtranslate('LBL_RECORD_ACTIONS', 'Core')}">
                    {foreach from=Core_RecordActionPermission_Model::getActions() key=ACTION item=ACTION_LABEL}
                        <option value="{$ACTION}" {if $TASK_OBJECT->isSelectedAction($ACTION)}selected="selected"{/if}>{vtranslate($ACTION_LABEL, 'Core')}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2 recordActionFieldsRow{if !$TASK_OBJECT->isSelectedAction('FieldEdit')} d-none{/if}">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTION_FIELDS', 'Core')}</div>
            <div class="col-lg-6">
                <select class="select2 inputElement" multiple="true" name="fields[]" data-placeholder="{vtranslate('LBL_RECORD_ACTION_FIELDS', 'Core')}">
                    {foreach from=Core_RecordActionPermissionTask_Helper::getFieldOptions($MODULE_MODEL) key=FIELD_NAME item=FIELD_LABEL}
                        <option value="{$FIELD_NAME|escape}" {if $TASK_OBJECT->isSelectedField($FIELD_NAME)}selected="selected"{/if}>{$FIELD_LABEL|escape}</option>
                    {/foreach}
                </select>
                <div class="form-text">{vtranslate('LBL_RECORD_ACTION_FIELDS_INFO', 'Core')}</div>
            </div>
        </div>
        <div class="row py-2 recordActionStatusValuesRow{if !$TASK_OBJECT->isSelectedAction('StatusChange')} d-none{/if}">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTION_STATUS_VALUES', 'Core')}</div>
            <div class="col-lg-6">
                <select class="select2 inputElement" multiple="true" name="statusValues[]" data-placeholder="{vtranslate('LBL_RECORD_ACTION_STATUS_VALUES', 'Core')}">
                    {foreach from=Core_RecordActionPermissionTask_Helper::getStatusOptions($MODULE_MODEL) item=STATUS_FIELD}
                        <optgroup label="{$STATUS_FIELD['label']|escape}">
                            {foreach from=$STATUS_FIELD['values'] key=STATUS_VALUE item=STATUS_LABEL}
                                <option value="{$STATUS_VALUE|escape}" {if $TASK_OBJECT->isSelectedStatusValue($STATUS_VALUE)}selected="selected"{/if}>{$STATUS_LABEL|escape}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
                <div class="form-text">{vtranslate('LBL_RECORD_ACTION_STATUS_VALUES_INFO', 'Core')}</div>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTION_EFFECT', 'Core')}</div>
            <div class="col-lg-6">
                <select class="select2 inputElement" name="effect">
                    <option value="deny" {if $TASK_OBJECT->effect neq 'allow'}selected="selected"{/if}>{vtranslate('LBL_RECORD_ACTION_DENY', 'Core')}</option>
                    <option value="allow" {if $TASK_OBJECT->effect eq 'allow'}selected="selected"{/if}>{vtranslate('LBL_RECORD_ACTION_ALLOW', 'Core')}</option>
                </select>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTION_MEMBERS', 'Core')}<span class="text-danger ms-1">*</span></div>
            <div class="col-lg-6">
                <select class="select2 inputElement" multiple="true" name="members[]" data-rule-required="true" data-placeholder="{vtranslate('LBL_RECORD_ACTION_MEMBERS', 'Core')}">
                    {foreach from=Core_RecordActionPermissionTask_Helper::getMemberOptions() key=MEMBER_TYPE item=MEMBERS}
                        <optgroup label="{vtranslate("LBL_RECORD_ACTION_MEMBERS_`$MEMBER_TYPE`", 'Core')}">
                            {foreach from=$MEMBERS key=MEMBER_ID item=MEMBER_NAME}
                                <option value="{$MEMBER_ID}" {if $TASK_OBJECT->isSelectedMember($MEMBER_ID)}selected="selected"{/if}>{$MEMBER_NAME|escape}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row py-2">
            <div class="col-lg-2">{vtranslate('LBL_RECORD_ACTION_REASON', 'Core')}</div>
            <div class="col-lg-6">
                <input type="text" class="inputElement form-control" name="reason" maxlength="255" value="{$TASK_OBJECT->reason|escape}"/>
                <div class="form-text">{vtranslate('LBL_RECORD_ACTION_REASON_INFO', 'Core')}</div>
            </div>
        </div>
    </div>
{/strip}
