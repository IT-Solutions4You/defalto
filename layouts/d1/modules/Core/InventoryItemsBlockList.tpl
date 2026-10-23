{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
<div class="h-main containerRelatedBlockEdit p-3">
    <div class="bg-body rounded py-3">
        <div class="container-fluid">
            <div class="row align-items-end">
                <div class="col-lg pb-3">
                    <h4 class="m-0">{vtranslate('LBL_PRODUCTBLOCKTPL', 'PDFMaker')}</h4>
                </div>
                <div class="col-lg-4 pb-3">
                    <label class="form-label text-secondary" for="inventoryBlockModule">{vtranslate('LBL_MODULE', 'Core')}</label>
                    <select id="inventoryBlockModule" class="form-select select2 inventoryBlockModule" data-list-url="index.php?module={$MANAGEMENT_MODULE}&view=InventoryItemsBlock&mode=list">
                        <option value="">{vtranslate('LBL_ALL', 'Core')}</option>
                        {foreach from=$INVENTORY_MODULES item=MODULE_LABEL key=MODULE_NAME}
                            <option value="{$MODULE_NAME}" {if $SOURCE_MODULE eq $MODULE_NAME}selected="selected"{/if}>{$MODULE_LABEL}</option>
                        {/foreach}
                    </select>
                </div>
                <div class="col-lg-auto pb-3">
                    <button type="button" class="btn btn-outline-secondary createInventoryBlock" data-create-url="index.php?module={$MANAGEMENT_MODULE}&view=InventoryItemsBlock&mode=edit" {if empty($SOURCE_MODULE)}disabled="disabled"{/if}>
                        <i class="bi bi-plus-lg"></i>
                        <span class="ms-2">{vtranslate('LBL_CREATE', 'Core')}</span>
                    </button>
                </div>
            </div>
        </div>
        <table class="table table-borderless">
            <thead>
                <tr class="listViewContentHeader bg-body-secondary">
                    <th class="text-secondary w-25">{vtranslate('LBL_ACTIONS', 'Core')}</th>
                    <th class="text-secondary">{vtranslate('LBL_NAME', 'Core')}</th>
                    <th class="text-secondary">{vtranslate('LBL_MODULE', 'Core')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$INVENTORY_BLOCK_MODELS item=INVENTORY_BLOCK_MODEL}
                    <tr class="border-bottom">
                        <td>
                            <a class="btn text-secondary" href="{$INVENTORY_BLOCK_MODEL->getManagementEditViewUrl($MANAGEMENT_MODULE)}" title="{vtranslate('LBL_EDIT', 'Core')}">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                            <button type="button" class="btn text-secondary deleteInventoryBlock" title="{vtranslate('LBL_DELETE', 'Core')}" data-module="{$MANAGEMENT_MODULE}" data-record="{$INVENTORY_BLOCK_MODEL->getId()}" data-message="{vtranslate('LBL_DELETE_CONFIRMATION', 'Core')}" data-success="{vtranslate('LBL_DELETED', 'Core')}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                        <td>{$INVENTORY_BLOCK_MODEL->getName()}</td>
                        <td>{vtranslate($INVENTORY_BLOCK_MODEL->getModuleName(), $INVENTORY_BLOCK_MODEL->getModuleName())}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
