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

class Settings_Vtiger_UpdateCompanyLogo_Action extends Settings_Vtiger_Basic_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();

        $logoDetails = $_FILES['logo'];
        $validationError = $moduleModel->getLogoValidationError($logoDetails);

        if (!$validationError) {
            $sanitizedFileName = ltrim(basename(' ' . Vtiger_Util_Helper::sanitizeUploadFileName($logoDetails['name'], vglobal('upload_badext'))));

            if (pathinfo($sanitizedFileName, PATHINFO_EXTENSION) !== 'txt') {
                if ($moduleModel->saveLogo($sanitizedFileName)) {
                    $moduleModel->set('logoname', $sanitizedFileName);
                    $moduleModel->save();
                } else {
                    $validationError = 'LBL_LOGO_SAVE_FAILED';
                }
            } else {
                $validationError = 'LBL_INVALID_IMAGE';
            }
        }

        $reloadUrl = $moduleModel->getIndexViewUrl();

        if ($validationError) {
            $reloadUrl .= '&error=' . urlencode($validationError);
        }

        header('Location: ' . $reloadUrl);
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
