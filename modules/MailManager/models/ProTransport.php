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
 * Transport factory – resolves the driver from the mailbox provider: IMAP with a password,
 * Google and Microsoft 365 mailboxes over IMAP with XOAUTH2 (access token from
 * Core_Auth_Model); all use MailManager_ProImapTransport_Helper. The Gmail REST and
 * Microsoft Graph drivers of the original extension are not ported (08-CONNECTIONS.md).
 */
class MailManager_ProTransport_Model
{
    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_IMAP = 'imap';
    public const PROVIDER_OFFICE365 = 'office365';

    /** Pro provider code => Core_Auth_Model provider name of the OAuth providers */
    public const OAUTH_PROVIDERS = [
        self::PROVIDER_GOOGLE    => Core_Auth_Model::PROVIDER_GOOGLE,
        self::PROVIDER_OFFICE365 => Core_Auth_Model::PROVIDER_MICROSOFT,
    ];

    /** Mailbox columns stored encrypted that the transport needs in plain form */
    private const SECRET_COLUMNS = ['imap_password', 'oauth_tokens'];

    /**
     * The one entry point for business code – takes the mailbox row exactly as stored
     * (secrets encrypted with the Defalto Encryption class) and decrypts what the driver
     * needs. Creating the transport does not connect yet; ask supports() before an operation.
     *
     * @param array $mailbox df_mailmanager_mailbox row as stored
     *
     * @return MailManager_ProBaseTransport_Helper
     * @throws Exception on unknown provider or a missing linked Basic account
     */
    public static function getForMailbox(array $mailbox): MailManager_ProBaseTransport_Helper
    {
        require_once 'include/utils/encryption.php';

        $mailbox = MailManager_ProMailbox_Model::getTransportRow($mailbox);
        $encryption = new Encryption();

        foreach (self::SECRET_COLUMNS as $column) {
            if (!empty($mailbox[$column])) {
                $mailbox[$column] = $encryption->decrypt($mailbox[$column]);
            }
        }

        return self::getInstance($mailbox);
    }

    /**
     * Driver for a row whose secrets are already plain – only for flows that never stored
     * them (connection test in the mailbox wizard). Everything else uses getForMailbox().
     *
     * @param array $mailbox mailbox row with plain secrets
     *
     * @return MailManager_ProBaseTransport_Helper
     * @throws Exception on unknown provider
     */
    public static function getInstance(array $mailbox): MailManager_ProBaseTransport_Helper
    {
        $provider = (string)($mailbox['provider'] ?? '');

        if (self::PROVIDER_IMAP === $provider || self::isOAuthProvider($provider)) {
            return new MailManager_ProImapTransport_Helper($mailbox);
        }

        throw new Exception('Mail Manager Pro: unknown mailbox provider "' . $provider . '"');
    }

    /**
     * Tells whether the provider signs in to IMAP with an OAuth access token (XOAUTH2).
     *
     * @param string $provider Pro provider code
     *
     * @return bool
     */
    public static function isOAuthProvider(string $provider): bool
    {
        return isset(self::OAUTH_PROVIDERS[$provider]);
    }

    /**
     * Returns the Pro provider code of a Basic account: the OAuth provider of its server
     * (Core_Auth_Model, the rule Basic uses too) or IMAP with a password.
     *
     * @param MailManager_Mailbox_Model $account Basic account
     *
     * @return string
     */
    public static function getProviderForAccount(MailManager_Mailbox_Model $account): string
    {
        if ($account->isOAuth()) {
            $provider = array_search(Core_Auth_Model::getProviderNameByServer((string)$account->server()), self::OAUTH_PROVIDERS, true);

            if (false !== $provider) {
                return $provider;
            }
        }

        return self::PROVIDER_IMAP;
    }
}
