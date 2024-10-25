<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Settings_InventoryItem_Module_Model extends Vtiger_Module_Model
{

    public static function getPicklistSupportedModules()
    {
        $db = PearDatabase::getInstance();
        $supportedModules = ['Quotes', 'PurchaseOrder', 'SalesOrder', 'Invoice', ];
        $query = "SELECT distinct vtiger_tab.tablabel, vtiger_tab.name as tabname
				  FROM vtiger_tab
				  WHERE vtiger_tab.name IN (" . generateQuestionMarks($supportedModules) . ") 
				    AND vtiger_tab.presence != 1
				  ORDER BY vtiger_tab.tabid";
        $result = $db->pquery($query, $supportedModules);

        $modulesModelsList = [];
        while ($row = $db->fetch_array($result)) {
            $moduleLabel = $row['tablabel'];
            $moduleName = $row['tabname'];
            $instance = new self();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }

        return $modulesModelsList;
    }

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
