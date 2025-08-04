<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_ActivityReminder_Action extends Vtiger_Action_Controller
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function getReminders(Vtiger_Request $request)
    {
        $recordModels = Appointments_Reminder_Model::getPopupRecords();
        $records = [];

        /**
         * @var Vtiger_Record_Model $record
         */
        foreach ($recordModels as $record) {
            Appointments_Reminder_Model::updateStatus((int)$record->getId());

            $recordInfo = $record->getDisplayableValues();
            $recordInfo['header_fields'] = Appointments_Reminder_Model::getHeaders($record);

            $records[] = $recordInfo;
        }

        $response = new Vtiger_Response();
        $response->setResult($records);
        $response->emit();
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function postpone(Vtiger_Request $request)
    {
        Appointments_Reminder_Model::updateStatus((int)$request->get('record'), 0);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);

        if (vtlib_isModuleActive($request->getModule())) {
            $mode = $request->getMode();
            if (!empty($mode)) {
                switch ($mode) {
                    case 'getReminders':
                        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];
                        break;

                    case 'postpone':
                        $permissions[] = ['module_parameter' => 'module', 'action' => 'EditView', 'record_parameter' => 'record'];
                        break;

                    default:
                        break;
                }
            }
        }

        return $permissions;
    }

    public function __construct()
    {
        $this->exposeMethod('getReminders');
        $this->exposeMethod('postpone');
    }
}