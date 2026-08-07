<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use Mpdf\QrCode\Output\Svg;
use Mpdf\QrCode\QrCode;


/**
 * Core two-factor authentication logic shared by the routed login action, the
 * challenge view and the settings actions.
 *
 * The pending challenge state lives in the session under a single namespace and
 * never grants access by itself: the user is logged in only once verifyCode()
 * succeeds and completeLogin() sets AUTHUSERID.
 */
class TwoFactorAuthentication_Service_Helper
{
    public const LOGIN_URL = 'index.php?module=Users&parent=Settings&view=Login';
    public const CHALLENGE_URL = 'index.php?module=TwoFactorAuthentication&view=Challenge';
    public const SESSION_KEY = 'two_factor_authentication';
    /** Session namespace for the logged-in self-service enrollment. */
    public const SELF_KEY = 'two_factor_authentication_self';
    public const METHOD_EMAIL = 'email';
    public const METHOD_TOTP = 'totp';

    /** Challenge stages (the session state machine). */
    public const STAGE_CHOOSE = 'choose';
    public const STAGE_EMAIL = 'email';
    public const STAGE_TOTP = 'totp';
    public const STAGE_ENROLL = 'enroll';

    /** One-time email passcode length. */
    public const CODE_LENGTH = 6;
    /** Email passcode validity in seconds. */
    public const CODE_TTL = 600;
    /** Failed attempts before the challenge is temporarily locked. */
    public const MAX_ATTEMPTS = 5;
    /** Lock duration in seconds after too many failed attempts. */
    public const LOCK_TTL = 300;
    /** Number of backup codes generated per batch. */
    public const BACKUP_CODE_COUNT = 5;

    /** Editable email template keys. */
    public const TPL_LOGIN = 'login';
    public const TPL_BACKUP = 'backup';

    /**
     * Activates the routed 2FA login flow.
     *
     * @return bool true when the routed flow is active afterwards
     */
    public static function activate(): bool
    {
        if (empty(self::getAllowedMethods())) {
            return false;
        }

        TwoFactorAuthentication_Config_Model::getInstance()->updateEnforceMode('all');

        return self::isActive();
    }

    /**
     * Starts a challenge for the given user. With a single allowed method (or a
     * remembered preference) it auto-selects it; otherwise it shows the method
     * chooser.
     *
     * @param int $userid
     * @param string $username
     * @return void
     */
    public static function beginChallenge($userid, $username): void
    {
        $userid = (int)$userid;
        $allowed = self::getAllowedMethods();

        $_SESSION[self::SESSION_KEY] = [
            'pending_userid' => $userid,
            'pending_username' => $username,
            'allowed' => $allowed,
            'stage' => self::STAGE_CHOOSE,
            'method' => '',
            'attempts' => 0,
            'lock_until' => 0,
            'code_hash' => '',
            'code_expiry' => 0,
            'enroll_secret' => '',
            'delivery_failed' => false,
        ];

        if (count($allowed) === 1) {
            self::selectMethod($allowed[0]);
        } else {
            // Auto-select only when the user has an explicit remembered choice;
            // a brand-new user (no row) sees the chooser.
            $row = self::getUserRow($userid);
            $remembered = (!empty($row) && !empty($row['method'])) ? (string)$row['method'] : '';

            if ($remembered !== '' && in_array($remembered, $allowed, true)) {
                self::selectMethod($remembered);
            }
        }
    }

    /**
     * Applies the user's method choice from the chooser screen and remembers it
     * as their preference.
     *
     * @param string $method
     * @return bool
     */
    public static function chooseMethod($method): bool
    {
        $state = self::getState();

        if (empty($state) || !self::isMethodAllowed($method)) {
            return false;
        }

        self::rememberMethod((int)$state['pending_userid'], $method);
        self::selectMethod($method);

        return true;
    }

