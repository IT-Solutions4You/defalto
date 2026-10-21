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
 * Add Mailbox wizard state kept in the PHP session between steps
 * (connection credentials / OAuth tokens live here until step 3 saves the mailbox)
 */
class Settings_MailManager_Wizard_Model
{
    public const SESSION_KEY = 'MailManagerProWizard';
    public const STATE_KEY = 'MailManagerProOAuthState';

    /** Consumer domains resolvable without an MX lookup */
    private const DOMAIN_MAP = [
        'gmail.com' => 'google',
        'googlemail.com' => 'google',
        'outlook.com' => 'office365',
        'outlook.sk' => 'office365',
        'outlook.cz' => 'office365',
        'hotmail.com' => 'office365',
        'live.com' => 'office365',
        'msn.com' => 'office365',
    ];

    /**
     * Returns the wizard state. It is kept encrypted in the session because it holds the
     * mailbox password until the mailbox is saved (N23).
     *
     * @return array
     */
    public static function get()
    {
        require_once 'include/utils/encryption.php';

        $stored = Vtiger_Session::get(self::SESSION_KEY);

        if (!is_string($stored) || '' === $stored) {
            return [];
        }

        $data = json_decode((new Encryption())->decrypt($stored), true);

        return is_array($data) ? $data : [];
    }

    /**
     * Merges values into the wizard state and stores it encrypted in the session.
     *
     * @param array $values
     *
     * @return void
     */
    public static function update(array $values)
    {
        require_once 'include/utils/encryption.php';

        $data = array_merge(self::get(), $values);
        Vtiger_Session::set(self::SESSION_KEY, (new Encryption())->encrypt((string)json_encode($data)));
    }

    /**
     * @return void
     */
    public static function clear()
    {
        Vtiger_Session::set(self::SESSION_KEY, '');
        Vtiger_Session::set(self::STATE_KEY, '');
    }

    /**
     * True when the wizard holds valid OAuth tokens for the given mailbox
     *
     * @param string $provider
     * @param string $email
     * @return bool
     */
    public static function isOAuthConnected($provider, $email)
    {
        $data = self::get();

        return !empty($data['tokens']['access_token'])
            && ($data['provider'] ?? '') === $provider
            && ($data['email'] ?? '') === $email;
    }

    /**
     * True when the wizard holds IMAP credentials verified by Test Connection
     *
     * @param string $email
     * @return bool
     */
    public static function isImapTested($email)
    {
        $data = self::get();

        return !empty($data['imap']['tested'])
            && ($data['email'] ?? '') === $email;
    }

    /**
     * True when the wizard holds outgoing (SMTP) settings verified by Test SMTP
     *
     * @param string $email
     * @return bool
     */
    public static function isSmtpTested($email)
    {
        $data = self::get();

        return !empty($data['smtp']['tested'])
            && ($data['email'] ?? '') === $email;
    }

    /**
     * Wizard step 2 (IMAP) connection test — plain socket IMAP LOGIN + LIST, no ext-imap
     * dependency. On success the credentials are stored in the wizard session for step 3.
     *
     * @param array $connection server, port, encryption, username, password (raw), email and
     *                          cert_fingerprint (a certificate the admin trusted in the dialog)
     *
     * @return array ['success', 'folders', 'message'] plus 'cert_error' and 'certificate' when
     *               the certificate did not verify
     */
    public static function testImap(array $connection): array
    {
        $server = trim($connection['server']);
        $port = (int)$connection['port'];
        $encryption = $connection['encryption'];
        $username = trim($connection['username']);
        $password = (string)$connection['password'];
        $email = trim($connection['email']);
        // a certificate the admin trusted in the dialog (sha256)
        $fingerprint = MailManager_ProImapClient_Helper::normalizeFingerprint($connection['cert_fingerprint']);

        if (!in_array($encryption, ['ssl', 'starttls', 'none'], true)) {
            $encryption = 'ssl';
        }

        // Empty password + already verified mailbox in this session → reuse the stored one
        // (the password is never rendered back into the form)
        if ($password === '' && self::isImapTested($email)) {
            $wizard = self::get();
            $password = (string)($wizard['imap']['password'] ?? '');
        }

        if ($server === '' || $port < 1 || $username === '' || $password === '') {
            return [
                'success' => false,
                'message' => vtranslate('LBL_MISSING_CONNECTION', 'Settings:MailManager'),
            ];
        }

        if (!MailManager_ProHostGuard_Model::isAllowed($server, $port)) {
            return [
                'success' => false,
                'message' => vtranslate('LBL_HOST_NOT_ALLOWED', 'Settings:MailManager'),
            ];
        }

        $result = self::testImapConnection($server, $port, $encryption, $username, $password, $fingerprint);

        // Thunderbird-style: the certificate did not verify — its details
        // let the admin decide whether to trust exactly this one
        if (!empty($result['cert_error'])) {
            $certificate = MailManager_ProImapClient_Helper::fetchCertificate($server, $port, $encryption);
            $result['certificate'] = $certificate
                ? Settings_MailManager_Certificate_Model::describe($certificate)
                : null;
        }

        if ($result['success']) {
            self::update([
                'email' => $email,
                'provider' => 'imap',
                'imap' => [
                    'tested' => true,
                    'server' => $server,
                    'port' => $port,
                    'encryption' => $encryption,
                    'username' => $username,
                    'password' => $password,
                    'cert_fingerprint' => $fingerprint,
                ],
                'folders' => $result['folders'],
                'folders_list' => $result['folders_list'],
                'folder_delimiter' => $result['delimiter'],
                'account_email' => $username,
            ]);
        }

        unset($result['folders_list'], $result['delimiter']);

        return $result;
    }

