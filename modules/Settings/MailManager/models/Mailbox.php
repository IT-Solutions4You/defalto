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
 * Mailbox persistence — df_mailmanager_mailbox (+ folders, shared access)
 */
class Settings_MailManager_Mailbox_Model
{
    /**
     * All mailboxes with the shared member count (Settings list)
     *
     * @return array
     */
    public static function getAll()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT m.* FROM df_mailmanager_mailbox m ORDER BY m.name',
            []
        );

        // the users of the shared mailboxes, by name
        $members = [];
        $accessResult = $adb->pquery(
            "SELECT mailboxid, member_id FROM df_mailmanager_mailbox_access WHERE member_type = 'user'",
            []
        );
        for ($i = 0, $count = $adb->num_rows($accessResult); $i < $count; $i++) {
            $memberId = (int)$adb->query_result($accessResult, $i, 'member_id');
            $members[(int)$adb->query_result($accessResult, $i, 'mailboxid')][$memberId] =
                trim((string)getUserFullName($memberId));
        }

        $rows = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            // the admin overview: whose personal mailbox it is, who works in a shared one
            $row['owner_name'] = trim((string)getUserFullName((int)$row['owner_userid']));
            // the owner is named on a line of his own — not once more among the members
            $names = $members[(int)$row['mailboxid']] ?? [];
            unset($names[(int)$row['owner_userid']]);
            sort($names);
            $row['member_names'] = implode(', ', array_filter($names));
            // the deputies the owner let into a personal mailbox
            $row['deputies'] = $row['mailbox_type'] === 'personal'
                ? MailManager_ProSharing_Model::getDeputies((int)$row['mailboxid'])
                : [];
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param int $mailboxId
     * @return array|null
     */
    public static function getById($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_mailbox WHERE mailboxid = ?', [$mailboxId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * @param int $mailboxId
     * @return array folder rows (remote_id, name, folder_type, sync_enabled)
     */
    public static function getFolders($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT remote_id, name, folder_type, sync_enabled FROM df_mailmanager_folder WHERE mailboxid = ? ORDER BY name',
            [$mailboxId]
        );

        $rows = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $rows[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $rows;
    }

    /**
     * @param int $mailboxId
     * @return array members in "user:ID" / "group:ID" format
     */
    public static function getAccessMembers($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT member_id, member_type FROM df_mailmanager_mailbox_access WHERE mailboxid = ?',
            [$mailboxId]
        );

        $members = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $members[] = $adb->query_result($result, $i, 'member_type') . ':' . $adb->query_result($result, $i, 'member_id');
        }

        return $members;
    }

    /**
     * Updates the mailbox settings (name, type, members, default, scanned folders);
     * connection settings are not editable here
     *
     * @param int $mailboxId
     * @param array $data ['name', 'mailbox_type', 'shared_with' => [], 'folders' => [], 'is_default' => bool,
     *                    'imap_cert_fingerprint' => string (trusted certificate, '' = none)]
     * @return void
     * @throws Exception when the mailbox does not exist
     */
    public static function update($mailboxId, array $data)
    {
        $adb = PearDatabase::getInstance();
        $mailbox = self::getById($mailboxId);
        if (!$mailbox) {
            throw new Exception('Mailbox not found');
        }

        // a personal mailbox linked to a Basic account takes its credentials from that
        // account and cannot become a shared mailbox
        if (!empty($mailbox['account_id']) && ($data['mailbox_type'] ?? $mailbox['mailbox_type']) !== $mailbox['mailbox_type']) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        if (!empty($data['is_default'])) {
            $adb->pquery(
                'UPDATE df_mailmanager_mailbox SET is_default = 0 WHERE owner_userid = ?',
                [$mailbox['owner_userid']]
            );
        }

        $smtp = self::prepareSmtp($data, $mailbox);
        // the trusted certificate (Thunderbird-style exception) belongs to IMAP mailboxes only
        // a save without the field (the owner's My Settings) keeps the stored one
        $fingerprint = $mailbox['provider'] === 'imap'
            ? MailManager_ProImapClient_Helper::normalizeFingerprint(
                $data['imap_cert_fingerprint'] ?? (string)$mailbox['imap_cert_fingerprint']
            )
            : '';
        $adb->pquery(
            'UPDATE df_mailmanager_mailbox
             SET name = ?, mailbox_type = ?, is_default = ?, imap_cert_fingerprint = ?,
                 smtp_mode = ?, smtp_server = ?, smtp_port = ?, smtp_encryption = ?,
                 smtp_username = ?, smtp_password = ?, modifiedtime = ?
             WHERE mailboxid = ?',
            [
                $data['name'], $data['mailbox_type'], !empty($data['is_default']) ? 1 : 0, $fingerprint ?: null,
                $smtp['mode'], $smtp['server'], $smtp['port'], $smtp['encryption'],
                $smtp['username'], $smtp['password'], date('Y-m-d H:i:s'), $mailboxId,
            ]
        );

        // the members of a shared mailbox are replaced; the deputies of a
        // personal one are the owner's, kept unless it turns shared or back
        if ($data['mailbox_type'] === 'shared' || $mailbox['mailbox_type'] !== $data['mailbox_type']) {
            $adb->pquery('DELETE FROM df_mailmanager_mailbox_access WHERE mailboxid = ?', [$mailboxId]);
        }
        if ($data['mailbox_type'] === 'shared') {
            self::saveAccess($mailboxId, $data['shared_with']);
        }

        // folders are changed only by forms that send the folder selection
        if (!array_key_exists('folders', $data)) {
            return;
        }

        $adb->pquery('UPDATE df_mailmanager_folder SET sync_enabled = 0 WHERE mailboxid = ?', [$mailboxId]);
        if ($data['folders']) {
            $adb->pquery(
                'UPDATE df_mailmanager_folder SET sync_enabled = 1 WHERE mailboxid = ? AND remote_id IN (' . generateQuestionMarks($data['folders']) . ')',
                array_merge([$mailboxId], $data['folders'])
            );
        }
    }

    /**
     * Outgoing server columns from the form. 'crm' mode clears the custom
     * settings; a blank password keeps the stored one (the form never
     * receives it)
     *
     * @param array $data form data ('smtp_mode', 'smtp_server', 'smtp_port',
     *                    'smtp_encryption', 'smtp_username', 'smtp_password')
     * @param array $current existing mailbox row ([] on create)
     * @return array mode/server/port/encryption/username/password (encrypted)
     */
    private static function prepareSmtp(array $data, array $current)
    {
        if (($data['smtp_mode'] ?? 'crm') !== 'custom') {
            return [
                'mode' => 'crm', 'server' => null, 'port' => null,
                'encryption' => null, 'username' => null, 'password' => null,
            ];
        }

        $server = trim((string)($data['smtp_server'] ?? ''));
        if ($server === '') {
            throw new Exception(vtranslate('LBL_SMTP_SERVER_REQUIRED', 'Settings:MailManager'));
        }

        $encryption = in_array($data['smtp_encryption'] ?? '', ['ssl', 'none'], true)
            ? $data['smtp_encryption']
            : 'tls';
        $port = (int)($data['smtp_port'] ?? 0);
        if ($port < 1) {
            $port = $encryption === 'ssl' ? 465 : 587;
        }

        // every save is checked, not only the connection test (N24)
        if (!MailManager_ProHostGuard_Model::isAllowed($server, $port)) {
            throw new Exception(vtranslate('LBL_PRO_HOST_NOT_ALLOWED', 'MailManager'));
        }

        $password = (string)($data['smtp_password'] ?? '');
        if ($password === '') {
            // unchanged — reuse whatever is stored (already encrypted)
            $password = (string)($current['smtp_password'] ?? '');
        } else {
            require_once 'include/utils/encryption.php';

            $encrypt = new Encryption();
            $password = $encrypt->encrypt($password);
        }

        return [
            'mode' => 'custom',
            'server' => $server,
            'port' => $port,
            'encryption' => $encryption,
            'username' => trim((string)($data['smtp_username'] ?? '')),
            'password' => $password,
        ];
    }

    /**
     * Creates the mailbox from the wizard session + step 3 form data
     *
     * @param array $wizard wizard session state (connection, folders_list)
     * @param array $data ['name', 'mailbox_type', 'shared_with' => [], 'folders' => [],
     *                     'sync_from_days' => int, 'is_default' => bool]
     * @param int $ownerUserId
     * @return int new mailboxid
     */
    public static function saveFromWizard(array $wizard, array $data, $ownerUserId)
    {
        require_once 'include/utils/encryption.php';

        $adb = PearDatabase::getInstance();
        $encryption = new Encryption();

        $provider = $wizard['provider'];
        $now = date('Y-m-d H:i:s');
        $syncFrom = $data['sync_from_days'] > 0 ? date('Y-m-d', strtotime('-' . $data['sync_from_days'] . ' days')) : null;

        $oauthAppId = null;
        $oauthTokens = null;
        $imap = [
            'server' => null, 'port' => null, 'encryption' => null, 'username' => null, 'password' => null,
            'cert_fingerprint' => '',
        ];

        // Release 1 creates IMAP mailboxes only; Google mailboxes come from the Basic account
        // (Core_Auth_Model), Microsoft 365 follows with the Core OAuth provider (08-CONNECTIONS.md)
        if ($provider !== 'imap') {
            throw new Exception('Mail Manager Pro: provider "' . $provider . '" is not supported yet');
        }

        $imap = $wizard['imap'];

        if (!MailManager_ProHostGuard_Model::isAllowed($imap['server'], $imap['port'])) {
            throw new Exception(vtranslate('LBL_PRO_HOST_NOT_ALLOWED', 'MailManager'));
        }

        $imap['password'] = $encryption->encrypt($imap['password']);

        if (!empty($data['is_default'])) {
            $adb->pquery('UPDATE df_mailmanager_mailbox SET is_default = 0 WHERE owner_userid = ?', [$ownerUserId]);
        }

        // announced by the server in the connection test (API providers use "/")
        $folderDelimiter = (string)($wizard['folder_delimiter'] ?? '');
        if ($folderDelimiter === '') {
            $folderDelimiter = $provider === 'imap' ? null : '/';
        }

        $smtp = self::prepareSmtp($data, []);
        $adb->pquery(
            'INSERT INTO df_mailmanager_mailbox
                (name, email, provider, mailbox_type, owner_userid, status, is_default, oauthappid, oauth_tokens,
                 imap_server, imap_port, imap_encryption, imap_username, imap_password,
                 imap_cert_fingerprint, folder_delimiter,
                 smtp_mode, smtp_server, smtp_port, smtp_encryption, smtp_username, smtp_password,
                 sync_from, createdtime, modifiedtime)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['name'], $wizard['email'], $provider, $data['mailbox_type'], $ownerUserId,
                'active', !empty($data['is_default']) ? 1 : 0, $oauthAppId, $oauthTokens,
                $imap['server'], $imap['port'], $imap['encryption'], $imap['username'], $imap['password'],
                MailManager_ProImapClient_Helper::normalizeFingerprint($imap['cert_fingerprint'] ?? '') ?: null,
                // the server announced it in the connection test (API providers: "/")
                $folderDelimiter,
                $smtp['mode'], $smtp['server'], $smtp['port'], $smtp['encryption'],
                $smtp['username'], $smtp['password'],
                $syncFrom, $now, $now,
            ]
        );
        $mailboxId = (int)$adb->getLastInsertID();

        self::saveFolders($mailboxId, $wizard['folders_list'] ?? [], $data['folders']);

        if ($data['mailbox_type'] === 'shared') {
            self::saveAccess($mailboxId, $data['shared_with']);
        }

        return $mailboxId;
    }

    /**
     * @param int $mailboxId
     * @param array $foldersList unified folder arrays from the connection step
     * @param array $selectedRemoteIds remote_ids checked in step 3
     * @return void
     */
    private static function saveFolders($mailboxId, array $foldersList, array $selectedRemoteIds)
    {
        $adb = PearDatabase::getInstance();
        foreach ($foldersList as $folder) {
            $adb->pquery(
                'INSERT INTO df_mailmanager_folder (mailboxid, remote_id, name, folder_type, sync_enabled)
                 VALUES (?,?,?,?,?)',
                [
                    $mailboxId,
                    $folder['remote_id'],
                    $folder['name'],
                    $folder['folder_type'] ?? 'custom',
                    in_array($folder['remote_id'], $selectedRemoteIds, true) ? 1 : 0,
                ]
            );
        }
    }

    /**
     * @param int $mailboxId
     * @param array $members values in "user:ID" / "group:ID" format
     * @return void
     */
    private static function saveAccess($mailboxId, array $members)
    {
        $adb = PearDatabase::getInstance();
        foreach ($members as $member) {
            [$type, $id] = array_pad(explode(':', $member, 2), 2, '');
            if (!in_array($type, ['user', 'group'], true) || !(int)$id) {
                continue;
            }
            $adb->pquery(
                'INSERT INTO df_mailmanager_mailbox_access (mailboxid, member_id, member_type) VALUES (?,?,?)',
                [$mailboxId, (int)$id, $type]
            );
        }
    }

    /**
     * Deletes the mailbox with all cached data, spam rules and the style
     * profile (CRM records created from mails and filed Emails stay)
     *
     * @param int $mailboxId
     * @return void
     */
    public static function delete($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $threadResult = $adb->pquery('SELECT threadid FROM df_mailmanager_thread WHERE mailboxid = ?', [$mailboxId]);
        $threadIds = [];
        $count = $adb->num_rows($threadResult);
        for ($i = 0; $i < $count; $i++) {
            $threadIds[] = (int)$adb->query_result($threadResult, $i, 'threadid');
        }

        if ($threadIds) {
            $questionMarks = generateQuestionMarks($threadIds);
            $adb->pquery(
                'DELETE a FROM df_mailmanager_attachment a
                 INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
                 WHERE m.threadid IN (' . $questionMarks . ')',
                $threadIds
            );
            $adb->pquery('DELETE FROM df_mailmanager_message WHERE threadid IN (' . $questionMarks . ')', $threadIds);
            $adb->pquery('DELETE FROM df_mailmanager_rel WHERE threadid IN (' . $questionMarks . ')', $threadIds);
            $adb->pquery('DELETE FROM df_mailmanager_unlink WHERE threadid IN (' . $questionMarks . ')', $threadIds);
            // the AI usage stays for the cost overview, only the thread reference goes
            $adb->pquery('UPDATE df_mailmanager_ailog SET threadid = NULL WHERE threadid IN (' . $questionMarks . ')', $threadIds);
        }

        $adb->pquery('DELETE FROM df_mailmanager_thread WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_folder WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_mailbox_access WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_spamrule WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_history WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_labelrule WHERE mailboxid = ?', [$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_config WHERE name = ?', ['ai_style_profile_' . (int)$mailboxId]);
        $adb->pquery('DELETE FROM df_mailmanager_mailbox WHERE mailboxid = ?', [$mailboxId]);
    }

    /**
     * The stored SMTP password of a mailbox, decrypted.
     *
     * @param int $mailboxId 0 = none
     *
     * @return string '' when the mailbox has none
     */
    public static function getSmtpPassword(int $mailboxId): string
    {
        $mailbox = $mailboxId ? self::getById($mailboxId) : null;

        if (!$mailbox || (string)$mailbox['smtp_password'] === '') {
            return '';
        }

        require_once 'include/utils/encryption.php';

        $encryption = new Encryption();

        return (string)$encryption->decrypt((string)$mailbox['smtp_password']);
    }

    /**
     * Saves the mailbox edited by the admin in Settings.
     *
     * @param int   $mailboxId
     * @param array $input name, mailbox_type, shared_with (JSON), folders (JSON), is_default,
     *                     imap_cert_fingerprint, smtp_mode, smtp_server, smtp_port,
     *                     smtp_encryption, smtp_username, smtp_password (raw)
     *
     * @return array ['success' => bool] plus 'message' on a failure
     */
    public static function saveSettings(int $mailboxId, array $input): array
    {
        $name = trim($input['name']);
        $mailboxType = $input['mailbox_type'] === 'shared' ? 'shared' : 'personal';
        $sharedWith = json_decode($input['shared_with'], true) ?: [];
        $folders = json_decode($input['folders'], true) ?: [];

        if ($mailboxId < 1 || $name === '' || ($mailboxType === 'shared' && !$sharedWith)) {
            return ['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'Settings:MailManager')];
        }

        try {
            self::update($mailboxId, [
                'name' => $name,
                'mailbox_type' => $mailboxType,
                'shared_with' => $sharedWith,
                'folders' => $folders,
                'is_default' => (int)$input['is_default'] === 1,
                'imap_cert_fingerprint' => (string)$input['imap_cert_fingerprint'],
                'smtp_mode' => $input['smtp_mode'] === 'custom' ? 'custom' : 'crm',
                'smtp_server' => $input['smtp_server'],
                'smtp_port' => $input['smtp_port'],
                'smtp_encryption' => $input['smtp_encryption'],
                'smtp_username' => $input['smtp_username'],
                'smtp_password' => $input['smtp_password'],
            ]);
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true];
    }
}
