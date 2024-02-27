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
    public static function sendMail(
        string $forModule,
        array $to,
        array $toIds = [],
        int|string $template = 0,
        string $fromName = '',
        string $fromEmail = '',
        string $subject = '',
        string $body = '',
        array $cc = [],
        array $bcc = [],
        string $attachment = 'all',
    ) {
        if (empty($to)) {
            return false;
        }

        global $root_directory, $current_language, $default_language, $default_charset, $current_user;

        if (empty($current_language)) {
            $current_language = $default_language;
        }

        $db = PearDatabase::getInstance();
        $emailMaker = new EMAILMaker_EMAILMaker_Model();

        if (!empty($template)) {
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

                if(file_exists($completePath) && $fileName != '')
                {
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
    }
}