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
 * Microsoft 365 / Google mailbox of a user (OAuth, decision D7): the user signs in through
 * Core_Auth_Model (auth.php), the tokens are stored in the user's Basic account
 * (vtiger_mail_accounts, shared contract 1) and Pro links that account as the personal
 * mailbox. Shared mailboxes stay IMAP with a password.
 */
class MailManager_ProOAuthAccount_Model
{
    /** Pro provider code => IMAP server of the provider */
    public const IMAP_SERVERS = [
        MailManager_ProTransport_Model::PROVIDER_GOOGLE    => 'imap.gmail.com',
        MailManager_ProTransport_Model::PROVIDER_OFFICE365 => 'outlook.office365.com',
    ];

    /**
     * Returns the IMAP server of an OAuth provider.
     *
     * @param string $provider Pro provider code
     *
     * @return string
     * @throws Exception on a provider without OAuth
     */
    public static function getImapServer(string $provider): string
    {
        if (!isset(self::IMAP_SERVERS[$provider])) {
            throw new Exception('Mail Manager Pro: provider "' . $provider . '" does not sign in with OAuth');
        }

        return self::IMAP_SERVERS[$provider];
    }

    /**
     * Returns the redirect URI to register in the OAuth application – the same value
     * Core_Auth_Model::getRedirectUri() sends to the provider.
     *
     * @param string $provider Pro provider code
     *
     * @return string
     * @throws Exception on a provider without OAuth
     */
    public static function getRedirectUri(string $provider): string
    {
        self::getImapServer($provider);

        $providerName = MailManager_ProTransport_Model::OAUTH_PROVIDERS[$provider];
        $url = rtrim((string)vglobal('site_URL'), '/') . '/auth.php';

        return Core_Auth_Model::PROVIDER_MICROSOFT === $providerName ? $url : $url . '?provider=' . $providerName;
    }

    /**
     * Returns the tokens the user obtained in this session through auth.php for the client ID.
     *
     * @param string $clientId OAuth application client ID
     *
     * @return array ['client_secret', 'token', 'access_token'], empty values when not signed in
     */
    public static function getSessionTokens(string $clientId): array
    {
        $authModel = Core_Auth_Model::getInstance();
        $authModel->set('client_id', $clientId);
        $session = $_SESSION[$authModel->getModuleName()][$authModel->getSessionName()] ?? [];

        return [
            'client_secret' => (string)($session['client_secret'] ?? ''),
            'token'         => (string)($session['token'] ?? ''),
            'access_token'  => (string)($session['client_access_token'] ?? ''),
        ];
    }

    /**
     * The account of a sign-in: a known OAuth provider, a valid address and the client ID.
     *
     * @param string $provider Pro provider code
     * @param string $email    mailbox address
     * @param string $clientId OAuth application client ID
     *
     * @return bool
     */
    public static function isValidAccount(string $provider, string $email, string $clientId): bool
    {
        return MailManager_ProTransport_Model::isOAuthProvider($provider) && filter_var($email, FILTER_VALIDATE_EMAIL) && '' !== $clientId;
    }

    /**
     * Stores the signed-in mailbox as the user's Basic account and links it as the personal
     * Pro mailbox. The IMAP sign-in is tested first, so a wrong account is never saved.
     *
     * @param int    $userId   signed-in user
     * @param string $provider Pro provider code
     * @param string $email    mailbox address (IMAP user name)
     * @param string $clientId OAuth application client ID used for the sign-in
     *
     * @return int personal mailbox ID
     * @throws Exception when the sign-in is missing, the IMAP login fails or the user has
     *                   another personal account
     */
    public static function saveAccount(int $userId, string $provider, string $email, string $clientId): int
    {
        $server = self::getImapServer($provider);
        $tokens = self::getSessionTokens($clientId);

        if ('' === $tokens['token'] || '' === $tokens['access_token']) {
            throw new Exception(vtranslate('LBL_PRO_OAUTH_NOT_SIGNED_IN', 'Settings:MailManager'));
        }

        $user = Users_Record_Model::getInstanceById($userId, 'Users');
        $account = MailManager_Mailbox_Model::getActiveInstance($user);

        if ($account->getAccountId() && 0 !== strcasecmp(trim((string)$account->username()), $email)) {
            throw new Exception(vtranslate('LBL_PRO_OAUTH_ACCOUNT_EXISTS', 'Settings:MailManager', $account->username()));
        }

        self::testImapLogin($server, $email, $tokens['access_token']);

        $account->setServer($server);
        $account->setUsername($email);
        $account->setProtocol('IMAP4');
        $account->setSSLType('ssl');
        $account->setCertValidate('validate-cert');
        $account->setClientId($clientId);
        $account->setClientSecret($tokens['client_secret']);
        $account->setClientToken($tokens['token']);
        $account->setClientAccessToken($tokens['access_token']);
        $account->save();

        $proMailbox = new MailManager_ProMailbox_Model();
        $proMailbox->linkBasicAccount($userId);
        $accountId = MailManager_Mailbox_Model::getActiveInstance($user)->getAccountId();

        foreach ($proMailbox->getLinkedMailboxes($userId) as $mailbox) {
            if ((int)$mailbox['account_id'] === $accountId) {
                $proMailbox->getMailboxTable()->updateData(
                    ['provider' => $provider, 'modifiedtime' => date('Y-m-d H:i:s')],
                    ['mailboxid' => (int)$mailbox['mailboxid']]
                );

                return (int)$mailbox['mailboxid'];
            }
        }

        throw new Exception('Mail Manager Pro: the personal mailbox could not be linked');
    }

    /**
     * Signs in to the provider IMAP server with the access token and logs out again.
     *
     * @param string $server      IMAP server
     * @param string $email       IMAP user name
     * @param string $accessToken OAuth access token
     *
     * @return void
     * @throws Exception when the login fails
     */
    protected static function testImapLogin(string $server, string $email, string $accessToken): void
    {
        $client = new MailManager_ProImapClient_Helper($server, 993, 'ssl', '');

        try {
            $client->connectXOAuth2($email, $accessToken);
        } finally {
            $client->disconnect();
        }
    }
}
