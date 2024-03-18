{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var=FIELD_INFO value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=FIELD_NAME value=$FIELD_MODEL->get('name')}
    <div class="form-check">
        <input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value="{if $IS_RELATION eq true}1{else}0{/if}"/>
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="checkbox" name="{$FIELD_MODEL->getFieldName()}"
               data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-fieldinfo="{$FIELD_INFO}"
               class="inputElement form-check-input" style="height: 1.3rem; width: 1.3rem" data-fieldtype="checkbox"
                {if $FIELD_MODEL->get('fieldvalue') eq true} checked="checked" {/if}
                {if $IS_RELATION eq true} disabled="disabled" {/if}
                {if !empty($SPECIAL_VALIDATOR)} data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)} {/if}
        />
    </div>
{/strip}