{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
    {if (!$FIELD_NAME)}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="Vtiger_Text_UIType">
        {if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}
            <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control inputElement textAreaElement col-lg-12 {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                    {if php7_count($FIELD_INFO['validator'])}
                        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                    {/if}
                >
            {purifyHtmlEventAttributes($FIELD_MODEL->get('fieldvalue'),true)|regex_replace:"/(?!\w)\&nbsp;(?=\w)/":" "}
            </textarea>
        {else}
            <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                    {if php7_count($FIELD_INFO['validator'])}
                        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                    {/if}
                >
            {purifyHtmlEventAttributes($FIELD_MODEL->get('fieldvalue'),true)|regex_replace:"/(?!\w)\&nbsp;(?=\w)/":" "}
            </textarea>
        {/if}
    </div>
{/strip}
