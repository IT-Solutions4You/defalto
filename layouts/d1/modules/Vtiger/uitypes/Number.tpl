{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
    {if $MODULE eq 'HelpDesk' && ($FIELD_MODEL->get('name') eq 'days' || $FIELD_MODEL->get('name') eq 'hours')}
        {assign var="FIELD_VALUE" value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
    {elseif $FIELD_MODEL->getFieldDataType() eq 'double'}
        {assign var="FIELD_VALUE" value=$FIELD_MODEL->get('fieldvalue')}
    {else}
        {assign var="FIELD_VALUE" value=$FIELD_MODEL->get('fieldvalue')}
    {/if}
    {if (!$FIELD_NAME)}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="Vtiger_Number_UIType">
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="form-control inputElement numberField replaceCommaWithDot" name="{$FIELD_NAME}"
               value="{$FIELD_VALUE}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                {if php7_count($FIELD_INFO['validator'])}
                    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                {/if}
        />
    </div>
{/strip}
