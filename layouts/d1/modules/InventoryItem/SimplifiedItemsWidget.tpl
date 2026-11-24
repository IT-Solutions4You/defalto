{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="inventoryItemSummaryItems">
    <table class="table table-borderless table-hover mb-0">
        {if count($SIMPLIFIED_ITEMS)}
            <thead>
            <tr class="border-bottom">
                <td class="font-bold">{vtranslate('Item name', 'InventoryItem')}</td>
                <td class="font-bold textAlignRight">{vtranslate('Quantity', 'InventoryItem')}</td>
                <td class="font-bold">{vtranslate('Unit', 'InventoryItem')}</td>
                <td class="font-bold textAlignRight">{vtranslate('Price', 'InventoryItem')}</td>
                {if !empty($SIMPLIFIED_TAXES)}
                    <td class="font-bold textAlignRight">{vtranslate('Tax', 'InventoryItem')}</td>
                {/if}
                <td class="font-bold textAlignRight">{vtranslate('Total', 'InventoryItem')}</td>
            </tr>
            </thead>
        {/if}
        <tbody>
        {foreach from=$SIMPLIFIED_ITEMS item=ROW}
            {if $ROW['is_text']}
                <tr class="border-bottom">
                    <td class="font-bold" colspan="{$SIMPLIFIED_COLUMNS}">{$ROW['item_text']|escape}</td>
                </tr>
            {else}
                <tr class="border-bottom">
                    <td class="font-bold">{$ROW['item_text']|escape}</td>
                    <td class="textAlignRight">{$ROW['quantity']}</td>
                    <td>{$SIMPLIFIED_UNITS[$ROW['unit']]|default:$ROW['unit']|escape}</td>
                    <td class="textAlignRight">{$ROW['price']}</td>
                    {if !empty($SIMPLIFIED_TAXES)}
                        <td class="textAlignRight">{if (float)$ROW['tax'] > 0}{$ROW['tax']}%{/if}</td>
                    {/if}
                    <td class="textAlignRight">{$ROW['total']}</td>
                </tr>
            {/if}
        {foreachelse}
            <tr>
                <td class="font-bold textAlignCenter" colspan="{$SIMPLIFIED_COLUMNS}">{vtranslate('NO_ITEMS_FOUND', 'InventoryItem')}</td>
            </tr>
        {/foreach}
        </tbody>
        {if count($SIMPLIFIED_ITEMS)}
            <tfoot>
            {if empty($SIMPLIFIED_TAXES)}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="font-bold textAlignRight">{vtranslate('Total')}</td>
                    <td class="font-bold textAlignRight">{$GRAND_TOTAL_DISPLAY}</td>
                </tr>
            {else}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="font-bold textAlignRight">{vtranslate('Price After Overall Discount', 'InventoryItem')}</td>
                    <td class="font-bold textAlignRight">{$PRICE_WITHOUT_VAT_DISPLAY}</td>
                </tr>
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="font-bold textAlignRight">{vtranslate('VAT', 'Core')}</td>
                    <td class="font-bold textAlignRight">{$VAT_DISPLAY}</td>
                </tr>
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="font-bold textAlignRight">{vtranslate('Price Total', 'InventoryItem')}</td>
                    <td class="font-bold textAlignRight">{$PRICE_TOTAL_DISPLAY}</td>
                </tr>
            {/if}
            </tfoot>
        {/if}
    </table>
</div>
