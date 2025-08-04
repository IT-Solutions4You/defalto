<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once 'config.php';
require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
include_once 'vtlib/Vtiger/Module.php';
vimport('includes.runtime.EntryPoint');

if (isset($_REQUEST['type']) && isset($_REQUEST['key']) && $_REQUEST['type'] == 'logo') {
    $logoPath = 'test/logo/';
    $allowedLogoImageFormats = Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats;
    $fileName = vtlib_purify($_REQUEST['key']);
    $finalFilePath = $logoPath . $fileName;
    $extension = explode('.', $fileName);
    $imageFormat = end($extension);

    if (in_array($imageFormat, $allowedLogoImageFormats)) {
        checkFileAccess($finalFilePath);
        Vtiger_ShowFile_Helper::show($finalFilePath, $imageFormat);
    }

    return;
}

Vtiger_ShowFile_Helper::handle(vtlib_purify($_REQUEST['fid']), vtlib_purify($_REQUEST['key']));