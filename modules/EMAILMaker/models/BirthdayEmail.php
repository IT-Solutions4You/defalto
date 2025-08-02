<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/VtlibUtils.php';

class EMAILMaker_BirthdayEmail_Model extends Vtiger_Base_Model
{
    /**
     * @var bool
     */
    public $EMAILMaker = false;

    /**
     * @return mixed
     */
    public function getOrganizationName(): string
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_organizationdetails WHERE organizationname !=?', ['']);
        $row = $adb->fetchByAssoc($result);

        return (string)$row['organizationname'];
    }

    /**
     * @return array|false
     */
    public function getTemplateData(): false|array
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT df.fieldname AS default_from, vtiger_emakertemplates.* 
                FROM vtiger_emakertemplates 
                INNER JOIN vtiger_emakertemplates_userstatus AS us ON us.templateid = vtiger_emakertemplates.templateid AND us.userid = vtiger_emakertemplates.owner 
                LEFT JOIN vtiger_emakertemplates_default_from AS df ON df.templateid = vtiger_emakertemplates.templateid AND df.userid = vtiger_emakertemplates.owner 
                WHERE vtiger_emakertemplates.templatename=? AND us.is_active=?';
        $result = $adb->pquery($sql, ['BIRTHDAY_EMAIL_CRON', '1']);

        if ($adb->num_rows($result)) {
            return $adb->query_result_rowdata($result);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getContacts(): array
    {
        $contacts = [];
        $actual_month = date('m');
        $actual_day = date('d');
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT vtiger_contactsubdetails.birthday, vtiger_contactdetails.* FROM vtiger_contactdetails 
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
                INNER JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid  
                WHERE vtiger_contactdetails.emailoptout=? AND vtiger_crmentity.deleted=? AND MONTH(birthday)=? AND DAY(birthday)=?';
        $result = $adb->pquery($sql, ['0', '0', $actual_month, $actual_day]);

        while ($row = $adb->fetchByAssoc($result)) {
            $email = $row['email'];

            if (empty($email)) {
                $email = $row['otheremail'];
            }

            if (empty($email)) {
                $email = $row['secondaryemail'];
            }

            $fullName = trim($row['firstname'] . " " . $row['lastname']);

            if (!empty($email)) {
                $contacts[$row['contactid']] = [
                    'fullname' => $fullName,
                    'email'    => $email,
                ];
            }
        }

        return $contacts;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function sendEmails(): void
    {
        $this->EMAILMaker = new EMAILMaker_EMAILMaker_Model();
        $contacts = $this->getContacts();

        if (count($contacts) > 0) {
            $templateData = $this->getTemplateData();

            if (empty($templateData)) {
                return;
            }

            $templateId = intval($templateData['templateid']);
            $defaultFrom = $templateData['default_from'];
            $subject = $templateData['subject'];
            $body = $templateData['body'];
            $ownerUserId = intval($templateData['owner']);
            $ownerUser = CRMEntity::getInstance('Users');
            $ownerUser->retrieveCurrentUserInfoFromFile($ownerUserId);
            $ownerLanguage = !empty($ownerUser->language) ? $ownerUser->language : vglobal('default_language');
            $fromName = $this->getOrganizationName();
            $replyAddress = '';

            if (!empty($defaultFrom) && $defaultFrom != '0_organization_email') {
                [$c, $email_field] = explode('_', $defaultFrom, 2);
                $fromName = trim($ownerUser->first_name . ' ' . $ownerUser->last_name);

                if (isset($ownerUser->column_fields[$email_field])) {
                    $replyAddress = $ownerUser->column_fields[$email_field];

                    if (empty($fromAddress)) {
                        $fromAddress = $replyAddress;
                    }
                }
            }

            if (empty($fromAddress)) {
                $fromAddress = $ownerUser->column_fields['email1'];

                if (empty($replyAddress)) {
                    $replyAddress = $fromAddress;
                }
            }

            $fromAddressIds = $ownerUserId . '|' . $fromAddress . '|Users';

            $attachments = !empty($templateId) ? $this->EMAILMaker->GetAttachmentsData($templateId) : [];

            foreach ($contacts as $contactId => $contactData) {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance('Contacts', $contactId, $ownerLanguage, $contactId, 'Contacts');
                $EMAILContentModel->setSubject($subject);
                $EMAILContentModel->setBody($body);
                $EMAILContentModel->getContent();

                $subject = $EMAILContentModel->getSubject();
                $body = $EMAILContentModel->getBody();

                $toAddress = $contactData['email'];
                $toAddressIds = $contactId . '|' . $toAddress . '|Contacts';
                $replyAddressIds = $contactId . '|' . $replyAddress . '|Contacts';

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $moduleName = 'ITS4YouEmails';
                $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
                $emailRecord->set('subject', $subject);
                $emailRecord->set('body', $body);
                $emailRecord->set('assigned_user_id', $ownerUser->id);
                $emailRecord->set('related_to', $contactId);
                $emailRecord->set('email_flag', 'SAVED');
                $emailRecord->set('from_email', $fromAddress);
                $emailRecord->set('from_email_ids', $fromAddressIds);
                $emailRecord->set('to_email', $toAddress);
                $emailRecord->set('to_email_ids', $toAddressIds);
                $emailRecord->set('reply_email', $replyAddress);
                $emailRecord->set('reply_email_ids', $replyAddressIds);
                $emailRecord->save();

                if (count($attachments) > 0) {
                    foreach ($attachments as $attachmentId) {
                        $emailRecord->saveAttachmentRelation($attachmentId);
                    }
                }

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecord->getId(), $moduleName);
                $emailRecord->set('from_name', $fromName);
                $emailRecord->set('reply_name', $fromName);
                $emailRecord->send();

                unset($emailRecord);
            }
        }
    }
}