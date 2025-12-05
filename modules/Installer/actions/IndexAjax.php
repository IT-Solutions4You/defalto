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
        $this->exposeMethod('licenseDelete');
        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @throws Exception
     */
    public function licenseSave(Vtiger_Request $request): void
    {
        $id = (int)$request->get('license_id');
        $name = $request->get('license_name');
        $message = vtranslate('LBL_LICENSE_NOT_ACTIVATED', 'Installer');
        $status = 'not_activated';

        if (!empty($id)) {
            $license = Installer_License_Model::getInstanceById($id);
        } else {
            $license = Installer_License_Model::getInstance($name);
        }

        $license->activate();

        if ($license->hasExpireDate()) {
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

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function licenseDelete(Vtiger_Request $request): void
    {
        $id = (int)$request->get('license_id');
        $message = vtranslate('LBL_LICENSE_ALREADY_DELETED', 'Installer');

        if (!empty($id)) {
            $license = Installer_License_Model::getInstanceById($id);

            if ($license) {
                $deactivate = Installer_Api_Model::getInstance()->deactivateLicenseInfo($license->getName());

                if ($deactivate) {
                    $license->delete();
                    $message = vtranslate('LBL_LICENSE_DELETED', 'Installer');
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'message' => $message]);
        $response->emit();
    }
}