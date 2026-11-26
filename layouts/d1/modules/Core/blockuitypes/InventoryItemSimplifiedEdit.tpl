{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* The fields of the block (tax region, totals...) stay in the form as hidden inputs, only the items, the currency, the tax region and the price book are edited here *}
<div style="display: none;">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {if $FIELD_NAME neq 'currency_id' && $FIELD_NAME neq 'region_id' && $FIELD_NAME neq 'pricebookid'}
            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
        {/if}
    {/foreach}
</div>
<div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom simplifiedItemsBlock" data-block="{$BLOCK_LABEL}" data-select-message="{vtranslate('LBL_SELECT_ITEM_FROM_LIST', 'InventoryItem')|escape}">
    <h4 class="fieldBlockHeader fw-bold py-3 px-4 m-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
    <div class="px-4 pb-3">
        <input type="hidden" name="simplifiedItemsPosted" value="1">
        {assign var=CURRENCY_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['currency_id']}
        {assign var=REGION_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['region_id']}
        {assign var=PRICEBOOK_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['pricebookid']}
        <div class="row g-3 mb-3 simplifiedItemsSettings">
            {if $CURRENCY_FIELD}
                {assign var=CURRENCY_VALUE value=$CURRENCY_FIELD->get('fieldvalue')}
                {assign var=CURRENCY_INFO value=$CURRENCY_FIELD->getFieldInfo()}
                <div class="col-sm-4">
                    <label class="form-label fw-bold" for="simplifiedCurrency">{vtranslate($CURRENCY_FIELD->get('label'), $MODULE)}</label>
                    <select class="form-select inputElement select2" id="simplifiedCurrency" name="currency_id" data-original="{$CURRENCY_VALUE}">
                        {foreach item=CURRENCY_LABEL key=CURRENCY_ID from=$CURRENCY_INFO['currencyList']}
                            <option value="{$CURRENCY_ID}" {if $CURRENCY_ID eq $CURRENCY_VALUE}selected{/if}>{$CURRENCY_LABEL|escape}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            {if $REGION_FIELD}
                {assign var=REGION_VALUE value=$REGION_FIELD->get('fieldvalue')}
                {assign var=REGION_INFO value=$REGION_FIELD->getFieldInfo()}
                <div class="col-sm-4">
                    <label class="form-label fw-bold" for="simplifiedRegion">{vtranslate($REGION_FIELD->get('label'), $MODULE)}</label>
                    <select class="form-select inputElement select2" id="simplifiedRegion" name="region_id" data-original="{$REGION_VALUE}">
                        <option value="0">{vtranslate('LBL_DEFAULT', 'Core')}</option>
                        {foreach item=REGION_LABEL key=REGION_ID from=$REGION_INFO['editablepicklistvalues']}
                            <option value="{$REGION_ID}" {if $REGION_ID eq $REGION_VALUE}selected{/if}>{$REGION_LABEL|escape}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
            {if $PRICEBOOK_FIELD}
                {assign var=PRICEBOOK_VALUE value=$PRICEBOOK_FIELD->get('fieldvalue')}
                <div class="col-sm-4">
                    <label class="form-label fw-bold" for="simplifiedPriceBook">{vtranslate($PRICEBOOK_FIELD->get('label'), $MODULE)}</label>
                    {* The price books of the currency of the document are offered, the list is filtered when the currency changes *}
                    <select class="form-select inputElement select2" id="simplifiedPriceBook" name="pricebookid" data-original="{$PRICEBOOK_VALUE}">
                        <option value="0">{vtranslate('LBL_NONE', 'InventoryItem')}</option>
                        {foreach item=PRICEBOOK key=PRICEBOOK_ID from=$SIMPLIFIED_PRICEBOOKS}
                            <option value="{$PRICEBOOK_ID}" data-currency="{$PRICEBOOK['currency_id']}" {if $PRICEBOOK_ID eq $PRICEBOOK_VALUE}selected{/if}>{$PRICEBOOK['name']|escape}</option>
                        {/foreach}
                    </select>
                </div>
            {/if}
        </div>
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
                {foreach key=ROW_INDEX item=ROW from=$SIMPLIFIED_ITEMS}
                    {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX=$ROW_INDEX ROW=$ROW}
                {/foreach}
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
        {foreach from=$ITEM_MODULES item=ITEM_MODULE_NAME}
            <template id="simplifiedItemRowTemplate{$ITEM_MODULE_NAME}">
                {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX='__INDEX__' ROW=['id' => 0, 'item_text' => '', 'productid' => 0, 'module' => $ITEM_MODULE_NAME, 'is_text' => false, 'quantity' => '1', 'unit' => '', 'price' => '', 'tax' => '', 'taxid' => 0, 'total' => '0.00', 'description' => '', 'purchase_cost' => '', 'pricebookid' => 0]}
            </template>
        {/foreach}
        <input type="hidden" id="simplifiedItemsNextIndex" value="{count($SIMPLIFIED_ITEMS)}">
    </div>
</div>
