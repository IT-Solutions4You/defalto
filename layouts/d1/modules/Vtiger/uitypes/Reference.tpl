{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    {assign var=REFERENCE_LIST value=$FIELD_MODEL->getReferenceList()}
    {assign var=REFERENCE_LIST_COUNT value=php7_count($REFERENCE_LIST)}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=AUTOFILL_VALUE value=$FIELD_MODEL->getAutoFillValue()}
    {assign var=QUICKCREATE_RESTRICTED_MODULES value=Vtiger_Functions::getNonQuickCreateSupportedModules()}
    <div class="Vtiger_Reference_UIType referencefield-wrapper {if !empty($FIELD_VALUE)}selected{/if}">
        {if $REFERENCE_LIST_COUNT eq 1}
            <input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}"/>
        {/if}
        {if $REFERENCE_LIST_COUNT gt 1}
            {assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
            {assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
            {if !empty($REFERENCED_MODULE_STRUCT)}
                {assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
            {/if}
            {if isset($REFERENCED_MODULE_NAME) && in_array($REFERENCED_MODULE_NAME, $REFERENCE_LIST)}
                <input name="popupReferenceModule" type="hidden" value="{$REFERENCED_MODULE_NAME}"/>
            {else}
                <input name="popupReferenceModule" type="hidden" value="{$REFERENCE_LIST[0]}"/>
            {/if}
        {/if}
        {assign var=displayId value=$FIELD_VALUE}
        <div class="input-group rounded-start flex-nowrap">
            {if $REFERENCE_LIST_COUNT > 1}
                {assign var=DISPLAYID value=$FIELD_MODEL->get('fieldvalue')}
                {assign var=REFERENCED_MODULE_STRUCT value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
                {if !empty($REFERENCED_MODULE_STRUCT)}
                    {assign var=REFERENCED_MODULE_NAME value=$REFERENCED_MODULE_STRUCT->get('name')}
                {/if}
                <select class="select2 referenceModulesList {if $FIELD_MODEL->isMandatory() eq true}reference-mandatory{/if}">
                    {foreach key=index item=value from=$REFERENCE_LIST}
                        <option value="{$value}" {if isset($REFERENCED_MODULE_NAME) && $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
                    {/foreach}
                </select>
            {/if}
            <input id="{$FIELD_NAME}_display" name="{$FIELD_MODEL->getFieldName()}_display" data-fieldname="{$FIELD_MODEL->getFieldName()}" data-fieldtype="reference" type="text"
               class="marginLeftZero autoComplete inputElement form-control w-50"
               value="{$FIELD_MODEL->getEditViewDisplayValue($displayId)}"
               placeholder="{vtranslate('LBL_TYPE_SEARCH',$MODULE)}"
               {if !empty($FIELD_VALUE)}
                   readonly="readonly"
               {/if}
                {if $FIELD_INFO["mandatory"] eq true}
                    data-rule-required="true"
                    data-rule-reference_required="true"
                {/if}
                {if php7_count($FIELD_INFO['validator'])}
                    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                {/if}
            />
            <input name="{$FIELD_MODEL->getFieldName()}" type="hidden" value="{$FIELD_VALUE}" class="sourceField" data-displayvalue='{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}' {if $AUTOFILL_VALUE} data-autofill={Zend_Json::encode($AUTOFILL_VALUE)} {/if}/>
            <a href="#" class="clearReferenceSelection input-group-text {if empty($FIELD_VALUE)}hide{/if}">
                <i class="fa fa-xmark"></i>
            </a>
            <span class="input-group-addon relatedPopup cursorPointer input-group-text" title="{vtranslate('LBL_SELECT', $MODULE)}">
                <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_select" class="fa fa-search"></i>
            </span>
            {if (($REQUEST_INSTANCE.view eq 'Edit') or ($MODULE_NAME eq 'Webforms')) && !in_array($REFERENCE_LIST[0],$QUICKCREATE_RESTRICTED_MODULES)}
                <span class="input-group-addon createReferenceRecord cursorPointer clearfix input-group-text" title="{vtranslate('LBL_CREATE', $MODULE)}">
                    <i id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_create" class="fa fa-plus"></i>
                </span>
            {/if}
        </div>

    </div>
{/strip}