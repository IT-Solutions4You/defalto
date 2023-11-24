<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return string
     */
    protected function generateIcsAttachment(): string
    {
        $recordModel = $this->invitedUsers->getRecordModel();
        $subject = $recordModel->getName();
        $description = $recordModel->get('description');
        $location = $recordModel->get('location');
        $assignedUserId = (int)$recordModel->get('smownerid');

        $userLabel = Vtiger_Functions::getUserRecordLabel($assignedUserId);
        $userEmail = $this->getUserEmail($assignedUserId);

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
     * @return string
     * @throws Exception
     */
    protected function getBody(): string
    {
        return $this->retrieveTemplateBody();
    }

    /**
     * @param $inviteUsers
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

        $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById($templateId, $templateLanguage, $recordModel->getModuleName(), $recordModel->getId(), $userFocus->column_fields['id'], 'Users');
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
     * @throws Exception
     */
    public function send()
    {
        include_once 'libraries/ToAscii/ToAscii.php';

        $attachment = $this->generateIcsAttachment();

        foreach ($this->invitedUsers->getUsers() as $invitedUserId) {
            if (empty($invitedUserId)) {
                continue;
            }

            $this->retrieveUserFocus($invitedUserId);
            $toAddress = $this->getUserEmail($invitedUserId);

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

        unlink($attachment);
    }

}