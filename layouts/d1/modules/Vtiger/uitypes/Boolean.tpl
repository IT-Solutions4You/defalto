{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    {if (!$FIELD_NAME)}
        {assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="form-check">
        <input type="hidden" name="{$FIELD_NAME}" value="" />
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="inputElement form-check-input" value="1" style="height: 1.3rem; width: 1.3rem" data-fieldname="{$FIELD_NAME}" data-fieldtype="checkbox" type="checkbox" name="{$FIELD_NAME}"
                {if $FIELD_MODEL->get('fieldvalue') eq true} checked="checked" {/if}
                {if !empty($SPECIAL_VALIDATOR)} data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}" {/if}
                {if $FIELD_INFO['mandatory'] eq true} data-rule-required="true" {/if}
                {if php7_count($FIELD_INFO['validator'])} data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}' {/if}
        />
    </div>
{/strip}
