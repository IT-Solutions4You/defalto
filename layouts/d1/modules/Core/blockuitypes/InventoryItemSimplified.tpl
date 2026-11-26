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
    <div class="px-3 pb-3">
        <div class="table-responsive">
            <table class="table table-borderless table-hover align-middle mb-0">
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
                            <td colspan="{$SIMPLIFIED_COLUMNS}" class="fw-bold">{$ROW['item_text']|escape}</td>
                        </tr>
                    {else}
                        <tr{if empty($ROW['description'])} class="border-bottom"{/if}>
                            <td>
                                {* The name in bold and an icon which opens the product or service in a new window, like in the standard block *}
                                <strong>{$ROW['item_text']|escape}</strong>
                                {if !empty($ROW['productid']) && !empty($ROW['module'])}
                                    <small><a class="text-primary ms-1" href="index.php?module={$ROW['module']}&view=Detail&record={$ROW['productid']}" target="_blank" title="{vtranslate($ROW['module'], $ROW['module'])|escape}"><i class="fa fa-external-link text-secondary"></i></a></small>
                                {/if}
                            </td>
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
                {if count($SIMPLIFIED_ITEMS)}
                    <tfoot>
                    {* The thicker line which ends the table and the row with the sum of the Total column, the same as in the standard block *}
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS}" class="p-0 border-0" style="height: 2px; line-height: 0; background-color: var(--bs-border-color-translucent);"></td>
                    </tr>
                    <tr class="border-bottom bg-light-subtle">
                        <td class="fw-semibold">{vtranslate('Total', 'InventoryItem')}</td>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 2}"></td>
                        <td class="text-end fw-semibold">{if empty($SIMPLIFIED_TAXES)}{$PRICE_WITHOUT_VAT_DISPLAY}{else}{$PRICE_TOTAL_DISPLAY}{/if}</td>
                    </tr>
                    </tfoot>
                {/if}
            </table>
        </div>
        {if count($SIMPLIFIED_ITEMS)}
            {* Totals of the document below the table on the right, the same rows as in the standard block *}
            <div class="d-flex justify-content-end mt-3">
                <div class="flex-shrink-0 w-100" style="max-width: 390px;">
                    <table class="table table-borderless m-0 simplifiedItemsTotals">
                        <tbody>
                        <tr>
                            <td class="text-end fw-bold">{vtranslate('Total', 'InventoryItem')}</td>
                            <td class="text-end fw-bold">{$PRICE_WITHOUT_VAT_DISPLAY}</td>
                        </tr>
                        {if !empty($SIMPLIFIED_TAXES)}
                            <tr>
                                <td class="text-end fw-bold">{vtranslate('VAT', 'InventoryItem')}</td>
                                <td class="text-end fw-bold">{$VAT_DISPLAY}</td>
                            </tr>
                            <tr>
                                <td class="text-end fw-bold">{vtranslate('Total with VAT', 'InventoryItem')}</td>
                                <td class="text-end fw-bold">{$PRICE_TOTAL_DISPLAY}</td>
                            </tr>
                        {/if}
                        <tr>
                            <td class="text-end fw-bold">{vtranslate('Adjustment', 'InventoryItem')}</td>
                            <td class="text-end fw-bold">{$ADJUSTMENT_DISPLAY}</td>
                        </tr>
                        <tr>
                            <td class="text-end fw-bold">{vtranslate('Grand Total', 'InventoryItem')}</td>
                            <td class="text-end fw-bold">{$GRAND_TOTAL_DISPLAY}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}
    </div>
</div>
