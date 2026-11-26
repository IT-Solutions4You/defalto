{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* A text row is the full width of the table, it only has the text. The other rows are the product or service with quantity, unit, price, tax and total
   and below them a row with the description. Every item is one tbody, so that both of its rows are added and removed together *}
{assign var=IS_TEXT_ROW value=empty($ROW['module'])}
{if !$IS_TEXT_ROW && !empty($SIMPLIFIED_UNITS_BY_MODULE[$ROW['module']])}
    {assign var=ROW_UNITS value=$SIMPLIFIED_UNITS_BY_MODULE[$ROW['module']]}
{else}
    {assign var=ROW_UNITS value=$SIMPLIFIED_UNITS}
{/if}
<tbody class="simplifiedItemRow{if $IS_TEXT_ROW} simplifiedTextRow{/if}" data-row-index="{$ROW_INDEX}" data-module="{$ROW['module']|default:''}" data-discount-type="{$ROW['discount_type']|default:''}" data-discount="{$ROW['discount']|default:0}" data-discount-amount="{$ROW['discount_amount']|default:0}">
<tr class="simplifiedItemMainRow">
    <td{if $IS_TEXT_ROW} colspan="{$SIMPLIFIED_COLUMNS}"{/if}>
        <input type="hidden" name="simplified_items[{$ROW_INDEX}][id]" value="{$ROW['id']}">
        <input type="hidden" class="simplifiedItemProductId" name="simplified_items[{$ROW_INDEX}][productid]" value="{$ROW['productid']|default:0}">
        <input type="hidden" class="simplifiedItemDeleted" name="simplified_items[{$ROW_INDEX}][deleted]" value="0">
        {if !$IS_TEXT_ROW}
            {* Not visible, but saved with the item: the purchase cost of the product (the margin is calculated from it) and the price book the price comes from *}
            <input type="hidden" class="simplifiedItemPurchaseCost" name="simplified_items[{$ROW_INDEX}][purchase_cost]" value="{$ROW['purchase_cost']|default:''}">
            <input type="hidden" class="simplifiedItemPriceBookId" name="simplified_items[{$ROW_INDEX}][pricebookid]" value="{$ROW['pricebookid']|default:0}">
        {/if}
        <input type="text" class="form-control simplifiedItemText" placeholder="{if !$IS_TEXT_ROW}{vtranslate($ROW['module'], $ROW['module'])}{else}{vtranslate('TEXT', 'InventoryItem')}{/if}" name="simplified_items[{$ROW_INDEX}][item_text]" value="{$ROW['item_text']|escape}" data-rule-required="true" autocomplete="off">
    </td>
    {if !$IS_TEXT_ROW}
        <td>
            <input type="text" inputmode="decimal" class="form-control text-end simplifiedItemQuantity" name="simplified_items[{$ROW_INDEX}][quantity]" value="{$ROW['quantity']}" autocomplete="off">
        </td>
        <td>
            <select class="form-select inputElement select2 simplifiedItemUnit" name="simplified_items[{$ROW_INDEX}][unit]">
                <option value=""></option>
                {assign var=UNIT_FOUND value=false}
                {foreach key=UNIT_VALUE item=UNIT_LABEL from=$ROW_UNITS}
                    <option value="{$UNIT_VALUE|escape}" {if $UNIT_VALUE eq $ROW['unit']}selected{assign var=UNIT_FOUND value=true}{/if}>{$UNIT_LABEL|escape}</option>
                {/foreach}
                {if $ROW['unit'] neq '' && !$UNIT_FOUND}
                    <option value="{$ROW['unit']|escape}" selected>{$ROW['unit']|escape}</option>
                {/if}
            </select>
        </td>
        <td>
            <input type="text" inputmode="decimal" class="form-control text-end simplifiedItemPrice" name="simplified_items[{$ROW_INDEX}][price]" value="{$ROW['price']}" autocomplete="off">
        </td>
        {if !empty($SIMPLIFIED_TAXES)}
            <td>
                {* The options are the taxes of the product, filled when it is selected. The value is the percentage, the same as in the standard block *}
                <input type="hidden" class="simplifiedItemTaxId" name="simplified_items[{$ROW_INDEX}][taxid]" value="{$ROW['taxid']|default:0}">
                <select class="form-select inputElement select2 text-end simplifiedItemTax" name="simplified_items[{$ROW_INDEX}][tax]">
                    <option value="0" data-taxid="0">{vtranslate('No Tax', 'InventoryItem')}</option>
                    {assign var=TAX_FOUND value=false}
                    {foreach item=TAX from=$SIMPLIFIED_TAXES}
                        {if $TAX['taxid'] eq $ROW['taxid'] && (float)$TAX['percentage'] eq (float)$ROW['tax']}{assign var=TAX_FOUND value=true}{/if}
                        <option value="{$TAX['percentage']}" data-taxid="{$TAX['taxid']}" {if $TAX['taxid'] eq $ROW['taxid'] && (float)$TAX['percentage'] eq (float)$ROW['tax']}selected{/if}>{$TAX['tax_label']|escape} ({$TAX['percentage']}%)</option>
                    {/foreach}
                    {if (float)$ROW['tax'] > 0 && !$TAX_FOUND}
                        {* Saved value of a tax which has changed since or which depends on the region *}
                        <option value="{$ROW['tax']}" data-taxid="{$ROW['taxid']|default:0}" selected>{$ROW['tax']}%</option>
                    {/if}
                </select>
            </td>
        {/if}
        <td class="text-end">
            {* Total of the row with the tax *}
            <span class="simplifiedItemTotal" data-total="{$ROW['total']|default:0}">{$ROW['total']}</span>
        </td>
    {/if}
    <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-secondary removeSimplifiedItem" title="{vtranslate('LBL_REMOVE', 'Vtiger')}"><i class="fa fa-trash"></i></button>
    </td>
</tr>
{if !$IS_TEXT_ROW}
    <tr class="simplifiedItemDescriptionRow">
        <td colspan="{$SIMPLIFIED_COLUMNS}">
            {* One line, 4 lines while the user writes in it *}
            <textarea class="form-control simplifiedItemDescription" rows="1" placeholder="{vtranslate('Description', 'InventoryItem')}" name="simplified_items[{$ROW_INDEX}][description]">{$ROW['description']|default:''|escape}</textarea>
        </td>
    </tr>
{/if}
</tbody>
