<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_Save_Action extends Vtiger_Save_Action
{
    public $savedRecordId;

    /**
     * @var bool|object
     */
    public $recurrenceDatabaseObject;

    /**
     * @param Vtiger_Request $request
     * @return object
     */
    public function saveRecord($request): object
    {
        $this->retrieveDatabaseRecurrence($request);

        $recordModel = $this->getRecordModelFromRequest($request);
        $recordModel->save();
        $this->savedRecordId = $recordModel->getId();

        $this->saveRepeatEvents($recordModel);

        return $recordModel;
    }

    /**
     * @param object $request
     * @return void
     */
    public function retrieveDatabaseRecurrence(object $request)
    {
        if ('edit' === $request->getMode()) {
            $this->recurrenceDatabaseObject = ITS4YouCalendar_Recurrence_Model::getRecurringObject($request->get('record'));
        }
    }

    /**
     * @param object $recordModel
     * @return void
     */
    public function saveRepeatEvents(object $recordModel)
    {
        $focus = $recordModel->getEntity();
        $focus->column_fields['recurringEditMode'] = $_REQUEST['recurringEditMode'];
        $focus->column_fields['recurring_type'] = $focus->column_fields['recurringtype'] = $_REQUEST['recurringtype'];

        list($_REQUEST['date_start'], $_REQUEST['time_start']) = explode(' ', $focus->column_fields['datetime_start_date']);
        list($_REQUEST['due_date'], $_REQUEST['time_end']) = explode(' ', $focus->column_fields['datetime_end_date']);

        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();
        $recurringDataChanged = ITS4YouCalendar_RepeatRecords_Model::checkRecurringDataChanged($recurrenceObject, $this->recurrenceDatabaseObject);

        if (ITS4YouCalendar_RepeatRecords_Model::validate($focus->column_fields) || ($recurringDataChanged && empty($recurrenceObject))) {
            ITS4YouCalendar_RepeatRecords_Model::repeatFromRequest($focus, $this->recurrenceDatabaseObject);
        }
    }
}