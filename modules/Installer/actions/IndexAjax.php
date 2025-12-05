<?php
/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Installer_IndexAjax_Action extends Vtiger_BasicAjax_Action
{
    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!$currentUserModel->isAdminUser()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('licenseSave');
        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function licenseSave(Vtiger_Request $request): void
    {
        $id = (int)$request->get('license_id');
        $name = $request->get('license_name');

        $licenseInfo = Installer_Api_Model::getInstance()->activateLicenseInfo($name);

        $message = vtranslate('LBL_LICENSE_NOT_ACTIVATED', 'Installer');
        $status = 'not_activated';

        if (!empty($licenseInfo['expires'])) {
            if (!empty($id)) {
                $license = Installer_License_Model::getInstanceById($id);
            } else {
                $license = Installer_License_Model::getInstance($name);
            }

            $license->set('name', $name);
            $license->setInfo($licenseInfo);
            $license->save();

            $message = vtranslate('LBL_LICENSE_ACTIVATED', 'Installer');
            $status = 'activated';

            Installer_ExtensionInstall_Model::clearCache();
            Installer_SystemInstall_Model::clearCache();
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'status' => $status, 'message' => $message]);
        $response->emit();
    }
}