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

class SMSProvider
{
    static function getInstance($providername)
    {
        if (!empty($providername)) {
            $providername = trim($providername);

            $filepath = dirname(__FILE__) . "/../providers/{$providername}.php";
            checkFileAccessForInclusion($filepath);

            $className = "SMSNotifier_" . $providername . "_Provider";
            if (!class_exists($className)) {
                include_once $filepath;
            }

            return new $className();
        }

        return false;
    }

    static function listAll()
    {
        $providers = [];
        if ($handle = opendir(dirname(__FILE__) . '/../providers')) {
            while (false !== ($file = readdir($handle))) {
                if (!in_array($file, ['.', '..', '.svn', 'CVS'])) {
                    if (preg_match("/(.*)\.php$/", $file, $matches)) {
                        $providers[] = $matches[1];
                    }
                }
            }
        }

        return $providers;
    }
}