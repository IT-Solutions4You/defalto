<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_Region_UIType extends Vtiger_Picklist_UIType
{
    /**
     * @throws Exception
     */
    public function getPicklistValues(): array
    {
        $regions = Core_TaxRegion_Model::getAllRegions();
        $values = [];

        foreach ($regions as $region) {
            $values[$region->getId()] = $region->getName();
        }

        return $values;
    }

    /**
     * @throws Exception
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return self::transformDisplayValue($value);
    }

    /**
     * @throws Exception
     */
    public static function transformDisplayValue($rawValue): string
    {
        $region = Core_TaxRegion_Model::getInstanceById((int)$rawValue);

        return $region ? $region->getName() : '';
    }
}