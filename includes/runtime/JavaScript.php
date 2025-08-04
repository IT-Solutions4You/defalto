<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
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

class Vtiger_JavaScript extends Vtiger_Viewer
{
    /**
     * Function to get the path of a given style sheet or default style sheet
     *
     * @param <String> $fileName
     *
     * @return <string / Boolean> - file path , false if not exists
     */
    public static function getFilePath($fileName = '')
    {
        if (empty($fileName)) {
            return false;
        }
        $filePath = self::getBaseJavaScriptPath() . '/' . $fileName;
        $completeFilePath = Vtiger_Loader::resolveNameToPath('~' . $filePath);

        if (file_exists($completeFilePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Function to get the Base Theme Path, until theme folder not selected theme folder
     * @return <string> - theme folder
     */
    public static function getBaseJavaScriptPath()
    {
        return 'layouts' . '/' . self::getLayoutName();
    }
}