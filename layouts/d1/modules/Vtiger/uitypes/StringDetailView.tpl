{**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*}
{* TODO: Review the order of parameters - good to eliminate $RECORD->getId, $RECORD should be used *}
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
        <span class="picklist-color py-1 px-2 rounded" {if !empty($PICKLIST_COLOR)} style="background-color: {$PICKLIST_COLOR}; color: {Settings_Picklist_Module_Model::getTextColor($PICKLIST_COLOR)};" {/if}> {trim($MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX])} </span>
        {if $MULTI_PICKLIST_VALUES[$MULTI_PICKLIST_INDEX+1] neq ''},{/if}
    {/foreach}
{elseif $FIELD_MODEL->getFieldDataType() eq 'currency'}
    {assign var=CURRENT_USER_MODEL value=Users_Record_Model::getCurrentUserModel()}
    {assign var=SYMBOL_PLACEMENT value=$CURRENT_USER_MODEL->get('currency_symbol_placement')}
    {assign var=CURRENCY_ID value=$RECORD->getCurrencyId()}
    {assign var=CURRENCY_INFO value=getCurrencySymbolandCRate($CURRENCY_ID)}
    {assign var=CURRENCY_SYMBOL value=$CURRENCY_INFO['symbol']}
    {if $SYMBOL_PLACEMENT eq '$1.0'}
        <span class="me-1">{$CURRENCY_SYMBOL}</span>
        <span class="currencyValue">{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}</span>
    {else}
        <span class="currencyValue">{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}</span>
        <span class="ms-1">{$CURRENCY_SYMBOL}</span>
    {/if}
{elseif  $FIELD_MODEL->get('name') eq 'signature'}
	{decode_html($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD))}
{else}
    {$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
{/if}
