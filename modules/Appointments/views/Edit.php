<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Edit_View extends Vtiger_Edit_View
{

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        Appointments_Module_Model::retrieveDefaultValuesForEdit($request);

        $recordId = (int)$request->get('record');
        Appointments_Datetime_UIType::$isAllDayEditContext = false;
        Appointments_Datetime_UIType::$allDayEditDates = [];

        if ($recordId) {
            /** @var Appointments_Record_Model $recordModel */
            $recordModel = $this->record ?: Vtiger_Record_Model::getInstanceById($recordId, $request->getModule());
            $this->record = $recordModel;
            Appointments_Datetime_UIType::$isAllDayEditContext = $recordModel->isAllDayEvent();

            if ($recordModel->isAllDayEvent()) {
                Appointments_Datetime_UIType::$allDayEditDates = [
                    'datetime_start' => $recordModel->getAllDayDate('datetime_start'),
                    'datetime_end'   => $recordModel->getAllDayDate('datetime_end'),
                ];
            }
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('RECURRING_INFORMATION', Appointments_Recurrence_Model::getRecurrenceInformation($request));

        parent::process($request);
    }
}
