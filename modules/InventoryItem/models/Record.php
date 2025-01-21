<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class InventoryItem_Record_Model extends Vtiger_Record_Model
{
    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->recalculate();
        parent::save();
    }

    private function recalculate() {

    }
}