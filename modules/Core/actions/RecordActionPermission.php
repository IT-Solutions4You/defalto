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
 * Removes one record action rule, or all rules of the record, from the detail banner (administrators only) and
 * returns to the record.
 */
class Core_RecordActionPermission_Action extends Core_Controller_Action
{
    /**
     * Allows the removal only to administrators and only for a record of the requested module.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        $recordId = (int)$request->get('record');
        $isAdmin = Users_Record_Model::getCurrentUserModel()->isAdminUser();

        if (!$isAdmin || !$recordId || getSalesEntityType($recordId) !== $request->getModule()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * Requires the CSRF token of a write request.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Deletes the rule (or all rules with "remove_all") and redirects back to the record detail.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $recordId = (int)$request->get('record');

        $actionPermission = Core_RecordActionPermission_Model::getInstance();

        if ($request->get('remove_all')) {
            $actionPermission->deleteRecordRules($recordId);
        } else {
            $actionPermission->deleteRule($recordId, (int)$request->get('rule_id'));
        }

        header('Location: ' . Vtiger_Record_Model::getInstanceById($recordId, $request->getModule())->getDetailViewUrl());
    }
}
