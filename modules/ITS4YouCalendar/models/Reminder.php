<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ITS4YouCalendar_Reminder_Model extends Vtiger_Base_Model
{
    public static function getPopupRecords()
    {
        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
        $recordModels = array();

        if ($activityReminder != '') {
            $currentTime = time();
            $date = date('Y-m-d', strtotime("+$activityReminder seconds", $currentTime));
            $time = date('H:i:s', strtotime("+$activityReminder seconds", $currentTime));
            $reminderActivitiesResult = 'SELECT record_id FROM its4you_remindme_popup
								INNER JOIN vtiger_crmentity ON its4you_remindme_popup.record_id = vtiger_crmentity.crmid
								WHERE its4you_remindme_popup.status = 0
								AND vtiger_crmentity.smownerid = ? AND vtiger_crmentity.deleted = 0
								AND its4you_remindme_popup.datetime_start <= ?
								LIMIT 20';
            $result = $db->pquery($reminderActivitiesResult, array($currentUserModel->getId(), $date . ' ' . $time));

            while ($row = $db->fetchByAssoc($result)) {
                $recordId = $row['record_id'];
                $recordModels[] = Vtiger_Record_Model::getInstanceById($recordId);
            }
        }

        return $recordModels;
    }

    /**
     * @param int $record_id
     * @param int $status
     * @return void
     */
    public static function updateStatus(int $record_id, int $status = 1)
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE its4you_remindme_popup set status = ? where record_id = ?',
            array($status, $record_id)
        );
    }

    /**
     * @param int $recordId
     * @param string $dateTimeStart
     * @return void
     */
    public static function saveRecord(int $recordId, string $dateTimeStart)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'DELETE FROM its4you_remindme_popup WHERE record_id=?',
            [$recordId]
        );
        $adb->pquery(
            'INSERT INTO its4you_remindme_popup (record_id, datetime_start, status) VALUES (?,?,?)',
            [$recordId, $dateTimeStart, 0]
        );
    }

    /**
     * @param Vtiger_Record_Model $record
     * @return array
     */
    public static function getHeaders(Vtiger_Record_Model $record): array
    {
        $module = $record->getModule();
        $headerFields = $module->getHeaderViewFieldsList();
        $headers = [];

        foreach ($headerFields as $headerField) {
            $headers[] = ['name' => $headerField->getName(), 'label' => vtranslate($headerField->label, $module->getName())];
        }

        return $headers;
    }

    public static function runCron()
    {

    }

    /**
     * @param int $recurring_id
     * @return void
     */
    public static function updateReminderSent(int $recurring_id = 0)
    {
        $adb = PearDatabase::getInstance();
        $query = "UPDATE its4you_remindme SET reminder_sent = ?";
        $params = array(1);

        if (0 !== $recurring_id) {
            $query .= " and recurringid =?";
            array_push($params, $recurring_id);
        }

        $adb->pquery($query, $params);
    }
}