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

        $parentDatabaseStartDateTime = $parentRecordModel->get(self::$dateStartField);
        list($parentDatabaseStartDate, $parentDatabaseStartTime) = explode(' ', $parentDatabaseStartDateTime);

        $parentDatabaseEndDateTime = $parentRecordModel->get(self::$dateEndField);
        list($parentDatabaseEndDate, $parentDatabaseEndTime) = explode(' ', $parentDatabaseEndDateTime);

        $interval = strtotime($parentDatabaseEndDateTime) - strtotime($parentDatabaseStartDateTime);
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
                            $recurringRecordsList = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsList($parentId);

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
                    ITS4YouCalendar_Recurrence_Model::deleteRelation($childRecordId);
                }

                $parentRecurringId = $parentId;
            }

            $dateIndex = 0;

            foreach ($recurrenceObject->recurringdates as $databaseStartDate) {
                $recordId = $childRecords[$dateIndex];

                if(empty($databaseStartDate)) {
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
                    list($recordDatabaseStartDate, $recordDatabaseStartTime) = explode(' ', $recordModel->get(self::$dateStartField));

                    if ('future' === $recurrenceMode && $recordDatabaseStartDate >= $parentDatabaseStartDate) {
                        $databaseEndDate = date('Y-m-d', strtotime($databaseStartDate) + $interval);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::getUserDateTime($databaseStartDate, $parentDatabaseStartTime));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::getUserDateTime($databaseEndDate, $parentDatabaseEndTime));
                            } elseif (!in_array($key, $skip_focus_fields) && !empty($delta[$key])) {
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
                        $databaseEndDate = date('Y-m-d', strtotime($databaseStartDate) + $interval);

                        foreach ($parentRecordModel->getData() as $key => $value) {
                            if ($key === self::$dateStartField) {
                                $recordModel->set(self::$dateStartField, self::getUserDateTime($databaseStartDate, $parentDatabaseStartTime));
                            } elseif ($key === self::$dateEndField) {
                                $recordModel->set(self::$dateEndField, self::getUserDateTime($databaseEndDate, $parentDatabaseEndTime));
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
        $skip_focus_fields = ['record_id', 'createdtime', 'modifiedtime', 'mode', 'id', 'deleted'];

        $parentDatabaseStartDateTime = $parentRecordModel->get(self::$dateStartField);
        list($parentDatabaseStartDate, $parentDatabaseStartTime) = explode(' ', $parentDatabaseStartDateTime);

        $parentDatabaseEndDateTime = $parentRecordModel->get(self::$dateEndField);
        list($parentDatabaseEndDate, $parentDatabaseEndTime) = explode(' ', $parentDatabaseEndDateTime);

        $interval = strtotime($parentDatabaseEndDateTime) - strtotime($parentDatabaseStartDateTime);

        foreach ($recurringDates as $index => $databaseStartDate) {
            if (0 === $index && $parentDatabaseStartDate === $databaseStartDate) {
                continue;
            }

            $databaseEndDate = date('Y-m-d', strtotime($databaseStartDate) + $interval);
            $recordModel = Vtiger_Record_Model::getCleanInstance($parentModule);

            foreach ($parentRecordModel->getData() as $key => $value) {
                if ($key === self::$dateStartField) {
                    $recordModel->set(self::$dateStartField, self::getUserDateTime($databaseStartDate, $parentDatabaseStartTime));
                } elseif ($key === self::$dateEndField) {
                    $recordModel->set(self::$dateEndField, self::getUserDateTime($databaseEndDate, $parentDatabaseEndTime));
                } elseif (!in_array($key, $skip_focus_fields)) {
                    $recordModel->set($key, $value);
                }
            }

            $recordModel->save();
            $recordId = intval($recordModel->getId());

            ITS4YouCalendar_Recurrence_Model::saveRelation($parentId, $recordId);
        }
    }

    public static function getUserDateTime($date, $time)
    {
        $dateTime = new DateTimeField($date . ' ' . $time);

        return $dateTime->getDisplayDate() . ' ' . $dateTime->getDisplayTime();
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

                if (intval($recurrenceId) !== $recordId) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recurrenceId);
                    $recordModel->delete();
                }
            }
        }
    }
}