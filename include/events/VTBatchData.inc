<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class VTBatchData
{

    /**
     * Gets the array of data from the entity object as an array.
     * @return An array representation VTEntityData
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * Function gives an array of VTEntityData
     *
     * @param <Array of VTEntityData> $data
     */
    function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Get the name of the module triggering the event
     * @return The module name.
     */
    function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Function sets the module name
     *
     * @param type $module
     */
    function setModuleName($module)
    {
        $this->moduleName = $module;
    }
}