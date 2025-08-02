<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * The configuration file for FHS system
 * is located at /etc/vtigercrm directory.
 */

include('config.inc.php');

$THIS_DIR = __DIR__;

/* Pre-install overrides */
if (!isset($dbconfig)) {
    error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
}

if (file_exists($THIS_DIR . '/config_override.php')) {
    include_once $THIS_DIR . '/config_override.php';
}

class VtigerConfig
{

    static function get($key, $defvalue = '')
    {
        if (self::has($key)) {
            global ${$key};

            return ${$key};
        }

        return $defvalue;
    }

    static function has($key)
    {
        global ${$key};

        return (isset(${$key}));
    }

    static function getOD($key, $defvalue = '')
    {
        return '';
    }

    static function hasOD($key)
    {
        return false;
    }
}