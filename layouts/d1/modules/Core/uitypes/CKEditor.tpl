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
    {assign var=UITYPE_MODEL value=$FIELD_MODEL->getUiTypeModel()}
    <div class="Core_CKEditor_UIType">
        <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="ckeditor form-control inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}"
        {if !empty($SPECIAL_VALIDATOR)} data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}' {/if}
        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
        {if php7_count($FIELD_INFO['validator'])} data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}' {/if}>{$UITYPE_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}</textarea>
    </div>
{/strip}
