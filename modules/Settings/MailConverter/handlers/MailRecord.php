<?php
/*********************************************************************************
 ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

/**
 * This class provides structured way of accessing details of email.
 */
class Settings_MailConverter_MailRecord_Handler
{
    protected $moduleName = 'MailConverter';
    public array $_attachments = [];
    /**
     * @var array
     */
    public array $_bcc;
    public string $_body = '';
    /**
     * @var array
     */
    public array $_cc;
    /**
     * @var int
     */
    public int $_date;
    /**
     * @var array
     */
    public array $_from;
    /**
     * @var string
     */
    public string $_fromname;
    public array $_inline_attachments = [];
    /**
     * @var string
     */
    public string $_subject;
    /**
     * @var array
     */
    public array $_to = [];
    /**
     * @var string|null
     */
    public string|null $_uniqueid = null;
    /** DEBUG Functionality. */
    public $debug = false;
    protected array $fromName;
    /**
     * Sets the Imap connection
     * @var null|object
     */
    protected null|object $mBox;
    /**
     * @var null|object
     */
    protected null|object $mFolder;
    /**
     * @var string
     */
    protected string $mFolderName = '';
    /**
     * @var false|object
     */
    protected false|object $mBoxMessage;
    protected int $mMsgNo = 0;

    // Flag to avoid re-parsing the email body.
    /**
     * Marks the mail Read/UnRead
     * @var Boolean
     */
    protected bool $mRead = false;
    protected int $mUid = 0;
    protected array $toName;
    protected null|object $mBoxFolder = null;

    /**
     * String representation of the object.
     */
    public function __toString()
    {
        $toString = 'FROM: [' . implode(',', $this->_from) . ']';
        $toString .= ',TO: [' . implode(',', $this->_to) . ']';

        if (!empty($this->_cc)) {
            $toString .= ',CC: [' . implode(',', $this->_cc) . ']';
        }
        if (!empty($this->_bcc)) {
            $toString .= ',BCC: [' . implode(',', $this->_bcc) . ']';
        }
        $toString .= ',DATE: [' . $this->_date . ']';
        $toString .= ',SUBJECT: [' . $this->_subject . ']';

        return $toString;
    }

    /**
     * @return void
     */
    public function clearAttachments(): void
    {
        $this->_attachments = [];
        $this->_inline_attachments = [];
    }

    /**
     * Gets the Mail Attachment
     * @return array List of Attachments
     */
    public function getAttachments()
    {
        return $this->_attachments;
    }

    /**
     * Gets the Mail BCC Email Addresses
     * @return array Email(s)
     */
    public function getBCC(): array
    {
        return $this->_bcc;
    }

    public function setBCC(array $value): void
    {
        $this->_bcc = $value;
    }

    /**
     * Gets the Mail Body
     * @param bool $safeHtml
     * @return string
     */
    public function getBody(bool $safeHtml = true): string
    {
        $body = $this->_body;

        if ($safeHtml) {
            $body = MailManager_Utils_Helper::safe_html_string($body);
        }

        return $body;
    }

    public function setBody(string $value): void
    {
        $this->_body = $value;
    }

    public function getBodyImage($content, $contentType)
    {
        return sprintf('data:%s;base64,%s', $contentType, base64_encode($content));
    }

    public function getBoxFolder()
    {
        return $this->mBoxFolder;
    }

    /**
     * @return object|bool
     */
    public function getBoxMessage(): object|bool
    {
        return $this->mBoxMessage;
    }

    /**
     * Gets the Mail CC Email Addresses
     * @return array Email(s)
     */
    public function getCC(): array
    {
        return $this->_cc;
    }

    public function setCC(array $value): void
    {
        $this->_cc = $value;
    }

