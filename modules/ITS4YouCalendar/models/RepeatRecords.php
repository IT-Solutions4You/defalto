<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_RepeatRecords_Model
{
    public static string $dateStartField = 'datetime_start';
    public static string $dateEndField = 'datetime_end';
    public static bool $recurringDataChanged = false;
    public static bool $recurringTypeChanged = false;

    /**
     * @param object $parentRecordModel
     * @param object|bool $recurrenceObjectDatabase
     * @return void
     */
    public static function repeatFromRequest(object $parentRecordModel, $recurrenceObjectDatabase = false)
    {
        $parentRecordId = $parentRecordModel->getId();
        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();

        self::$recurringDataChanged = self::checkRecurringDataChanged($recurrenceObject, $recurrenceObjectDatabase);

        if (self::$recurringDataChanged && $recurrenceObjectDatabase && $recurrenceObject->recur_type != $recurrenceObjectDatabase->recur_type) {
            self::$recurringTypeChanged = true;
        } else {
            self::$recurringTypeChanged = false;
        }

        if(empty($recurrenceObjectDatabase) && self::$recurringDataChanged) {
            ITS4YouCalendar_Recurrence_Model::saveRelation($parentRecordId, $parentRecordId);
        }

        if (self::validate($parentRecordModel->getData())) {
            self::repeat($parentRecordModel, $recurrenceObject);
        } elseif (empty($recurrenceObject) && self::$recurringDataChanged) {
            self::delete($parentRecordId);
        }
    }

    /**
     * @param array|TrackableObject $data
     * @return bool
     */
    public static function validate($data): bool
    {
        return !empty($data['recurringtype']) && '--None--' !== $data['recurringtype'] && 'current' !== $data['recurringEditMode'];
    }

    /**
     * @param object $recurrenceObjectRequest
     * @param object|bool $recurrenceObjectDatabase
     * @return bool
     */
    public static function checkRecurringDataChanged(object $recurrenceObjectRequest, $recurrenceObjectDatabase): bool
    {
        if ($recurrenceObjectDatabase
            && ($recurrenceObjectRequest->recur_type == $recurrenceObjectDatabase->recur_type)
            && ($recurrenceObjectRequest->recur_freq == $recurrenceObjectDatabase->recur_freq)
            && ($recurrenceObjectRequest->recurringdates[0] == $recurrenceObjectDatabase->recurringdates[0])
            && ($recurrenceObjectRequest->recurringenddate == $recurrenceObjectDatabase->recurringenddate)
            && ($recurrenceObjectRequest->dayofweek_to_rpt == $recurrenceObjectDatabase->dayofweek_to_rpt)
            && ($recurrenceObjectRequest->repeat_monthby == $recurrenceObjectDatabase->repeat_monthby)
            && ($recurrenceObjectRequest->rptmonth_datevalue == $recurrenceObjectDatabase->rptmonth_datevalue)
            && ($recurrenceObjectRequest->rptmonth_daytype == $recurrenceObjectDatabase->rptmonth_daytype)
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @var $parentRecordModel Vtiger_Record_Model
     * Repeat Activity instance till given limit.
     */
    public static function repeat(object $parentRecordModel, object $recurrenceObject)
    {
        $adb = PearDatabase::getInstance();
        $parentId = $parentRecordModel->getId();
        $parentModule = $parentRecordModel->getModuleName();
        $recurrenceMode = $parentRecordModel->get('recurringEditMode');

        list($parentStartDate, $parentStartTime) = explode(' ', $parentRecordModel->get(self::$dateStartField));
        list($parentEndDate, $parentEndTime) = explode(' ', $parentRecordModel->get(self::$dateEndField));

        $interval = strtotime($parentEndDate) - strtotime($parentStartDate);
        $vtEntityDelta = new VTEntityDelta();
        $delta = $vtEntityDelta->getEntityDelta($parentModule, $parentId, true);

        $skip_focus_fields = array('mode', 'record_id', 'createdtime', 'modifiedtime', 'id');

        if ('edit' === $parentRecordModel->get('mode')) {
            $childQuery = 'SELECT * FROM its4you_recurring_rel WHERE record_id=?';
            $childResult = $adb->pquery($childQuery, array($parentId));
            $parentRecurringId = $parentId;

            if (!$adb->num_rows($childResult)) {
                $queryResult = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE recurrence_id=?', array($parentId));

                if ($adb->num_rows($queryResult)) {
                    $parentRecurringId = $adb->query_result($queryResult, 0, 'record_id');
                    $childResult = $adb->pquery($childQuery, array($parentRecurringId));

                    if ('all' === $recurrenceMode) {
                        $parentModel = Vtiger_Record_Model::getInstanceById($parentId);
                        $parentResult = $adb->pquery('SELECT 1 FROM its4you_recurring_rel WHERE recurrence_id=?', array($parentRecurringId));

                        if ($adb->num_rows($parentResult)) {
                            $parentModel = Vtiger_Record_Model::getInstanceById($parentRecurringId);
                        } else {
                            $recurringRecordsList = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsList($parentId);

                            foreach ($recurringRecordsList as $parentRecurringId => $recurringRecords) {
                                $parentModel = Vtiger_Record_Model::getInstanceById($recurringRecords[0]);
                            }
                        }

                        list($parentDateStart, $parentTimeStart) = explode(' ', $parentModel->get(self::$dateStartField));

                        $_REQUEST['date_start'] = $parentDateStart;
                        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();
                    }
                }
            }

            $childResult = $adb->pquery($childQuery, array($parentRecurringId));
            $childRecords = array();

            while ($row = $adb->fetchByAssoc($childResult)) {
                $childRecords[] = $row['recurrence_id'];
            }

            if ('future' === $recurrenceMode) {
                $parentKey = array_keys($childRecords, $parentId);
                $childRecords = array_slice($childRecords, $parentKey[0]);
            }

            $updatedRecords = array();

            if (self::$recurringTypeChanged && 'future' === $recurrenceMode) {
                foreach ($childRecords as $childRecordId) {
                    ITS4YouCalendar_Recurrence_Model::deleteRelation($childRecordId);
                }

                $parentRecurringId = $parentId;
            }

            $dateIndex = 0;

            foreach ($recurrenceObject->recurringdates as $startDate) {
                $recordId = $childRecords[$dateIndex];

                if(empty($startDate)) {
                    continue;
                }

                if (!empty($recordId)) {
                    $dateIndex++;

                    if (!self::$recurringDataChanged && empty($delta[self::$dateStartField]) && empty($delta[self::$dateEndField])) {
                        $skip_focus_fields[] = self::$dateStartField;
                        $skip_focus_fields[] = self::$dateEndField;
                    }

                    if ($dateIndex == 0 && $parentStartDate == $startDate && 'future' !== $recurrenceMode) {
                        $updatedRecords[] = $recordId;
                        continue;
                    }

                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                    list($recordDateStart, $recordTimeStart) = explode(' ', $recordModel->get(self::$dateStartField));

                    if ('future' === $recurrenceMode && $recordDateStart >= $parentStartDate) {
                        $endDateTime = strtotime($startDate) + $interval;
                        $endDate = date('Y-m-d', $endDateTime);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if (in_array($key, $skip_focus_fields)) {
                                // skip copying few fields
                            } elseif ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::updateDateField($value, $startDate));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::updateDateField($value, $endDate));
                            } elseif (!empty($delta[$key])) {
                                $recordModel->set($key, $value);
                            }
                        }

                        $updatedRecords[] = $recordId;

                        $recordModel->set('id', $recordId);
                        $recordModel->set('mode', 'edit');
                        $recordModel->save();

                        if (self::$recurringTypeChanged) {
                            ITS4YouCalendar_Recurrence_Model::saveRelation($parentId, $recordId);
                        }
                    } elseif ('all' === $recurrenceMode) {
                        $endDateTime = strtotime($startDate) + $interval;
                        $endDate = date('Y-m-d', $endDateTime);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if (in_array($key, $skip_focus_fields)) {
                                // skip copying few fields
                            } elseif ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::updateDateField($value, $startDate));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::updateDateField($value, $endDate));
                            } elseif (!empty($delta[$key])) {
                                $recordModel->set($key, $value);
                            }
                        }

                        $updatedRecords[] = $recordId;

                        $recordModel->set('id', $recordId);
                        $recordModel->set('mode', 'edit');
                        $recordModel->save();
                    }
                } elseif (self::$recurringDataChanged) {
                    $datesList = array();
                    $datesList[] = $startDate;

                    self::createRecurringEvents($parentRecordModel, $recurrenceObject, $datesList, $parentRecurringId);
                }
            }

            $deletingRecords = array_diff($childRecords, $updatedRecords);

            if (self::$recurringDataChanged && !empty($deletingRecords)) {
                foreach ($deletingRecords as $deletingRecord) {
                    ITS4YouCalendar_Recurrence_Model::deleteRelation($deletingRecord);

                    $recordModel = Vtiger_Record_Model::getInstanceById($deletingRecord);
                    $recordModel->delete();
                }
            }
        } else {
            $recurringDates = $recurrenceObject->recurringdates;

            self::createRecurringEvents($parentRecordModel, $recurrenceObject, $recurringDates);
        }
    }

    /**
     * @param string $value
     * @param string $newValue
     * @return string
     */
    public static function updateDateField(string $value, string $newValue): string
    {
        $dateValue = explode(' ', $value);
        $dateValue[0] = $newValue;

        return trim(implode(' ', $dateValue));
    }

    /**
     * @param Vtiger_Record_Model $parentRecordModel
     * @param object $recurrenceObject
     * @param array $recurringDates
     * @param int|bool $parentId
     * @return void
     */
    public static function createRecurringEvents(object $parentRecordModel, object $recurrenceObject, array $recurringDates, $parentId = false)
    {
        if (empty($parentId)) {
            $parentId = $parentRecordModel->getId();
        }

        $parentModule = $parentRecordModel->getModuleName();
        $skip_focus_fields = array('record_id', 'createdtime', 'modifiedtime', 'mode', 'id', 'deleted');

        list($recordStartDate, $recordStartTime) = explode(' ', $parentRecordModel->get(self::$dateStartField));
        list($recordEndDate, $recordEndTime) = explode(' ', $parentRecordModel->get(self::$dateEndField));

        $interval = strtotime($recordEndDate) - strtotime($recordStartDate);

        foreach ($recurringDates as $index => $startDate) {
            if(0 === $index  && $recordStartDate === $startDate) {
                continue;
            }

            $endDateTime = strtotime($startDate) + $interval;
            $endDate = date('Y-m-d', $endDateTime);

            $recordModel = Vtiger_Record_Model::getCleanInstance($parentModule);

            foreach ($parentRecordModel->getData() as $key => $value) {
                if (in_array($key, $skip_focus_fields)) {
                    // skip copying few fields
                } elseif ($key === self::$dateStartField) {
                    $recordModel->set(self::$dateStartField, self::updateDateField($value, $startDate));
                } elseif ($key === self::$dateEndField) {
                    $recordModel->set(self::$dateEndField, self::updateDateField($value, $endDate));
                } else {
                    $recordModel->set($key, $value);
                }
            }

            $recordModel->save();
            $recordId = (int)$recordModel->getId();

            ITS4YouCalendar_Recurrence_Model::saveRelation($parentId, $recordId);
        }
    }

    /**
     * @param int $recordId
     * @return void
     */
    public static function delete(int $recordId)
    {
        $recurringRecordsList = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsList($recordId);

        foreach ($recurringRecordsList as $recurrenceIds) {
            foreach ($recurrenceIds as $recurrenceId) {
                ITS4YouCalendar_Recurrence_Model::deleteRelation($recurrenceId);

                if ((int)$recurrenceId !== $recordId) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recurrenceId);
                    $recordModel->delete();
                }
            }
        }
    }
}