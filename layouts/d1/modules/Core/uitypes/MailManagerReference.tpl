{**
* This file is part of the IT-Solutions4You CRM Software.
*
* (c) IT-Solutions4You s.r.o [info@its4you.sk]
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
{strip}
    {assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
    {assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
    {assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
    {assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
    <div class="Core_MailManagerReference_UIType">
        <input type="hidden" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" name="{$FIELD_NAME}" value="{$FIELD_VALUE}"
                {if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}' {/if}
                {if $FIELD_INFO['mandatory'] eq true} data-rule-required="true" {/if}
                {if php7_count($FIELD_INFO['validator'])} data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}' {/if}
        >
        <input type="text" class="form-control" value="{$FIELD_VALUE}" disabled="disabled">
    </div>
{/strip}
