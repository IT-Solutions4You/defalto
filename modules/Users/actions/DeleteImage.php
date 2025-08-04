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

class Users_DeleteImage_Action extends Vtiger_Action_Controller
{
    public function checkPermission(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $record = $request->get('id');

        if (!(Users_Privileges_Model::isPermitted($moduleName, 'EditView', $record) && Users_Privileges_Model::isPermitted($moduleName, 'Delete', $record))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }
    }

    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordId = $request->get('record');
        $imageId = $request->get('imageid');

        $response = new Vtiger_Response();
        if ($recordId) {
            $recordModel = Users_Record_Model::getInstanceById($recordId, $moduleName);
            $status = $recordModel->deleteImage($imageId);
            if ($status) {
                $response->setResult([vtranslate('LBL_IMAGE_DELETED_SUCCESSFULLY', $moduleName)]);
            }
        } else {
            $response->setError(vtranslate('LBL_IMAGE_NOT_DELETED', $moduleName));
        }

        $response->emit();
    }
}