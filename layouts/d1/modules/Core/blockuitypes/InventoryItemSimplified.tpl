{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* Simplified product block: read-only, the items are changed in the edit view *}
<div class="mt-3 bg-body rounded block block_{$BLOCK_LABEL_KEY} simplifiedItemsDetail" data-block="{$BLOCK_LABEL_KEY}" data-blockid="{$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}">
    <div class="p-3">
        <span class="fs-4 fw-bold text-truncate">{vtranslate($BLOCK_LABEL_KEY, $MODULE_NAME)}</span>
    </div>
    <div class="px-3 pb-3 table-responsive">
        <table class="table table-borderless align-middle mb-0">
            <thead>
            <tr class="border-bottom">
                <th class="fw-bold w-50">{vtranslate('Item name', 'InventoryItem')}</th>
                <th class="fw-bold text-end">{vtranslate('Quantity', 'InventoryItem')}</th>
                <th class="fw-bold">{vtranslate('Unit', 'InventoryItem')}</th>
                <th class="fw-bold text-end">{vtranslate('Price', 'InventoryItem')}</th>
                {if !empty($SIMPLIFIED_TAXES)}
                    <th class="fw-bold text-end">{vtranslate('Tax', 'InventoryItem')}</th>
                {/if}
                <th class="fw-bold text-end">{vtranslate('Total', 'InventoryItem')}</th>
            </tr>
            </thead>
            <tbody>
            {foreach item=ROW from=$SIMPLIFIED_ITEMS}
                {if $ROW['is_text']}
                    <tr class="border-bottom">
                        <td colspan="{$SIMPLIFIED_COLUMNS}">{$ROW['item_text']|escape}</td>
                    </tr>
                {else}
                    <tr{if empty($ROW['description'])} class="border-bottom"{/if}>
                        <td>{$ROW['item_text']|escape}</td>
                        <td class="text-end">{$ROW['quantity']}</td>
                        <td>{$SIMPLIFIED_UNITS[$ROW['unit']]|default:$ROW['unit']|escape}</td>
                        <td class="text-end">{$ROW['price']}</td>
                        {if !empty($SIMPLIFIED_TAXES)}
                            <td class="text-end">{if (float)$ROW['tax'] > 0}{$ROW['tax']}%{/if}</td>
                        {/if}
                        <td class="text-end">{$ROW['total']}</td>
                    </tr>
                    {if !empty($ROW['description'])}
                        <tr class="border-bottom">
                            <td colspan="{$SIMPLIFIED_COLUMNS}" class="text-muted small pt-0">{$ROW['description']|escape|nl2br}</td>
                        </tr>
                    {/if}
                {/if}
            {foreachelse}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS}" class="text-center">{vtranslate('NO_ITEMS_FOUND', 'InventoryItem')}</td>
                </tr>
            {/foreach}
            </tbody>
            <tfoot>
            {if empty($SIMPLIFIED_TAXES)}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Total', 'InventoryItem')}</td>
                    <td class="text-end fw-bold">{$SIMPLIFIED_TOTAL}</td>
                </tr>
            {else}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Price After Overall Discount', 'InventoryItem')}</td>
                    <td class="text-end fw-bold">{$PRICE_WITHOUT_VAT_DISPLAY}</td>
                </tr>
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('VAT', 'Core')}</td>
                    <td class="text-end fw-bold">{$VAT_DISPLAY}</td>
                </tr>
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Price Total', 'InventoryItem')}</td>
                    <td class="text-end fw-bold">{$PRICE_TOTAL_DISPLAY}</td>
                </tr>
            {/if}
            </tfoot>
        </table>
    </div>
</div>
