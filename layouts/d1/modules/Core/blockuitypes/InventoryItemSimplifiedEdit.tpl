{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* The fields of the block (currency, tax region, totals...) stay in the form as hidden inputs, only the items are edited here *}
<div style="display: none;">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
    {/foreach}
</div>
<div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom simplifiedItemsBlock" data-block="{$BLOCK_LABEL}" data-select-message="{vtranslate('LBL_SELECT_ITEM_FROM_LIST', 'InventoryItem')|escape}">
    <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
    <div class="px-4 pb-3">
        <input type="hidden" name="simplifiedItemsPosted" value="1">
        <div class="table-responsive">
            <table class="table table-borderless align-middle mb-2" id="simplifiedItemsTable">
                <thead>
                <tr class="border-bottom">
                    <th class="fw-bold w-50">{vtranslate('Item name', 'InventoryItem')}</th>
                    <th class="fw-bold text-end" style="min-width: 110px;">{vtranslate('Quantity', 'InventoryItem')}</th>
                    <th class="fw-bold" style="min-width: 130px;">{vtranslate('Unit', 'InventoryItem')}</th>
                    <th class="fw-bold text-end" style="min-width: 130px;">{vtranslate('Price', 'InventoryItem')}</th>
                    {if !empty($SIMPLIFIED_TAXES)}
                        <th class="fw-bold text-end" style="min-width: 150px;">{vtranslate('Tax', 'InventoryItem')}</th>
                    {/if}
                    <th class="fw-bold text-end" style="min-width: 130px;">{vtranslate('Total', 'InventoryItem')}</th>
                    <th style="width: 48px;"></th>
                </tr>
                </thead>
                <tbody>
                {foreach key=ROW_INDEX item=ROW from=$SIMPLIFIED_ITEMS}
                    {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX=$ROW_INDEX ROW=$ROW}
                {/foreach}
                </tbody>
                <tfoot>
                {if empty($SIMPLIFIED_TAXES)}
                    <tr class="border-top">
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Total', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsGrandTotal">0</td>
                        <td></td>
                    </tr>
                {else}
                    <tr class="border-top">
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Price After Overall Discount', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsNetTotal">0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('VAT', 'Core')}</td>
                        <td class="text-end fw-bold simplifiedItemsTaxTotal">0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Price Total', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsGrandTotal">0</td>
                        <td></td>
                    </tr>
                {/if}
                </tfoot>
            </table>
        </div>
        {* The same buttons and the same rule as the standard product block, but they add a row instead of opening a popup *}
        <div class="btn-toolbar simplifiedItemAddButtons{if isset($ITEMS_EDITABLE) && !$ITEMS_EDITABLE} d-none{/if}">
            <div class="recordLabel verticalAlignMiddle me-2 font-bold">{vtranslate('Add', 'InventoryItem')}: </div>
            <button type="button" class="btn btn-primary active mb-2 me-2 addSimplifiedItem" id="addSimplifiedText" data-modulename="" data-label="{vtranslate('TEXT', 'InventoryItem')}">
                <i class="fa fa-i-cursor me-2"></i>
                <span>{vtranslate('TEXT', 'InventoryItem')}</span>
            </button>
            {foreach from=$ITEM_MODULES item=ITEM_MODULE_NAME}
                <button type="button" class="btn btn-primary active mb-2 me-2 addSimplifiedItem" id="addSimplified{$ITEM_MODULE_NAME}" data-modulename="{$ITEM_MODULE_NAME}" data-label="{vtranslate($ITEM_MODULE_NAME, $ITEM_MODULE_NAME)}">
                    {Vtiger_Module_Model::getModuleIconPath($ITEM_MODULE_NAME)}&nbsp;&nbsp;{vtranslate($ITEM_MODULE_NAME, $ITEM_MODULE_NAME)}
                </button>
            {/foreach}
        </div>
        {* Text row: only the text. Product or service row: the module is set when the row is added *}
        <template id="simplifiedTextRowTemplate">
            {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX='__INDEX__' ROW=['id' => 0, 'item_text' => '', 'productid' => 0, 'module' => '', 'is_text' => true, 'quantity' => '', 'unit' => '', 'price' => '', 'tax' => '', 'taxid' => 0, 'total' => '']}
        </template>
        <template id="simplifiedItemRowTemplate">
            {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX='__INDEX__' ROW=['id' => 0, 'item_text' => '', 'productid' => 0, 'module' => 'Products', 'is_text' => false, 'quantity' => '1', 'unit' => '', 'price' => '', 'tax' => '', 'taxid' => 0, 'total' => '0.00']}
        </template>
        <input type="hidden" id="simplifiedItemsNextIndex" value="{count($SIMPLIFIED_ITEMS)}">
    </div>
</div>
