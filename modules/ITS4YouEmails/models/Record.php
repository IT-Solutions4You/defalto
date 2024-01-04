<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouEmails_Record_Model extends Vtiger_Record_Model
{
    public static $FLAG_SENT = 'SENT';
    public static $FLAG_ERROR = 'ERROR';
    public static $FLAG_SAVED = 'SAVED';
    public $relatedToFields = [];
    /**
     * @var ITS4YouEmails_Mailer_Model
     */
    public $mailer;
    public $logo = false;
    public $emailNames = [];

    /**
     * @return string
     * @throws Exception
     */
    public static function getVtigerFromEmailField()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT from_email_field FROM vtiger_systems WHERE from_email_field != ? AND server_type = ?', array('', 'email'));

        return $adb->query_result($result, 0, 'from_email_field');
    }

    /**
     * @param int $userId
     * @param string $type
     * @return array
     * @throws Exception
     */
    public static function getUserDataByType($userId, $type = 'email1')
    {
        $adb = PearDatabase::getInstance();
        $userResult = $adb->pquery(sprintf('SELECT first_name, last_name, %s AS email  FROM vtiger_users WHERE id=?', $type), array($userId));

        return $adb->query_result_rowdata($userResult);
    }

    /**
     * @param string $sourceModule
     * @return string
     */
    public static function getSelectTemplateUrl($sourceRecord, $sourceModule)
    {
        if (vtlib_isModuleActive('EMAILMaker')) {
            return 'module=EMAILMaker&view=Popup&src_record=' . $sourceRecord . '&src_module=' . $sourceModule;
        }

        return 'module=EmailTemplates&view=Popup&src_record=' . $sourceRecord . '&src_module=' . $sourceModule;
    }

    /**
     * @param int $userId
     * @return string
     * @throws Exception
     */
    public static function getSignature($userId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT signature FROM vtiger_users WHERE id=?', array($userId));

        return nl2br($adb->query_result($result, 0, 'signature'));
    }

    /**
     * @throws \ITS4You\PHPMailer\Exception
     */
    public function sendEmail()
    {
        global $ITS4YouEmails_SendingClass;

        $mailer = $this->getMailer();

        if (!empty($ITS4YouEmails_SendingClass) && class_exists($ITS4YouEmails_SendingClass)) {
            $success = $ITS4YouEmails_SendingClass::send($mailer);
        } else {
            $success = $mailer->send();
        }

        return $success;
    }

    public function send()
    {
        $success = false;
        $mailer = $this->getMailer();

        try {
            if (!$this->isEmpty('smtp')) {
                $mailer->retrieveSMTPById($this->get('smtp'));
            } else {
                $mailer->retrieveSMTPVtiger();
            }

            $mailer->isHTML(true);

            $this->retrieveContent();
            $this->retrieveReplyTo();
            $this->retrieveFromEmail();
            $this->retrieveToEmails();
            $this->retrieveCCEmails();
            $this->retrieveBCCEmails();
            $this->retrieveAttachments();
            $this->retrieveLogo();
            $this->retrieveImages();
            $this->retrieveInReplyTo();
            $this->convertImagesToEmbed();

            $success = $this->sendEmail();

            $this->set('subject', $mailer->Subject);
            $this->setBody($mailer->Body);

            if ($success) {
                $this->set('result', vtranslate('LBL_SUCCESS_EMAIL', $this->getModuleName()));
                $this->set('email_flag', self::$FLAG_SENT);

                $mailer->saveMessageId();
                $this->saveEmailToSentFolder();
            } else {
                $this->set('result', vtranslate('LBL_ERROR_EMAIL', $this->getModuleName()) . $mailer->ErrorInfo);
                $this->set('email_flag', self::$FLAG_ERROR);
            }
        } catch (Exception $e) {
            $this->set('result', vtranslate('LBL_ERROR_EMAIL', $this->getModuleName()) . $mailer->ErrorInfo . ' | Exception: ' . $e->getMessage());
            $this->set('email_flag', self::$FLAG_ERROR);
        }

        $this->set('mode', 'edit');
        $this->save();

        return $success;
    }

    /**
     * @return ITS4YouEmails_Mailer_Model|\ITS4You\PHPMailer\PHPMailer
     */
    public function getMailer()
    {
        if (empty($this->mailer)) {
            $this->retrieveMailer();
        }

        return $this->mailer;
    }

    public function setMailer($value)
    {
        $this->mailer = $value;
    }

    public function retrieveMailer()
    {
        $this->setMailer(ITS4YouEmails_Mailer_Model::getCleanInstance());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function retrieveContent()
    {
        $mailer = $this->getMailer();
        $subject = $this->getSubject();
        $body = $this->getBody();

        if (vtlib_isModuleActive('EMAILMaker')) {
            $record = $this->get('related_to');
            $module = getSalesEntityType($record);

            $toEmailIds = $this->getJsonArray('to_email_ids');

            foreach ($toEmailIds as $toEmailId) {
                list($recipientId, $recipientEmail, $recipientModule) = explode('|', $toEmailId);

                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance($module, $record, $this->get('email_template_language'), $recipientId, $recipientModule);
                $EMAILContentModel->setSubject($subject);
                $EMAILContentModel->setBody($body);

                if (!$this->isEmpty('email_template_data')) {
                    foreach ((array)$this->get('email_template_data') as $email_key => $email_value) {
                        $EMAILContentModel->set($email_key, $email_value);
                    }
                }

                $EMAILContentModel->getContent(true, !empty($module));

                $subject = $EMAILContentModel->getSubject();
                $body = $EMAILContentModel->getBody();
                $images = $EMAILContentModel->getEmailImages();

                if (count($images)) {
                    foreach ($images as $imageId => $imageData) {
                        $this->setImage($imageId, $imageData['path'], $imageData['name']);
                    }
                }
            }
        }

        $mailer->Subject = $this->getProcessedSubject($subject);
        $mailer->Body = $this->getProcessedBody($body);
        $mailer->AltBody = $this->getProcessedAltBody($body);
    }

    public function getSubject()
    {
        return strip_tags(decode_html($this->get('subject')));
    }

    public function getBody()
    {
        return decode_html($this->get('body'));
    }

    public function getJsonArray($name)
    {
        $value = $this->get($name);

        if (!is_array($value) && !$this->isEmpty($name)) {
            $value = json_decode(htmlspecialchars_decode($value));
        }

        return (array)$value;
    }

    public function setBody($content)
    {
        if (!$this->isEmpty('images')) {
            $images = (array)$this->get('images');

            foreach ($images as $id => $data) {
                $content = str_replace('cid:' . $id, $data['path'], $content);
            }
        }

        $this->set('body', $content);
    }

    public function setImage($id, $path, $name = null)
    {
        $images = (array)$this->get('images');
        $images[$id] = [
            'cid' => $id,
            'path' => $path,
            'name' => !empty($name) ? $name : basename($path),
        ];

        $this->set('images', $images);
    }

    public function getProcessedSubject($content)
    {
        $content = decode_html($content);

        return $this->convertVariables($content);
    }

    public function convertVariables($content)
    {
        $relatedTo = $this->get('related_to');
        $relatedToModule = getSalesEntityType($relatedTo);

        $content = getMergedDescription($content, Users_Record_Model::getCurrentUserModel()->getId(), 'Users');

        $toEmailIds = $this->getJsonArray('to_email_ids');

        foreach ($toEmailIds as $toEmailId) {
            list($record, $email, $module) = explode('|', $toEmailId);

            if (!empty($module) && !empty($record) && isRecordExists($record)) {
                $content = getMergedDescription($content, $record, $module);
            }
        }

        if (!empty($relatedToModule) && !empty($relatedTo) && isRecordExists($relatedTo)) {
            $content = getMergedDescription($content, $relatedTo, $relatedToModule);
        }

        return $content;
    }

    public function getProcessedBody($content)
    {
        $content = decode_html($content);
        $content = $this->convertVariables($content);
        $content = purifyHtmlEventAttributes($content);
        //$content = decode_emptyspace_html($content);
        $content = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);

        if (strpos($content, '$logo$')) {
            $content = str_replace('$logo$', '<img src="cid:companyLogo" alt="logo"/>', $content);
            $this->logo = true;
        }

        $content = $this->convertUrlsToTrackUrls($content);
        $content = $this->convertCssToInline($content);
        $content .= $this->getTrackImageDetails();

        return $content;
    }

    public function convertUrlsToTrackUrls($content, $type = 'html')
    {
        if ($this->isEmailTrackEnabled()) {
            $extractedUrls = Vtiger_Functions::getUrlsFromHtml($content);

            foreach ($extractedUrls as $sourceUrl => $value) {
                $trackingUrl = $this->getTrackUrlForClicks($sourceUrl);
                $content = $this->replaceLinkWithShortUrl($content, $trackingUrl, $sourceUrl, $type);
            }
        }

        return $content;
    }

    /**
     * @return bool
     */
    public function isEmailTrackEnabled()
    {
        $emailTracking = vglobal('email_tracking');

        if (empty($emailTracking) || 'Yes' === $emailTracking) {
            return true;
        }

        return false;
    }

    /**
     * @param int $parentId
     * @param string $redirectUrl
     * @param string $linkName
     * @return string
     */
    public function getTrackUrlForClicks($redirectUrl = false, $linkName = false)
    {
        $params = [
            'record' => $this->getId(),
            'parentId' => $this->getRelatedTo(),
            'method' => 'click',
        ];

        if ($redirectUrl) {
            $params['redirectUrl'] = $redirectUrl;
        }

        if ($linkName) {
            $params['linkName'] = $linkName;
        }

        return Vtiger_ShortURL_Helper::generateURL([
            'handler_path' => 'modules/ITS4YouEmails/handlers/Tracker.php',
            'handler_class' => 'ITS4YouEmails_Tracker_Handler',
            'handler_function' => 'process',
            'handler_data' => $params,
        ]);
    }

    public function getRelatedTo()
    {
        return intval($this->get('related_to'));
    }

    /**
     * @param string $content
     * @param string $toReplace
     * @param string $search
     * @param string $type
     * @return string
     */
    public function replaceLinkWithShortUrl($content, $toReplace, $search, $type)
    {
        if ('html' === $type) {
            $search = '"' . $search . '"';
            $toReplace = '"' . $toReplace . '"';
        }

        $position = strpos($content, $search);

        if (false !== $position) {
            return substr_replace($content, $toReplace, $position) . substr($content, $position + strlen($search));
        }

        return $content;
    }

    public function convertCssToInline($content)
    {
        if (preg_match('/<style[^>]+>(?<css>[^<]+)<\/style>/s', $content)) {
            require_once 'libraries/InStyle/InStyle.php';

            $inStyle = new InStyle();
            $convertedContent = $inStyle->convert($content);

            if (!empty($convertedContent)) {
                $content = $convertedContent;
            }
        }

        return $content;
    }

    public function getTrackImageDetails()
    {
        if ($this->isEmailTrackEnabled()) {
            $trackURL = Vtiger_ShortURL_Helper::generateURL([
                'handler_path' => 'modules/ITS4YouEmails/handlers/Tracker.php',
                'handler_class' => 'ITS4YouEmails_Tracker_Handler',
                'handler_function' => 'process',
                'handler_data' => [
                    'record' => $this->getId(),
                    'parentId' => $this->getRelatedTo(),
                    'method' => 'open',
                ]
            ]);

            return "<img src='$trackURL' alt='' width='1' height='1'>";
        }

        return null;
    }

    public function getProcessedAltBody($body)
    {
        $plainBody = decode_html($body);
        $plainBody = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', '', $plainBody);
        $plainBody = preg_replace(array("/<p>/i", "/<br>/i", "/<br \/>/i"), array("\n", "\n", "\n"), $plainBody);
        $plainBody = strip_tags($plainBody);
        $plainBody = (new ToAscii())->convertToAscii($plainBody, '');

        return $this->convertUrlsToTrackUrls($plainBody, 'plain');
    }

    public function retrieveReplyTo()
    {
        $mailer = $this->getMailer();

        if (!$this->isEmpty('reply_email')) {
            $replyName = !$this->isEmpty('reply_name') ? $this->get('reply_name') : $this->getEmailName($this->get('reply_email_ids'));

            $mailer->addReplyTo($this->get('reply_email'), $replyName);
        }
    }

    public function getEmailName($emailId)
    {
        list($record, $email, $module) = explode('|', $emailId);

        $module = trim(decode_html($module));

        if ($record && is_numeric($record)) {
            $names = getEntityName($module, [$record]);

            return decode_html($names[$record]);
        } elseif ('email' === $record && $module) {
            return $module;
        }

        return '';
    }

    /**
     * @throws \ITS4You\PHPMailer\Exception
     */
    public function retrieveFromEmail()
    {
        $mailer = $this->getMailer();

        if (!empty($mailer->FromName)) {
            $fromName = $mailer->FromName;
        } elseif (!$this->isEmpty('from_name')) {
            $fromName = $this->get('from_name');
        } else {
            $fromName = $this->getEmailName($this->get('from_email_ids'));
        }

        if (!empty($mailer->From)) {
            $fromEmail = $mailer->From;
        } else {
            $fromEmail = $this->get('from_email');
        }

        $mailer->setFrom($fromEmail, $fromName);
    }

    /**
     * @throws Exception
     */
    public function retrieveToEmails()
    {
        $mailer = $this->getMailer();
        $toEmailsNames = $this->getEmailNames('to_email_ids');

        foreach ($this->getToEmails() as $toKey => $toEmail) {
            if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                $mailer->addAddress($toEmail, $toEmailsNames[$toKey]);
            }
        }
    }

    public function getEmailNames($name)
    {
        if (!isset($this->emailNames[$name])) {
            $this->retrieveEmailNames($name);
        }

        return $this->emailNames[$name];
    }

    public function setEmailNames($name, $values)
    {
        $this->emailNames[$name] = $values;
    }

    public function retrieveEmailNames($name)
    {
        $emailIds = $this->getJsonArray($name);

        foreach ($emailIds as $key => $emailId) {
            $emailIds[$key] = $this->getEmailName($emailId);
        }

        $this->setEmailNames($name, $emailIds);
    }

    public function getToEmails()
    {
        return $this->getJsonArray('to_email');
    }

    /**
     * @throws Exception
     */
    public function retrieveCCEmails()
    {
        $mailer = $this->getMailer();
        $ccEmailsNames = $this->getEmailNames('cc_email_ids');

        foreach ($this->getCCEmails() as $ccKey => $ccEmail) {
            if (filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                $mailer->addCC($ccEmail, $ccEmailsNames[$ccKey]);
            }
        }
    }

    public function getCCEmails()
    {
        return $this->getJsonArray('cc_email');
    }

    /**
     * @throws Exception
     */
    public function retrieveBCCEmails()
    {
        $mailer = $this->getMailer();
        $bccEmailsNames = $this->getEmailNames('bcc_email_ids');

        foreach ($this->getBCCEmails() as $bccKey => $bccEmail) {
            if (filter_var($bccEmail, FILTER_VALIDATE_EMAIL)) {
                $mailer->addBCC($bccEmail, $bccEmailsNames[$bccKey]);
            }
        }
    }

    public function getBCCEmails()
    {
        return $this->getJsonArray('bcc_email');
    }

    /**
     * @throws Exception
     */
    public function retrieveAttachments()
    {
        $mailer = $this->getMailer();

        foreach ($this->getAttachments() as $attachment) {
            $fileNameWithPath = vglobal('root_directory') . $attachment['filenamewithpath'];

            if (is_file($fileNameWithPath)) {
                $mailer->addAttachment($fileNameWithPath, $attachment['attachment']);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function getAttachments()
    {
        $attachments = $this->getRelatedAttachments();
        $documents = $this->getRelatedDocuments();

        if (!empty($documents)) {
            foreach ($documents as $document) {
                $flag = false;

                foreach ($attachments as $attachment) {
                    if ($attachment['fileid'] == $document['fileid']) {
                        $flag = true;
                        break;
                    }
                }

                if (!$flag) {
                    $attachments[] = $document;
                }
            }
        }

        return $attachments;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRelatedAttachments()
    {
        $attachments = array();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_seattachmentsrel.crmid=?',
            array($this->getId())
        );

        if ($adb->num_rows($result)) {
            while ($row = $adb->fetchByAssoc($result)) {
                $attachmentId = $row['attachmentsid'];
                $fileName = decode_html($row['name']);
                $storedName = !empty($row['storedname']) ? decode_html($row['storedname']) : $fileName;
                $path = $row['path'];
                $fileNameWithPath = $path . $attachmentId . '_' . $storedName;

                $attachments[] = array(
                    'attachment' => $fileName,
                    'fileid' => $attachmentId,
                    'storedname' => $storedName,
                    'path' => $path,
                    'filenamewithpath' => $fileNameWithPath,
                    'size' => filesize($fileNameWithPath),
                    'type' => $row['type'],
                    'cid' => $row['cid'],
                );
            }
        }

        return $attachments;
    }

    public function getRelatedDocuments()
    {
        $adb = PearDatabase::getInstance();

        $result = $adb->pquery(
            'SELECT * FROM vtiger_senotesrel
						INNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?
						INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid
						INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
						WHERE vtiger_crmentity.deleted=0',
            array($this->getId())
        );
        $documents = array();

        if ($adb->num_rows($result)) {
            while ($row = $adb->fetchByAssoc($result)) {
                $fileName = decode_html($row['name']);
                $storedName = !empty($row['storedname']) ? decode_html($row['storedname']) : $fileName;

                $documents[] = array(
                    'name' => $row['filename'],
                    'docid' => $row['notesid'],
                    'path' => $row['path'],
                    'type' => $row['type'],
                    'fileid' => $row['attachmentsid'],
                    'attachment' => $fileName,
                    'storedname' => $storedName,
                    'size' => $this->getFormattedFileSize($row['filesize']),
                    'filenamewithpath' => $row['path'] . $row['attachmentsid'] . '_' . $storedName
                );
            }
        }
        return $documents;
    }

    /**
     * @param $value
     * @return string
     */
    public function getFormattedFileSize($value)
    {
        if (1024 > $value) {
            $value = sprintf('%0.2fB', round($value, 2));
        } elseif (1024 < $value && 1048576 > $value) {
            $value = sprintf('%0.2fKB', round($value / 1024, 2));
        } elseif (1048576 < $value) {
            $value = sprintf('%0.2fMB', round($value / (1024 * 1024), 2));
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public function retrieveLogo()
    {
        if ($this->logo) {
            $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
            $companyLogoDetails = $companyDetails->getLogo();

            $mailer = $this->getMailer();
            $mailer->AddEmbeddedImage($companyLogoDetails->get('imagepath'), 'companyLogo', 'attachment', 'base64', 'image/jpg');
        }
    }

    public function retrieveImages()
    {
        if (!$this->isEmpty('images')) {
            $mailer = $this->getMailer();
            $images = (array)$this->get('images');

            foreach ($images as $id => $data) {
                $mailer->AddEmbeddedImage($data['path'], $id, $data['name']);
            }
        }
    }

    public function retrieveInReplyTo()
    {
        $toEmailIds = $this->getJsonArray('to_email_ids');
        $toEmailId = reset($toEmailIds);

        list($record, $address, $module) = explode('|', $toEmailId);

        if (empty($record) || empty($module) || 'Users' === $module) {
            return;
        }

        $mailer = $this->getMailer();
        $mailer->MessageRecordID = $record;

        $inReplyToMessageId = $mailer->getMessageIdFromMailScanner();
        $generatedMessageId = $mailer->getMessageId();

        if (empty($inReplyToMessageId)) {
            $inReplyToMessageId = $generatedMessageId;
        }

        if (!empty($inReplyToMessageId)) {
            $mailer->AddCustomHeader('In-Reply-To', $inReplyToMessageId);
        }

        if (!empty($generatedMessageId)) {
            $mailer->MessageID = $generatedMessageId;
        }
    }

    /**
     * @throws Exception
     */
    public function convertImagesToEmbed()
    {
        $mailer = $this->getMailer();
        $re = '/<img.*?src="(.*?)"[^>]*>/';
        preg_match_all($re, $mailer->Body, $matches, PREG_SET_ORDER, 0);
        $num = 0;

        foreach ($matches as $match) {
            list($image, $url) = $match;

            if ($this->isImageUsed($url) || $this->isImageEmbed($url)) {
                continue;
            }

            $num++;
            $cid = 'EmailsImage' . $num;
            $embedUrl = $this->getImageEmbed($url);

            if ($mailer->addEmbeddedImage($embedUrl, $cid, basename($embedUrl))) {
                $mailer->Body = str_replace($image, $mailer->replaceImageSrc($image, $url, 'cid:' . $cid), $mailer->Body);
                $this->setImage($cid, $url);
            }
        }
    }

    public function isImageUsed($url)
    {
        $images = (array)$this->get('images');

        return (bool)array_search($url, array_column($images, 'path'));
    }

    public function isImageEmbed($url)
    {
        return substr($url, 0, 4) === 'cid:';
    }

    public function getImageEmbed($url)
    {
        $replaceUrl = $url;
        $siteUrl = vglobal('site_URL');
        $rootDirectory = vglobal('root_directory');

        return trim(str_replace(array($siteUrl, $rootDirectory, trim($siteUrl, '/\\'), trim($rootDirectory, '/\\')), array('', ''), $replaceUrl), '/\\');
    }

    public function saveEmailToSentFolder()
    {
        if (true === $this->get('skip_save_email_to_sent_folder')) {
            return;
        }

        $mailer = $this->getMailer();
        $mailString = $mailer->getMailString();
        $mailBoxModel = MailManager_Mailbox_Model::activeInstance();
        $folderName = $mailBoxModel->folder();

        if (!empty($folderName) && !empty($mailString)) {
            $connector = MailManager_Connector_Connector::connectorWithModel($mailBoxModel, '');
            $message = str_replace("\n", "\r\n", $mailString);

            if (function_exists('mb_convert_encoding')) {
                $folderName = mb_convert_encoding($folderName, 'UTF7-IMAP', 'UTF-8');
            }

            if ($connector->mBox) {
                imap_append($connector->mBox, $connector->mBoxUrl . $folderName, $message, "\\Seen");
            }
        }
    }

    public function save()
    {
        $this->retrieveRelatedToInfo();

        parent::save();
    }

    public function retrieveRelatedToInfo()
    {
        if ('edit' !== $this->get('mode')) {
            $emailIds = explode(',', $this->get('to_email_ids'));

            foreach ($emailIds as $emailId) {
                list($emailRecord, $emailAddress, $emailModule) = explode('|', trim($emailId));

                if (!empty($emailRecord) && !empty($emailModule)) {
                    $field = $this->getRelatedToFields()[$emailModule];

                    if ($this->isEmpty($field)) {
                        $this->set($field, $emailRecord);
                    }
                }
            }
        }
    }

    public function getRelatedToFields()
    {
        if (empty($this->relatedToFields)) {
            $this->retrieveRelatedToFields();
        }

        return $this->relatedToFields;
    }

    public function retrieveRelatedToFields()
    {
        $module = Vtiger_Module_Model::getInstance('ITS4YouEmails');
        $block = Vtiger_Block_Model::getInstance('LBL_RELATED_TO', $module);
        $this->relatedToFields = [
            'Users' => 'user_id',
        ];

        /** @var Vtiger_Field_Model $field */
        foreach ($block->getFields() as $field) {
            $fieldName = $field->get('name');

            if ('related_to' === $fieldName || 10 !== intval($field->get('uitype'))) {
                continue;
            }

            $referenceModules = (array)$field->getReferenceList();
            $this->relatedToFields[reset($referenceModules)] = $fieldName;
        }
    }

    public function clearRelatedToInfo()
    {
        foreach ($this->getRelatedToFields() as $module => $field) {
            $this->set($field, '');
        }
    }

    public function isEmailOpenedRecently($recordId)
    {
        $lastOpenTime = date('Y-m-d H:i:s', strtotime("-1 hours"));
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT 1 FROM its4you_emails_access WHERE record_id = ? AND mail_id = ? AND access_time > ?', [$recordId, $this->getId(), $lastOpenTime]);

        return boolval($adb->num_rows($result));
    }

    public function saveAccess($recordId, $accessId = '')
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('INSERT INTO its4you_emails_access (record_id, mail_id, access_id, access_time) VALUES (?,?,?,?)', [
            $recordId,
            $this->getId(),
            $accessId,
            date('Y-m-d H:i:s')
        ]);
    }

    public function saveAccessCount($value)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'UPDATE its4you_emails SET access_count=? WHERE its4you_emails_id=?',
            [$value, $this->getId()]
        );
    }

    public function saveClickCount($value)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'UPDATE its4you_emails SET click_count=? WHERE its4you_emails_id=?',
            [$value, $this->getId()]
        );
    }

    public function saveAttachment($filePath, $fileName, $storedName, $ownerId, $fileType = '', $description = '')
    {
        $adb = PearDatabase::getInstance();
        $recordId = $adb->getUniqueID('vtiger_crmentity');
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentDate = $adb->formatDate(date('Y-m-d H:i:s'), true);
        $params1 = array(
            'crmid' => $recordId,
            'smcreatorid' => $currentUser->id,
            'smownerid' => $ownerId,
            'setype' => 'Documents Attachment',
            'description' => $description,
            'createdtime' => $currentDate,
            'modifiedtime' => $currentDate,
        );
        $params2 = array(
            'attachmentsid' => $recordId,
            'name' => $fileName,
            'description' => $description,
            'type' => $fileType,
            'path' => $filePath,
        );

        if (columnExists('storedname', 'vtiger_attachments')) {
            $params2['storedname'] = $storedName;
        }

        $adb->pquery($this->getInsertQuery('vtiger_crmentity', $params1), $params1);
        $adb->pquery($this->getInsertQuery('vtiger_attachments', $params2), $params2);

        $this->saveAttachmentRelation($recordId);
    }

    public function getInsertQuery($table, $params)
    {
        return sprintf('INSERT INTO %s (%s) VALUES (%s)', $table, implode(',', array_keys($params)), generateQuestionMarks($params));
    }

    /**
     * @param int $recordId
     * @return void
     */
    public function saveAttachmentRelation($recordId)
    {
        if (empty($recordId)) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $adb->pquery('INSERT INTO vtiger_seattachmentsrel (crmid, attachmentsid) VALUES (?,?)', array($this->getId(), $recordId));
    }

    public function setEmailRelation($parentRecord)
    {
        $parentModule = getSalesEntityType($parentRecord);

        if ($parentModule) {
            $parentModuleModel = Vtiger_Module_Model::getInstance($parentModule);
            $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $this->getModule());

            if ($relationModel) {
                $relationModel->addRelation($parentRecord, $this->getId());
            }
        }
    }

    public function saveDocumentRelation($recordId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('INSERT INTO vtiger_senotesrel (crmid, notesid)VALUES (?,?)', array($this->getId(), $recordId));
    }

    public function getAddressesFromEmailIds($emailIds)
    {
        $emails = array();

        foreach ($emailIds as $emailId) {
            list($record, $address, $module) = explode('|', $emailId);

            $emails[] = $address;
        }

        return $emails;
    }

    /**
     * @throws Exception
     */
    public function savePDF()
    {
        if (!vtlib_isModuleActive('PDFMaker') && !class_exists('PDFMaker_PDFMaker_Model')) {
            return;
        }

        $templateIds = $this->getTemplateIds();

        if (empty($templateIds)) {
            return;
        }

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $PDFMaker = new PDFMaker_PDFMaker_Model();
        $PDFTemplateLanguage = $this->getPDFTemplateLanguage();
        $focus = $this->getEntity();
        $relatedModule = $this->getRelatedModule();
        $relatedRecords = $this->getRelatedRecords();
        $fileName = $this->getFileName();

        if (!$this->isEmpty('is_merge_templates')) {
            $PDFMaker->createPDFAndSaveFile($request, $templateIds, $focus, $relatedRecords, $fileName, $relatedModule, $PDFTemplateLanguage);
        } else {
            foreach ($templateIds as $templateId) {
                $PDFMaker->createPDFAndSaveFile($request, $templateId, $focus, $relatedRecords, $fileName, $relatedModule, $PDFTemplateLanguage);
            }
        }
    }

    /**
     * @return array
     */
    public function getTemplateIds()
    {
        return array_filter(explode(';', $this->get('pdf_template_ids')));
    }

    /**
     * @return string
     */
    public function getPDFTemplateLanguage()
    {
        if ($this->isEmpty('pdf_template_language')) {
            return $this->get('email_template_language');
        }

        return $this->get('pdf_template_language');
    }

    public function getRelatedModule()
    {
        if ($this->isEmpty('related_to_module')) {
            $this->set('related_to_module', getSalesEntityType($this->get('related_to')));
        }

        return $this->get('related_to_module');
    }

    public function getRelatedRecords()
    {
        return [$this->get('related_to')];
    }

    /**
     * @throws Exception
     */
    public function getFileName()
    {
        $relatedModule = $this->getRelatedModule();

        if (!empty($relatedModule)) {
            $fieldName = $this->getNumberFieldName($relatedModule);
        }

        if (!empty($fieldName) && !$this->isEmpty($fieldName)) {
            return (new PDFMaker_PDFMaker_Model())->generate_cool_uri($this->get($fieldName)) . '.pdf';
        }

        return $this->getDefaultFileName();
    }

    /**
     * @return string
     * @throws Exception
     * @var string $module
     */
    public function getNumberFieldName($module)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT fieldname FROM vtiger_field WHERE uitype=? AND tabid=?', [4, getTabId($module)]);

        return $adb->query_result($result, 0, 'fieldname');
    }

    public function getDefaultFileName()
    {
        return 'doc_' . date('ymdHi') . '.pdf';
    }

    public function getAccessCountValue()
    {
        return $this->get('access_count');
    }
}