{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <div class="my-3">
        <strong>{vtranslate('LBL_SET_FIELD_VALUES',$QUALIFIED_MODULE)}</strong>
    </div>
    <div class="my-3">
        <button type="button" class="btn btn-outline-secondary" id="addFieldBtn">
            <i class="fa fa-plus"></i>
            <span class="ms-2">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</span>
        </button>
    </div>
    <div class="conditionsContainer my-3" id="save_fieldvaluemapping">
        {assign var=FIELD_VALUE_MAPPING value=ZEND_JSON::decode($TASK_OBJECT->field_value_mapping)}
        <input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{Vtiger_Util_Helper::toSafeHTML($TASK_OBJECT->field_value_mapping)}'/>
        {foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
            <div class="row conditionRow my-3">
                <div class="cursorPointer col-auto text-center">
                    <span class="btn btn-outline-secondary deleteCondition">
						<i class="fa fa-trash"></i>
					</span>
                </div>
                <div class="col-4">
                    <select name="fieldname" class="select2 form-select" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
                        <option></option>
                        {foreach from=$RECORD_STRUCTURE  item=FIELDS}
                            {foreach from=$FIELDS item=FIELD_MODEL}
                                {if (!($FIELD_MODEL->get('workflow_fieldEditable') eq true)) or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))}
                                    {continue}
                                {/if}
                                {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                                {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
                                {assign var=FIELD_MODULE_MODEL value=$FIELD_MODEL->getModule()}
                                <option value="{$FIELD_MODEL->get('workflow_columnname')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('workflow_columnname')}selected="" {/if}data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}"
                                        {if ($FIELD_MODULE_MODEL->get('name') eq 'Events') and ($FIELD_NAME eq 'recurringtype')}
                                            {assign var=PICKLIST_VALUES value=Calendar_Field_Model::getReccurencePicklistValues()}
                                            {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                        {/if}
                                        data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}'>
                                    {vtranslate($FIELD_MODEL->get('workflow_columnlabel'), $SOURCE_MODULE)}
                                </option>
                            {/foreach}
                        {/foreach}
                    </select>
                </div>

                <div class="fieldUiHolder col-4">
                    <input type="text" class="getPopupUi inputElement form-control" readonly="" name="fieldValue" value="{$FIELD_MAP['value']}"/>
                    <input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}"/>
                </div>
            </div>
        {/foreach}
        {include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
    </div>
    <br>
    <div class="row my-3 basicAddFieldContainer hide">
		<div class="cursorPointer col-auto text-center">
            <span class="btn btn-outline-secondary deleteCondition">
				<i class="fa fa-trash"></i>
			</span>
		</div>
        <div class="col-4">
            <select name="fieldname" data-placeholder="{vtranslate('LBL_SELECT_FIELD',$QUALIFIED_MODULE)}">
                <option></option>
                {foreach from=$RECORD_STRUCTURE  item=FIELDS}
                    {foreach from=$FIELDS item=FIELD_MODEL}
                        {if (!($FIELD_MODEL->get('workflow_fieldEditable') eq true))  or ($MODULE_MODEL->get('name')=="Documents" and in_array($FIELD_MODEL->get('name'),$RESTRICTFIELDS))}
                            {continue}
                        {/if}
                        {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
                        {assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
                        {assign var=FIELD_MODULE_MODEL value=$FIELD_MODEL->getModule()}
                        <option value="{$FIELD_MODEL->get('workflow_columnname')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}"
                                {if ($FIELD_MODULE_MODEL->get('name') eq 'Events') and ($FIELD_NAME eq 'recurringtype')}
                                    {assign var=PICKLIST_VALUES value=Calendar_Field_Model::getReccurencePicklistValues()}
                                    {$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
                                {/if}
                                data-fieldinfo='{Vtiger_Functions::jsonEncode($FIELD_INFO)}'>
                            {vtranslate($FIELD_MODEL->get('workflow_columnlabel'), $SOURCE_MODULE)}
                        </option>
                    {/foreach}
                {/foreach}
            </select>
        </div>
        <div class="fieldUiHolder col-4">
            <input type="text" class="inputElement form-control" readonly="" name="fieldValue" value=""/>
            <input type="hidden" name="valuetype" value="rawtext"/>
        </div>
    </div>
{/strip}
