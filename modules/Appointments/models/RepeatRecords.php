<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_RepeatRecords_Model
{
    /**
     * @var string
     */
    public static string $dateEndField = 'datetime_end';
    /**
     * @var string
     */
    public static string $dateStartField = 'datetime_start';
    /**
     * @var bool
     */
    public static bool $recurringDataChanged = false;
    /**
     * @var bool
     */
    public static bool $recurringTypeChanged = false;

    /**
     * @param object      $recurrenceObjectRequest
     * @param object|bool $recurrenceObjectDatabase
     *
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
     * @param Vtiger_Record_Model $parentRecordModel
     * @param object              $recurrenceObject
     * @param array               $recurringDates
     * @param int|bool            $parentId
     *
     * @return void
     */
    public static function createRecurringEvents(object $parentRecordModel, object $recurrenceObject, array $recurringDates, $parentId = false)
    {
        if (empty($parentId)) {
            $parentId = $parentRecordModel->getId();
        }

        $parentModule = $parentRecordModel->getModuleName();
        $skip_focus_fields = ['record_id', 'createdtime', 'modifiedtime', 'mode', 'id', 'deleted'];

        $parentDatabaseStartDateTime = $parentRecordModel->get(self::$dateStartField);
        [$parentDatabaseStartDate, $parentDatabaseStartTime] = explode(' ', $parentDatabaseStartDateTime);

        $parentDatabaseEndDateTime = $parentRecordModel->get(self::$dateEndField);
        [$parentDatabaseEndDate, $parentDatabaseEndTime] = explode(' ', $parentDatabaseEndDateTime);

        $isAllDay = $parentRecordModel instanceof Appointments_Record_Model && $parentRecordModel->isAllDayEvent();
        $interval = self::getRepeatInterval($parentDatabaseStartDateTime, $parentDatabaseEndDateTime, $isAllDay);

        foreach ($recurringDates as $index => $databaseStartDate) {
            if (0 === $index && $parentDatabaseStartDate === $databaseStartDate) {
                continue;
            }

            $databaseEndDate = self::getRepeatEndDate($databaseStartDate, $interval, $isAllDay);
            $recordModel = Vtiger_Record_Model::getCleanInstance($parentModule);

            foreach ($parentRecordModel->getData() as $key => $value) {
                if ($key === self::$dateStartField) {
                    $recordModel->set(self::$dateStartField, self::getRepeatDateTime($databaseStartDate, $parentDatabaseStartTime, $isAllDay));
                } elseif ($key === self::$dateEndField) {
                    $recordModel->set(self::$dateEndField, self::getRepeatDateTime($databaseEndDate, $parentDatabaseEndTime, $isAllDay));
                } elseif (!in_array($key, $skip_focus_fields)) {
                    $recordModel->set($key, $value);
                }
            }

            $recordModel->save();
            $recordId = intval($recordModel->getId());

            Appointments_Recurrence_Model::saveRelation($parentId, $recordId);
        }
    }

    /**
     * @param int $recordId
     *
     * @return void
     */
    public static function delete(int $recordId)
    {
        $recurringRecordsList = Appointments_Recurrence_Model::getRecurringRecordsList($recordId);

        foreach ($recurringRecordsList as $recurrenceIds) {
            foreach ($recurrenceIds as $recurrenceId) {
                Appointments_Recurrence_Model::deleteRelation($recurrenceId);

                if (intval($recurrenceId) !== $recordId) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recurrenceId);
                    $recordModel->delete();
                }
            }
        }
    }

    /**
     * @param $date
     * @param $time
     *
     * @return string
     */
    public static function getUserDateTime($date, $time)
    {
        $dateTime = new DateTimeField($date . ' ' . $time);

        return $dateTime->getDisplayDate() . ' ' . $dateTime->getDisplayTime();
    }

    /**
     * @param string $date
     * @param string $time
     * @param bool   $isAllDay
     *
     * @return string
     */
    public static function getRepeatDateTime(string $date, string $time, bool $isAllDay): string
    {
        if ($isAllDay) {
            return $date . ' 00:00:00';
        }

        return self::getUserDateTime($date, $time);
    }

    /**
     * @param string $startDateTime
     * @param string $endDateTime
     * @param bool   $isAllDay
     *
     * @return int
     */
    public static function getRepeatInterval(string $startDateTime, string $endDateTime, bool $isAllDay): int
    {
        if ($isAllDay) {
            $startDate = new DateTimeImmutable(explode(' ', $startDateTime)[0]);
            $endDate = new DateTimeImmutable(explode(' ', $endDateTime)[0]);

            return (int)$startDate->diff($endDate)->days;
        }

        return strtotime($endDateTime) - strtotime($startDateTime);
    }

    /**
     * @param string $startDate
     * @param int    $interval
     * @param bool   $isAllDay
     *
     * @return string
     */
    public static function getRepeatEndDate(string $startDate, int $interval, bool $isAllDay): string
    {
        if ($isAllDay) {
            return (new DateTimeImmutable($startDate))->modify('+' . $interval . ' days')->format('Y-m-d');
        }

        return date('Y-m-d', strtotime($startDate) + $interval);
    }

    /**
     * @var $parentRecordModel Vtiger_Record_Model
     *                         Repeat Activity instance till given limit.
     */
    public static function repeat(object $parentRecordModel, object $recurrenceObject)
    {
        $adb = PearDatabase::getInstance();
        $parentId = $parentRecordModel->getId();
        $parentModule = $parentRecordModel->getModuleName();
        $recurrenceMode = $parentRecordModel->get('recurringEditMode');

        $parentDatabaseStartDateTime = $parentRecordModel->get(self::$dateStartField);
        [$parentDatabaseStartDate, $parentDatabaseStartTime] = explode(' ', $parentDatabaseStartDateTime);

        $parentDatabaseEndDateTime = $parentRecordModel->get(self::$dateEndField);
        [$parentDatabaseEndDate, $parentDatabaseEndTime] = explode(' ', $parentDatabaseEndDateTime);

        $isAllDay = $parentRecordModel instanceof Appointments_Record_Model && $parentRecordModel->isAllDayEvent();
        $interval = self::getRepeatInterval($parentDatabaseStartDateTime, $parentDatabaseEndDateTime, $isAllDay);
        $vtEntityDelta = new VTEntityDelta();
        $delta = $vtEntityDelta->getEntityDelta($parentModule, $parentId, true);

        $skip_focus_fields = ['mode', 'record_id', 'createdtime', 'modifiedtime', 'id'];

        if ('edit' === $parentRecordModel->get('mode')) {
            $childQuery = 'SELECT * FROM its4you_recurring_rel WHERE record_id=?';
            $childResult = $adb->pquery($childQuery, [$parentId]);
            $parentRecurringId = $parentId;

            if (!$adb->num_rows($childResult)) {
                $queryResult = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE recurrence_id=?', [$parentId]);

                if ($adb->num_rows($queryResult)) {
                    $parentRecurringId = $adb->query_result($queryResult, 0, 'record_id');
                    $childResult = $adb->pquery($childQuery, [$parentRecurringId]);

                    if ('all' === $recurrenceMode) {
                        $parentModel = Vtiger_Record_Model::getInstanceById($parentId);
                        $parentResult = $adb->pquery('SELECT 1 FROM its4you_recurring_rel WHERE recurrence_id=?', [$parentRecurringId]);

                        if ($adb->num_rows($parentResult)) {
                            $parentModel = Vtiger_Record_Model::getInstanceById($parentRecurringId);
                        } else {
                            $recurringRecordsList = Appointments_Recurrence_Model::getRecurringRecordsList($parentId);

                            foreach ($recurringRecordsList as $parentRecurringId => $recurringRecords) {
                                $parentModel = Vtiger_Record_Model::getInstanceById($recurringRecords[0]);
                            }
                        }

                        $_REQUEST['date_start'] = explode(' ', $parentModel->getDisplayValue(self::$dateStartField))[0];
                        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();
                    }
                }
            }

            $childResult = $adb->pquery($childQuery, [$parentRecurringId]);
            $childRecords = [];

            while ($row = $adb->fetchByAssoc($childResult)) {
                $childRecords[] = $row['recurrence_id'];
            }

            if ('future' === $recurrenceMode) {
                $parentKey = array_keys($childRecords, $parentId);
                $childRecords = array_slice($childRecords, $parentKey[0]);
            }

            $updatedRecords = [];

            if (self::$recurringTypeChanged && 'future' === $recurrenceMode) {
                foreach ($childRecords as $childRecordId) {
                    Appointments_Recurrence_Model::deleteRelation($childRecordId);
                }

                $parentRecurringId = $parentId;
            }

            $dateIndex = 0;

            foreach ($recurrenceObject->recurringdates as $databaseStartDate) {
                $recordId = $childRecords[$dateIndex];

                if (empty($databaseStartDate)) {
                    continue;
                }

                if (!empty($recordId)) {
                    $dateIndex++;

                    if (!self::$recurringDataChanged && empty($delta[self::$dateStartField]) && empty($delta[self::$dateEndField])) {
                        $skip_focus_fields[] = self::$dateStartField;
                        $skip_focus_fields[] = self::$dateEndField;
                    }

                    if ($dateIndex == 0 && $parentDatabaseStartDate == $databaseStartDate && 'future' !== $recurrenceMode) {
                        $updatedRecords[] = $recordId;
                        continue;
                    }

                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                    [$recordDatabaseStartDate, $recordDatabaseStartTime] = explode(' ', $recordModel->get(self::$dateStartField));

                    if ('future' === $recurrenceMode && $recordDatabaseStartDate >= $parentDatabaseStartDate) {
                        $databaseEndDate = self::getRepeatEndDate($databaseStartDate, $interval, $isAllDay);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::getRepeatDateTime($databaseStartDate, $parentDatabaseStartTime, $isAllDay));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::getRepeatDateTime($databaseEndDate, $parentDatabaseEndTime, $isAllDay));
                            } elseif (!in_array($key, $skip_focus_fields) && !empty($delta[$key])) {
                                $recordModel->set($key, $value);
                            }
                        }

                        $updatedRecords[] = $recordId;

                        $recordModel->set('id', $recordId);
                        $recordModel->set('mode', 'edit');
                        $recordModel->save();

                        if (self::$recurringTypeChanged) {
                            Appointments_Recurrence_Model::saveRelation($parentId, $recordId);
                        }
                    } elseif ('all' === $recurrenceMode) {
                        $databaseEndDate = self::getRepeatEndDate($databaseStartDate, $interval, $isAllDay);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::getRepeatDateTime($databaseStartDate, $parentDatabaseStartTime, $isAllDay));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::getRepeatDateTime($databaseEndDate, $parentDatabaseEndTime, $isAllDay));
                            } elseif (!in_array($key, $skip_focus_fields) && !empty($delta[$key])) {
                                $recordModel->set($key, $value);
                            }
                        }

                        $updatedRecords[] = $recordId;

                        $recordModel->set('id', $recordId);
                        $recordModel->set('mode', 'edit');
                        $recordModel->save();
                    }
                } elseif (self::$recurringDataChanged) {
                    $datesList = [];
                    $datesList[] = $databaseStartDate;

                    self::createRecurringEvents($parentRecordModel, $recurrenceObject, $datesList, $parentRecurringId);
                }
            }

            $deletingRecords = array_diff($childRecords, $updatedRecords);

            if (self::$recurringDataChanged && !empty($deletingRecords)) {
                foreach ($deletingRecords as $deletingRecord) {
                    Appointments_Recurrence_Model::deleteRelation($deletingRecord);

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
     * @param object      $parentRecordModel
     * @param object|bool $recurrenceObjectDatabase
     *
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

        if (empty($recurrenceObjectDatabase) && self::$recurringDataChanged) {
            if (!Appointments_Recurrence_Model::isRelationExists($parentRecordId, $parentRecordId)) {
                Appointments_Recurrence_Model::saveRelation($parentRecordId, $parentRecordId);
            }
        }

        if (self::validate($parentRecordModel->getData())) {
            self::repeat($parentRecordModel, $recurrenceObject);
        } elseif (empty($recurrenceObject) && self::$recurringDataChanged) {
            self::delete($parentRecordId);
        }
    }

    /**
     * @param string $value
     * @param string $newValue
     *
     * @return string
     */
    public static function updateDateField(string $value, string $newValue): string
    {
        $dateValue = explode(' ', $value);
        $dateValue[0] = $newValue;

        return trim(implode(' ', $dateValue));
    }

    /**
     * @param array|TrackableObject $data
     *
     * @return bool
     */
    public static function validate($data): bool
    {
        return !empty($data['recurringtype']) && '--None--' !== $data['recurringtype'] && 'current' !== $data['recurringEditMode'];
    }
}
