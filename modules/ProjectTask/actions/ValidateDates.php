<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ProjectTask_ValidateDates_Action extends Core_Controller_Action
{
    protected static array $referenceModules = ['projectid' => 'Project', 'milestoneid' => 'ProjectMilestone'];
    protected static array $dateFields = ['startdate', 'enddate'];

    public function checkPermission(Vtiger_Request $request): bool
    {
        $id = (int)$request->get('record');
        $permitted = $id ? ProjectTask_Record_Model::isQuickEditPermitted($id)
            : Users_Privileges_Model::isPermitted('ProjectTask', 'CreateView');

        if (!$permitted) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    public function process(Vtiger_Request $request)
    {
        $id = (int)$request->get('record');
        $record = $id ? Vtiger_Record_Model::getInstanceById($id, 'ProjectTask') : new ProjectTask_Record_Model();

        foreach (self::$referenceModules as $field => $module) {
            if ($request->has($field)) {
                $relatedId = (int)$request->get($field);

                if (!$record->isDateReferencePermitted($field, $module, $relatedId)) {
                    throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
                }

                $record->set($field, $relatedId);
            }
        }

        foreach (self::$dateFields as $field) {
            if ($request->has($field)) {
                $value = $request->get($field);

                if (!is_scalar($value) && $value !== null) {
                    throw new Exception(vtranslate('LBL_INVALID_DATE_RANGE', 'Project'));
                }

                $record->set($field, (string)$value);
            }
        }

        $error = $record->getDateValidationError();
        $response = new Vtiger_Response();
        $response->setResult($error === null ? true : vtranslate($error, 'Project'));
        $response->emit();
    }
}
