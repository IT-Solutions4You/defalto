<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

class Core_InventoryItem_BlockUIType extends Vtiger_Base_Model implements Core_Interface_BlockUIType
{
    /**
     * @inheritDoc
     */
    public function getTemplateName(): string
    {
        return 'blockuitypes/InventoryItemEdit.tpl';
    }

    /**
     * @inheritDoc
     */
    public function getDetailViewTemplateName(): string
    {
        return 'blockuitypes/InventoryItem.tpl';
    }
}