{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<table class="table table-borderless table-hover">
    {if count($ITEMS)}
    <thead>
    <tr class="border-bottom">
        <td class="font-bold">{vtranslate('Product Name', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{vtranslate('Quantity', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{vtranslate('Price After Overall Discount', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{vtranslate('VAT', 'Core')}</td>
        <td class="font-bold textAlignRight">{vtranslate('Price Total', 'InventoryItem')}</td>
    </tr>
    </thead>
    {/if}
    <tbody>
    {foreach from=$ITEMS item=ITEM}
        <tr class="border-bottom">
            {if $ITEM.entityType eq 'Text'}
            <td class="font-bold bg-secondary bg-opacity-10" colspan="5">{$ITEM.item_text}</td>
            {else}
            <td class="font-bold">{$ITEM.item_text}</td>
            <td class="textAlignRight">{$ITEM.quantity_display}</td>
            <td class="textAlignRight">{$ITEM.price_after_overall_discount_display}</td>
            <td class="textAlignRight">
                {$ITEM.tax_amount_display}
                {if $ITEM.tax ne '' && $ITEM.tax ne 0}
                    ({$ITEM.tax}%)
                {/if}
            </td>
            <td class="textAlignRight">{$ITEM.price_total_display}</td>
            {/if}
        </tr>
    {foreachelse}
        <tr>
            <td class="font-bold textAlignCenter" colspan="5">{vtranslate('NO_ITEMS_FOUND', 'InventoryItem')}</td>
        </tr>
    {/foreach}
    </tbody>
    <tfoot>
    <tr>
        <td></td>
        <td class="font-bold textAlignRight">{vtranslate('Total')}</td>
        <td class="font-bold textAlignRight">{$PRICE_WITHOUT_VAT_DISPLAY}</td>
        <td class="font-bold textAlignRight">{$VAT_DISPLAY}</td>
        <td class="font-bold textAlignRight">{$PRICE_TOTAL_DISPLAY}</td>
    </tr>
    </tfoot>
</table>
{if count($ITEMS)}
<table class="table table-borderless">
    <tbody>
    {if $ADJUSTMENT != 0 && $ADJUSTMENT != ''}
    <tr>
        <td class="font-bold textAlignRight" style="width:100%">{vtranslate('Adjustment', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight" style="white-space:nowrap">{$ADJUSTMENT_DISPLAY}</td>
    </tr>
    {/if}
    <tr>
        <td class="font-bold textAlignRight" style="width:100%">{vtranslate('Grand Total', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight" style="white-space:nowrap">{$GRAND_TOTAL_DISPLAY}</td>
    </tr>
    <tr>
        <td class="font-bold textAlignRight" style="width:100%">{vtranslate('LBL_MARGIN', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight" style="white-space:nowrap">{$MARGIN_COMBINED}</td>
    </tr>
    </tbody>
</table>
{/if}
