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
 * Template data of the Add Mailbox wizard (Edit.tpl): step 1 = email &
 * provider, step 2 = incoming mail (OAuth / IMAP), step 3 = outgoing mail
 * (own SMTP, IMAP only), step 4 = mailbox settings;
 * the admin also edits existing mailboxes. The admin runs it in Settings,
 * a user adds an own personal mailbox from the module (user mode)
 */
class Settings_MailManager_WizardView_Model
{
    /**
     * @param array $input record, step, email, name and provider of the request
     * @param bool $userMode a user's own mailbox: always personal, no edit, the
     *                       OAuth applications stay the admin's
     * @return array template variables
     */
    public static function getViewData(array $input, $userMode)
    {
        $recordId = $userMode ? 0 : (int)$input['record'];

        $step = (int)$input['step'];
        if ($step < 1 || $step > 4) {
            $step = 1;
        }

        $email = trim($input['email']);
        $mailboxName = trim($input['name']);
        $provider = $input['provider'];
        if (!in_array($provider, ['office365', 'google', 'imap'], true)) {
            $provider = '';
        }
        if (!$recordId && $step > 1 && ($provider === '' || $email === '' || $mailboxName === '')) {
            $step = 1;
        }
        // Microsoft / Google mailboxes are saved from step 2 (personal, OAuth sign-in)
        if ($step > 2 && $provider !== 'imap') {
            $step = 2;
        }

        $data = [
            'STEP' => $recordId ? 4 : $step,
            'IS_EDIT' => $recordId > 0,
            'RECORD_ID' => $recordId,
            'EMAIL' => $email,
            'MAILBOX_NAME' => $mailboxName,
            'PROVIDER' => $provider,
            'USER_MODE' => (bool)$userMode,
            // the wizard steps and the way back — Settings for the admin,
            // the inbox for a user
            'WIZARD_URL' => $userMode
                ? 'index.php?module=MailManager&view=ProAddMailbox&app=TOOLS'
                : 'index.php?module=MailManager&parent=Settings&view=Edit',
            'LIST_URL' => $userMode
                ? 'index.php?module=MailManager&view=List&app=TOOLS'
                : 'index.php?module=MailManager&parent=Settings&view=Index',
        ];

        if ($recordId) {
            $data = array_merge($data, self::getEditModeData($recordId));
        } elseif ($step === 2) {
            $data = array_merge($data, $provider === 'imap'
                ? self::getImapStepData($email)
                : self::getOAuthStepData($provider, $email));
        } elseif ($step === 3) {
            $data = array_merge($data, self::getSmtpStepData($email));
        } elseif ($step === 4) {
            $data = array_merge($data, self::getSettingsStepData($provider, $email));
        }

        return $data;
    }

    /**
     * Edit of an existing mailbox — the step 3 form prefilled from the database
     * (connection settings are not editable here)
     *
     * @param int $recordId
     * @return array
     */
    private static function getEditModeData($recordId)
    {
        $mailbox = Settings_MailManager_Mailbox_Model::getById($recordId);
        if (!$mailbox) {
            return ['SETTINGS_READY' => false];
        }

        $foldersList = Settings_MailManager_Mailbox_Model::getFolders($recordId);
        $delimiter = MailManager_ProFolder_Model::getDelimiter($recordId, array_column($foldersList, 'name'));

        foreach ($foldersList as $index => $folder) {
            $foldersList[$index]['checked'] = (int)$folder['sync_enabled'] === 1;
        }

        return [
            'SETTINGS_READY' => true,
            'EMAIL' => $mailbox['email'],
            'PROVIDER' => $mailbox['provider'],
            'ACCOUNT_EMAIL' => $mailbox['email'],
            'FOLDERS_LIST' => self::buildFolderTree($foldersList, $delimiter),
            'FOLDER_DELIMITER' => $delimiter,
            'MAILBOX_NAME' => $mailbox['name'],
            'MAILBOX_TYPE' => $mailbox['mailbox_type'],
            'IS_DEFAULT' => (int)$mailbox['is_default'] === 1,
            'IMAP_CERT_FINGERPRINT' => (string)$mailbox['imap_cert_fingerprint'],
            // Outgoing server — the stored password never leaves the server,
            // the form only knows whether one is set
            'SMTP_MODE' => $mailbox['smtp_mode'],
            'SMTP_SERVER' => (string)$mailbox['smtp_server'],
            'SMTP_PORT' => $mailbox['smtp_port'] ? (int)$mailbox['smtp_port'] : '',
            'SMTP_ENCRYPTION' => (string)$mailbox['smtp_encryption'],
            'SMTP_USERNAME' => (string)$mailbox['smtp_username'],
            'SMTP_HAS_PASSWORD' => (string)$mailbox['smtp_password'] !== '',
            'MEMBERS_SELECTED' => Settings_MailManager_Mailbox_Model::getAccessMembers($recordId),
            'USERS_LIST' => get_user_array(false, 'Active'),
        ];
    }

