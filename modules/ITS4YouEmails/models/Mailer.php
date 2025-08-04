<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use League\OAuth2\Client\Provider\Google as Google;
use PHPMailer\PHPMailer\OAuth as OAuth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ITS4YouEmails_Mailer_Model extends PHPMailer
{
    public $CharSet = PHPMailer::CHARSET_UTF8;
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
                $this->retrieveSMTPData($smtpRecord);
            }
        } else {
            $this->retrieveSMTPVtiger();
        }
    }

    public function retrieveSMTPData($smtpRecord)
    {
        $this->setMailerType($smtpRecord->get('mailer_type'));
        $this->setSMTP(
            $smtpRecord->get('server'),
            $smtpRecord->get('server_username'),
            $smtpRecord->getDecodedPassword(),
            !$smtpRecord->isEmpty('smtp_auth'),
            $smtpRecord->get('server_protocol'),
            $smtpRecord->get('server_port'),

            $smtpRecord->get('provider'),
            $smtpRecord->get('client_id'),
            $smtpRecord->get('client_secret'),
            $smtpRecord->get('client_token'),
        );

        if (!$smtpRecord->isEmpty('from_email_field')) {
            $this->setFrom($smtpRecord->get('from_email_field'));
        }

        if (!$smtpRecord->isEmpty('from_name_field')) {
            $this->FromName = $smtpRecord->get('from_name_field');
        }
    }

    public function retrieveSMTPVtiger()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_systems WHERE server_type=?', ['email']);

        if ($adb->num_rows($result)) {
            $row = $adb->query_result_rowdata($result);
            $server = decode_html($row['server']);
            $this->setSMTP(
                $server,
                $row['server_username'],
                self::fromProtectedText($row['server_password']),
                !empty($row['smtp_auth']),
                null,
                null,
                $this->getProviderByServer($server),
                decode_html($row['client_id']),
                decode_html($row['client_secret']),
                decode_html($row['client_token'])
            );

            if (!empty($row['from_email_field'])) {
                $this->setFrom($row['from_email_field']);
            }
        }
    }

    public function getProviderByServer($server): string
    {
        if (str_contains($server, 'gmail.com')) {
            return 'Google';
        }

        return '';
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
                $this->retrieveSMTPData($smtpRecord);
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
            $this->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];
        }
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param bool   $auth
     *
     * @return void
     */
    public function setSMTP($host, $username, $password, $auth = true, $smtpSecure = '', $port = 25, $provider = '', $clientId = '', $clientSecret = '', $refreshToken = '')
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
        $this->SMTPSecure = $smtpSecure;
        $this->Port = $port;

        [$smtpSecure, $host] = explode('://', $this->Host);

        if ('tls' === $smtpSecure) {
            $this->SMTPSecure = $smtpSecure;
            $this->Host = $host;
        }

        if (!empty($refreshToken)) {
            if ('Google' === $provider) {
                $this->AuthType = 'XOAUTH2';

                $provider = new Google([
                    'clientId'     => $clientId,
                    'clientSecret' => $clientSecret,
                ]);
                $oAuth = new OAuth([
                    'provider'     => $provider,
                    'clientId'     => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName'     => $username,
                ]);
                $this->setOAuth($oAuth);
            }
        }
    }

    public function replaceImageSrc($content, $fromUrl, $toUrl)
    {
        return str_replace(
            [
                'src="' . $fromUrl . '"',
                "src='" . $fromUrl . "'",
            ],
            [
                'src="' . $toUrl . '"',
                'src="' . $toUrl . '"'
            ],
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
        $result = $db->pquery('SELECT messageid FROM vtiger_mailscanner_ids WHERE crmid=?', [$crmId]);

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
            [$crmId, 'null']
        );

        if ($db->num_rows($existingResultObject)) {
            $existingResult = json_decode($db->query_result($existingResultObject, 'refids', 0), true);

            if (is_array($existingResult)) {
                $existingResultValue = array_merge($existingResult, [$messageId]);
                $refIds = json_encode($existingResultValue);
                $db->pquery(
                    'UPDATE vtiger_mailscanner_ids SET refids=? WHERE crmid=?',
                    [$refIds, $crmId]
                );
            }
        } else {
            $db->pquery(
                'INSERT INTO vtiger_mailscanner_ids (messageid, crmid) VALUES(?,?)',
                [$messageId, $crmId]
            );
        }
    }

    public static function generateMessageID(): string
    {
        return sprintf("<%s.%s@%s>", base_convert(microtime(), 10, 36), base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36), gethostname());
    }

    public static function updateMessageIdByCrmId($messageId, $crmId): void
    {
        $db = PearDatabase::getInstance();
        $existingResult = [];
        $existingResultObject = $db->pquery("SELECT refids FROM vtiger_mailscanner_ids WHERE crmid=? AND refids != 'null'", [$crmId]);

        if ($db->num_rows($existingResultObject)) {
            $existingResult = json_decode($db->query_result($existingResultObject, 'refids', 0), true);
            // Checking if first parameter is not an array
            if (is_array($existingResult)) {
                $existingResultValue = array_merge($existingResult, [$messageId]);
                $refIds = json_encode($existingResultValue);
                $db->pquery(
                    'UPDATE vtiger_mailscanner_ids SET refids=? WHERE crmid=?',
                    [$refIds, $crmId]
                );
            }
        } else {
            $db->pquery(
                'INSERT INTO vtiger_mailscanner_ids (messageid, crmid) VALUES(?,?)',
                [$messageId, $crmId]
            );
        }
    }

    public static function getReplyToEmail()
    {
        $defaultReplyTo = vglobal('default_reply_to');
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $replyTo = $currentUserModel->get('email1');

        if ($defaultReplyTo == 'outgoing_server_from_email') {
            $fromEmail = Settings_Vtiger_Systems_Model::getFromEmailField();

            if (!empty($fromEmail)) {
                $replyTo = $fromEmail;
            }
        } elseif ($defaultReplyTo == 'hepldesk_support_email') {
            $helpDeskEmail = vglobal('HELPDESK_SUPPORT_EMAIL_ID');

            if (!empty($helpDeskEmail)) {
                $replyTo = $helpDeskEmail;
            }
        }

        return $replyTo;
    }

    public static function getFromEmailAddress()
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        $fromEmail = Settings_Vtiger_Systems_Model::getFromEmailField();

        if (empty($fromEmail)) {
            $fromEmail = $currentUserModel->get('email1');
        }

        return $fromEmail;
    }
}