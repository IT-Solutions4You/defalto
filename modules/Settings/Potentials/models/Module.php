<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_Potentials_Module_Model extends Settings_Leads_Module_Model
{
    /**
     * Function to get the Restricted ui Types
     * @return <array> $restrictedUitypes
     */
    public function getRestrictedUitypes()
    {
        $restrictedUitypes = parent::getRestrictedUitypes();
        $pos = array_search(10, $restrictedUitypes);
        unset($restrictedUitypes[$pos]);

        return $restrictedUitypes;
    }

    /**
     * Function to get instance of module
     *
     * @param <String> $moduleName
     *
     * @return <Settings_Potentials_Module_Model>
     */
    public static function getInstance($moduleName)
    {
        $moduleModel = parent::getInstance($moduleName);
        $objectProperties = get_object_vars($moduleModel);

        $moduleModel = new self();
        foreach ($objectProperties as $properName => $propertyValue) {
            $moduleModel->$properName = $propertyValue;
        }

        return $moduleModel;
    }
}