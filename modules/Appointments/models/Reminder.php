<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Appointments_Reminder_Model extends Vtiger_Base_Model
{
    /**
     * @param array $values
     * @return array
     */
    public static function getAddressIds(array $values): array
    {
        $ids = [];

        foreach ($values as $id => $value) {
            $ids[] = $id . '|' . $value . '|Users';
        }

        return $ids;
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
     * @return array
     */
    public static function getPopupRecords(): array
    {
        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $activityReminder = $currentUserModel->getCurrentUserActivityReminderInSeconds();
        $recordModels = [];

        if ($activityReminder != '') {
            $currentTime = time();
            $date = date('Y-m-d', strtotime("+$activityReminder seconds", $currentTime));
            $time = date('H:i:s', strtotime("+$activityReminder seconds", $currentTime));
            $reminderActivitiesResult = 'SELECT its4you_remindme_popup.record_id FROM its4you_remindme_popup 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = its4you_remindme_popup.record_id 
                INNER JOIN its4you_remindme ON its4you_remindme.record_id = its4you_remindme_popup.record_id 
				WHERE its4you_remindme_popup.status = 0 
                AND vtiger_crmentity.deleted = 0 
				AND its4you_remindme.reminder_time > 0 
                AND vtiger_crmentity.smownerid = ? 
				AND its4you_remindme_popup.datetime_start <= ? 
				LIMIT 20';
            $result = $db->pquery($reminderActivitiesResult, [$currentUserModel->getId(), $date . ' ' . $time]);

            while ($row = $db->fetchByAssoc($result)) {
                $recordId = $row['record_id'];
                $recordModels[] = Vtiger_Record_Model::getInstanceById($recordId);
            }
        }

        return $recordModels;
    }

    /**
     * @return int
     * @throws Exception
     */
    public static function getReminderFrequency(): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT frequency FROM vtiger_cron_task WHERE name = ? AND handler_file = ?', ['SendReminder', 'cron/SendReminder.service']);

        return (int)$adb->query_result($result, 0, 'frequency');
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        $subject = $this->get('subject');

        $dateTime = new DateTimeField($this->get('datetime_start'));
        $dateTimeInOwnerFormat = $dateTime->getDisplayDateTimeValue($this->get('owner_focus'));

        return vtranslate('Reminder', $this->get('record_module_name')) . ': ' . decode_html($subject) . ' @ ' . $dateTimeInOwnerFormat;
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

    /**
     * @param int|string $value
     * @return false|int
     */
    public static function getTime($value)
    {
        $date = new DateTimeField($value);
        $userFormattedString = $date->getDisplayDate();
        $timeFormattedString = $date->getDisplayTime();
        $dBFormattedDate = DateTimeField::convertToDBFormat($userFormattedString);

        return strtotime($dBFormattedDate . ' ' . $timeFormattedString);
    }

    /**
     * @return bool
     */
    public function isSendingReady(): bool
    {
        $reminderTime = $this->get('reminder_time') * 60;
        $differenceOfActivityTimeAndCurrentTime = (strtotime($this->get('sending_datetime')) - time());

        return ($differenceOfActivityTimeAndCurrentTime > 0)
            && (($differenceOfActivityTimeAndCurrentTime <= $reminderTime) || ($differenceOfActivityTimeAndCurrentTime <= $this->get('sending_frequency')));
    }

    /**
     * @return void
     */
    public function retrieveInvitedUsers()
    {
        $recordId = $this->get('record_id');
        $invitedUsers = Appointments_InvitedUsers_Model::getInstance($recordId);
        $invitedUsers->retrieveUsers();

        $this->set('invited_users', $invitedUsers->getUsersInfo());
    }

    /**
     * @return void
     */
    public function retrieveRecordModel()
    {
        $recordModel = Vtiger_Record_Model::getInstanceById($this->get('record_id'));
        $recordModuleName = $recordModel->getModuleName();

        $this->set('record_model', $recordModel);
        $this->set('record_module_name', $recordModuleName);
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

    /**
     * @throws Exception
     */
    public static function runCron()
    {
        $adb = PearDatabase::getInstance();
        $query = 'SELECT vtiger_crmentity.crmid AS record_id, 
       vtiger_crmentity.description, 
       vtiger_crmentity.smownerid AS owner_id, 
       its4you_calendar.datetime_start AS calendar_datetime,
       its4you_remindme.reminder_time,
       its4you_remindme.reminder_sent,
       its4you_remindme_popup.datetime_start AS remind_datetime
        FROM its4you_calendar
		INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid=its4you_calendar.its4you_calendar_id
		INNER JOIN its4you_remindme ON its4you_remindme.record_id=its4you_calendar.its4you_calendar_id
		INNER JOIN its4you_remindme_popup ON its4you_remindme_popup.record_id=its4you_calendar.its4you_calendar_id
		WHERE its4you_calendar.datetime_start >= CURRENT_TIMESTAMP 
		  AND vtiger_crmentity.crmid != 0 
		  AND its4you_remindme.reminder_sent = 0 
		  AND its4you_remindme.reminder_time != 0
		  AND (its4you_calendar.status is NULL OR its4you_calendar.status NOT IN (?,?))
		GROUP BY its4you_calendar.its4you_calendar_id';
        $result = $adb->pquery($query, ['Held', 'Cancelled']);

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
     * @return void
     * @throws Exception
     */
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

    /**
     * @return void
     */
    public function updateReminderSent()
    {
        $recordId = $this->get('record_id');
        $adb = PearDatabase::getInstance();
        $query = 'UPDATE its4you_remindme SET reminder_sent=? WHERE record_id=?';
        $params = [1, $recordId];

        if (!$this->isEmpty('recurring_id')) {
            $query .= ' AND recurring_id =?';
            $params[] = $this->get('recurring_id');
        }

        $adb->pquery($query, $params);
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
            [$status, $record_id]
        );
    }
}