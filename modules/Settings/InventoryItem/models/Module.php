<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_InventoryItem_Module_Model extends Vtiger_Module_Model
{
    /**
     * Static Function to get the instance of Vtiger Module Model for the given id or name
     *
     * @param $value id or name of the module
     */
    public static function getInstance($value)
    {
        $instance = false;
        $moduleObject = parent::getInstance($value);
        if ($moduleObject) {
            $instance = self::getInstanceFromModuleObject($moduleObject);
        }

        return $instance;
    }

    /**
     * Function to get the instance of Vtiger Module Model from a given Vtiger_Module object
     *
     * @param Vtiger_Module $moduleObj
     *
     * @return Vtiger_Module_Model instance
     */
    public static function getInstanceFromModuleObject(Vtiger_Module $moduleObj)
    {
        $objectProperties = get_object_vars($moduleObj);
        $moduleModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $moduleModel->$properName = $propertyValue;
        }

        return $moduleModel;
    }
}