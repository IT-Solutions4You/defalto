<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ITS4YouEmails_Mailer_Model extends PHPMailer
{
    public $debug = false;
    public $embedImages;
    public $Host;
    public $Username;
    public $Password;
    public $SMTPAuth;
    public $SMTPSecure;
    public $Body;
    public $Subject;
    public $SMTPDebug;
    public $MessageRecordID;

    /**
     * @return ITS4YouEmails_Mailer_Model
     */
    public static function getCleanInstance()
    {
        return new self();
    }

    /**
     * @throws Exception
     */
    public function retrieveSMTP($userId)
    {
        if (vtlib_isModuleActive('ITS4YouSMTP') && method_exists('ITS4YouSMTP_Record_Model', 'getInstanceByUserId')) {
            $smtpRecord = ITS4YouSMTP_Record_Model::getInstanceByUserId($userId);

            if ($smtpRecord) {
                $this->setMailerType($smtpRecord->get('mailer_type'));
                $this->setSMTP($smtpRecord->get('server'), $smtpRecord->get('server_username'), $smtpRecord->getDecodedPassword(), !$smtpRecord->isEmpty('smtp_auth'));

                if (!$smtpRecord->isEmpty('from_email_field')) {
                    $this->setFrom($smtpRecord->get('from_email_field'));
                }
            }
        } else {
            $this->retrieveSMTPVtiger();
        }
    }

    public function retrieveSMTPVtiger()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_systems WHERE server_type=?', ['email']);

        if ($adb->num_rows($result)) {
            $row = $adb->query_result_rowdata($result);

            $this->setSMTP($row['server'], $row['server_username'], self::fromProtectedText($row['server_password']), !empty($row['smtp_auth']));

            if (!empty($row['from_email_field'])) {
                $this->setFrom($row['from_email_field']);
            }
        }
    }

    public function setMailerType($type)
    {
        if (!empty($type)) {
            global $ITS4YouEmails_Mailer;

            $ITS4YouEmails_Mailer = $type;
        }
    }

    public function retrieveSMTPById($record)
    {
        if (vtlib_isModuleActive('ITS4YouSMTP') && method_exists('ITS4YouSMTP_Record_Model', 'getInstanceBySMTPId')) {
            $smtpRecord = ITS4YouSMTP_Record_Model::getInstanceBySMTPId($record);

            if ($smtpRecord) {
                $this->setMailerType($smtpRecord->get('mailer_type'));
                $this->setSMTP($smtpRecord->get('server'), $smtpRecord->get('server_username'), $smtpRecord->getDecodedPassword(), !$smtpRecord->isEmpty('smtp_auth'));

                if (!$smtpRecord->isEmpty('from_email_field')) {
                    $this->setFrom($smtpRecord->get('from_email_field'));
                }
            }
        } else {
            $this->retrieveSMTPVtiger();
        }
    }

    public function retrieveMailer()
    {
        global $ITS4YouEmails_Mailer;

        if (!empty($ITS4YouEmails_Mailer)) {
            switch ($ITS4YouEmails_Mailer) {
                case 'smtp':
                    $this->isSMTP();
                    break;
                case 'sendmail':
                    $this->isSendmail();
                    break;
                case 'qmail':
                    $this->isQmail();
                    break;
                case 'mail':
                    $this->isMail();
                    break;
            }
        } else {
            $this->IsSMTP();
        }
    }

    public function retrieveSMTPOptions()
    {
        global $ITS4YouEmails_SMTPOptions;

        if (!empty($ITS4YouEmails_SMTPOptions)) {
            $this->SMTPOptions = $ITS4YouEmails_SMTPOptions;
        } else {
            $this->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param bool $auth
     * @return void
     */
    public function setSMTP($host, $username, $password, $auth = true)
    {
        $this->retrieveMailer();
        $this->retrieveSMTPOptions();

        if ($this->debug) {
            $this->SMTPDebug = 2;
        }

        $this->Host = $host;
        $this->Username = $username;
        $this->Password = $password;
        $this->SMTPAuth = $auth;

        $hostInfo = explode('://', $this->Host);
        $smtpSecure = $hostInfo[0];

        if ('tls' === $smtpSecure) {
            $this->SMTPSecure = $smtpSecure;
            $this->Host = $hostInfo[1];
        }
    }

    public function replaceImageSrc($content, $fromUrl, $toUrl)
    {
        return str_replace(
            array(
                'src="' . $fromUrl . '"',
                "src='" . $fromUrl . "'",
            ),
            array(
                'src="' . $toUrl . '"',
                'src="' . $toUrl . '"'
            ),
            $content
        );
    }

    public function sendPreview($from, $to)
    {
        try {
            $this->setFrom($from, 'From Email');
            $this->addAddress($to, 'To Email');
            $this->isHTML(true);
            $this->Subject = 'Test SMTP: Preview email subject';
            $this->Body = 'Test SMTP: <b>Preview email body</b>';
            $this->AltBody = strip_tags($this->Body);
            $this->send();

            echo 'Mail has been sent successfully!';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->ErrorInfo}";
        }
    }

    /**
     * Function to mask input text.
     */
    static function toProtectedText($text)
    {
        if (empty($text)) {
            return $text;
        }

        require_once 'include/utils/encryption.php';
        $encryption = new Encryption();

        return '$ve$' . $encryption->encrypt($text);
    }

    /*
     * Function to determine if text is masked.
     */
    static function isProtectedText($text)
    {
        return !empty($text) && (strpos($text, '$ve$') === 0);
    }

    /*
     * Function to unmask the text.
     */
    static function fromProtectedText($text)
    {
        if (static::isProtectedText($text)) {
            require_once 'include/utils/encryption.php';
            $encryption = new Encryption();

            return $encryption->decrypt(substr($text, 4));
        }

        return $text;
    }

    public function getMailString()
    {
        return $this->MIMEHeader . $this->MIMEBody;
    }

    public function getMessageIdFromMailScanner()
    {
        $crmId = $this->MessageRecordID;
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT messageid FROM vtiger_mailscanner_ids WHERE crmid=?', array($crmId));

        return $db->query_result($result, 'messageid');
    }

    public function getMessageId()
    {
        return sprintf("<%s.%s@%s>", base_convert(microtime(), 10, 36), base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36), gethostname());
    }

    public function saveMessageId()
    {
        $messageId = $this->MessageID;
        $crmId = $this->MessageRecordID;

        if (empty($messageId) || empty($crmId)) {
            return;
        }

        $db = PearDatabase::getInstance();
        $existingResultObject = $db->pquery(
            'SELECT refids FROM vtiger_mailscanner_ids WHERE crmid=? AND refids != ?',
            array($crmId, 'null')
        );

        if ($db->num_rows($existingResultObject)) {
            $existingResult = json_decode($db->query_result($existingResultObject, 'refids', 0), true);

            if (is_array($existingResult)) {
                $existingResultValue = array_merge($existingResult, array($messageId));
                $refIds = json_encode($existingResultValue);
                $db->pquery(
                    'UPDATE vtiger_mailscanner_ids SET refids=? WHERE crmid=?',
                    array($refIds, $crmId)
                );
            }
        } else {
            $db->pquery(
                'INSERT INTO vtiger_mailscanner_ids (messageid, crmid) VALUES(?,?)',
                array($messageId, $crmId)
            );
        }
    }
}