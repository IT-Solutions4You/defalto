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
    <div class="Vtiger_String_UIType">
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" data-fieldname="{$FIELD_NAME}" data-fieldtype="string" class="inputUITypeString inputElement form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_NAME}" value="{decode_html($FIELD_MODEL->get('fieldvalue'))|php7_htmlentities}"
                {if $FIELD_MODEL->get('uitype') eq '3' || $FIELD_MODEL->get('uitype') eq '4'|| $FIELD_MODEL->isReadOnly()}
                    {if $FIELD_MODEL->get('uitype') neq '106'}
                        readonly
                    {elseif $FIELD_MODEL->get('uitype') eq '106' && $MODE eq 'edit'}
                        readonly
                    {/if}
                {/if}
                {if !empty($SPECIAL_VALIDATOR)}data-validator="{Zend_Json::encode($SPECIAL_VALIDATOR)}"{/if}
                {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                {if !empty($FIELD_INFO['validator']) && (php7_count($FIELD_INFO['validator']))}
                    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                {/if}
        />
    </div>
{/strip}
