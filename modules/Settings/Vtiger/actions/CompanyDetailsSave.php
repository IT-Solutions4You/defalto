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

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action
{
    public function process(Vtiger_Request $request)
    {
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $reloadUrl = $moduleModel->getIndexViewUrl();

        $this->saveCompanyDetails($request);

        $response = new Vtiger_Response();
        $response->setResult(['reloadUrl' => $reloadUrl]);

        return $response;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function saveCompanyDetails(Vtiger_Request $request): void
    {
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
        $qualifiedModuleName = $request->getModule(false);

        if (!$request->get('organizationname')) {
            throw new Exception(vtranslate('LBL_FIELDS_INFO_IS_EMPTY', $qualifiedModuleName), 103);
        }

        $logoName = false;

        if (!empty($_FILES['logo']['name'])) {
            $logoDetails = $_FILES['logo'];
            $validationError = $moduleModel->getLogoValidationError($logoDetails);

            if ($validationError) {
                $validationMessage = vtranslate($validationError, $qualifiedModuleName);

                if ($validationError === 'LBL_MAXIMUM_SIZE_EXCEEDS') {
                    $validationMessage = sprintf($validationMessage, $moduleModel->getLogoMaxFileSizeInMegabytes());
                }

                throw new Exception($validationMessage, 103);
            }

            global $upload_badext;// from config.inc.php
            $logoName = sanitizeUploadFileName($logoDetails['name'], $upload_badext);

            if (pathinfo($logoName, PATHINFO_EXTENSION) !== 'txt') {
                if (!$moduleModel->saveLogo($logoName)) {
                    throw new Exception(vtranslate('LBL_LOGO_SAVE_FAILED', $qualifiedModuleName), 103);
                }
            } else {
                throw new Exception(vtranslate('LBL_INVALID_IMAGE', $qualifiedModuleName), 103);
            }
        }

        $fields = $moduleModel->getFields();
        $removeLogo = $request->get('remove_logo') === '1' && !$logoName;
        $currentLogoName = decode_html((string)$moduleModel->get('logoname'));

        foreach ($fields as $fieldName => $fieldType) {
            $fieldValue = $request->get($fieldName);

            if ($fieldName === 'logoname') {
                if (!empty($logoDetails['name']) && $logoName) {
                    $fieldValue = decode_html(ltrim(basename(" " . $logoName)));
                } elseif ($removeLogo) {
                    $fieldValue = '';
                } else {
                    $fieldValue = decode_html($moduleModel->get($fieldName));
                }
            } else {
                $fieldValue = strip_tags(decode_html($fieldValue));
            }

            // In OnBoard company detail page we will not be sending all the details
            if ($request->has($fieldName) || ($fieldName == "logoname")) {
                $moduleModel->set($fieldName, $fieldValue);
            }
        }

        if ($removeLogo) {
            $moduleModel->removeLogo($currentLogoName);
        } else {
            $moduleModel->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
