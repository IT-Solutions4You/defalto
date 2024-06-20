<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Vtiger_InventoryItem_BlockUIType extends Vtiger_Base_Model implements Vtiger_Interface_BlockUIType
{
    /**
     * @inheritDoc
     */
    public function getTemplateName(): string
    {
        return '';
    }
    /**
     * @inheritDoc
     */
    public function getDetailViewTemplateName(): string
    {
        return 'blockuitypes/InventoryItem.tpl';
    }
}