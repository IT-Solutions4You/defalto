{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
    {assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
    {if (!$FIELD_NAME)}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
    {/if}
    {assign var="fieldValue" value=purifyHtmlEventAttributes($FIELD_MODEL->get('fieldvalue'),true)}
    {if $fieldValue === null}
        {assign var="fieldValue" value=""}
    {/if}
    <div class="Vtiger_Text_UIType">
        {if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}
            <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control inputElement textAreaElement col-lg-12 {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                    {if php7_count($FIELD_INFO['validator'])}
                        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                    {/if}
                >
            {$fieldValue|regex_replace:"/(?!\w)\&nbsp;(?=\w)/":" "}
            </textarea>
        {else}
            <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control inputElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                    {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                    {if php7_count($FIELD_INFO['validator'])}
                        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                    {/if}
                >
            {$fieldValue|regex_replace:"/(?!\w)\&nbsp;(?=\w)/":" "}
            </textarea>
        {/if}
    </div>
{/strip}