    /**
     * Gets the Mail Date
     * @param Boolean $format
     * @return string Date
     */
    public function getDate($format = false)
    {
        $date = $this->_date;

        if ($date) {
            if ($format) {
                $dateTimeFormat = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $date));
                [$date, $time, $AMorPM] = explode(' ', $dateTimeFormat);

                $pos = strpos($dateTimeFormat, date(DateTimeField::getPHPDateFormat()));

                if ($pos === false) {
                    return $date . ' ' . $time . ' ' . $AMorPM;
                } else {
                    return vtranslate('LBL_TODAY') . ' ' . $time . ' ' . $AMorPM;
                }
            } else {
                return Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s', $date));
            }
        }

        return '';
    }

    /**
     * Sets the Mail Date
     * @param int $date time value
     */
    public function setDate($date)
    {
        $this->_date = $date;
    }

    public function getEmailAddresses(array $values): array
    {
        $emails = [];

        foreach ($values as $value) {
            $emails[] = $value->mail;
        }

        return $emails;
    }

    public function getEmailNames(array $values)
    {
        $names = [];

        foreach ($values as $value) {
            $names[] = $value->full;
        }

        return $names;
    }

    public function getFolder()
    {
        return $this->mFolder;
    }

    /**
     * @return mixed
     */
    public function getFolderName(): string
    {
        if (!empty($this->mFolderName)) {
            return $this->mFolderName;
        }

        $folder = $this->getFolder();
        $boxFolder = $this->getBoxFolder();
        $name = '';

        if ($folder) {
            $name = $folder->getName();
        }

        if ($boxFolder) {
            $name = $boxFolder->name;
        }

        $this->setFolderName($name);


        return $this->mFolderName;
    }

    /**
     * Gets the Mail From
     * @return array
     */
    public function getFrom(): array
    {
        return $this->_from;
    }

    /**
     * Sets the Mail From Email Address
     * @param array $from Email
     */
    public function setFrom(array $from): void
    {
        $this->_from = $from;
    }

    public function getFromName($length = 0): string
    {
        $value = implode(', ', $this->fromName);

        if ($length) {
            $value = substr($value, 0, $length);
        }

        return $value;
    }

    public function setFromName(array $fromName): void
    {
        $this->fromName = $fromName;
    }

    /**
     * @return array
     */
    public function getInlineAttachments()
    {
        return $this->_inline_attachments;
    }

    /**
     * Gets the Mail Message Number
     * @return Integer
     */
    public function getMsgNo()
    {
        return $this->mMsgNo;
    }

    /**
     * Gets the Mail Subject
     * @param Boolean $safehtml
     * @return String
     */
    public function getSubject($safeHtml = true): string
    {
        $mailSubject = str_replace('_', ' ', $this->_subject);

        if ($safeHtml) {
            return MailManager_Utils_Helper::safe_html_string($mailSubject);
        }

        return $mailSubject;
    }

    /**
     * Sets the Mail Subject
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->_subject = $subject;
    }

    /**
     * Gets the Mail To Email Addresses
     * @return array Email(s)
     */
    public function getTo(): array
    {
        return $this->_to;
    }

    /**
     * Sets the Mail To Email Address
     * @param array $to Email
     */
    public function setTo(array $to): void
    {
        $this->_to = $to;
    }

    public function getToName($length = 0): string
    {
        $value = implode(', ', $this->toName);

        if ($length) {
            $value = substr($value, 0, $length);
        }

        return $value;
    }

    public function setToName(array $toName): void
    {
        $this->toName = $toName;
    }

    public function getUid(): int
    {
        return $this->mUid;
    }

    /**
     * Gets the Mail Unique Identifier
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->_uniqueid;
    }

    /**
     * @param string|null $value
     * @return void
     */
    public function setUniqueId(string|null $value): void
    {
        $this->_uniqueid = $value;
    }

    /**
     * Checks if the Mail is read
     * @return Boolean
     */
    public function isRead()
    {
        return $this->mRead;
    }

    function log($message = false)
    {
        if (!$message) {
            $message = $this->__toString();
        }

        global $log;
        if ($log && $this->debug) {
            $log->debug($message);
        } elseif ($this->debug) {
            echo var_export($message, true) . "\n";
        }
    }

    public function replaceInlineAttachments()
    {
        $body = $this->getBody();

        foreach ($this->getInlineAttachments() as $attachment) {
            if (!empty($attachment['attachment_url'])) {
                $image = $attachment['attachment_url'];
            } else {
                $image = $this->getBodyImage($attachment['data'], $attachment['type']);
            }

            $body = str_replace('cid:' . $attachment['cid'], $image, $body);
        }

        $this->setBody($body);
    }

    /**
     * @throws AppException
     */
    public function retrieveAttachments($withContent = true, $attachmentName = null, int $attachmentId = null): void
    {
        $this->clearAttachments();
        $this->retrieveAttachmentsFromDB($withContent, $attachmentName, $attachmentId);
        $this->retrieveAttachmentsFromMessage();
        $this->replaceInlineAttachments();
    }

    /**
     * @param bool $withContent
     * @param string|null $aName
     * @param int|null $aId
     * @return void
     */
    public function retrieveAttachmentsFromDB(bool $withContent, string|null $aName = null, int|null $aId = null)
    {
    }

    public function retrieveAttachmentsFromMessage(): void
    {
        if (!empty($this->_attachments)) {
            return;
        }

        if (!$this->validateBoxMessage()) {
            return;
        }

        $mMessage = $this->getBoxMessage();
        $attachments = $mMessage->getAttachments();

        foreach ($attachments as $attachment) {
            $attributes = $attachment->getAttributes();
            $cId = $attributes['id'];
            $content = $attachment->content;
            $name = $attributes['filename'];
            $disposition = $attributes['disposition'];
            $size = $attributes['size'];

            if ('inline' === $disposition) {
                $this->_inline_attachments[] = ['cid' => $cId, 'filename' => $name, 'data' => $content, 'type' => $attributes['content_type'], 'size' => $size];
            } else {
                $this->_attachments[] = ['filename' => $name, 'data' => $content, 'type' => $attributes['content_type'], 'size' => $size];
            }
        }
    }

    public function retrieveBody(): void
    {
        if (!$this->validateBoxMessage()) {
            return;
        }

        $mMessage = $this->getBoxMessage();

        if ($mMessage) {
            $body = $mMessage->getHTMLBody();

            if (empty($body)) {
                $body = $mMessage->getTextBody();
            }

            $this->setBody($body);
        }
    }

    /**
     * @throws AppException
     */
    public function retrieveRecord(): void
    {
        $this->retrieveRecordFromDB();
        $this->retrieveRecordFromMessage();
    }

    public function retrieveRecordFromDB()
    {
    }

    public function retrieveRecordFromMessage(): void
    {
        if (!$this->validateBoxMessage()) {
            return;
        }

        $mMessage = $this->getBoxMessage();
        $attributes = $mMessage->getAttributes();

        $this->setUid(intval($mMessage->getUid()));
        $this->setUniqueId($attributes['message_id']);
        $this->setMsgNo((int)$attributes['msgn']);

        $from = $attributes['from']->all();
        $this->setFrom($this->getEmailAddresses($from));
        $this->setFromName($this->getEmailNames($from));
        $to = $attributes['to']->all();
        $this->setTo($this->getEmailAddresses($to));
        $this->setToName($this->getEmailNames($to));
        $this->setCC(!empty($attributes['cc']) ? $this->getEmailAddresses($attributes['cc']->all()) : []);
        $this->setBCC(!empty($attributes['bcc']) ? $this->getEmailAddresses($attributes['bcc']->all()) : []);

        $this->setSubject($mMessage->getSubject());
        $this->setDate($attributes['date']->get()->getTimestamp());
        $this->setRead('Seen' === $mMessage->getFlags()->get('seen'));
    }

    public function setBox($value)
    {
        $this->mBox = $value;
    }

    public function setBoxFolder($value): void
    {
        $this->mBoxFolder = $value;
    }

    public function setBoxMessage($value)
    {
        $this->mBoxMessage = $value;
    }

    public function setFolder($value)
    {
        $this->mFolder = $value;
    }

    /**
     * @param string $mFolderName
     */
    public function setFolderName(string $value): void
    {
        $this->mFolderName = $value;
    }

    /**
     * Sets the Mail Message Number
     * @param Integer $value
     */
    public function setMsgNo(int $value): void
    {
        $this->mMsgNo = $value;
    }

    /**
     * Sets if the Mail is read
     * @param Boolean $read
     */
    public function setRead($read)
    {
        $this->mRead = $read;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setUid(int $value): void
    {
        $this->mUid = $value;
    }

    /**
     * @return bool
     */
    public function validateBoxMessage(): bool
    {
        return !empty($this->mBoxMessage);
    }

    /**
     * @return bool
     */
    public function validateUid(): bool
    {
        return !empty($this->mUid);
    }

    /**
     * @param string $fileName
     * @param string $fileContent
     * @param string $fileType
     * @return array|null
     * @throws AppException
     */
    public function saveAttachmentFile(string $fileName, string $fileContent, string $fileType): null|array
    {
        if (empty($fileContent)) {
            return null;
        }

        $attachment = Core_Attachment_Model::getInstance($this->moduleName);
        $attachment->retrieveDefault($fileName);
        $attachment->setType($fileType);
        $attachment->saveFile($fileContent);

        if ($attachment->validateSaveFile()) {
            $attachment->save();
        }

        return $attachment->getData();
    }

    public function getBodyText(): string
    {
        return strip_tags($this->getBody());
    }
}