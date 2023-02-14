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
     * @param object $focus
     * @param object|bool $recurrenceObjectDatabase
     * @return void
     */
    public static function repeatFromRequest(object $focus, $recurrenceObjectDatabase = false)
    {
        $focusRecordId = (int)$focus->column_fields['id'];
        $recurrenceObject = Vtiger_Functions::getRecurringObjValue();

        self::$recurringDataChanged = self::checkRecurringDataChanged($recurrenceObject, $recurrenceObjectDatabase);

        if (self::$recurringDataChanged && $recurrenceObjectDatabase && $recurrenceObject->recur_type != $recurrenceObjectDatabase->recur_type) {
            self::$recurringTypeChanged = true;
        } else {
            self::$recurringTypeChanged = false;
        }

        if(empty($recurrenceObjectDatabase) && self::$recurringDataChanged) {
            ITS4YouCalendar_Recurrence_Model::saveRelation($focusRecordId, $focusRecordId);
        }

        if (self::validate($focus->column_fields)) {
            self::repeat($focus, $recurrenceObject);
        } elseif (empty($recurrenceObject) && self::$recurringDataChanged) {
            self::delete($focusRecordId);
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
     * Repeat Activity instance till given limit.
     */
    public static function repeat(object $focus, object $recurrenceObject)
    {
        $adb = PearDatabase::getInstance();
        $parentId = $focus->column_fields['id'];
        $parentModule = getSalesEntityType($parentId);

        $recurrenceMode = $focus->column_fields['recurringEditMode'];

        list($focusStartDate, $focusStartTime) = explode(' ', $focus->column_fields[self::$dateStartField]);
        list($focusEndDate, $focusEndTime) = explode(' ', $focus->column_fields[self::$dateEndField]);

        $interval = strtotime($focusEndDate) - strtotime($focusStartDate);

        $base_focus = CRMEntity::getInstance($parentModule);
        $base_focus->column_fields = $focus->column_fields;
        $base_focus->id = $focus->id;

        $vtEntityDelta = new VTEntityDelta();
        $delta = $vtEntityDelta->getEntityDelta($parentModule, $parentId, true);

        $skip_focus_fields = array('record_id', 'createdtime', 'modifiedtime', 'id');

        if ('edit' === $focus->column_fields['mode']) {
            $childRecords = array();
            $result = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE record_id=?', array($parentId));
            $numberOfRows = $adb->num_rows($result);
            $parentRecurringId = $parentId;

            if ($numberOfRows <= 0) {
                $queryResult = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE recurrence_id=?', array($parentId));

                if ($adb->num_rows($queryResult) > 0) {
                    $parentRecurringId = $adb->query_result($queryResult, 0, 'record_id');
                    $result = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE record_id=?', array($parentRecurringId));
                    $numberOfRows = $adb->num_rows($result);

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

            for ($i = 0; $i < $numberOfRows; $i++) {
                $childRecords[] = $adb->query_result($result, $i, 'recurrence_id');
            }

            if ('future' === $recurrenceMode) {
                $parentKey = array_keys($childRecords, $parentId);
                $childRecords = array_slice($childRecords, $parentKey[0]);
            }


            $i = 0;
            $updatedRecords = array();

            if (self::$recurringTypeChanged && 'future' === $recurrenceMode) {
                foreach ($childRecords as $childRecordId) {
                    ITS4YouCalendar_Recurrence_Model::deleteRelation($childRecordId);
                }

                $parentRecurringId = $parentId;
            }

            foreach ($recurrenceObject->recurringdates as $index => $startDate) {
                $recordId = $childRecords[$i];

                if (!empty($recordId) && !empty($startDate)) {
                    $i++;

                    if (!self::$recurringDataChanged && empty($delta[self::$dateStartField]) && empty($delta[self::$dateEndField])) {
                        $skip_focus_fields[] = self::$dateStartField;
                        $skip_focus_fields[] = self::$dateEndField;
                    }

                    if ($index == 0 && $focusStartDate == $startDate && 'future' !== $recurrenceMode) {
                        $updatedRecords[] = $recordId;
                        continue;
                    }

                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                    $recordModel->set('mode', 'edit');
                    list($recordDateStart, $recordTimeStart) = explode(' ', $recordModel->get(self::$dateStartField));

                    if ('future' === $recurrenceMode && $recordDateStart >= $focusStartDate) {
                        $endDateTime = strtotime($startDate) + $interval;
                        $endDate = date('Y-m-d', $endDateTime);

                        foreach ($base_focus->column_fields as $key => $value) {
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

                        $recordModel->set('id', $recordId);
                        $updatedRecords[] = $recordId;
                        $recordModel->save();

                        if (self::$recurringTypeChanged) {
                            ITS4YouCalendar_Recurrence_Model::saveRelation($parentId, $recordId);
                        }
                    } elseif ('all' === $recurrenceMode) {
                        $endDateTime = strtotime($startDate) + $interval;
                        $endDate = date('Y-m-d', $endDateTime);

                        foreach ($base_focus->column_fields as $key => $value) {
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

                        $recordModel->set('id', $recordId);
                        $updatedRecords[] = $recordId;
                        $recordModel->save();
                    }
                } elseif (empty($recordId) && !empty($startDate) && self::$recurringDataChanged) {
                    $datesList = array();
                    $datesList[] = $startDate;

                    self::createRecurringEvents($focus, $recurrenceObject, $datesList, $parentRecurringId);
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

            self::createRecurringEvents($focus, $recurrenceObject, $recurringDates);
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
     * @param object $focus
     * @param object $recurrenceObject
     * @param array $recurringDates
     * @param int|bool $parentId
     * @return void
     */
    public static function createRecurringEvents(object $focus, object $recurrenceObject, array $recurringDates, $parentId = false)
    {
        if (empty($parentId)) {
            $parentId = $focus->column_fields['id'];
        }

        $parentModule = getSalesEntityType($parentId);
        $skip_focus_fields = array('record_id', 'createdtime', 'modifiedtime', 'mode', 'id', 'deleted');

        list($recordStartDate, $recordStartTime) = explode(' ', $focus->column_fields[self::$dateStartField]);
        list($recordEndDate, $recordEndTime) = explode(' ', $focus->column_fields[self::$dateEndField]);

        $interval = strtotime($recordEndDate) - strtotime($recordStartDate);

        foreach ($recurringDates as $startDate) {
            $endDateTime = strtotime($startDate) + $interval;
            $endDate = date('Y-m-d', $endDateTime);

            $recordModel = Vtiger_Record_Model::getCleanInstance($parentModule);

            foreach ($focus->column_fields as $key => $value) {
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
     * @param int $focusRecordId
     * @return void
     */
    public static function delete(int $focusRecordId)
    {
        $recurringRecordsList = ITS4YouCalendar_Recurrence_Model::getRecurringRecordsList($focusRecordId);

        foreach ($recurringRecordsList as $recordIds) {
            foreach ($recordIds as $recordId) {
                ITS4YouCalendar_Recurrence_Model::deleteRelation($recordId);

                if ((int)$recordId !== $focusRecordId) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                    $recordModel->delete();
                }
            }
        }
    }
}