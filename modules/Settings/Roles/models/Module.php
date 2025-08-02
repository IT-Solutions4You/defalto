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

/*
 * Settings Module Model Class
 */

class Settings_Roles_Module_Model extends Settings_Vtiger_Module_Model
{
    var $baseTable = 'vtiger_role';
    var $baseIndex = 'roleid';
    var $listFields = ['roleid' => 'Role Id', 'rolename' => 'Name'];
    var $name = 'Roles';

    /**
     * Function to get the url for default view of the module
     * @return <string> - url
     */
    public function getDefaultUrl()
    {
        return 'index.php?module=Roles&parent=Settings&view=Index';
    }

    /**
     * Function to get the url for Create view of the module
     * @return <string> - url
     */
    public function getCreateRecordUrl()
    {
        return 'index.php?module=Roles&parent=Settings&view=Index';
    }
}