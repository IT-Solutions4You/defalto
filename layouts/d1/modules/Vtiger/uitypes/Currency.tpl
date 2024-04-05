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
    <div class="Vtiger_Currency_UIType">
        {if $RECORD}
            {assign var=CURRENCY_ID value=$RECORD->getCurrencyId()}
        {else}
            {assign var=CURRENCY_ID value=Users_Record_Model::getCurrentUserModel()->get('currency_id')}
        {/if}
        {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($CURRENCY_ID)}
        {assign var=CURRENCY_SYMBOL value=$CURRENCY_INFO['symbol']}
        {if $FIELD_MODEL->get('uitype') eq '71'}
            <div class="input-group inputElement">
                <span class="input-group-text currencyUITypeSymbol">{$CURRENCY_SYMBOL}</span>
                <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="form-control inputElement currencyField replaceCommaWithDot" name="{$FIELD_NAME}"
                       value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} data-rule-currency='true'
                        {if php7_count($FIELD_INFO['validator'])}
                            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                        {/if}
                />
            </div>
        {elseif ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_NAME eq 'unit_price')}
            <div class="input-group inputElement">
                <span class="input-group-text currencyUITypeSymbol" id="baseCurrencySymbol">{$CURRENCY_SYMBOL}</span>
                <input id="{$MODULE}-editview-fieldname-{$FIELD_NAME}" type="text" class="form-control inputElement unitPrice currencyField replaceCommaWithDot" name="{$FIELD_NAME}"
                       value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                       data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'
                        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} data-rule-currency='true'
                        {if php7_count($FIELD_INFO['validator'])}
                            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                        {/if}
                />
                <input type="hidden" name="base_currency" value="{$BASE_CURRENCY_NAME}">
                <input type="hidden" name="cur_{$BASE_CURRENCY_ID}_check" value="on">
                <input type="hidden" id="requstedUnitPrice" name="{$BASE_CURRENCY_NAME}" value="">
            </div>
            {if $REQUEST_INSTANCE.view eq 'Edit'}
                <div class="clearfix">
                    <a id="moreCurrencies" class="span cursorPointer">{vtranslate('LBL_MORE_CURRENCIES', $MODULE)}>></a>
                    <span id="moreCurrenciesContainer" class="hide"></span>
                </div>
            {/if}
        {else}
            <div class="input-group">
                <span class="input-group-text currencyUITypeSymbol" id="basic-addon1">{$CURRENCY_SYMBOL}</span>
                <input type="text" class="form-control input-lg currencyField replaceCommaWithDot" name="{$FIELD_NAME}"
                       value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}
                       {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} data-rule-currency='true'
                        {if php7_count($FIELD_INFO['validator'])}
                            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
                        {/if}
                />
            </div>
        {/if}
    </div>
    {* TODO - UI Type 72 needs to be handled. Multi-currency support also needs to be handled *}
{/strip}
