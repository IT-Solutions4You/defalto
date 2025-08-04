<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once("include/events/SqlResultIterator.inc");

class VTEntityMethodManager
{
    protected $adb;

    function __construct($adb)
    {
        $this->adb = $adb;
    }

    function addEntityMethod($moduleName, $methodName, $functionPath, $functionName)
    {
        $adb = $this->adb;
        $id = $adb->getUniqueId("com_vtiger_workflowtasks_entitymethod");
        $adb->pquery(
            "insert into com_vtiger_workflowtasks_entitymethod (workflowtasks_entitymethod_id, module_name, function_path, function_name, method_name) values (?,?,?,?,?)",
            [$id, $moduleName, $functionPath, $functionName, $methodName]
        );
    }

    function executeMethod($entityData, $methodName)
    {
        $adb = $this->adb;
        $moduleName = $entityData->getModuleName();
        $result = $adb->pquery("select function_path, function_name from com_vtiger_workflowtasks_entitymethod where module_name=? and method_name=?", [$moduleName, $methodName]);
        if ($adb->num_rows($result) != 0) {
            $data = $adb->raw_query_result_rowdata($result, 0);
            $functionPath = $data['function_path'];
            $functionName = $data['function_name'];
            require_once($functionPath);
            $functionName($entityData);
        }
    }

    function methodsForModule($moduleName)
    {
        $adb = $this->adb;
        $result = $adb->pquery("select method_name from com_vtiger_workflowtasks_entitymethod where module_name=?", [$moduleName]);
        $it = new SqlResultIterator($adb, $result);
        $methodNames = [];
        foreach ($it as $row) {
            $methodNames[] = $row->method_name;
        }

        return $methodNames;
    }
    /*
    private function methodExists($object, $methodName){
        $className = get_class($object);
        $class = new ReflectionClass($className);
        $methods = $class->getMethods();
        foreach($methods as $method){
            if($method->getName()==$methodName){
                return true;
            }
        }
        return false;
    }*/

    /**
     * Function to remove workflowtasks entity method
     *
     * @param <String> Module Name
     * @param <String> Entity Method Name.
     */
    function removeEntityMethod($moduleName, $methodName)
    {
        $adb = $this->adb;
        $adb->pquery("DELETE FROM com_vtiger_workflowtasks_entitymethod WHERE module_name = ? and method_name= ?", [$moduleName, $methodName]);
    }
}