    /**
     * @param string $server
     * @param int    $port
     * @param string $encryption  ssl | starttls | none
     * @param string $username
     * @param string $password
     * @param string $fingerprint trusted certificate (sha256), '' = verify normally
     *
     * @return array ['success', 'folders' => count, 'folders_list', 'delimiter', 'message'] or
     *               ['success' => false, 'folders' => 0, 'message', 'cert_error' => bool]
     */
    private static function testImapConnection($server, $port, $encryption, $username, $password, $fingerprint): array
    {
        $client = new MailManager_ProImapClient_Helper($server, $port, $encryption, $fingerprint);

        try {
            $client->connect($username, $password);
            $foldersList = [];

            foreach ($client->listFolders() as $folder) {
                $foldersList[] = [
                    'remote_id' => $folder['remote_id'],
                    'name' => $folder['name'],
                    'folder_type' => MailManager_ProImapClient_Helper::folderType($folder['flags'], $folder['name']),
                ];
            }

            $delimiter = $client->getDelimiter();
        } catch (Exception $e) {
            $client->disconnect();
            $message = $e->getMessage();
            $certError = $message === MailManager_ProImapClient_Helper::CERTIFICATE_ERROR;

            if ($certError) {
                $message = vtranslate('LBL_CERT_UNTRUSTED', 'Settings:MailManager');
            } elseif ($message === MailManager_ProImapClient_Helper::LOGIN_ERROR) {
                $message = vtranslate('LBL_IMAP_LOGIN_FAILED', 'Settings:MailManager');
            }

            return ['success' => false, 'folders' => 0, 'message' => $message, 'cert_error' => $certError];
        }

        $client->disconnect();

        return [
            'success' => true,
            'folders' => count($foldersList),
            'folders_list' => $foldersList,
            'delimiter' => $delimiter,
            'message' => '',
        ];
    }

    /**
     * The IMAP password tested in this wizard session — "same as IMAP" of the SMTP step.
     *
     * @return string
     */
    public static function getImapPassword(): string
    {
        return (string)(self::get()['imap']['password'] ?? '');
    }

    /**
     * Wizard step 3 / mailbox detail SMTP test. A tested server of the wizard (no mailbox
     * yet) is kept in the wizard session for saving the mailbox.
     *
     * @param string $server     not empty
     * @param mixed  $port
     * @param mixed  $encryption ssl | none | tls (anything else)
     * @param mixed  $username
     * @param string $password   resolved password
     * @param int    $recordId   tested mailbox, 0 = the wizard
     *
     * @return array ['success', 'message', ...] of MailManager_ProMailer_Model::testSmtp()
     */
    public static function testSmtp(string $server, $port, $encryption, $username, string $password, int $recordId): array
    {
        $encryption = in_array($encryption, ['ssl', 'none'], true)
            ? $encryption
            : 'tls';
        $port = (int)$port;
        $username = trim((string)$username);

        if (!MailManager_ProHostGuard_Model::isAllowed($server, $port)) {
            return [
                'success' => false,
                'message' => vtranslate('LBL_HOST_NOT_ALLOWED', 'Settings:MailManager'),
            ];
        }

        $result = MailManager_ProMailer_Model::testSmtp($server, $port, $encryption, $username, $password);

        if ($result['success'] && !$recordId) {
            self::update(['smtp' => [
                'server' => $server,
                'port' => $port,
                'encryption' => $encryption,
                'username' => $username,
                'password' => $password,
                'tested' => true,
            ]]);
        }

        if (!$result['success']) {
            $result['message'] = vtranslate('LBL_SMTP_TEST_FAILED', 'Settings:MailManager')
                . ($result['message'] !== '' ? ' ' . $result['message'] : '');
        }

        return $result;
    }

