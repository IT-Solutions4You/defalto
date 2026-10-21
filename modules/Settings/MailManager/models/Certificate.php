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
 * The IMAP server certificate for the Thunderbird-style trust dialog —
 * whether it verifies, whether it is the trusted one, and its details
 */
class Settings_MailManager_Certificate_Model
{
    /**
     * @param string $server
     * @param int $port
     * @param string $encryption ssl | starttls | none
     * @param string $trustedFingerprint sha256 the mailbox trusts ('' = none)
     * @return array ['verified' => bool, 'trusted' => bool, 'certificate' => array|null (display values)]
     */
    public static function check($server, $port, $encryption, $trustedFingerprint = '')
    {
        if (!in_array($encryption, ['ssl', 'starttls'], true)) {
            return ['verified' => false, 'trusted' => false, 'certificate' => null];
        }
        $certificate = MailManager_ProImapClient_Helper::fetchCertificate($server, $port, $encryption);
        $trusted = MailManager_ProImapClient_Helper::normalizeFingerprint($trustedFingerprint);

        return [
            'verified' => MailManager_ProImapClient_Helper::probeCertificate($server, $port, $encryption),
            'trusted' => $certificate !== null && $trusted !== '' && $trusted === $certificate['fingerprint'],
            'certificate' => $certificate ? self::describe($certificate) : null,
        ];
    }

    /**
     * Display values of a certificate — dates in the user's format
     *
     * @param array $certificate ImapClient::fetchCertificate() result
     * @return array ['fingerprint' (raw), 'fingerprint_display', 'subject', 'issuer', 'valid', 'expired', 'self_signed']
     */
    public static function describe(array $certificate)
    {
        $date = function ($timestamp) {
            if (!$timestamp) {
                return '';
            }
            $field = new DateTimeField(gmdate('Y-m-d H:i:s', $timestamp));

            return $field->getDisplayDate();
        };

        return [
            'fingerprint' => $certificate['fingerprint'],
            'fingerprint_display' => strtoupper(implode(':', str_split($certificate['fingerprint'], 2))),
            'subject' => $certificate['subject'],
            'issuer' => $certificate['issuer'],
            'valid' => $date($certificate['valid_from']) . ' – ' . $date($certificate['valid_to']),
            'expired' => $certificate['valid_to'] && $certificate['valid_to'] < time(),
            'self_signed' => (bool)$certificate['self_signed'],
        ];
    }
}
