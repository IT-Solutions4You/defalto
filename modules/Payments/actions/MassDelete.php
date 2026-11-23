<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Mass delete of the Payments list: every selected payment is deleted in its own payment module with the delete
 * permission of that module, so the delete handlers recalculate its documents. Payments has no records itself.
 */
class Payments_MassDelete_Action extends Core_Controller_Action
{
    /**
     * Requires access to the Payments list; the delete permission is checked per payment in its module.
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    /**
     * Deletes the selected payments and reports the skipped ones.
     *
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $sources = Payments_Sources_Helper::getAccessibleSources();
        $rows = Payments_ListView_Model::getInstanceForRequest($request)->getSelectedRows($request);
        $actionPermission = Core_RecordActionPermission_Model::getInstance();
        $actionPermission->preload(array_column($rows, 'id'));
        $skipped = [];

        foreach ($rows as $row) {
            $moduleName = $sources[$row['source']]['module'] ?? '';

            if ($moduleName !== '' && Users_Privileges_Model::isPermitted($moduleName, 'Delete', $row['id'])) {
                Vtiger_Record_Model::getInstanceById($row['id'], $moduleName)->delete();
            } else {
                $skipped[$row['id']] = $actionPermission->getDenyReason($row['id'], Core_RecordActionPermission_Model::ACTION_DELETE)
                    ?? vtranslate('LBL_PERMISSION_DENIED');
            }
        }

        $response = new Vtiger_Response();
        $response->setResult([
            'viewname' => $request->get('viewname'),
            'module' => $request->getModule(),
            'message' => $actionPermission->getSkippedMessage($skipped),
        ]);
        $response->emit();
    }

    /**
     * Deleting changes data, so the request must pass the write access check.
     *
     * @param Vtiger_Request $request
     * @return bool
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