    /**
     * Clears the pending challenge state.
     *
     * @return void
     */
    public static function clearChallenge(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * @param int $userid
     * @return void
     */
    private static function clearFailures($userid): void
    {
        TwoFactorAuthentication_User_Model::getInstance()->clearFailures((int)$userid);
    }

    /**
     * Clears any self-service session state.
     *
     * @return void
     */
    public static function clearSelf(): void
    {
        unset($_SESSION[self::SELF_KEY]);
    }

    /**
     * Completes the login for the pending user, mirroring the session setup
     * performed by the core Users_Login_Action.
     *
     * @param Vtiger_Request $request
     * @return void
     */
    public static function completeLogin(): string
    {
        $state = self::getState();
        $userid = (int)$state['pending_userid'];
        $username = (string)$state['pending_username'];

        self::clearFailures($userid);
        self::clearChallenge();

        return Users_Authentication_Model::completeLogin($userid, $username);
    }

    /**
     * Confirms TOTP enrollment during login: verifies the first app code and
     * stores the secret. Backup codes are not shown here to avoid overwhelming
     * the user; they generate their own from the self-service page.
     *
     * @param string $code
     * @return bool
     */
    public static function confirmEnrollment($code): bool
    {
        $state = self::getState();

        if (empty($state) || ($state['stage'] ?? '') !== self::STAGE_ENROLL) {
            return false;
        }

        if (self::getLockRemaining() > 0) {
            return false;
        }

        $timeSlice = TwoFactorAuthentication_TOTP_Helper::matchSlice(
            (string)$state['enroll_secret'],
            (string)$code
        );

        if ($timeSlice === PHP_INT_MIN) {
            self::registerFailure();

            return false;
        }

        self::saveTotpSecret(
            (int)$state['pending_userid'],
            (string)$state['enroll_secret'],
            $timeSlice
        );

        return true;
    }

    /**
     * Confirms a self-service enrollment: verifies the first code, stores the
     * secret and stashes freshly generated backup codes for one-time display.
     *
     * @param int $userid
     * @param string $code
     * @return bool
     */
    public static function confirmSelfEnrollment($userid, $code): bool
    {
        $secret = (string)($_SESSION[self::SELF_KEY]['secret'] ?? '');

        $timeSlice = $secret === ''
            ? PHP_INT_MIN
            : TwoFactorAuthentication_TOTP_Helper::matchSlice($secret, (string)$code);

        if ($timeSlice === PHP_INT_MIN) {
            return false;
        }

        self::saveTotpSecret((int)$userid, $secret, $timeSlice);
        self::clearSelf();

        return true;
    }

    /**
     * @param int $userid
     * @return int number of unused backup codes
     */
    public static function countUnusedBackupCodes($userid): int
    {
        return TwoFactorAuthentication_BackupCode_Model::getInstance()
            ->countUnusedByUserId((int)$userid);
    }

    /**
     * Deactivates the routed 2FA login flow.
     *
     * @return bool true when the routed flow is inactive afterwards
     */
    public static function deactivate(): bool
    {
        TwoFactorAuthentication_Config_Model::getInstance()->updateEnforceMode('off');

        return !self::isActive();
    }

    /**
     * @param string $encrypted
     * @return string the decrypted secret, or '' on failure
     */
    private static function decryptSecret(string $encrypted): string
    {
        if (!self::isTotpMethodAvailable()) {
            return '';
        }

        $key = hash('sha256', (string)vglobal('application_unique_key'), true);

        if (str_starts_with($encrypted, 'v2:')) {
            $raw = base64_decode(substr($encrypted, 3), true);

            if ($raw === false || strlen($raw) <= 28) {
                return '';
            }

            $iv = substr($raw, 0, 12);
            $tag = substr($raw, 12, 16);
            $cipher = substr($raw, 28);
            $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

            return $plain === false ? '' : $plain;
        }

        // Compatibility with secrets migrated from the legacy extension.
        $raw = base64_decode($encrypted, true);

        if ($raw === false || strlen($raw) <= 16) {
            return '';
        }

        $plain = openssl_decrypt(
            substr($raw, 16),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            substr($raw, 0, 16)
        );

        return $plain === false ? '' : $plain;
    }

    /**
     * Emails the given backup codes to a user's primary address.
     *
     * @param int $userid
     * @param array $codes
     * @return bool true when the mailer accepted the message
     */
    public static function emailBackupCodes($userid, array $codes): bool
    {
        $user = Users_Record_Model::getInstanceById((int)$userid, 'Users');
        $email = (string)$user->get('email1');
        $userName = (string)$user->get('user_name');

        if (trim($email) === '') {
            return false;
        }

        $companyName = (string)Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
        $mail = self::renderEmail(self::TPL_BACKUP, [
            '$username$' => $userName,
            '$codes$' => implode('<br>', $codes),
            '$company$' => $companyName,
        ]);

        return self::sendWithIts4YouEmails((int)$userid, $email, $userName, $mail);
    }

    /**
     * Encrypts a secret for storage using a key derived from the application key.
     *
     * @param string $plain
     * @return string versioned base64(iv . authentication tag . ciphertext)
     */
    private static function encryptSecret(string $plain): string
    {
        if (!self::isTotpMethodAvailable()) {
            throw new RuntimeException('The TOTP encryption runtime is unavailable.');
        }

        $key = hash('sha256', (string)vglobal('application_unique_key'), true);
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($cipher === false || strlen($tag) !== 16) {
            throw new RuntimeException('Unable to encrypt the authenticator secret.');
        }

        return 'v2:' . base64_encode($iv . $tag . $cipher);
    }

    /**
     * @return string a grouped, human-readable backup code (e.g. ABCD-2345)
     */
    private static function generateBackupCode(): string
    {
        // Crockford-style alphabet without ambiguous characters.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';

        for ($i = 0; $i < 8; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return substr($code, 0, 4) . '-' . substr($code, 4, 4);
    }

    /**
     * Generates a fresh batch of one-time backup codes for a user, replacing
     * any previous codes. Plaintext codes are returned once for display and
     * only their hashes are stored.
     *
     * @param int $userid
     * @return array list of plaintext backup codes
     */
    public static function generateBackupCodes($userid): array
    {
        $userid = (int)$userid;
        $codes = [];
        $hashes = [];

        for ($i = 0; $i < self::BACKUP_CODE_COUNT; $i++) {
            $code = self::generateBackupCode();
            $codes[] = $code;
            $hashes[] = password_hash(self::normalizeBackupCode($code), PASSWORD_DEFAULT);
        }

        TwoFactorAuthentication_BackupCode_Model::getInstance()->saveHashes($userid, $hashes);

        return $codes;
    }

    /**
     * @param int $length
     * @return string a zero-padded numeric passcode
     */
    private static function generateNumericCode(int $length): string
    {
        $max = (10 ** $length) - 1;

        return str_pad((string)random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * @return array allowed second-factor methods (in display order). Email is
     *     dropped when no outgoing mail server is configured, since email codes
     *     cannot be delivered without one.
     */
    public static function getAllowedMethods(): array
    {
        $config = TwoFactorAuthentication_Config_Model::getInstance()->retrieveAllowedMethods();
        $methods = [];

        if ($config !== null) {
            if ((int)$config['allow_email'] === 1) {
                $methods[] = self::METHOD_EMAIL;
            }
            if ((int)$config['allow_totp'] === 1) {
                $methods[] = self::METHOD_TOTP;
            }
        }

        if (empty($methods)) {
            // Defensive default (config row or columns missing).
            $methods = [self::METHOD_EMAIL];
        }

        if (in_array(self::METHOD_EMAIL, $methods, true) && !self::isEmailMethodAvailable()) {
            $methods = array_values(array_filter($methods, static fn($m) => $m !== self::METHOD_EMAIL));
        }

        if (in_array(self::METHOD_TOTP, $methods, true) && !self::isTotpMethodAvailable()) {
            $methods = array_values(array_filter($methods, static fn($m) => $m !== self::METHOD_TOTP));
        }

        if (empty($methods)) {
            if (self::isEmailMethodAvailable()) {
                $methods = [self::METHOD_EMAIL];
            } elseif (self::isTotpMethodAvailable()) {
                $methods = [self::METHOD_TOTP];
            }
        }

        return $methods;
    }

    public static function getChallengeUrl(): string
    {
        return self::CHALLENGE_URL;
    }

    /**
     * Returns a template (admin-customized from DB, or the localized default).
     *
     * @param string $key
     * @return array{subject: string, body: string}
     */
    public static function getEmailTemplate(string $key): array
    {
        $template = TwoFactorAuthentication_EmailTemplate_Model::getInstance()->retrieveByKey($key);

        if ($template !== null) {
            return [
                'subject' => (string)$template['subject'],
                'body' => decode_html((string)$template['body']),
            ];
        }

        $upper = strtoupper($key);

        return [
            'subject' => vtranslate('LBL_TPL_' . $upper . '_SUBJECT', 'TwoFactorAuthentication'),
            'body' => decode_html(vtranslate('LBL_TPL_' . $upper . '_BODY', 'TwoFactorAuthentication')),
        ];
    }

    /**
     * Returns all editable email templates with their available variables, for
     * the admin "Email templates" tab.
     *
     * @return array
     */
    public static function getEmailTemplates(): array
    {
        $vars = [
            self::TPL_LOGIN => ['$username$', '$code$', '$minutes$', '$company$'],
            self::TPL_BACKUP => ['$username$', '$codes$', '$company$'],
        ];

        $templates = [];

        foreach ($vars as $key => $keyVars) {
            $tpl = self::getEmailTemplate($key);
            $templates[] = [
                'key' => $key,
                'subject' => $tpl['subject'],
                'body' => $tpl['body'],
                'vars' => $keyVars,
            ];
        }

        return $templates;
    }

    /**
     * @return string global enforcement mode ('all' | 'off')
     */
    public static function getEnforceMode(): string
    {
        $mode = TwoFactorAuthentication_Config_Model::getInstance()->retrieveEnforceMode();

        // Missing configuration must never turn enforcement on implicitly.
        return $mode ?? 'off';
    }

    /**
     * Builds the QR code and manual key for the enrollment screen.
     *
     * @return array{secret: string, uri: string, qr: string}
     */
    public static function getEnrollmentContext(): array
    {
        $state = self::getState();
        $secret = (string)($state['enroll_secret'] ?? '');
        $account = (string)($state['pending_username'] ?? '');

        $company = (string)Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
        $issuer = $company !== '' ? $company : 'Defalto CRM';

        $uri = TwoFactorAuthentication_TOTP_Helper::getProvisioningUri($secret, $account, $issuer);

        return [
            'secret' => $secret,
            'uri' => $uri,
            'qr' => TwoFactorAuthentication_TOTP_Helper::renderQrSvg($uri),
        ];
    }

    /**
     * @return int seconds remaining on the lock, 0 when not locked
     */
    public static function getLockRemaining(): int
    {
        $state = self::getState();

        if (empty($state)) {
            return 0;
        }

        $sessionRemaining = (int)$state['lock_until'] - time();
        $row = self::getUserRow((int)$state['pending_userid']);
        $storedLock = !empty($row['locked_until']) ? strtotime((string)$row['locked_until']) : 0;
        $storedRemaining = $storedLock - time();
        $remaining = max($sessionRemaining, $storedRemaining);

        return $remaining > 0 ? $remaining : 0;
    }

    public static function getLoginUrl(): string
    {
        return self::LOGIN_URL;
    }

    /**
     * @param int $userid
     * @return string the user's preferred method, defaulting to email
     */
    public static function getPreferredMethod($userid): string
    {
        $row = self::getUserRow($userid);

        return (!empty($row) && !empty($row['method'])) ? (string)$row['method'] : self::METHOD_EMAIL;
    }

    /**
     * @return array backup codes stashed for one-time display, or empty
     */
    public static function getSelfBackupCodes(): array
    {
        return $_SESSION[self::SELF_KEY]['codes'] ?? [];
    }

    /**
     * Builds the QR code and manual key for the self-service enrollment screen.
     *
     * @param string $account user identifier shown in the app
     * @return array{secret: string, uri: string, qr: string}
     */
    public static function getSelfEnrollmentContext(string $account): array
    {
        $secret = (string)($_SESSION[self::SELF_KEY]['secret'] ?? '');
        $company = (string)Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
        $issuer = $company !== '' ? $company : 'Defalto CRM';
        $uri = TwoFactorAuthentication_TOTP_Helper::getProvisioningUri($secret, $account, $issuer);

        return [
            'secret' => $secret,
            'uri' => $uri,
            'qr' => TwoFactorAuthentication_TOTP_Helper::renderQrSvg($uri),
        ];
    }

    /**
     * @return array|null the current pending challenge state
     */
    public static function getState(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    /**
     * @param int $userid
     * @return array|null per-user 2FA row, or null when none exists
     */
    public static function getUserRow($userid): ?array
    {
        return TwoFactorAuthentication_User_Model::getInstance()->retrieveByUserId((int)$userid);
    }

    /**
     * Returns all active users with their exemption, method (empty = not chosen
     * yet), enrollment state and unused backup-code count, for the admin "Users"
     * management table.
     *
     * @return array
     */
    public static function getUsersOverview(): array
    {
        return TwoFactorAuthentication_User_Model::getInstance()->retrieveUsersOverview();
    }

    /**
     * @return bool whether a challenge is currently pending
     */
    public static function hasPendingChallenge(): bool
    {
        $state = self::getState();

        return !empty($state) && !empty($state['pending_userid']);
    }

    /**
     * @return bool whether a self-service enrollment is in progress
     */
    public static function hasSelfEnrollment(): bool
    {
        return !empty($_SESSION[self::SELF_KEY]['secret']);
    }

    /**
     * Whether the routed 2FA login flow is enabled in module configuration.
     *
     * @return bool
     */
    public static function isActive(): bool
    {
        return self::getEnforceMode() === 'all';
    }

    /**
     * @param int $userid
     * @return bool whether the user is a Vtiger administrator
     */
    public static function isAdmin($userid): bool
    {
        return Users_Record_Model::getInstanceById((int)$userid, 'Users')->isAdminUser();
    }

    public static function isEmailMethodAvailable(): bool
    {
        try {
            return self::isOutgoingMailConfigured()
                && vtlib_isModuleActive('ITS4YouEmails')
                && ITS4YouEmails_Module_Model::isPHPMailerInstalled();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @param int $userid
     * @return bool whether the user has completed authenticator enrollment
     */
    public static function isEnrolled($userid): bool
    {
        $row = self::getUserRow($userid);

        return !empty($row) && (int)$row['enrolled'] === 1 && !empty($row['secret']);
    }

    /**
     * @param string $method
     * @return bool
     */
    public static function isMethodAllowed($method): bool
    {
        return in_array($method, self::getAllowedMethods(), true);
    }

    /**
     * @return bool whether an outgoing mail server is configured
     */
    public static function isOutgoingMailConfigured(): bool
    {
        $system = Settings_Vtiger_Systems_Model::getInstanceFromServerType('email', 'OutgoingServer');

        return trim((string)$system->get('server')) !== '';
    }

    /**
     * Whether a second factor must be passed for the given user.
     *
     * Default policy is "required for all users"; an explicit per-user exempt
     * flag opts a user out. New users are protected automatically (no row =
     * not exempt).
     *
     * @param int $userid
     * @return bool
     */
    public static function isRequiredForUser($userid): bool
    {
        $userid = (int)$userid;

        if ($userid <= 0) {
            return false;
        }

        if (self::getEnforceMode() !== 'all') {
            return false;
        }

        $row = self::getUserRow($userid);

        return empty($row) || (int)$row['exempt'] !== 1;
    }

    public static function isTotpMethodAvailable(): bool
    {
        return function_exists('openssl_encrypt')
            && function_exists('openssl_decrypt')
            && function_exists('openssl_get_cipher_methods')
            && in_array('aes-256-gcm', openssl_get_cipher_methods(), true)
            && class_exists(QrCode::class)
            && class_exists(Svg::class)
            && class_exists(SimpleXMLElement::class);
    }

    /**
     * @param string $code
     * @return string the backup code stripped of grouping and uppercased
     */
    private static function normalizeBackupCode(string $code): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }

    /**
     * Regenerates the user's backup codes, stashes them for one-time display and
     * emails a copy (best effort) using the backup-codes template.
     *
     * @param int $userid
     * @return void
     */
    public static function regenSelfBackupCodes($userid): void
    {
        $codes = self::generateBackupCodes((int)$userid);
        $_SESSION[self::SELF_KEY] = ['codes' => $codes];

        self::emailBackupCodes((int)$userid, $codes);
    }

    /**
     * Records a failed attempt and locks the challenge once the limit is hit.
     *
     * @return void
     */
    private static function registerFailure(): void
    {
        $state = self::getState();
        $state['attempts'] = (int)$state['attempts'] + 1;
        $userid = (int)$state['pending_userid'];
        TwoFactorAuthentication_User_Model::getInstance()
            ->updateFailureState($userid, self::MAX_ATTEMPTS, self::LOCK_TTL);

        $row = self::getUserRow($userid);
        $storedAttempts = (int)$row['failed_attempts'];
        $storedLock = !empty($row['locked_until']) ? strtotime((string)$row['locked_until']) : 0;

        if ($storedLock > time()) {
            $state['lock_until'] = $storedLock;
            $state['attempts'] = 0;
        } else {
            $state['attempts'] = $storedAttempts;
        }

        $_SESSION[self::SESSION_KEY] = $state;
    }

    /**
     * Remembers the user's last chosen method as their preference.
     *
     * @param int $userid
     * @param string $method
     * @return void
     */
    private static function rememberMethod($userid, string $method): void
    {
        TwoFactorAuthentication_User_Model::getInstance()->saveMethod((int)$userid, $method);
    }

    /**
     * Renders a template with the given variables substituted.
     *
     * @param string $key
     * @param array $vars placeholder => value
     * @return array{subject: string, body: string}
     */
    private static function renderEmail(string $key, array $vars): array
    {
        $tpl = self::getEmailTemplate($key);

        return [
            'subject' => strtr($tpl['subject'], $vars),
            'body' => strtr($tpl['body'], $vars),
        ];
    }

    /**
     * Re-sends a fresh email passcode for the current pending challenge.
     *
     * @return bool true when a code was sent
     */
    public static function resendEmailCode(): bool
    {
        $state = self::getState();

        if (empty($state) || ($state['stage'] ?? '') !== self::STAGE_EMAIL) {
            return false;
        }

        return self::sendNewEmailCode();
    }

    /**
     * Restores a template to its default (drops the custom row).
     *
     * @param string $key
     * @return void
     */
    public static function resetEmailTemplate(string $key): void
    {
        TwoFactorAuthentication_EmailTemplate_Model::getInstance()->deleteByKey($key);
    }

    /**
     * Resets a user's second factor: clears the authenticator secret and backup
     * codes so the user re-enrolls (or re-chooses) on their next login. Used by
     * admins when a user loses access to their device.
     *
     * @param int $userid
     * @return void
     */
    public static function resetUser($userid): void
    {
        $userid = (int)$userid;

        // method '' = not chosen, so the user re-picks at next login (the reset
        // is for app users who lost their device).
        TwoFactorAuthentication_User_Model::getInstance()->reset($userid);
        TwoFactorAuthentication_BackupCode_Model::getInstance()->deleteByUserId($userid);
    }

    /**
     * @param string $key
     * @param string $subject
     * @param string $body
     * @return void
     */
    public static function saveEmailTemplate(string $key, string $subject, string $body): void
    {
        TwoFactorAuthentication_EmailTemplate_Model::getInstance()->saveTemplate($key, $subject, $body);
    }

    /**
     * Stores the TOTP secret (encrypted) and marks the user enrolled.
     *
     * @param int $userid
     * @param string $secretPlain
     * @param int|null $timeSlice already consumed enrollment time slice
     * @return void
     */
    private static function saveTotpSecret($userid, string $secretPlain, ?int $timeSlice = null): void
    {
        $encrypted = self::encryptSecret($secretPlain);
        TwoFactorAuthentication_User_Model::getInstance()->saveTotpEnrollment(
            (int)$userid,
            self::METHOD_TOTP,
            $encrypted,
            $timeSlice
        );
    }

    /**
     * Transitions the challenge into the stage for the chosen method: email
     * sends a passcode, TOTP either asks for a code or starts enrollment.
     *
     * @param string $method
     * @return void
     */
    private static function selectMethod($method): void
    {
        $state = self::getState();
        $state['method'] = $method;
        $_SESSION[self::SESSION_KEY] = $state;

        if ($method === self::METHOD_EMAIL) {
            self::sendNewEmailCode();

            return;
        }

        $row = self::getUserRow((int)$state['pending_userid']);

        if (!empty($row) && (int)$row['enrolled'] === 1 && !empty($row['secret'])) {
            self::setStage(self::STAGE_TOTP);
        } else {
            $state = self::getState();
            $state['enroll_secret'] = TwoFactorAuthentication_TOTP_Helper::generateSecret();
            $state['stage'] = self::STAGE_ENROLL;
            $_SESSION[self::SESSION_KEY] = $state;
        }
    }

    private static function sendEmailCode($userid, string $code): bool
    {
        $user = Users_Record_Model::getInstanceById((int)$userid, 'Users');
        $email = (string)$user->get('email1');
        $userName = (string)$user->get('user_name');

        if (trim($email) === '') {
            return false;
        }

        $companyName = (string)Vtiger_CompanyDetails_Model::getInstanceById()->get('organizationname');
        $mail = self::renderEmail(self::TPL_LOGIN, [
            '$username$' => $userName,
            '$code$' => $code,
            '$minutes$' => (string)(int)round(self::CODE_TTL / 60),
            '$company$' => $companyName,
        ]);

        return self::sendWithIts4YouEmails((int)$userid, $email, $userName, $mail);
    }

    /**
     * Generates and sends a fresh email passcode and moves to the email stage.
     *
     * @return bool true when the mailer accepted the message
     */
    private static function sendNewEmailCode(): bool
    {
        $state = self::getState();
        $code = self::generateNumericCode(self::CODE_LENGTH);
        $state['code_hash'] = hash('sha256', $code);
        $state['code_expiry'] = time() + self::CODE_TTL;
        $state['stage'] = self::STAGE_EMAIL;
        $state['delivery_failed'] = false;
        $_SESSION[self::SESSION_KEY] = $state;

        $sent = self::sendEmailCode((int)$state['pending_userid'], $code);

        if (!$sent) {
            $state = self::getState();
            $state['delivery_failed'] = true;
            $_SESSION[self::SESSION_KEY] = $state;
        }

        return $sent;
    }

    /**
     * Sends a system message through the ITS4YouEmails extension and its
     * configured SMTP transport.
     *
     * @param int $userid
     * @param string $email
     * @param string $recipientName
     * @param array $mail
     * @return bool
     */
    private static function sendWithIts4YouEmails(int $userid, string $email, string $recipientName, array $mail): bool
    {
        if (!function_exists('vtlib_isModuleActive') || !vtlib_isModuleActive('ITS4YouEmails')) {
            return false;
        }

        try {
            $mailer = ITS4YouEmails_Mailer_Model::getCleanInstance();
            $mailer->retrieveSMTP($userid);

            if (empty($mailer->From)) {
                $fromEmail = (string)vglobal('HELPDESK_SUPPORT_EMAIL_ID');
                $fromName = (string)vglobal('HELPDESK_SUPPORT_NAME');

                if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                    $mailer->setFrom($fromEmail, $fromName);
                }
            }

            $mailer->addAddress($email, $recipientName);
            $mailer->isHTML(true);
            $mailer->CharSet = 'UTF-8';
            $mailer->Subject = $mail['subject'];
            $mailer->Body = $mail['body'];
            $mailer->AltBody = trim(strip_tags($mail['body']));

            $emailRecord = new ITS4YouEmails_Record_Model();
            $emailRecord->setMailer($mailer);

            return (bool)$emailRecord->sendEmail();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Stores which methods users may use. Email is only kept on when an outgoing
     * mail server is configured, and at least one usable method always remains.
     *
     * @param bool $email
     * @param bool $totp
     * @return void
     */
    public static function setAllowedMethods(bool $email, bool $totp): void
    {
        if ($email && !self::isEmailMethodAvailable()) {
            $email = false;
        }

        if ($totp && !self::isTotpMethodAvailable()) {
            $totp = false;
        }

        if (!$email && !$totp) {
            if (self::isEmailMethodAvailable()) {
                $email = true;
            } elseif (self::isTotpMethodAvailable()) {
                $totp = true;
            }
        }

        TwoFactorAuthentication_Config_Model::getInstance()->updateAllowedMethods($email, $totp);
    }

    /**
     * Sets (or clears) the 2FA exemption for a single user.
     *
     * @param int $userid
     * @param bool $exempt
     * @return void
     */
    public static function setExempt($userid, bool $exempt): void
    {
        TwoFactorAuthentication_User_Model::getInstance()->saveExemption((int)$userid, $exempt);
    }

    /**
     * @param string $stage
     * @return void
     */
    private static function setStage(string $stage): void
    {
        $state = self::getState();
        $state['stage'] = $stage;
        $_SESSION[self::SESSION_KEY] = $state;
    }

    /**
     * Sets the user's preferred (default) method, keeping any existing
     * authenticator secret so they can switch back without re-enrolling.
     *
     * @param int $userid
     * @param string $method
     * @return void
     */
    public static function setUserMethod($userid, string $method): void
    {
        TwoFactorAuthentication_User_Model::getInstance()->saveMethod((int)$userid, $method);
    }

    /**
     * Starts a logged-in self-service authenticator enrollment by generating a
     * secret kept in the session (rendered by getSelfEnrollmentContext).
     *
     * @return void
     */
    public static function startSelfEnrollment(): void
    {
        $_SESSION[self::SELF_KEY] = ['secret' => TwoFactorAuthentication_TOTP_Helper::generateSecret()];
    }

    /**
     * Returns to the method chooser (only meaningful when more than one method
     * is allowed).
     *
     * @return void
     */
    public static function switchMethod(): void
    {
        $state = self::getState();

        if (empty($state)) {
            return;
        }

        $state['stage'] = self::STAGE_CHOOSE;
        $state['method'] = '';
        $state['code_hash'] = '';
        $state['code_expiry'] = 0;
        $state['enroll_secret'] = '';
        $state['delivery_failed'] = false;
        $_SESSION[self::SESSION_KEY] = $state;
    }

    /**
     * Validates a backup code and marks it used on a match.
     *
     * @param int $userid
     * @param string $input
     * @return bool
     */
    private static function verifyBackupCode($userid, string $input): bool
    {
        $normalized = self::normalizeBackupCode($input);

        if ($normalized === '') {
            return false;
        }

        $backupCodeModel = TwoFactorAuthentication_BackupCode_Model::getInstance();
        $codes = $backupCodeModel->retrieveUnusedByUserId((int)$userid);

        foreach ($codes as $row) {
            $id = (int)$row['id'];
            $hash = (string)$row['code_hash'];

            if (password_verify($normalized, $hash)) {
                return $backupCodeModel->updateUsed($id);
            }
        }

        return false;
    }

    /**
     * Verifies a submitted code against the email passcode or a backup code.
     *
     * On too many failures the challenge is temporarily locked. Successful
     * verification does not clear the pending state; the caller completes the
     * login with completeLogin().
     *
     * @param string $input
     * @return bool
     */
    public static function verifyCode($input): bool
    {
        $state = self::getState();

        if (empty($state) || empty($state['pending_userid'])) {
            return false;
        }

        if (self::getLockRemaining() > 0) {
            return false;
        }

        $input = trim((string)$input);
        $userid = (int)$state['pending_userid'];
        $stage = $state['stage'] ?? '';
        $verified = false;

        // A backup code is accepted in either entry stage.
        if (self::verifyBackupCode($userid, $input)) {
            $verified = true;
        } elseif ($stage === self::STAGE_EMAIL) {
            $verified = self::verifyEmailCode($state, $input);
        } elseif ($stage === self::STAGE_TOTP) {
            $verified = self::verifyTotpCode($userid, $input);
        }

        if (!$verified) {
            self::registerFailure();
        }

        return $verified;
    }

    /**
     * Validates the email passcode against the stored hash and expiry.
     *
     * @param array $state
     * @param string $input
     * @return bool
     */
    private static function verifyEmailCode(array $state, string $input): bool
    {
        if (empty($state['code_hash']) || time() > (int)$state['code_expiry']) {
            return false;
        }

        $input = preg_replace('/\s+/', '', $input);

        return hash_equals((string)$state['code_hash'], hash('sha256', $input));
    }

    /**
     * Validates a TOTP code against the user's stored (encrypted) secret.
     *
     * @param int $userid
     * @param string $input
     * @return bool
     */
    private static function verifyTotpCode($userid, string $input): bool
    {
        $row = self::getUserRow($userid);

        if (empty($row) || empty($row['secret'])) {
            return false;
        }

        $secret = self::decryptSecret((string)$row['secret']);

        if ($secret === '') {
            return false;
        }

        $slice = TwoFactorAuthentication_TOTP_Helper::matchSlice($secret, $input);

        if ($slice === PHP_INT_MIN) {
            return false;
        }

        // Reject replay: a code is accepted only once. The matched time step must
        // be newer than the last accepted one (guards reuse within the ± window).
        $lastStep = isset($row['totp_last_step']) ? (int)$row['totp_last_step'] : 0;

        if ($slice <= $lastStep) {
            return false;
        }

        $userModel = TwoFactorAuthentication_User_Model::getInstance();

        if (!$userModel->updateTotpLastStep((int)$userid, $slice)) {
            return false;
        }

        if (!str_starts_with((string)$row['secret'], 'v2:')) {
            $userModel->updateSecret((int)$userid, self::encryptSecret($secret));
        }

        return true;
    }
}
