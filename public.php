<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
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