{**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 *}
<table class="table table-borderless table-hover">
    {if count($ITEMS)}
    <thead>
    <tr class="border-bottom">
        <td class="font-bold">{vtranslate('Product Name', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{vtranslate('Quantity', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{vtranslate('Price Total', 'InventoryItem')}</td>
    </tr>
    </thead>
    {/if}
    <tbody>
    {foreach from=$ITEMS item=ITEM}
        <tr class="border-bottom">
            {if $ITEM.entityType eq 'Text'}
            <td class="font-bold bg-secondary bg-opacity-10" colspan="3">{$ITEM.item_text}</td>
            {else}
            <td class="font-bold">{$ITEM.item_text}</td>
            <td class="textAlignRight">{$ITEM.quantity_display}</td>
            <td class="textAlignRight">{$ITEM.price_total_display}</td>
            {/if}
        </tr>
    {foreachelse}
        <tr>
            <td class="font-bold textAlignCenter" colspan="3">{vtranslate('NO_ITEMS_FOUND', 'InventoryItem')}</td>
        </tr>
    {/foreach}
    </tbody>
    {if count($ITEMS)}
    <tfoot>
    <tr>
        <td class="font-bold textAlignRight" colspan="2">{vtranslate('Total without VAT', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{$PRICE_WITHOUT_VAT_DISPLAY}</td>
    </tr>
    <tr>
        <td class="font-bold textAlignRight" colspan="2">{vtranslate('VAT', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{$VAT_DISPLAY}</td>
    </tr>
    {if $ADJUSTMENT != 0 && $ADJUSTMENT != ''}
    <tr>
        <td class="font-bold textAlignRight" colspan="2">{vtranslate('Total with VAT', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{$PRICE_TOTAL_DISPLAY}</td>
    </tr>
    <tr>
        <td class="font-bold textAlignRight" colspan="2">{vtranslate('Adjustment', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{$ADJUSTMENT_DISPLAY}</td>
    </tr>
    {/if}
    <tr>
        <td class="font-bold textAlignRight" colspan="2">{vtranslate('Grand Total', 'InventoryItem')}</td>
        <td class="font-bold textAlignRight">{$GRAND_TOTAL_DISPLAY}</td>
    </tr>
    </tfoot>
    {/if}
</table>