    /**
     * Wizard step 4: saves the connected mailbox and ends the wizard.
     *
     * @param array $input  email, provider, name (raw), mailbox_type, shared_with (JSON),
     *                      folders (JSON), sync_from_days, is_default
     * @param int   $userId the owner
     *
     * @return array ['success' => true, 'mailboxid'] or ['success' => false, 'message']
     */
    public static function saveMailbox(array $input, int $userId): array
    {
        $email = trim($input['email']);
        $provider = $input['provider'];
        $name = trim((string)$input['name']);
        $mailboxType = $input['mailbox_type'] === 'shared' ? 'shared' : 'personal';
        $sharedWith = json_decode($input['shared_with'], true) ?: [];
        $folders = json_decode($input['folders'], true) ?: [];
        $syncFromDays = (int)$input['sync_from_days'];
        $isDefault = (int)$input['is_default'] === 1;
        $wizard = self::get();
        $connected = $provider === 'imap'
            ? self::isImapTested($email)
            : self::isOAuthConnected($provider, $email);

        if (!$connected) {
            return ['success' => false, 'message' => vtranslate('LBL_NOT_CONNECTED_WARNING', 'Settings:MailManager')];
        }

        $smtp = ['smtp_mode' => 'crm'];

        if ($provider === 'imap') {
            if (!self::isSmtpTested($email)) {
                return [
                    'success' => false,
                    'message' => vtranslate('LBL_SMTP_NOT_TESTED', 'Settings:MailManager'),
                ];
            }

            $smtp = [
                'smtp_mode' => 'custom',
                'smtp_server' => $wizard['smtp']['server'],
                'smtp_port' => $wizard['smtp']['port'],
                'smtp_encryption' => $wizard['smtp']['encryption'],
                'smtp_username' => $wizard['smtp']['username'],
                'smtp_password' => $wizard['smtp']['password'],
            ];
        }

        if ($name === '' || ($mailboxType === 'shared' && !$sharedWith)) {
            return ['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'Settings:MailManager')];
        }

        try {
            $mailboxId = Settings_MailManager_Mailbox_Model::saveFromWizard($wizard, [
                'name' => $name,
                'mailbox_type' => $mailboxType,
                'shared_with' => $sharedWith,
                'folders' => $folders,
                'sync_from_days' => $syncFromDays,
                'is_default' => $isDefault,
            ] + $smtp, $userId);
            self::clear();
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true, 'mailboxid' => $mailboxId];
    }

    /**
     * Wizard step 1: the provider of an address — known consumer domains, otherwise the
     * MX records of the domain.
     *
     * @param string $email
     *
     * @return array ['detected' => bool, 'provider' => google | office365 | imap | '']
     */
    public static function detectProvider(string $email): array
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['detected' => false, 'provider' => ''];
        }

        $domain = strtolower(substr(strrchr($email, '@'), 1));
        $provider = self::DOMAIN_MAP[$domain] ?? self::detectFromMx($domain);

        return [
            'detected' => $provider !== '',
            'provider' => $provider !== '' ? $provider : 'imap',
        ];
    }

    /**
     * @param string $domain
     *
     * @return string google | office365 | imap, '' when the domain has no MX record
     */
    private static function detectFromMx(string $domain): string
    {
        $mxHosts = [];

        if (!function_exists('dns_get_record') || !@getmxrr($domain, $mxHosts) || !$mxHosts) {
            return '';
        }

        $mxList = strtolower(implode(' ', $mxHosts));

        if (strpos($mxList, 'outlook.com') !== false || strpos($mxList, 'office365') !== false) {
            return 'office365';
        }

        if (strpos($mxList, 'google.com') !== false || strpos($mxList, 'googlemail.com') !== false) {
            return 'google';
        }

        return 'imap';
    }
}