    /**
     * Step 2 data for the IMAP variant (prefills from the wizard session)
     *
     * @param string $email
     * @return array
     */
    private static function getImapStepData($email)
    {
        $wizard = Settings_MailManager_Wizard_Model::get();
        $imap = ($wizard['email'] ?? '') === $email ? ($wizard['imap'] ?? []) : [];
        $domain = strtolower((string)substr(strrchr($email, '@'), 1));

        return [
            'IMAP_SERVER' => $imap['server'] ?? ('imap.' . $domain),
            'IMAP_PORT' => $imap['port'] ?? 993,
            'IMAP_ENCRYPTION' => $imap['encryption'] ?? 'ssl',
            'IMAP_CERT_FINGERPRINT' => (string)($imap['cert_fingerprint'] ?? ''),
            'IMAP_USERNAME' => $imap['username'] ?? $email,
            'IMAP_TESTED' => Settings_MailManager_Wizard_Model::isImapTested($email),
            'FOLDERS' => (int)($wizard['folders'] ?? 0),
        ];
    }

    /**
     * Step 2 data for the OAuth variant (app configuration + connection status)
     *
     * @param string $provider
     * @param string $email
     * @return array
     */
    private static function getOAuthStepData($provider, $email)
    {
        // Microsoft 365 / Google sign in through Core_Auth_Model as the user's personal
        // mailbox (MailManager_ProOAuthAccount_Model); shared mailboxes use IMAP
        return [
            'REDIRECT_URI' => MailManager_ProOAuthAccount_Model::getRedirectUri($provider),
            'MICROSOFT_TENANT' => Core_Auth_Model::getMicrosoftTenant(),
        ];
    }

    /**
     * Step 3 data — the outgoing server, prefilled from the IMAP connection
     * (most hosts use the same account for both)
     *
     * @param string $email
     * @return array
     */
    private static function getSmtpStepData($email)
    {
        $wizard = Settings_MailManager_Wizard_Model::get();
        $sameMailbox = ($wizard['email'] ?? '') === $email;
        $imap = $sameMailbox ? ($wizard['imap'] ?? []) : [];
        $smtp = $sameMailbox ? ($wizard['smtp'] ?? []) : [];

        $domain = strtolower((string)substr(strrchr($email, '@'), 1));
        $imapServer = (string)($imap['server'] ?? '');
        $defaultServer = $imapServer !== ''
            ? preg_replace('/^imap\./i', 'smtp.', $imapServer)
            : 'smtp.' . $domain;

        return [
            'IMAP_CONNECTED' => Settings_MailManager_Wizard_Model::isImapTested($email),
            'SMTP_SERVER' => $smtp['server'] ?? $defaultServer,
            'SMTP_PORT' => $smtp['port'] ?? 587,
            'SMTP_ENCRYPTION' => $smtp['encryption'] ?? 'tls',
            'SMTP_USERNAME' => $smtp['username'] ?? ($imap['username'] ?? $email),
            'SMTP_TESTED' => Settings_MailManager_Wizard_Model::isSmtpTested($email),
        ];
    }

