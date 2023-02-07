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

    public static function saveInvitedUsers($recordId, $userIds)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('DELETE FROM its4you_invited_users WHERE record_id=?', [$recordId]);

        foreach ($userIds as $userId) {
            $adb->pquery('INSERT INTO its4you_invited_users (record_id, user_id) VALUES (?,?)', [$recordId, $userId]);
        }
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

    /**
     * @throws Exception
     */
    public static function runCron()
    {
        $adb = PearDatabase::getInstance();
        $query = "SELECT vtiger_crmentity.crmid AS record_id, 
       vtiger_crmentity.description, 
       vtiger_crmentity.smownerid AS owner_id, 
       its4you_calendar.datetime_start AS calendar_datetime,
       its4you_remindme.reminder_time,
       its4you_remindme.reminder_sent,
       its4you_remindme.recurring_id,
       its4you_remindme_popup.datetime_start AS remind_datetime
        FROM its4you_calendar
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=its4you_calendar.its4you_calendar_id
		INNER JOIN its4you_remindme ON its4you_remindme.record_id=its4you_calendar.its4you_calendar_id
		INNER JOIN its4you_remindme_popup ON its4you_remindme_popup.record_id=its4you_calendar.its4you_calendar_id
		WHERE DATE_FORMAT(its4you_calendar.datetime_start,'%Y-%m-%d, %H:%i:%s') >= ? 
		  AND vtiger_crmentity.crmid != 0 
		  AND (its4you_calendar.status is NULL OR its4you_calendar.status NOT IN ('Held','Cancelled'))
		  AND its4you_remindme.reminder_sent = 0 
		  AND its4you_remindme.reminder_time != 0
		GROUP BY its4you_calendar.its4you_calendar_id";
        $result = $adb->pquery($query, array(date('Y-m-d H:i:s')));

        if ($adb->num_rows($result)) {
            $reminderFrequency = self::getReminderFrequency();

            while ($row = $adb->fetchByAssoc($result)) {
                $sendingDatetime = $row['calendar_datetime'];
                $reminder = new self();
                $reminder->setData($row);
                $reminder->retrieveRecordModel();
                $reminder->set('sending_datetime', $sendingDatetime);
                $reminder->set('sending_frequency', $reminderFrequency);

                if ($reminder->isSendingReady()) {
                    $reminder->retrieveInvitedUsers();
                    $reminder->retrieveUserFocus();
                    $reminder->sendEmail();
                    $reminder->updateReminderSent();
                }
            }
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    public static function getReminderFrequency(): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT frequency FROM vtiger_cron_task WHERE name = "SendReminder" AND handler_file = "cron/SendReminder.service"', array());

        return (int)$adb->query_result($result, 0, 'frequency');
    }

    public function retrieveRecordModel()
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($this->get('record_id'));
        $recordModuleName = $recordModel->getModuleName();

        $this->set('record_model', $recordModel);
        $this->set('record_module_name', $recordModuleName);
    }

    public function isSendingReady()
    {
        $reminderTime = $this->get('reminder_time') * 60;
        $currentTime = self::getTime(null);
        $activityTime = self::getTime($this->get('sending_datetime'));
        $differenceOfActivityTimeAndCurrentTime = ($activityTime - $currentTime);

        return ($differenceOfActivityTimeAndCurrentTime > 0)
            && (($differenceOfActivityTimeAndCurrentTime <= $reminderTime) || ($differenceOfActivityTimeAndCurrentTime <= $this->get('sending_frequency')));
    }

    public static function getTime($value)
    {
        $date = new DateTimeField($value);
        $userFormattedString = $date->getDisplayDate();
        $timeFormattedString = $date->getDisplayTime();
        $dBFormattedDate = DateTimeField::convertToDBFormat($userFormattedString);

        return strtotime($dBFormattedDate . ' ' . $timeFormattedString);
    }

    public function retrieveInvitedUsers()
    {
        $recordId = $this->get('record_id');
        $adb = PearDatabase::getInstance();
        $query = 'SELECT vtiger_users.email1 as email, its4you_invited_users.user_id 
            FROM its4you_invited_users 
            INNER JOIN vtiger_users ON vtiger_users.id=its4you_invited_users.user_id 
            WHERE its4you_invited_users.record_id =? AND vtiger_users.deleted=? AND vtiger_users.status=?';
        $user_result = $adb->pquery($query, array($recordId, 0, 'Active'));
        $invitedUsers = [];

        if ($adb->num_rows($user_result) >= 1) {
            while ($row = $adb->fetch_array($user_result)) {
                $invitedUsers[$row['user_id']] = $row['email'];
            }
        }

        $this->set('invited_users', $invitedUsers);
    }

    /**
     * @throws Exception
     */
    public function retrieveUserFocus()
    {
        $ownerId = $this->get('owner_id');
        $invitedUsersList = array_keys((array)$this->get('invited_users'));

        if (!in_array($ownerId, $invitedUsersList)) {
            $ownerId = $invitedUsersList[0];
        }

        $ownerFocus = CRMEntity::getInstance('Users');
        $ownerFocus->retrieve_entity_info($ownerId, 'Users');

        $this->set('owner_id', $ownerId);
        $this->set('owner_focus', $ownerFocus);
    }

    public function sendEmail()
    {
        global $HELPDESK_SUPPORT_EMAIL_ID;

        $from_email = $HELPDESK_SUPPORT_EMAIL_ID;

        if (empty($from_email)) {
            $from_email = 'reminders@localserver.com';
        }

        $toAddress = array_filter((array)$this->get('invited_users'));
        $subject = $this->getSubject();
        $body = $this->getBody();
        $ownerId = $this->get('owner_id');

        /** @var ITS4YouEmails_Record_Model $emailRecord */
        $moduleName = 'ITS4YouEmails';
        $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
        $emailRecord->set('subject', $subject);
        $emailRecord->set('body', $body);
        $emailRecord->set('email_flag', 'SAVED');

        if (!empty($from_email)) {
            $emailRecord->set('from_email', $from_email);
            $emailRecord->set('from_email_ids', $ownerId . '|' . $from_email . '|Users');
        }

        $emailRecord->set('to_email', implode(',', $toAddress));
        $emailRecord->set('to_email_ids', implode('|', self::getAddressIds($toAddress)));
        $emailRecord->set('email_template_ids', $this->get('template_id'));
        $emailRecord->set('email_template_language', $this->get('template_language'));
        $emailRecord->save();

        /** @var ITS4YouEmails_Record_Model $emailRecord */
        $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecord->getId(), $moduleName);
        $emailRecord->send();
    }

    public function getSubject()
    {
        $subject = $this->get('subject');

        $dateTime = new DateTimeField($this->get('datetime_start'));
        $dateTimeInOwnerFormat = $dateTime->getDisplayDateTimeValue($this->get('owner_focus'));

        return vtranslate('Reminder', $this->get('record_module_name')) . ': ' . decode_html($subject) . ' @ ' . $dateTimeInOwnerFormat;
    }

    /**
     * @throws Exception
     */
    public function getBody(): string
    {
        global $site_URL;

        $recordModel = $this->get('record_model');
        $recordModuleName = $this->get('record_module_name');

        return sprintf(
            '%s<br/> %s <a href="%s/%s">%s</a>',
            $this->getTemplateBody(),
            vtranslate('LBL_CLICK_HERE_TO_VIEW', $recordModuleName),
            $site_URL,
            $recordModel->getDetailViewUrl(),
            vtranslate('LBL_RECORD', $recordModuleName)
        );
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getTemplateBody(): string
    {
        $adb = PearDatabase::getInstance();
        $query = 'SELECT templateid AS template_id FROM vtiger_emakertemplates WHERE subject=? AND module=?';
        $result = $adb->pquery($query, ['Reminder', $this->get('record_module_name')]);

        if ($adb->num_rows($result)) {
            $templateId = $adb->query_result($result, 0, 'template_id');
            $templateLanguage = $this->get('user_focus')->column_field['language'];

            $this->set('template_id', $templateId);
            $this->set('template_language', $templateLanguage);

            $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $templateLanguage, $this->get('record_module_name'), $this->get('record_id'));
            $EMAILContentModel->getContent();

            return $EMAILContentModel->getBody();
        }

        return '';
    }

    public static function getAddressIds($values): array
    {
        $ids = [];

        foreach ($values as $id => $value) {
            $ids[] = $id . '|' . $value . '|Users';
        }

        return $ids;
    }

    /**
     * @return void
     */
    public function updateReminderSent()
    {
        $recordId = $this->get('record_id');
        $adb = PearDatabase::getInstance();
        $query = 'UPDATE its4you_remindme SET reminder_sent=? WHERE record_id=?';
        $params = array(1, $recordId);

        if (!$this->isEmpty('recurring_id')) {
            $query .= ' AND recurring_id =?';
            $params[] = $this->get('recurring_id');
        }

        $adb->pquery($query, $params);
    }

    public static function getContactsNames($recordId): string
    {
        $adb = PearDatabase::getInstance();
        $query = 'SELECT * FROM vtiger_cntactivityrel WHERE activityid=?';
        $result = $adb->pquery($query, array($recordId));
        $contactNames = [];

        if ($adb->num_rows($result)) {
            while ($row = $adb->fetchByAssoc($result)) {
                $contactId = $row['contactid'];
                $contactName = Vtiger_Util_Helper::getRecordName($contactId);

                $contactNames[] = $contactName;
            }
        }

        return implode(', ', $contactNames);
    }
}