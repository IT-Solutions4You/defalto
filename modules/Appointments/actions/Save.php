<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_Save_Action extends Vtiger_Save_Action
{
    /**
     * @var bool|object
     */
    public $recurrenceDatabaseObject;
    /**
     * @var
     */
    public $savedRecordId;

    /**
     * @param object $request
     *
     * @return void
     */
    public function retrieveDatabaseRecurrence(object $request)
    {
        if ('edit' === $request->getMode()) {
            $this->recurrenceDatabaseObject = Appointments_Recurrence_Model::getRecurringObject($request->get('record'));
        }
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return object
     */
    public function saveRecord($request): object
    {
        $request->set('invite_users', implode(';', (array)$request->get('invite_users')));
        $this->retrieveDatabaseRecurrence($request);

        /** @var Appointments_Record_Model $recordModel */
        $recordModel = $this->getRecordModelFromRequest($request);
        Appointments_DateTime_Helper::setAllDayDatesFromRequest($request, $recordModel);
        $this->setRecurrenceDateTimeRequest($request);
        $recordModel->save();

        $this->savedRecordId = $recordModel->getId();
        $this->saveRepeatEvents();

        return $recordModel;
    }

    protected function setRecurrenceDateTimeRequest(Vtiger_Request $request): void
    {
        [$startDate, $startTime] = $this->getRecurrenceDateTimeParts($request, 'datetime_start');
        [$endDate, $endTime] = $this->getRecurrenceDateTimeParts($request, 'datetime_end');

        $request->setGlobal('date_start', $startDate);
        $request->setGlobal('time_start', $startTime);
        $request->setGlobal('due_date', $endDate);
        $request->setGlobal('time_end', $endTime);
    }

    protected function getRecurrenceDateTimeParts(Vtiger_Request $request, string $fieldName): array
    {
        $date = trim((string)$request->get($fieldName . '_date'));
        $time = trim((string)$request->get($fieldName . '_time'));

        if ('' === $date || '' === $time) {
            $dateTime = preg_split('/\s+/', trim((string)$request->get($fieldName)), 2);
            $date = '' !== $date ? $date : ($dateTime[0] ?? '');
            $time = '' !== $time ? $time : ($dateTime[1] ?? '');
        }

        return [$date, $time];
    }

    /**
     * @return void
     */
    public function saveRepeatEvents()
    {
        if (empty($this->savedRecordId)) {
            return;
        }

        $recordId = $this->savedRecordId;
        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $recordModel->set('mode', 'edit');
        $recordModel->set('recurringEditMode', $_REQUEST['recurringEditMode']);
        $recordModel->set('recurring_type', $_REQUEST['recurringtype']);
        $recordModel->set('recurringtype', $_REQUEST['recurringtype']);

        if ($recordModel->isAllDayEvent()) {
            $_REQUEST['date_start'] = DateTimeField::convertToUserFormat($recordModel->getAllDayDate('datetime_start'));
            $_REQUEST['time_start'] = '00:00:00';
            $_REQUEST['due_date'] = DateTimeField::convertToUserFormat($recordModel->getAllDayDate('datetime_end'));
            $_REQUEST['time_end'] = '00:00:00';
        } else {
            $startDateTime = new DateTimeField($recordModel->get('datetime_start'));
            $endDateTime = new DateTimeField($recordModel->get('datetime_end'));

            $_REQUEST['date_start'] = $startDateTime->getDisplayDate();
            $_REQUEST['time_start'] = $startDateTime->getDisplayTime();
            $_REQUEST['due_date'] = $endDateTime->getDisplayDate();
            $_REQUEST['time_end'] = $endDateTime->getDisplayTime();
        }

        $recurrenceObject = Core_RecurringType_Model::getRecurringObjValue();

        if ($recurrenceObject) {
            $recurringDataChanged = Appointments_RepeatRecords_Model::checkRecurringDataChanged($recurrenceObject, $this->recurrenceDatabaseObject);

            if (Appointments_RepeatRecords_Model::validate($recordModel->getData()) || ($recurringDataChanged && empty($recurrenceObject))) {
                Appointments_RepeatRecords_Model::repeatFromRequest($recordModel, $this->recurrenceDatabaseObject);
            }
        }
    }
}
