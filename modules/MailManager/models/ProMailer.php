<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Outgoing server test of Mail Manager Pro mailboxes. Sending itself goes through
 * ITS4YouEmails (07-SENDING.md); this model only checks that an SMTP login works.
 */
class MailManager_ProMailer_Model
{
    /**
     * Connects and authenticates to an SMTP server without sending anything.
     *
     * @param string $server
     * @param int    $port
     * @param string $encryption ssl, tls or none
     * @param string $username
     * @param string $password plain password
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testSmtp($server, $port, $encryption, $username, $password): array
    {
        $mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
        $secure = in_array($encryption, ['ssl', 'tls'], true) ? $encryption : '';
        $mailer->setSMTP((string)$server, (string)$username, (string)$password, '' !== (string)$username, $secure, (int)$port);
        $mailer->Timeout = 15;

        try {
            $connected = $mailer->smtpConnect();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        if (!$connected) {
            return ['success' => false, 'message' => (string)$mailer->ErrorInfo];
        }

        $mailer->smtpClose();

        return ['success' => true, 'message' => ''];
    }
}
