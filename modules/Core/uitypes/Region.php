<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Region_UIType extends Vtiger_Picklist_UIType
{
    /**
     * @throws AppException
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
     * @throws AppException
     */
    public function getDisplayValue($value, $record = false, $recordInstance = false)
    {
        return self::transformDisplayValue($value);
    }

    /**
     * @throws AppException
     */
    public static function transformDisplayValue($rawValue): string
    {
        $region = Core_TaxRegion_Model::getInstanceById((int)$rawValue);

        return $region ? $region->getName() : '';
    }
}