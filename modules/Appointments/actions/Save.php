<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return object
     */
    public function saveRecord($request): object
    {
        $request->set('invite_users', implode(';', (array)$request->get('invite_users')));
        $this->retrieveDatabaseRecurrence($request);

        /** @var Appointments_Record_Model $recordModel */
        $recordModel = $this->getRecordModelFromRequest($request);
        $recordModel->save();

        $this->savedRecordId = $recordModel->getId();
        $this->saveRepeatEvents();

        return $recordModel;
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

        $_REQUEST['date_start'] = $_REQUEST['datetime_start_date'];
        $_REQUEST['time_start'] = $_REQUEST['datetime_start_time'];
        $_REQUEST['date_end'] = $_REQUEST['datetime_end_date'];
        $_REQUEST['time_end'] = $_REQUEST['datetime_end_time'];

        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();

        if ($recurrenceObject) {
            $recurringDataChanged = Appointments_RepeatRecords_Model::checkRecurringDataChanged($recurrenceObject, $this->recurrenceDatabaseObject);

            if (Appointments_RepeatRecords_Model::validate($recordModel->getData()) || ($recurringDataChanged && empty($recurrenceObject))) {
                Appointments_RepeatRecords_Model::repeatFromRequest($recordModel, $this->recurrenceDatabaseObject);
            }
        }
    }
}