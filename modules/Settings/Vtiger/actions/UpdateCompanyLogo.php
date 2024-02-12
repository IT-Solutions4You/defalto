<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Vtiger_UpdateCompanyLogo_Action extends Settings_Vtiger_Basic_Action {

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();

        $logoDetails = $_FILES['logo'];
        $saveLogo = Vtiger_Functions::validateImage($logoDetails);

        if ($saveLogo) {
            $sanitizedFileName = ltrim(basename(' ' . Vtiger_Util_Helper::sanitizeUploadFileName($logoDetails['name'], vglobal('upload_badext'))));

            if (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) != 'txt') {
                $moduleModel->saveLogo($sanitizedFileName);
                $moduleModel->set('logoname', $sanitizedFileName);
                $moduleModel->save();
            } else {
                $saveLogo = false;
            }
        }

        $reloadUrl = $moduleModel->getIndexViewUrl();

        if (!$saveLogo) {
            $reloadUrl .= '&error=LBL_INVALID_IMAGE';
        }

        header('Location: ' . $reloadUrl);
    }
    
    public function validateRequest(Vtiger_Request $request) {
        $request->validateWriteAccess();
    }
}