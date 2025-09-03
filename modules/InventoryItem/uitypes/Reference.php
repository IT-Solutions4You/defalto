<?php
/*
 *
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */


class InventoryItem_Reference_UIType extends Vtiger_Reference_UIType
{
    public function getRelatedBlockDisplayValue(mixed $value, bool|int $record, object|bool $recordInstance): string
    {
        $value = parent::getRelatedBlockDisplayValue($value, $record, $recordInstance);

        if (empty($value) && $recordInstance && 'productid' === $this->getFieldModel()->getName()) {
            return (string)$recordInstance->get('item_text');
        }

        return $value;
    }
}