{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{* modules/Settings/Workflows/views/CreateEntity.php *}
{* START YOUR IMPLEMENTATION FROM BELOW. Use {debug} for information *}
<input type="hidden" id="fieldValueMapping" name="field_value_mapping" value='{$TASK_OBJECT->field_value_mapping}' />
<input type="hidden" value="{if $TASK_ID}{$TASK_OBJECT->reference_field}{else}{$REFERENCE_FIELD_NAME}{/if}" name='reference_field' id='reference_field' />
<div class="conditionsContainer" id="save_fieldvaluemapping">
	{if $RELATED_MODULE_MODEL_NAME neq '' && getTabid($RELATED_MODULE_MODEL_NAME)}
		<div class="row py-2">
			<div class="col-lg-2"></div>
			<div class="col-lg-6">
				<button type="button" class="btn btn-outline-secondary" id="addFieldBtn">
					<i class="fa-solid fa-plus"></i>
					<span class="ms-2">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</span>
				</button>
			</div>
		</div>
		{assign var=RELATED_MODULE_MODEL value=Vtiger_Module_Model::getInstance($TASK_OBJECT->entity_type)}
		{assign var=FIELD_VALUE_MAPPING value=ZEND_JSON::decode($TASK_OBJECT->field_value_mapping)}
		{foreach from=$FIELD_VALUE_MAPPING item=FIELD_MAP}
			{assign var=SELECTED_FIELD_MODEL value=$RELATED_MODULE_MODEL->getField($FIELD_MAP['fieldname'])}
			{if empty($SELECTED_FIELD_MODEL)}
				{continue}
			{/if}
			{assign var=SELECTED_FIELD_MODEL_FIELD_TYPE value=$SELECTED_FIELD_MODEL->getFieldDataType()}
			<div class="row py-2 conditionRow">
				<div class="col-lg-2">
					<select name="fieldname" class="select2" style="min-width: 250px" {if $SELECTED_FIELD_MODEL->isMandatory() || ($DISABLE_ROW eq 'true') } disabled="" {/if} >
						<option value="none"></option>
						{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$FIELD_MODEL->get('name')}" {if $FIELD_MAP['fieldname'] eq $FIELD_MODEL->get('name')} {if $FIELD_MODEL->isMandatory()}{assign var=MANDATORY_FIELD value=true} {else} {assign var=MANDATORY_FIELD value=false} {/if}{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
								{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}{if $SELECTED_FIELD_MODEL->isMandatory() and $FIELD_MODEL->getName() neq 'assigned_user_id'}<span class="redColor">*</span>{/if}
							</option>	
						{/foreach}
					</select>
					<input name="modulename" type="hidden"
							{if $FIELD_MAP['modulename'] eq $SOURCE_MODULE} value="{$SOURCE_MODULE}" {/if}
							{if $FIELD_MAP['modulename'] eq $RELATED_MODULE_MODEL_NAME} value="{$RELATED_MODULE_MODEL_NAME}" {/if}
					/>
				</div>
				<div class="fieldUiHolder col-lg-6">
					<input type="text" class="getPopupUi inputElement form-control" {if ($DISABLE_ROW eq 'true')} disabled=""{/if} readonly="" name="fieldValue" value="{$FIELD_MAP['value']}" />
					<input type="hidden" name="valuetype" value="{$FIELD_MAP['valuetype']}" />
				</div>
				{if $MANDATORY_FIELD neq true}
					<div class="cursorPointer col-lg-auto">
						<div class="deleteCondition btn btn-outline-secondary">
							<i class="fa fa-trash"></i>
						</div>
					</div>
				{/if}
			</div>
		{/foreach}

		{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
	{else}
		{if $RELATED_MODULE_MODEL}
			<div>
				<button type="button" class="btn btn-outline-secondary" id="addFieldBtn">{vtranslate('LBL_ADD_FIELD',$QUALIFIED_MODULE)}</button>
			</div>
			{assign var=MANDATORY_FIELD_MODELS value=$RELATED_MODULE_MODEL->getMandatoryFieldModels()}
			{foreach from=$MANDATORY_FIELD_MODELS item=MANDATORY_FIELD_MODEL}
				{if in_array($SOURCE_MODULE, $MANDATORY_FIELD_MODEL->getReferenceList())}
					{continue}
				{/if}
				<div class="row py-2 conditionRow form-group">
					<div class="col-lg-2">
						<select name="fieldname" class="select2" disabled="" data-width="100%">
							<option value="none"></option>
							{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
								{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
								<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" {if $FIELD_MODEL->get('name') eq $MANDATORY_FIELD_MODEL->get('name')} {assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldDataType()} selected=""{/if} data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
									{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}<span class="redColor">*</span>
								</option>	
							{/foreach}
						</select>
						{if ($FIELD_TYPE eq 'picklist' || $FIELD_TYPE eq 'multipicklist')}
							<input type="hidden" name="modulename" value="{$RELATED_MODULE_MODEL->get('name')}" />
						{else}
							<input type="hidden" name="modulename" value="{$SOURCE_MODULE}" />
						{/if}
					</div>
					<div class="col-lg-6 fieldUiHolder">
						<input type="text" class="getPopupUi inputElement" name="fieldValue" value="" />
						<input type="hidden" name="valuetype" value="rawtext" />
					</div>
				</div>
			{/foreach}
			{include file="FieldExpressions.tpl"|@vtemplate_path:$QUALIFIED_MODULE RELATED_MODULE_MODEL=$RELATED_MODULE_MODEL MODULE_MODEL=$MODULE_MODEL FIELD_EXPRESSIONS=$FIELD_EXPRESSIONS}
		{/if}
	{/if}
</div>
{if $RELATED_MODULE_MODEL}
	<div class="row py-2 basicAddFieldContainer hide">
		<div class="col-lg-2">
			<select name="fieldname" data-width="100%">
				<option value="none">{vtranslate('LBL_NONE',$QUALIFIED_MODULE)}</option>
				{foreach from=$RELATED_MODULE_MODEL->getFields() item=FIELD_MODEL}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{if !$FIELD_MODEL->isMandatory()}
					<option value="{$FIELD_MODEL->get('name')}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_MODEL->get('name')}" data-fieldinfo='{ZEND_JSON::encode($FIELD_INFO)}' >
						{vtranslate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}
					</option>
					{/if}
				{/foreach}
			</select>
			<input type="hidden" name="modulename" value="{$SOURCE_MODULE}" />
		</div>
		<div class="fieldUiHolder col-lg-6">
			<input type="text" class="inputElement form-control" readonly="" name="fieldValue" value="" />
			<input type="hidden" name="valuetype" value="rawtext" />
		</div>
		<div class="cursorPointer col-lg-auto">
			<div class="btn btn-outline-secondary deleteCondition">
				<i class="fa fa-trash"></i>
			</div>
		</div>
	</div>
{/if}
