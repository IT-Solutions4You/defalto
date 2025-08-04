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
    {assign var="dateFormat" value=$USER_MODEL->get('date_format')}
    {if (!isset($FIELD_NAME))}
        {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
    {/if}
    <div class="input-group inputElement">
        <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="dateField form-control {if $IGNOREUIREGISTRATION}ignore-ui-registration{/if}" data-fieldname="{$FIELD_NAME}" data-fieldtype="date" name="{$FIELD_NAME}" data-date-format="{$dateFormat}"
               value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                {if isset($MODE) && $MODE eq 'edit' && $FIELD_NAME eq 'due_date'} data-user-changed-time="true" {/if}
                {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
                {if php7_count($FIELD_INFO['validator'])}
                    data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                {/if} data-rule-date="true"/>
        <div class="input-group-addon input-group-text">
            <i class="fa fa-calendar "></i>
        </div>
    </div>
{/strip}
