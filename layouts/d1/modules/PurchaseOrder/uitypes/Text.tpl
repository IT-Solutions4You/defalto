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
    {assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
    {if $FIELD_MODEL->get('uitype') eq '19' || $FIELD_MODEL->get('uitype') eq '20'}
        <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="form-control inputElement textAreaElement {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" {if $FIELD_NAME eq "notecontent"}id="{$FIELD_NAME}"{/if} {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
            {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
            {if php7_count($FIELD_INFO['validator'])}
                data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
            {/if}
        >{$FIELD_MODEL->get('fieldvalue')}</textarea>
    {else}
        {if $REQUEST_INSTANCE.view neq 'Detail'}
            {assign var=blockLabel value=$RECORD_STRUCTURE['LBL_PO_INFORMATION']}
            {assign var=fieldModel value=$blockLabel['accountid']}
            {$pickList = ['' => 'LBL_SELECT_ADDRESS_OPTION', 'company'=> 'LBL_COMPANY_ADDRESS',
            'account' => 'LBL_ACCOUNT_ADDRESS', 'vendor'=> 'LBL_VENDOR_ADDRESS', 'contact' => 'LBL_CONTACT_ADDRESS'
            ]}
            {if $FIELD_NAME eq "bill_street"}
                {append var='pickList' value='Shipping Address' index='bill'}
                <div class="mb-3">
                    <select id="BillingAddress" name="BillingAddress" data-target="bill" class="select2">
                        {foreach from=$pickList key=keys item=value}
                            {if $keys eq 'vendor' or $keys eq 'contact' or $keys eq 'account'}
                                {$modl = ucfirst($keys|cat:"s")}
                                {if  vtlib_isModuleActive($modl)}
                                    <option value="{$keys}">{vtranslate($value,$MODULE)}</option>
                                {/if}
                            {else}
                                <option value="{$keys}">{vtranslate($value,$MODULE)}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            {elseif $FIELD_NAME eq "ship_street"}
                {append var='pickList' value='LBL_BILLING_ADDRESS' index='ship'}
                <div class="mb-3">
                    <select id="ShippingAddress" name="ShippingAddress" data-target="ship" class="select2">
                        {foreach from=$pickList key=keys item=value}
                            {if $keys eq 'vendor' or $keys eq 'contact' or $keys eq 'account'}
                                {$modl = ucfirst($keys|cat:"s")}
                                {if  vtlib_isModuleActive($modl)}
                                    <option value="{$keys}">{vtranslate($value,$MODULE)}</option>
                                {/if}
                            {else}
                                <option value="{$keys}">{vtranslate($value,$MODULE)}</option>
                            {/if}
                        {/foreach}
                    </select>
                </div>
            {/if}
        {/if}
        <textarea rows="3" id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" class="inputElement form-control {if $FIELD_MODEL->isNameField()}nameField{/if}" name="{$FIELD_MODEL->getFieldName()}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
            {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if}
            {if php7_count($FIELD_INFO['validator'])}
                data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
            {/if}
        >{$FIELD_MODEL->get('fieldvalue')}</textarea>
    {/if}
{/strip}