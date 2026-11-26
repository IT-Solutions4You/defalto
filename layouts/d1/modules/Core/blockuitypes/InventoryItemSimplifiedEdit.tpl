{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}

{* The fields of the block (tax region, totals...) stay in the form as hidden inputs, only the items, the currency, the tax region, the price book and the adjustment are edited here *}
<div style="display: none;">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']}
        {if $FIELD_NAME neq 'currency_id' && $FIELD_NAME neq 'region_id' && $FIELD_NAME neq 'pricebookid' && $FIELD_NAME neq 'adjustment'}
            {include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
        {/if}
    {/foreach}
</div>
<div id="{$BLOCK->getEditViewId()}" class="fieldBlockContainer mb-3 border-bottom simplifiedItemsBlock" data-block="{$BLOCK_LABEL}" data-select-message="{vtranslate('LBL_SELECT_ITEM_FROM_LIST', 'InventoryItem')|escape}">
    {assign var=CURRENCY_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['currency_id']}
    {assign var=REGION_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['region_id']}
    {assign var=PRICEBOOK_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['pricebookid']}
    {* The same buttons as the header of the standard block. The values are kept in the hidden selects (they are posted), the buttons only change them *}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-3 px-4">
        <h4 class="fieldBlockHeader fw-bold m-0 p-0">{vtranslate($BLOCK_LABEL, $MODULE)}</h4>
        <div class="d-flex flex-wrap justify-content-end gap-2 simplifiedItemsSettings">
            {if $PRICEBOOK_FIELD}
                {assign var=PRICEBOOK_VALUE value=$PRICEBOOK_FIELD->get('fieldvalue')}
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" tabindex="-1">{vtranslate('SINGLE_PriceBooks', 'PriceBooks')}</button>
                    <select class="d-none" id="simplifiedPriceBook" name="pricebookid" data-original="{$PRICEBOOK_VALUE}">
                        <option value="0">{vtranslate('LBL_NONE', 'InventoryItem')}</option>
                        {foreach item=PRICEBOOK key=PRICEBOOK_ID from=$SIMPLIFIED_PRICEBOOKS}
                            <option value="{$PRICEBOOK_ID}" data-currency="{$PRICEBOOK['currency_id']}" {if $PRICEBOOK_ID eq $PRICEBOOK_VALUE}selected{/if}>{$PRICEBOOK['name']|escape}</option>
                        {/foreach}
                    </select>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-bd-light dropdown-toggle btn-outline-secondary simplifiedSettingButton" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <ul class="dropdown-menu dropdown-menu-end simplifiedSettingMenu" data-select="#simplifiedPriceBook">
                            <li><a class="dropdown-item" data-value="0">{vtranslate('LBL_NONE', 'InventoryItem')}</a></li>
                            {foreach item=PRICEBOOK key=PRICEBOOK_ID from=$SIMPLIFIED_PRICEBOOKS}
                                <li><a class="dropdown-item" data-value="{$PRICEBOOK_ID}" data-currency="{$PRICEBOOK['currency_id']}">{$PRICEBOOK['name']|escape}</a></li>
                            {/foreach}
                        </ul>
                    </div>
                </div>
            {/if}
            {if $CURRENCY_FIELD}
                {assign var=CURRENCY_VALUE value=$CURRENCY_FIELD->get('fieldvalue')}
                {assign var=CURRENCY_INFO value=$CURRENCY_FIELD->getFieldInfo()}
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" tabindex="-1">{vtranslate($CURRENCY_FIELD->get('label'), $MODULE)}</button>
                    <select class="d-none" id="simplifiedCurrency" name="currency_id" data-original="{$CURRENCY_VALUE}">
                        {foreach item=CURRENCY_LABEL key=CURRENCY_ID from=$CURRENCY_INFO['currencyList']}
                            <option value="{$CURRENCY_ID}" {if $CURRENCY_ID eq $CURRENCY_VALUE}selected{/if}>{$CURRENCY_LABEL|escape}</option>
                        {/foreach}
                    </select>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-bd-light dropdown-toggle btn-outline-secondary simplifiedSettingButton" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <ul class="dropdown-menu dropdown-menu-end simplifiedSettingMenu" data-select="#simplifiedCurrency">
                            {foreach item=CURRENCY_LABEL key=CURRENCY_ID from=$CURRENCY_INFO['currencyList']}
                                <li><a class="dropdown-item" data-value="{$CURRENCY_ID}">{$CURRENCY_LABEL|escape}</a></li>
                            {/foreach}
                            {if $CURRENT_USER_MODEL->isAdminUser()}
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?module=Currency&parent=Settings&view=List"><span class="fa fa-cog module-icon dt-menu-icon"></span>&nbsp;&nbsp;{vtranslate('Settings')}</a></li>
                            {/if}
                        </ul>
                    </div>
                </div>
            {/if}
            {if $REGION_FIELD}
                {assign var=REGION_VALUE value=$REGION_FIELD->get('fieldvalue')}
                {assign var=REGION_INFO value=$REGION_FIELD->getFieldInfo()}
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary" tabindex="-1">{vtranslate($REGION_FIELD->get('label'), $MODULE)}</button>
                    <select class="d-none" id="simplifiedRegion" name="region_id" data-original="{$REGION_VALUE}">
                        <option value="0">{vtranslate('Default', 'InventoryItem')}</option>
                        {foreach item=REGION_LABEL key=REGION_ID from=$REGION_INFO['editablepicklistvalues']}
                            <option value="{$REGION_ID}" {if $REGION_ID eq $REGION_VALUE}selected{/if}>{$REGION_LABEL|escape}</option>
                        {/foreach}
                    </select>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-bd-light dropdown-toggle btn-outline-secondary simplifiedSettingButton" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <ul class="dropdown-menu dropdown-menu-end simplifiedSettingMenu" data-select="#simplifiedRegion">
                            <li><a class="dropdown-item" data-value="0">{vtranslate('Default', 'InventoryItem')}</a></li>
                            {foreach item=REGION_LABEL key=REGION_ID from=$REGION_INFO['editablepicklistvalues']}
                                <li><a class="dropdown-item" data-value="{$REGION_ID}">{$REGION_LABEL|escape}</a></li>
                            {/foreach}
                            {if $CURRENT_USER_MODEL->isAdminUser()}
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="index.php?module=Core&parent=Settings&view=Taxes&mode=regions"><span class="fa fa-cog module-icon dt-menu-icon"></span>&nbsp;&nbsp;{vtranslate('Settings')}</a></li>
                            {/if}
                        </ul>
                    </div>
                </div>
            {/if}
        </div>
    </div>
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
                {foreach key=ROW_INDEX item=ROW from=$SIMPLIFIED_ITEMS}
                    {include file=vtemplate_path('partials/SimplifiedItemRow.tpl', 'InventoryItem') ROW_INDEX=$ROW_INDEX ROW=$ROW}
                {/foreach}
                <tfoot>
                {assign var=ADJUSTMENT_FIELD value=$RECORD_STRUCTURE['LBL_ITEM_DETAILS']['adjustment']}
                {if empty($SIMPLIFIED_TAXES)}
                    <tr class="border-top">
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Total', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsGrandTotal">0</td>
                        <td></td>
                    </tr>
                {else}
                    <tr class="border-top">
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Total', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsNetTotal">0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('VAT', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsTaxTotal">0</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Total with VAT', 'InventoryItem')}</td>
                        <td class="text-end fw-bold simplifiedItemsGrandTotal">0</td>
                        <td></td>
                    </tr>
                {/if}
                {if $ADJUSTMENT_FIELD}
                    <tr>
                        <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Adjustment', 'InventoryItem')}</td>
                        <td class="simplifiedItemsAdjustmentCell">{include file=vtemplate_path($ADJUSTMENT_FIELD->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$ADJUSTMENT_FIELD FIELD_NAME='adjustment'}</td>
                        <td></td>
                    </tr>
                {/if}
                <tr>
                    <td colspan="{$SIMPLIFIED_COLUMNS - 1}" class="text-end fw-bold">{vtranslate('Grand Total', 'InventoryItem')}</td>
                    <td class="text-end fw-bold simplifiedItemsFinalTotal">0</td>
                    <td></td>
                </tr>
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
