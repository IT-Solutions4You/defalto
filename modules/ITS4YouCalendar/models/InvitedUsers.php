<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_InvitedUsers_Model extends Vtiger_Base_Model
{
    public PearDatabase $adb;
    public string $startDateField = 'datetime_start';
    public string $endDateField = 'datetime_end';

    public static function getAccessibleUsers()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = $currentUser->getAccessibleUsers();

        unset($users[$currentUser->getId()]);

        return $users;
    }

    public function retrieveUsers()
    {
        $recordId = $this->getRecord();
        $query = 'SELECT vtiger_users.email1 as email, its4you_invited_users.user_id 
            FROM its4you_invited_users 
            INNER JOIN vtiger_users ON vtiger_users.id=its4you_invited_users.user_id 
            WHERE its4you_invited_users.record_id =? AND vtiger_users.deleted=? AND vtiger_users.status=?';
        $user_result = $this->adb->pquery($query, [$recordId, 0, 'Active']);
        $invitedUsers = [];

        if ($this->adb->num_rows($user_result)) {
            while ($row = $this->adb->fetch_array($user_result)) {
                $invitedUsers[$row['user_id']] = $row['email'];
            }
        }

        $this->setUsers(array_keys($invitedUsers));
        $this->setUsersInfo($invitedUsers);
    }

    public function getRecord(): int
    {
        return (int)$this->get('record_id');
    }

    public function setUsers(array $value)
    {
        $this->set('invite_users', $value);
    }

    /**
     * @param array $value
     * @return void
     */
    public function setUsersInfo(array $value)
    {
        $this->set('invite_users_info', $value);
    }

    /**
     * @return array
     */
    public function getUsersInfo(): array
    {
        return (array)$this->get('invite_users_info');
    }

    public function saveUsers()
    {
        foreach ($this->getUsers() as $userId) {
            if (empty($userId)) {
                continue;
            }

            $this->adb->pquery('INSERT INTO its4you_invited_users (record_id, user_id) VALUES (?,?)', [$this->get('record_id'), $userId]);
        }
    }

    public function getUsers(): array
    {
        return (array)$this->get('invite_users');
    }

    public function deleteUsers()
    {
        $this->adb->pquery('DELETE FROM its4you_invited_users WHERE record_id=?', [$this->get('record_id')]);
    }

    /**
     * @return void
     * @throws phpmailerException
     */
    public function sendInvitation()
    {
        include_once 'libraries/ToAscii/ToAscii.php';

        $attachment = $this->generateIcsAttachment();

        foreach ($this->getUsers() as $invitedUserId) {
            if (!empty($invitedUserId)) {
                $this->retrieveUserFocus($invitedUserId);

                $toAddress = self::getUserEmail($invitedUserId);

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $moduleName = 'ITS4YouEmails';
                $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
                $emailRecord->set('subject', $this->getSubject());
                $emailRecord->set('body', $this->getBody());
                $emailRecord->set('email_flag', 'SAVED');
                $emailRecord->set('to_email', $toAddress);
                $emailRecord->set('to_email_ids', $invitedUserId . '|' . $toAddress . '|Users');
                $emailRecord->set('email_template_ids', $this->get('template_id'));
                $emailRecord->set('email_template_language', $this->get('template_language'));
                $emailRecord->save();

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecord->getId(), $moduleName);
                $emailRecord->getMailer()->AddAttachment($attachment, '', 'base64', 'text/calendar');
                $emailRecord->send();
            }
        }
        unlink($attachment);
    }

    public function generateIcsAttachment()
    {
        $recordModel = $this->getRecordModel();
        $subject = $recordModel->getName();
        $description = $recordModel->get('description');
        $location = $recordModel->get('location');
        $assignedUserId = (int)$recordModel->get('smownerid');

        $userLabel = Vtiger_Functions::getUserRecordLabel($assignedUserId);
        $userEmail = self::getUserEmail($assignedUserId);

        $fileName = str_replace(' ', '_', decode_html($subject));
        $fileName = 'test/upload/' . $fileName . '.ics';
        $fp = fopen($fileName, "w");

        fwrite($fp, "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\n");
        fwrite($fp, "ORGANIZER;CN=" . $userLabel . ":MAILTO:" . $userEmail . "\n");
        fwrite($fp, "DTSTART:" . date('Ymd\THis\Z', strtotime($recordModel->get($this->startDateField))) . "\n");
        fwrite($fp, "DTEND:" . date('Ymd\THis\Z', strtotime($recordModel->get($this->endDateField))) . "\n");
        fwrite($fp, "DTSTAMP:" . date('Ymd\THis\Z') . "\n");
        fwrite($fp, "DESCRIPTION:" . $description . "\nLOCATION:" . $location . "\n");
        fwrite($fp, "STATUS:CONFIRMED\nSUMMARY:" . $subject . "\nEND:VEVENT\nEND:VCALENDAR");
        fclose($fp);

        return $fileName;
    }

    /**
     * @return Vtiger_Record_Model
     */
    public function getRecordModel(): Vtiger_Record_Model
    {
        return $this->get('record_model');
    }

    /**
     * @param int $userId
     * @return string
     */
    public static function getUserEmail(int $userId): string
    {
        $fields = ['email1', 'email2', 'secondaryemail'];
        $userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');

        foreach ($fields as $field) {
            if (!$userRecordModel->isEmpty($field)) {
                return $userRecordModel->get($field);
            }
        }

        return '';
    }

    /**
     * @throws Exception
     */
    public function retrieveUserFocus($userId)
    {
        $focus = CRMEntity::getInstance('Users');
        $focus->retrieve_entity_info($userId, 'Users');

        $this->set('user_focus', $focus);
    }

    /**
     * @param int $recordId
     * @return static
     */
    public static function getInstance(int $recordId): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->setRecord($recordId);
        $instance->retrieveRecordModel();

        return $instance;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setRecord(int $value)
    {
        $this->set('record_id', $value);
    }

    public function retrieveRecordModel()
    {
        $this->set('record_model', Vtiger_Record_Model::getInstanceById($this->getRecord()));
    }

    public function getSubject()
    {
        $recordModel = $this->getRecordModel();
        $moduleName = $recordModel->getModuleName();

        if ('edit' === $recordModel->get('mode')) {
            $subject = vtranslate('LBL_UPDATED_INVITATION', $moduleName) . ' : ';
        } else {
            $subject = vtranslate('LBL_INVITATION', $moduleName) . ' : ';
        }

        $subject .= $recordModel->getName();

        return $subject;
    }

    public function getBody(): string
    {
        return $this->getTemplateBody();
    }

    public function getTemplateBody(): string
    {
        $recordModel = $this->getRecordModel();
        $result = $this->adb->pquery(
            'SELECT templateid AS template_id FROM vtiger_emakertemplates WHERE subject=? AND module=?',
            ['Invitation', $recordModel->getModuleName()]
        );

        if ($this->adb->num_rows($result)) {
            $userFocus = $this->get('user_focus');
            $templateId = $this->adb->query_result($result, 0, 'template_id');
            $templateLanguage = $userFocus->column_fields['language'];

            $this->set('template_id', $templateId);
            $this->set('template_language', $templateLanguage);

            $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $templateLanguage, $recordModel->getModuleName(), $recordModel->getId(), $userFocus->column_fields['id'], 'Users');
            $EMAILContentModel->getContent();

            return $EMAILContentModel->getBody();
        }

        return '';
    }
}