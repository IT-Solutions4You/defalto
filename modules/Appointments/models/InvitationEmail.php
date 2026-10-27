<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_InvitationEmail_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    protected PearDatabase $adb;
    /**
     * @var string
     */
    protected string $endDateField = 'datetime_end';
    /**
     * @var Appointments_InvitedUsers_Model
     */
    protected Appointments_InvitedUsers_Model $invitedUsers;
    /**
     * @var string
     */
    protected string $startDateField = 'datetime_start';

    /**
     * @param string $value
     *
     * @return string
     */
    protected function escapeIcsText(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $value);
    }

    /**
     * @param string $line
     *
     * @return string
     */
    protected function foldIcsLine(string $line): string
    {
        $lines = [];

        while (strlen($line) > 75) {
            $length = 75;

            while ($length > 0 && 0x80 === (ord($line[$length]) & 0xC0)) {
                --$length;
            }

            $lines[] = substr($line, 0, $length);
            $line = ' ' . substr($line, $length);
        }

        $lines[] = $line;

        return implode("\r\n", $lines);
    }

    /**
     * @return string
     */
    protected function getIcsContent(): string
    {
        $recordModel = $this->invitedUsers->getRecordModel();
        $assignedUserId = (int)$recordModel->get('assigned_user_id');
        $userLabel = str_replace(['"', "\r", "\n"], '', (string)Vtiger_Functions::getUserRecordLabel($assignedUserId));
        $userEmail = $this->getUserEmail($assignedUserId);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'BEGIN:VEVENT',
            'ORGANIZER;CN="' . $userLabel . '":MAILTO:' . $userEmail,
            'DTSTART:' . date('Ymd\THis\Z', strtotime($recordModel->get($this->startDateField))),
            'DTEND:' . date('Ymd\THis\Z', strtotime($recordModel->get($this->endDateField))),
            'DTSTAMP:' . date('Ymd\THis\Z'),
            'DESCRIPTION:' . $this->escapeIcsText((string)$recordModel->get('description')),
            'LOCATION:' . $this->escapeIcsText((string)$recordModel->get('location')),
            'STATUS:CONFIRMED',
            'SUMMARY:' . $this->escapeIcsText(decode_html((string)$recordModel->getName())),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map([$this, 'foldIcsLine'], $lines)) . "\r\n";
    }

    /**
     * The file name is generated and never derived from the subject, which may contain path separators.
     *
     * @return string
     */
    protected function getIcsFilePath(): string
    {
        return 'test/upload/invitation_' . (int)$this->invitedUsers->getRecordModel()->getId() . '_' . uniqid() . '.ics';
    }

    /**
     * @return string
     */
    protected function getIcsAttachmentName(): string
    {
        $name = decode_html((string)$this->invitedUsers->getRecordModel()->getName());
        $name = preg_replace('/[\/\\\\:*?"<>|\x00-\x1F\x7F\s]+/u', '_', $name);
        $name = trim((string)mb_substr((string)$name, 0, 80), '_.');

        return ('' === $name ? 'invitation' : $name) . '.ics';
    }

    /**
     * @return string empty string when the file could not be written
     */
    protected function generateIcsAttachment(): string
    {
        global $log;

        $filePath = $this->getIcsFilePath();

        if (false === @file_put_contents($filePath, $this->getIcsContent())) {
            $log->error('Appointments invitation: unable to write ICS file ' . $filePath);

            return '';
        }

        return $filePath;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getBody(): string
    {
        return $this->retrieveTemplateBody();
    }

    /**
     * @param $inviteUsers
     *
     * @return self
     */
    public static function getInstance($inviteUsers): self
    {
        $instance = new self();
        $instance->invitedUsers = $inviteUsers;
        $instance->adb = PearDatabase::getInstance();

        return $instance;
    }

    /**
     * @return string
     */
    protected function getSubject(): string
    {
        $recordModel = $this->invitedUsers->getRecordModel();
        $moduleName = $recordModel->getModuleName();

        if ('edit' === $recordModel->get('mode')) {
            $subject = vtranslate('LBL_UPDATED_INVITATION', $moduleName) . ' : ';
        } else {
            $subject = vtranslate('LBL_INVITATION', $moduleName) . ' : ';
        }

        $subject .= $recordModel->getName();

        return $subject;
    }

    /**
     * @param int $userId
     *
     * @return string
     */
    protected function getUserEmail(int $userId): string
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
     * @return object|null
     */
    protected function getUserFocus()
    {
        return $this->get('user_focus');
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function retrieveTemplateBody(): string
    {
        if (!method_exists('EMAILMaker_EMAILContent_Model', 'getInstanceById')) {
            return '';
        }

        $recordModel = $this->invitedUsers->getRecordModel();
        $result = $this->adb->pquery(
            'SELECT templateid AS template_id FROM vtiger_emakertemplates WHERE subject=? AND module=?',
            ['Invitation', $recordModel->getModuleName()]
        );

        if (!$this->adb->num_rows($result)) {
            return '';
        }

        $userFocus = $this->getUserFocus();
        $templateId = $this->adb->query_result($result, 0, 'template_id');
        $templateLanguage = $userFocus->column_fields['language'];

        $this->set('template_id', $templateId);
        $this->set('template_language', $templateLanguage);

        $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById(
            $templateId,
            $templateLanguage,
            $recordModel->getModuleName(),
            $recordModel->getId(),
            $userFocus->column_fields['id'],
            'Users'
        );
        $EMAILContentModel->getContent();

        return $EMAILContentModel->getBody();
    }

    /**
     * @throws Exception
     */
    protected function retrieveUserFocus($userId)
    {
        $focus = CRMEntity::getInstance('Users');
        $focus->retrieve_entity_info($userId, 'Users');

        $this->set('user_focus', $focus);
    }

    /**
     * @return array
     */
    protected function getInvitedUserIds(): array
    {
        return array_filter($this->invitedUsers->getUsers());
    }

    /**
     * @throws Exception
     */
    public function send()
    {
        $invitedUserIds = $this->getInvitedUserIds();

        if (empty($invitedUserIds)) {
            return;
        }

        include_once 'libraries/ToAscii/ToAscii.php';

        $attachment = $this->generateIcsAttachment();

        if ('' === $attachment) {
            return;
        }

        try {
            foreach ($invitedUserIds as $invitedUserId) {
                $this->retrieveUserFocus($invitedUserId);
                $toAddress = $this->getUserEmail($invitedUserId);

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $moduleName = 'ITS4YouEmails';
                $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
                $emailRecord->set('subject', $this->getSubject());
                $emailRecord->set('body', $this->getBody());
                $emailRecord->set('email_flag', 'SAVED');
                $emailRecord->set('related_to', $this->invitedUsers->getRecordModel()->getId());
                $emailRecord->set('to_email', $toAddress);
                $emailRecord->set('to_email_ids', $invitedUserId . '|' . $toAddress . '|Users');
                $emailRecord->set('email_template_ids', $this->get('template_id'));
                $emailRecord->set('email_template_language', $this->get('template_language'));
                $emailRecord->save();

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecord->getId(), $moduleName);
                $emailRecord->getMailer()->AddAttachment($attachment, $this->getIcsAttachmentName(), 'base64', 'text/calendar');
                $emailRecord->send();
            }
        } finally {
            if (is_file($attachment)) {
                unlink($attachment);
            }
        }
    }

}
