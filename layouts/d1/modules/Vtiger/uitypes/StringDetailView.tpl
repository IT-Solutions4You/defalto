{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* TODO: Review the order of parameters - good to eliminate $RECORD->getId, $RECORD should be used *}
{strip}
{if $FIELD_MODEL->getFieldDataType() eq 'picklist' and $MODULE neq 'Users'}
    {assign var=PICKLIST_COLOR value=Settings_Picklist_Module_Model::getPicklistColorByValue($FIELD_MODEL->getName(), $FIELD_MODEL->get('fieldvalue'))}
    <span {if !empty($PICKLIST_COLOR)} class="picklist-color py-1 px-2 rounded" style="background-color: {$PICKLIST_COLOR}; line-height:15px; color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {/if}>
        {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
    </span>
{elseif $FIELD_MODEL->getFieldDataType() eq 'multipicklist' and $MODULE neq 'Users'}
    {assign var=PICKLIST_DISPLAY_VALUE value=$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
    {assign var=MULTI_RAW_PICKLIST_VALUES value=explode('|##|',$FIELD_MODEL->get('fieldvalue'))}
    {assign var=MULTI_PICKLIST_VALUES value=explode(',',$PICKLIST_DISPLAY_VALUE)}
    {foreach item=MULTI_PICKLIST_VALUE key=MULTI_PICKLIST_INDEX from=$MULTI_RAW_PICKLIST_VALUES}
        {assign var=PICKLIST_COLOR value=Settings_Picklist_Module_Model::getPicklistColorByValue($FIELD_MODEL->getName(), trim($MULTI_PICKLIST_VALUE))}
        <div class="picklist-color d-inline-block me-1 mb-1 py-1 px-2 rounded bg-body-secondary" {if !empty($PICKLIST_COLOR)} style="background-color: {$PICKLIST_COLOR}; color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {/if}>{trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])}</div>
    {/foreach}
{elseif $FIELD_MODEL->getFieldDataType() eq 'currency'}
    {assign var=CURRENCY_INFO value=Vtiger_Functions::getCurrencySymbolandRate($RECORD->getCurrencyId())}
    <span class="currencyValue" data-currency-symbol="{$CURRENCY_INFO['symbol']}">
        {CurrencyField::appendCurrencySymbol($RECORD->getDisplayValue($FIELD_MODEL->getName()), $CURRENCY_INFO['symbol'])}
    </span>
{elseif $FIELD_MODEL->get('name') eq 'signature'}
    {decode_html($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD))}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
{/strip}