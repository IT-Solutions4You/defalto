<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use PHPMailer\PHPMailer\Exception;

class EMAILMaker_Utils_Helper
{
    /**
     * @param string|array $to
     * @param string       $fromName
     * @param string       $fromEmail
     * @param string       $subject
     * @param string       $body
     * @param bool         $useGivenFromEmailAddress
     *
     * @return bool|int|string
     * @throws Exception
     */
    public static function sendMail(
        string|array $to,
        string $fromName = '',
        string $fromEmail = '',
        string $subject = '',
        string $body = '',
        bool $useGivenFromEmailAddress = false,
    ): bool|int|string {
        if (empty($to)) {
            return false;
        }

        global $current_language, $default_language, $HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_EMAIL_REPLY_ID;

        if (empty($current_language)) {
            $current_language = $default_language;
        }

        if ($fromEmail == '') {
            $fromEmail = getUserEmailByName($fromName);
        }

        $systemFromEmail = Settings_Vtiger_Systems_Model::getFromEmailField();

        if ($_REQUEST['module'] == 'ITS4YouEmails' && ($_REQUEST['action'] == 'mailsend' || $_REQUEST['action'] == 'Save')) {
            $replyToEmail = $fromEmail;
        } else {
            $replyToEmail = $systemFromEmail;
        }

        if ($systemFromEmail != '' && !$useGivenFromEmailAddress) {
            //setting from email to the defined email address in the outgoing server configuration
            $fromEmail = $systemFromEmail;
        }

        $body = self::addSignature($body, $fromName, $fromEmail);

        $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance('ITS4YouEmails');
        $mail = $emailRecord->getMailer();
        $mail->retrieveSMTPVtiger();

        if (empty($mail->Host)) {
            return 'Mail Host is not configured';
        }

        $mail->Subject = $subject;
        $mail->Body = decode_html($body);
        $mail->AltBody = $emailRecord->getProcessedAltBody($body);
        $mail->From = $fromEmail;
        $mail->FromName = self::chooseFromName($fromName);
        self::setToAddresses($mail, $to);
        $mail->WordWrap = 50;

        $mail->Body = $emailRecord->convertCssToInline($mail->Body);
        $emailRecord->convertImagesToEmbed();
        $mail->IsHTML();

        if (!empty($replyToEmail)) {
            $mail->AddReplyTo($replyToEmail);
        }

        if ($HELPDESK_SUPPORT_EMAIL_REPLY_ID && $HELPDESK_SUPPORT_EMAIL_ID != $HELPDESK_SUPPORT_EMAIL_REPLY_ID) {
            $mail->AddReplyTo($HELPDESK_SUPPORT_EMAIL_REPLY_ID);
        }

        if (!$mail->Send()) {
            $msg = array_search($mail->ErrorInfo, $mail->getTranslations());

            if (in_array($msg, ['connect_host', 'from_failed', 'recipients_failed'])) {
                return $msg;
            }

            return 'Unspecified error during sending';
        }

        return true;
    }

    /**
     * @param string $fromName
     *
     * @return string
     * @throws Exception
     */
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
            $result = $db->pquery(
                'select signature, userlabel from vtiger_users where user_name=? or user_name=? or email1=? or email2=? or secondaryemail=?',
                [$fromName, $fromEmail, $fromEmail, $fromEmail, $fromEmail]
            );

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

    /**
     * @param ITS4YouEmails_Mailer_Model $mail
     * @param string|array               $emailAddresses
     *
     * @return void
     * @throws Exception
     */
    private static function setToAddresses(ITS4YouEmails_Mailer_Model $mail, string|array $emailAddresses): void
    {
        if (empty($emailAddresses)) {
            return;
        }

        if (!is_array($emailAddresses)) {
            $emailAddresses = explode(',', $emailAddresses);
        }

        foreach ($emailAddresses as $emailAddress) {
            $mail->addAddress($emailAddress);
        }
    }
}