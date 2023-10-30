{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is: vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=$FIELD_MODEL->getFieldInfo()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{if (!$FIELD_NAME)}
    {assign var="FIELD_NAME" value=$FIELD_MODEL->getFieldName()}
{/if}
{assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
{assign var=SYMBOL_PLACEMENT value=$CURRENT_USER_MODEL->get('currency_symbol_placement')}
{if is_object($RECORD)}
    {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($RECORD->fetchCurrencyId())}
    {assign var=CURRENCY_SYMBOL value=$CURRENCY_INFO['symbol']}
{else}
    {assign var=CURRENCY_SYMBOL value=$USER_MODEL->get('currency_symbol')}
{/if}
{if $FIELD_MODEL->get('uitype') eq '71'}
<div class="input-group inputElement">
    {if $SYMBOL_PLACEMENT eq '$1.0'}<span class="input-group-addon input-group-addon-right">{$CURRENCY_SYMBOL}</span>{/if}
	<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" class="inputElement currencyField" name="{$FIELD_NAME}"
	value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), true)}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
    {if $FIELD_INFO["mandatory"] eq true} data-rule-required = "true" {/if} data-rule-currency='true'
    {if php7_count($FIELD_INFO['validator'])}
        data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
    {/if}
    title="{if $SYMBOL_PLACEMENT eq '$1.0'}{$USER_MODEL->get('currency_symbol')}&nbsp;{/if}{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}{if $SYMBOL_PLACEMENT neq '$1.0'}&nbsp;{$USER_MODEL->get('currency_symbol')}{/if}"
    />
    {if $SYMBOL_PLACEMENT neq '$1.0'}<span class="input-group-addon input-group-addon-left">{$CURRENCY_SYMBOL}</span>{/if}
</div>
{else if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_NAME eq 'unit_price')}
	<div class="input-group inputElement" style="float:none;">
        {if $SYMBOL_PLACEMENT eq '$1.0'}<span class="input-group-addon input-group-addon-right" id="baseCurrencySymbol">{$BASE_CURRENCY_SYMBOL}</span>{/if}
        <input id="{$MODULE}-editview-fieldname-{$FIELD_NAME}"  type="text" class="inputElement unitPrice currencyField" name="{$FIELD_NAME}"
            value="{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
            data-decimal-separator='{$USER_MODEL->get('currency_decimal_separator')}' data-group-separator='{$USER_MODEL->get('currency_grouping_separator')}' data-number-of-decimal-places='{$USER_MODEL->get('no_of_currency_decimals')}'
            {if $FIELD_INFO["mandatory"] eq true} data-rule-required = "true" {/if} data-rule-currency='true'
            {if php7_count($FIELD_INFO['validator'])}
                data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
            {/if}
        />
        {if $SYMBOL_PLACEMENT neq '$1.0'}<span class="input-group-addon input-group-addon-right" id="baseCurrencySymbol">{$BASE_CURRENCY_SYMBOL}</span>{/if}
          <input type="hidden" name="base_currency" value="{$BASE_CURRENCY_NAME}">
          <input type="hidden" name="cur_{$BASE_CURRENCY_ID}_check" value="on">
          <input type="hidden" id="requstedUnitPrice" name="{$BASE_CURRENCY_NAME}" value="">
	</div>
    {if $smarty.request.view eq 'Edit'}
    <div class="clearfix">
        <a id="moreCurrencies" class="span cursorPointer">{vtranslate('LBL_MORE_CURRENCIES', $MODULE)}>></a>
        <span id="moreCurrenciesContainer" class="hide"></span>
    </div>
    {/if}
{else}
<div class="input-group">
    {if $SYMBOL_PLACEMENT eq '$1.0'}<span class="input-group-addon" id="basic-addon1">{$USER_MODEL->get('currency_symbol')}</span>{/if}
    <input type="text" class="input-lg currencyField" name="{$FIELD_NAME}"
        value="{$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'))}" {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}
        {if $FIELD_INFO["mandatory"] eq true} data-rule-required="true" {/if} data-rule-currency='true'
        {if php7_count($FIELD_INFO['validator'])}
            data-specific-rules='{ZEND_JSON::encode($FIELD_INFO["validator"])}'
        {/if}
          />
    {if $SYMBOL_PLACEMENT neq '$1.0'}<span class="input-group-addon" id="basic-addon1">{$USER_MODEL->get('currency_symbol')}</span>{/if}
</div>
{/if}
{/strip}