    /**
     * Step 4 data — mailbox settings form (folders from the connection,
     * users for the shared mailbox picker)
     *
     * @param string $provider
     * @param string $email
     * @return array
     */
    private static function getSettingsStepData($provider, $email)
    {
        $wizard = Settings_MailManager_Wizard_Model::get();
        // IMAP needs both servers tested, Microsoft / Google the sign-in
        $ready = $provider === 'imap'
            ? Settings_MailManager_Wizard_Model::isImapTested($email)
                && Settings_MailManager_Wizard_Model::isSmtpTested($email)
            : Settings_MailManager_Wizard_Model::isOAuthConnected($provider, $email);

        $delimiter = (string)($wizard['folder_delimiter'] ?? '');
        $foldersList = $wizard['folders_list'] ?? [];
        foreach ($foldersList as $index => $folder) {
            $foldersList[$index]['checked'] = in_array($folder['folder_type'] ?? '', ['inbox', 'sent'], true);
        }

        // Shared mailbox members are users only — groups proved confusing here (Matúš, 2026-07-15)
        // Mailbox name is collected in step 1 (mandatory, no auto-guess — Matúš, 2026-07-15)
        return [
            'SETTINGS_READY' => $ready,
            'ACCOUNT_EMAIL' => $wizard['account_email'] ?? '',
            'FOLDERS_LIST' => self::buildFolderTree($foldersList, $delimiter),
            'FOLDER_DELIMITER' => $delimiter,
            'MAILBOX_TYPE' => 'personal',
            // the first mailbox of its owner (the signed-in user) is the
            // default; another one must not take that over by itself
            'IS_DEFAULT' => !self::userOwnsMailbox((int)Users_Record_Model::getCurrentUserModel()->getId()),
            'MEMBERS_SELECTED' => [],
            'USERS_LIST' => get_user_array(false, 'Active'),
        ];
    }

    /**
     * @param int $userId
     * @return bool the user owns a mailbox already
     */
    private static function userOwnsMailbox($userId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_mailbox WHERE owner_userid = ? LIMIT 1',
            [$userId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Sorts folders into a display tree: special folders first
     * (INBOX, Drafts, Sent, Archive, Spam, Trash), then custom ones
     * alphabetically; children indent under their parent path
     *
     * @param array $folders unified folder arrays
     * @param string $delimiter hierarchy delimiter ('' = flat list)
     * @return array with added depth / display / sort keys
     */
    private static function buildFolderTree(array $folders, $delimiter)
    {
        $rankMap = ['inbox' => 0, 'drafts' => 1, 'sent' => 2, 'archive' => 3, 'spam' => 4, 'trash' => 5];

        $typeByName = [];
        foreach ($folders as $folder) {
            $typeByName[mb_strtolower($folder['name'])] = $folder['folder_type'] ?? 'custom';
        }

        foreach ($folders as $index => $folder) {
            $segments = $delimiter !== '' ? explode($delimiter, $folder['name']) : [$folder['name']];
            $rootType = $typeByName[mb_strtolower($segments[0])] ?? ($folder['folder_type'] ?? 'custom');

            $folders[$index]['depth'] = count($segments) - 1;
            $folders[$index]['display'] = end($segments);
            $folders[$index]['rank'] = $rankMap[$rootType] ?? 6;
            $folders[$index]['sort_path'] = mb_strtolower($folder['name']);
        }

        usort($folders, function ($a, $b) {
            return [$a['rank'], $a['sort_path']] <=> [$b['rank'], $b['sort_path']];
        });

        // After path sorting a direct child immediately follows its parent
        $total = count($folders);
        foreach ($folders as $index => $folder) {
            $prefix = $folder['sort_path'] . $delimiter;
            $folders[$index]['has_children'] = $delimiter !== ''
                && $index + 1 < $total
                && strpos($folders[$index + 1]['sort_path'], $prefix) === 0;
        }

        return $folders;
    }
}
