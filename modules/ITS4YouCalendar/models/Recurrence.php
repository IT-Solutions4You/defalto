<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_Recurrence_Model extends Vtiger_Base_Model
{
    public PearDatabase $adb;
    public string $tableIndex = 'recurring_id';
    public string $table = 'its4you_recurring';

    public static function getRecurringRecordsList($recordId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM its4you_recurring_rel WHERE record_id=? OR record_id = (SELECT record_id FROM its4you_recurring_rel WHERE recurrence_id=?)', array($recordId, $recordId));
        $numberOfRows = $adb->num_rows($result);
        $parentRecurringId = $adb->query_result($result, 0, 'record_id');
        $childRecords = array();

        for ($i = 0; $i < $numberOfRows; $i++) {
            $childRecords[] = $adb->query_result($result, $i, 'recurrence_id');
        }

        return [
            $parentRecurringId => $childRecords,
        ];
    }

    public static function getRecurrenceInformation($request): array
    {
        $recordId = $request->get('record');
        $recurringObject = self::getRecurringObject($recordId);

        if ($recurringObject) {
            $recurringData['recurringcheck'] = 'Yes';
            $recurringData['repeat_frequency'] = $recurringObject->getRecurringFrequency();
            $recurringData['eventrecurringtype'] = $recurringObject->getRecurringType();
            $recurringEndDate = $recurringObject->getRecurringEndDate();

            if (!empty($recurringEndDate)) {
                $recurringData['recurringenddate'] = $recurringEndDate->get_formatted_date();
            }

            $recurringInfo = $recurringObject->getUserRecurringInfo();

            if ($recurringObject->getRecurringType() == 'Weekly') {
                $noOfDays = php7_count($recurringInfo['dayofweek_to_repeat']);

                for ($i = 0; $i < $noOfDays; ++$i) {
                    $recurringData['week' . $recurringInfo['dayofweek_to_repeat'][$i]] = 'checked';
                }
            } elseif ($recurringObject->getRecurringType() == 'Monthly') {
                $recurringData['repeatMonth'] = $recurringInfo['repeatmonth_type'];

                if ($recurringInfo['repeatmonth_type'] == 'date') {
                    $recurringData['repeatMonth_date'] = $recurringInfo['repeatmonth_date'];
                } else {
                    $recurringData['repeatMonth_daytype'] = $recurringInfo['repeatmonth_daytype'];
                    $recurringData['repeatMonth_day'] = $recurringInfo['dayofweek_to_repeat'][0];
                }
            }
        } else {
            $recurringData['recurringcheck'] = 'No';
        }

        return $recurringData;
    }

    public static function getRecurringObject($recordId)
    {
        if (empty($recordId)) {
            return false;
        }

        $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $recurringModel = self::getInstanceByRecord($recordId);

        if ($recurringModel->isExists()) {
            list($date_start, $time_start) = explode(' ', $recordModel->get('datetime_start'));
            list($date_end, $time_end) = explode(' ', $recordModel->get('datetime_end'));

            return RecurringType::fromDBRequest([
                'date_start' => $date_start,
                'time_start' => $time_start,
                'due_date' => $date_end,
                'time_end' => $time_end,
                'recurringtype' => $recurringModel->get('recurring_type'),
                'recurringfreq' => $recurringModel->get('recurring_frequency'),
                'recurringenddate' => $recurringModel->get('recurring_end_date'),
                'recurringinfo' => $recurringModel->get('recurring_info'),
            ]);
        }


        return false;
    }

    /**
     * @param int $recordId
     * @return ITS4YouCalendar_Recurrence_Model
     */
    public static function getInstanceByRecord(int $recordId): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->set('record_id', $recordId);
        $instance->retrieveId();
        $instance->retrieveData();

        return $instance;
    }

    public function retrieveId()
    {
        $result = $this->adb->pquery('SELECT record_id FROM its4you_recurring_rel WHERE recurrence_id=?', [$this->get('record_id')]);

        if ($this->adb->num_rows($result)) {
            $this->set('record_id', (int)$this->adb->query_result($result, 0, 'record_id'));
        }
    }

    public function retrieveData()
    {
        $result = $this->adb->pquery('SELECT * FROM its4you_recurring WHERE record_id=?', [$this->get('record_id')]);

        if ($this->adb->num_rows($result)) {
            $this->setData((array)$this->adb->fetchByAssoc($result));
        }
    }

    public function isExists(): bool
    {
        return !$this->isEmpty($this->tableIndex);
    }

    /**
     * @param int $recordId - first recurrence record
     * @param int $recurrenceId - other or first recurrence record
     * @return void
     */
    public static function saveRelation(int $recordId, int $recurrenceId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'INSERT INTO its4you_recurring_rel (record_id,recurrence_id) VALUES (?,?)',
            [$recordId, $recurrenceId]
        );
    }

    /**
     * @param int $recurrenceId - other or first recurrence record
     * @return void
     */
    public static function deleteRelation(int $recurrenceId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'DELETE FROM its4you_recurring_rel WHERE recurrence_id=?',
            [$recurrenceId]
        );
    }

    public function delete()
    {
        $tableIndex = $this->tableIndex;
        $table = $this->table;
        $this->adb->pquery(
            sprintf('DELETE FROM %s WHERE %s=?', $table, $tableIndex),
            [$this->get($tableIndex)]
        );
    }

    public function save()
    {
        $tableIndex = $this->tableIndex;
        $table = $this->table;
        $params = [
            'record_id' => $this->get('record_id'),
            'recurring_date' => $this->get('recurring_date'),
            'recurring_end_date' => $this->get('recurring_end_date'),
            'recurring_type' => $this->get('recurring_type'),
            'recurring_frequency' => $this->get('recurring_frequency'),
            'recurring_info' => $this->get('recurring_info'),
        ];

        if ($this->isEmpty($tableIndex)) {
            $params[$tableIndex] = $this->adb->getUniqueID($table);
            $columns = implode(',', array_keys($params));
            $query = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, $columns, generateQuestionMarks($params));
        } else {
            $columns = implode('=?,', array_keys($params));
            $params[$tableIndex] = $this->get($tableIndex);
            $query = sprintf('UPDATE %s SET %s=? WHERE %s=?', $table, $columns, $tableIndex);
        }

        $this->adb->pquery($query, $params);
    }
}