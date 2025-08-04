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

#[\AllowDynamicProperties]
class Vtiger_Cache_Connector_Memory
{
    function set($key, $value)
    {
        $this->$key = $value;
    }

    function get($key)
    {
        return isset($this->$key) ? $this->$key : false;
    }

    function flush()
    {
        return true;
    }

    function delete($key)
    {
        $this->$key = null;
    }

    public static function getInstance()
    {
        static $singleton = null;
        if ($singleton === null) {
            $singleton = new self();
        }

        return $singleton;
    }
}
