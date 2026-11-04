{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    {if empty($LIST_PREVIEW) && InventoryItem_ParentEntity_Model::isParentEditable($RECORD->getId(), $MODULE_NAME)}
        <div class="float-end dropdown inventoryItemWidgetAdd"{foreach from=InventoryItem_ParentEntity_Model::getItemPopupValues($RECORD) key=VALUE_NAME item=VALUE} data-{$VALUE_NAME}="{$VALUE|escape}"{/foreach}>
            <button type="button" class="btn btn-sm text-secondary fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-plus"></i>
                <span class="ms-2">{vtranslate('LBL_ADD', $MODULE_NAME)}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#" data-modulename="">
                        <i class="fa fa-i-cursor me-2"></i>
                        <span>{vtranslate('TEXT', 'InventoryItem')}</span>
                    </a>
                </li>
                {foreach from=InventoryItem_ItemModules_Model::getItemModules() item=ITEM_MODULE_NAME}
                    <li>
                        <a class="dropdown-item" href="#" data-modulename="{$ITEM_MODULE_NAME}">
                            {Vtiger_Module_Model::getModuleIconPath($ITEM_MODULE_NAME)}
                            <span class="ms-2">{vtranslate($ITEM_MODULE_NAME, $ITEM_MODULE_NAME)}</span>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
{/strip}
