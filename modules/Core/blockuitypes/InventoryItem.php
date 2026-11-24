<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_InventoryItem_BlockUIType extends Vtiger_Base_Model implements Core_Interface_BlockUIType
{
    /**
     * @inheritDoc
     */
    public function getTemplateName(): string
    {
        if (InventoryItem_Module_Model::isSimplifiedBlock()) {
            return 'blockuitypes/InventoryItemSimplifiedEdit.tpl';
        }

        return 'blockuitypes/InventoryItemEdit.tpl';
    }

    /**
     * @inheritDoc
     */
    public function getDetailViewTemplateName(): string
    {
        if (InventoryItem_Module_Model::isSimplifiedBlock()) {
            return 'blockuitypes/InventoryItemSimplified.tpl';
        }

        return 'blockuitypes/InventoryItem.tpl';
    }
}