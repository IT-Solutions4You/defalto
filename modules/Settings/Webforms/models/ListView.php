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

class Settings_Webforms_ListView_Model extends Settings_Vtiger_ListView_Model
{
    /**
     * Function which returns Basic List Query for webform.
     */
    public function getBasicListQuery()
    {
        $module = $this->getModule();
        $listFields = $module->listFields;

        $listQuery = "SELECT ";
        foreach ($listFields as $fieldName => $fieldLabel) {
            $listQuery .= $module->baseTable . ".$fieldName, ";
        }
        $listQuery .= $module->baseTable . '.' . $module->baseIndex . ' FROM ' . $module->baseTable .
            ' INNER JOIN vtiger_tab ON vtiger_tab.name=' . $module->baseTable . '.targetmodule WHERE vtiger_tab.presence IN (0,2)';

        return $listQuery;
    }
}