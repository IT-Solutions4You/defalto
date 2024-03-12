<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class EMAILMaker_Utils_Helper
{
    public static function sendTemplateMail(
        int|string $template,
        string $forModule,
        array $to,
        array $toIds = [],
        string $fromName = '',
        string $fromEmail = '',
        array $cc = [],
        array $bcc = [],
        string $attachment = 'all',
    ) {
        if (empty($to)) {
            return;
        }

        if (empty($template)) {
            return;
        }

        global $root_directory, $current_language, $default_language, $default_charset, $current_user;

        if (empty($current_language)) {
            $current_language = $default_language;
        }

        $db = PearDatabase::getInstance();
        $emailMaker = new EMAILMaker_EMAILMaker_Model();

        $attachments = [];

        $templateSql = 'SELECT * FROM vtiger_emakertemplates WHERE ';

        if (is_int($template)) {
            $templateSql .= ' templateid = ? ';
        } else {
            $templateSql .= ' templatename = ? ';
        }

        $templateSql .= ' AND deleted = 0 AND module = ? ';

        $templateRes = $db->pquery($templateSql, [$template, $forModule]);

        if ($db->num_rows($templateRes)) {
            $templateRow = $db->fetchByAssoc($templateRes);
            $templateId = $templateRow['templateid'];

            if (empty($subject)) {
                $subject = $templateRow['subject'];
            }

            if (empty($body)) {
                $body = $templateRow['body'];
            }

            if (empty($fromEmail)) {
                $fromEmail = EMAILMaker_Record_Model::getDefaultFromEmail($templateId);
            }

            if ($attachment != 'none') {
                $attachmentsSql = "SELECT vtiger_seattachmentsrel.attachmentsid FROM vtiger_notes 
                              INNER JOIN vtiger_crmentity 
                                 ON vtiger_crmentity.crmid = vtiger_notes.notesid
                              INNER JOIN vtiger_seattachmentsrel 
                                 ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid   
                              INNER JOIN vtiger_emakertemplates_documents 
                                 ON vtiger_emakertemplates_documents.documentid = vtiger_notes.notesid
                              WHERE vtiger_crmentity.deleted = '0' AND vtiger_emakertemplates_documents.templateid = ?";
                $attachmentsResult = $db->pquery($attachmentsSql, [$templateId]);

                if ($db->num_rows($attachmentsResult)) {
                    while ($attachmentsRow = $db->fetchByAssoc($attachmentsResult)) {
                        $attachments[] = $attachmentsRow['attachmentsid'];
                    }
                }
            }
        }

        if ($attachment !== 'none') {
            if (isset($_REQUEST['filename_hidden'])) {
                $fileName = $_REQUEST['filename_hidden'];
            } else {
                $fileName = $_FILES['filename']['name'];
            }

            $completePath = $root_directory . 'test/upload/' . $fileName;

            if (file_exists($completePath) && $fileName != '') {
                $attachments[] = $completePath;
            }
        }

        foreach ($to as $toKey => $email) {
            $crmId = 0;

            if (isset($toIds[$toKey])) {
                $crmId = $toIds[$toKey];
            }

            $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance($forModule, $crmId, $current_language, $crmId, $forModule);
            $EMAILContentModel->setSubject($subject);
            $EMAILContentModel->setBody($body);
            $EMAILContentModel->getContent();

            $subject = $EMAILContentModel->getSubject();
            $subject = html_entity_decode($subject, ENT_QUOTES, $default_charset);
            $body = $EMAILContentModel->getBody();

            $emailRecordModel = ITS4YouEmails_Record_Model::getCleanInstance('ITS4YouEmails');
            $emailRecordModel->set('email_flag', 'SAVED');
            $emailRecordModel->set('related_to', $crmId);
            $emailRecordModel->set('from_email', $fromEmail);
//                $emailRecordModel->set('from_email_ids', $accountRecordData['assigned_user_id'] . '|' . $fromAddress . '|Users');
            $emailRecordModel->set('reply_email', $fromEmail);
//                $emailRecordModel->set('reply_email_ids', $accountRecordData['assigned_user_id'] . '|' . $replyToEmail . '|Users');
            $emailRecordModel->set('to_email', $email);
            $emailRecordModel->set('to_email_ids', $crmId . '|' . $email . '|' . $forModule);

            if (!empty($cc)) {
                $emailRecordModel->set('cc_email', implode(',', $cc));
                $emailRecordModel->set('cc_email_ids', implode(',', array_map(fn($ccAddress) => 'email|' . $ccAddress . '|', $cc)));
            }

            if (!empty($bcc)) {
                $emailRecordModel->set('bcc_email', implode(',', $bcc));
                $emailRecordModel->set('bcc_email_ids', implode(',', array_map(fn($bccAddress) => 'email|' . $bccAddress . '|', $bcc)));
            }

            if (!empty($current_user)) {
                $emailRecordModel->set('assigned_user_id', $current_user->id);
            } elseif (!empty($crmId)) {
                $emailRecordModel->set('assigned_user_id', getRecordOwnerId($crmId));
            }

            $emailRecordModel->set('subject', $subject);
            $emailRecordModel->set('body', $body);
            $emailRecordModel->save();

            foreach ($attachments as $attachment) {
                $emailRecordModel->saveAttachmentRelation($attachment);
            }

            $emailRecordId = $emailRecordModel->getId();
            $emailRecordModel = ITS4YouEmails_Record_Model::getInstanceById($emailRecordId);
            $emailRecordModel->set('from_name', $fromName);
            $emailRecordModel->send();
        }
    }

    public static function sendMail(
        string $forModule,
        array $to,
        array $toIds = [],
        string $fromName = '',
        string $fromEmail = '',
        string $subject = '',
        string $body = '',
        array $cc = [],
        array $bcc = [],
        string $attachment = '',
        $emailid = '',
        $logo = '',
        $useGivenFromEmailAddress = false,
        $useSignature = 'Yes',
        $inReplyToMessageId = ''
    ) {
        if (empty($to)) {
            return false;
        }

        global $root_directory, $current_language, $default_language, $default_charset, $current_user;

        if (empty($current_language)) {
            $current_language = $default_language;
        }

        $db = PearDatabase::getInstance();

        if ($fromEmail == '') {
            $fromEmail = getUserEmailByName($fromName);
        }

        $systemFromEmail = Settings_Vtiger_Systems_Model::getFromEmailField();

        if (isUserInitiated()) {
            $replyToEmail = $fromEmail;
        } else {
            $replyToEmail = $systemFromEmail;
        }

        if ($systemFromEmail != '' && !$useGivenFromEmailAddress) {
            //setting from _email to the defined email address in the outgoing server configuration
            $fromEmail = $systemFromEmail;
        }

        if ($useSignature == 'Yes') {
            $body = self::addSignature($body, $fromName, $fromEmail);
        }

        $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance('ITS4YouEmails');

        $mail = ITS4YouEmails_Mailer_Model::getCleanInstance();
        $mail->Subject = $subject;
        $mail->Body = decode_html($body);
        $mail->AltBody = $emailRecord->getProcessedAltBody($body);
        $mail->retrieveSMTPVtiger();
        $mail->From = $fromEmail;
        $mail->FromName = self::chooseFromName($fromName);

    }
    protected static function chooseFromName(string $fromName = ''): string
    {
        global $HELPDESK_SUPPORT_NAME;

        $userFullName = trim(VTCacheUtils::getUserFullName($fromName));

        if ($fromName == $HELPDESK_SUPPORT_NAME) {
            $userFullName = $HELPDESK_SUPPORT_NAME;
        }

        if (!empty($userFullName)) {
            return $userFullName;
        }

        $db = PearDatabase::getInstance();
        $rs = $db->pquery('select first_name,last_name,userlabel from vtiger_users where user_name=?', [$fromName]);

        if ($db->num_rows($rs)) {
            $fullName = $db->query_result($rs, 0, 'userlabel');
            VTCacheUtils::setUserFullName($fromName, $fullName);
            return $fullName;
        }

        return '';
    }


    /**
     * Function to add the user's signature with the content passed
     *
     * @param string $contents
     * @param string $fromName
     * @param string $fromEmail
     *
     * @return string
     * @throws Exception
     */
    public static function addSignature(string $contents, string $fromName, string $fromEmail = ''): string
    {
        $db = PearDatabase::getInstance();
        $db->println('Inside the function addSignature');

        $sign = VTCacheUtils::getUserSignature($fromName);

        if ($sign == null) {
            $result = $db->pquery('select signature, userlabel from vtiger_users where user_name=? or user_name=? or email1=? or email2=? or secondaryemail=?',
                [$fromName, $fromEmail, $fromEmail, $fromEmail, $fromEmail]);

            if ($db->num_rows($result)) {
                $row = $db->fetchByAssoc($result);
                $sign = $row['signature'];
                VTCacheUtils::setUserSignature($fromName, $sign);
                VTCacheUtils::setUserSignature($fromEmail, $sign);
                VTCacheUtils::setUserFullName($fromName, $row['userlabel']);
            }
        }

        $sign = nl2br($sign);

        if ($sign != '') {
            $contents .= '<br><br>' . $sign;
            $db->println("Signature is added with the body => '." . $sign . "'");
        } else {
            $db->println("Signature is empty for the user => '" . $fromName . "'");
        }

        return $contents;
    }

}