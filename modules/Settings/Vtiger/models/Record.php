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

/**
 * Roles Record Model Class
 */
abstract class Settings_Vtiger_Record_Model extends Vtiger_Base_Model
{
    abstract function getId();

    abstract function getName();

    /**
     * Function to get the instance of Settings module model
     * @return Settings_Vtiger_Module_Model instance
     */
    public static function getInstance()
    {
        [$name] = func_get_args();
        if (empty($name)) {
            $name = 'Settings:Vtiger';
        }
        $modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $name);

        return new $modelClassName();
    }

    public function getRecordLinks()
    {
        $links = [];
        $recordLinks = [];
        foreach ($recordLinks as $recordLink) {
            $links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
        }

        return $links;
    }

    public function getDisplayValue($key)
    {
        return $this->get($key);
    }
}