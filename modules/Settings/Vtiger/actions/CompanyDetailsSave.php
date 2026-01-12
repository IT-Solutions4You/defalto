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

        try {
            $this->Save($request);
        } catch (Exception $e) {
            if ($e->getMessage() == "LBL_INVALID_IMAGE") {
                $reloadUrl .= '&error=LBL_INVALID_IMAGE';
            } elseif ($e->getMessage() == "LBL_FIELDS_INFO_IS_EMPTY") {
                $reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
            }
        }
        header('Location: ' . $reloadUrl);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function Save(Vtiger_Request $request): void
    {
        $moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();

        if (!$request->get('organizationname')) {
            throw new Exception('LBL_FIELDS_INFO_IS_EMPTY', 103);
        }

        $logoName = false;

        if (!empty($_FILES['logo']['name'])) {
            $logoDetails = $_FILES['logo'];
            $saveLogo = Vtiger_Functions::validateImage($logoDetails);
            global $upload_badext;// from config.inc.php
            $logoName = sanitizeUploadFileName($logoDetails['name'], $upload_badext);

            if ($saveLogo && pathinfo($logoName, PATHINFO_EXTENSION) != 'txt') {
                $moduleModel->saveLogo($logoName);
            } else {
                $saveLogo = false;
            }
        } else {
            $saveLogo = true;
        }

        if (!$saveLogo) {
            throw new Exception('LBL_INVALID_IMAGE', 103);
        }

        $fields = $moduleModel->getFields();

        foreach ($fields as $fieldName => $fieldType) {
            $fieldValue = $request->get($fieldName);

            if ($fieldName === 'logoname') {
                if (!empty($logoDetails['name']) && $logoName) {
                    $fieldValue = decode_html(ltrim(basename(" " . $logoName)));
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

        $moduleModel->save();
    }

    /**
     * @inheritDoc
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}