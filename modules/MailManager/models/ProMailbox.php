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
 * Pro mailboxes (df_mailmanager_mailbox). A personal mailbox with account_id is linked to
 * the user's Basic account in vtiger_mail_accounts: its server, user name, password and
 * Google OAuth token always come from there (01-DATA.md §2), so Basic keeps working after
 * the Membership ends and a password changed in Basic is used by Pro.
 */
class MailManager_ProMailbox_Model extends Core_DatabaseData_Model
{
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_SHARED = 'shared';

    protected string $table = 'df_mailmanager_mailbox';
    protected string $tableId = 'mailboxid';

    /**
     * Returns the configured table accessor for df_mailmanager_mailbox.
     *
     * @return self
     */
    public function getMailboxTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * Returns the active mailboxes due for a sync (not waiting after failures), never
     * synced first, then the oldest sync.
     *
     * @return array[] df_mailmanager_mailbox rows
     */
    public function getSyncMailboxes(): array
    {
        $db = $this->retrieveDB()->getDB();
        $result = $db->pquery(
            "SELECT * FROM df_mailmanager_mailbox
             WHERE status = 'active' AND (next_retry_at IS NULL OR next_retry_at <= ?)
             ORDER BY last_sync_at IS NULL DESC, last_sync_at",
            [date('Y-m-d H:i:s')]
        );
        $rows = [];

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Returns true when the mailbox row is a personal mailbox linked to a Basic account.
     *
     * @param array $mailbox df_mailmanager_mailbox row
     *
     * @return bool
     */
    public static function isLinkedToBasicAccount(array $mailbox): bool
    {
        return self::TYPE_PERSONAL === ($mailbox['mailbox_type'] ?? '') && !empty($mailbox['account_id']);
    }

    /**
     * Returns the mailbox row ready for the transport: for a personal mailbox linked to a
     * Basic account, the connection data and the encrypted password are taken from that
     * account; shared mailboxes are returned unchanged.
     *
     * @param array $mailbox df_mailmanager_mailbox row (secrets encrypted)
     *
     * @return array
     * @throws Exception when the linked Basic account no longer exists
     */
    public static function getTransportRow(array $mailbox): array
    {
        if (!self::isLinkedToBasicAccount($mailbox)) {
            return $mailbox;
        }

        $account = MailManager_Mailbox_Model::getActiveInstance(Users_Record_Model::getInstanceById((int)$mailbox['owner_userid'], 'Users'));

        if ($account->getAccountId() !== (int)$mailbox['account_id']) {
            throw new Exception('Mail Manager Pro: the linked Mail Manager account no longer exists');
        }

        $mailbox['imap_server'] = $account->server();
        $mailbox['imap_port'] = $account->getPort();
        $mailbox['imap_encryption'] = self::convertBasicSslType((string)$account->ssltype());
        $mailbox['imap_username'] = $account->username();
        $mailbox['imap_password'] = $account->password(false);
        $mailbox['provider'] = MailManager_ProTransport_Model::getProviderForAccount($account);
        $mailbox['oauth_access_token'] = $account->getClientAccessToken();

        return $mailbox;
    }

    /**
     * Converts the Basic SSL setting (ssl, tls, notls) to the Pro IMAP encryption
     * (ssl = implicit TLS, starttls, '' = none).
     *
     * @param string $sslType Basic ssltype value
     *
     * @return string
     */
    public static function convertBasicSslType(string $sslType): string
    {
        return match (strtolower($sslType)) {
            'tls' => 'starttls',
            'notls' => '',
            default => 'ssl',
        };
    }

    /**
     * Config key of the initial sync window in days for taken-over Basic accounts (D19).
     */
    public const SYNC_FROM_DAYS_SETTING = 'sync_from_days';

    /**
     * Default initial sync window in days (D19).
     */
    public const DEFAULT_SYNC_FROM_DAYS = 90;

    /**
     * Takes the Basic Mail Manager accounts over as personal Pro mailboxes (01-DATA.md §5):
     * a new account gets a linked mailbox row (credentials stay in vtiger_mail_accounts),
     * a removed account disables its mailbox, nothing is ever deleted. Idempotent.
     *
     * @param int|null $userId one user (current user on a Pro visit) or null for all active users
     *
     * @return int number of mailboxes created or re-activated
     * @throws Exception
     */
    public function linkBasicAccounts(?int $userId = null): int
    {
        $userIds = null === $userId ? array_keys(Users_Record_Model::getAll(true, false)) : [$userId];
        $changed = 0;

        foreach ($userIds as $ownerId) {
            $changed += $this->linkBasicAccount((int)$ownerId);
        }

        return $changed;
    }

    /**
     * Links the Basic account of one user (see linkBasicAccounts()): keeps an existing link,
     * re-activates it, switches to a new Basic account or disables the mailbox when the
     * user has no Basic account any more.
     *
     * @param int $userId
     *
     * @return int 1 when a mailbox was created or re-activated, otherwise 0
     * @throws Exception
     */
    public function linkBasicAccount(int $userId): int
    {
        $db = $this->retrieveDB()->getDB();
        $lockName = 'df_mm_link_' . $userId;
        $lock = $db->pquery('SELECT GET_LOCK(?, 10) AS locked', [$lockName]);

        if (1 !== (int)$db->query_result($lock, 0, 'locked')) {
            return 0;
        }

        try {
            return $this->saveBasicAccountLink($userId);
        } finally {
            $db->pquery('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    /**
     * Applies the Basic account link of one user while the per-user link lock is held.
     *
     * @param int $userId
     *
     * @return int 1 when a mailbox was created or re-activated, otherwise 0
     * @throws Exception
     */
    protected function saveBasicAccountLink(int $userId): int
    {
        $account = MailManager_Mailbox_Model::getActiveInstance(Users_Record_Model::getInstanceById($userId, 'Users'));
        $accountId = $account->getAccountId();
        $table = $this->getMailboxTable();

        foreach ($this->getLinkedMailboxes($userId) as $mailbox) {
            if ($accountId && (int)$mailbox['account_id'] === $accountId) {
                if ('active' === $mailbox['status']) {
                    return 0;
                }

                $table->updateData(['status' => 'active', 'modifiedtime' => date('Y-m-d H:i:s')], ['mailboxid' => (int)$mailbox['mailboxid']]);

                return 1;
            }

            if ('active' === $mailbox['status']) {
                $table->updateData(['status' => 'disabled', 'modifiedtime' => date('Y-m-d H:i:s')], ['mailboxid' => (int)$mailbox['mailboxid']]);
            }
        }

        if (!$accountId) {
            return 0;
        }

        $this->createBasicAccountMailbox($userId, $account);

        return 1;
    }

    /**
     * Returns the personal mailboxes of a user that are linked to a Basic account.
     *
     * @param int $userId
     *
     * @return array[] df_mailmanager_mailbox rows
     */
    public function getLinkedMailboxes(int $userId): array
    {
        $db = $this->retrieveDB()->getDB();
        $result = $db->pquery(
            'SELECT mailboxid, account_id, status FROM df_mailmanager_mailbox
             WHERE owner_userid = ? AND mailbox_type = ? AND account_id IS NOT NULL ORDER BY mailboxid',
            [$userId, self::TYPE_PERSONAL]
        );
        $rows = [];

        while ($row = $db->fetchByAssoc($result, -1, false)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Creates the personal Pro mailbox of a Basic account. Connection data is not copied;
     * it is read from the Basic account on every connection (getTransportRow()).
     *
     * @param int                       $userId
     * @param MailManager_Mailbox_Model $account the user's Basic account
     *
     * @return int new mailbox ID
     */
    public function createBasicAccountMailbox(int $userId, MailManager_Mailbox_Model $account): int
    {
        // the Basic login is usually the address; otherwise the user's e-mail is used
        $address = (string)$account->username();

        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            $address = (string)Users_Record_Model::getInstanceById($userId, 'Users')->get('email1') ?: $address;
        }

        $days = (int)((new MailManager_ProConfig_Model())->getValue(self::SYNC_FROM_DAYS_SETTING) ?? self::DEFAULT_SYNC_FROM_DAYS);
        $now = date('Y-m-d H:i:s');
        $table = $this->getMailboxTable();
        $hasDefault = null !== $table->selectData(['mailboxid'], ['owner_userid' => $userId, 'is_default' => 1]);

        return (int)$table->insertData([
            'name' => mb_substr($address, 0, 100),
            'email' => mb_substr($address, 0, 150),
            'provider' => MailManager_ProTransport_Model::getProviderForAccount($account),
            'mailbox_type' => self::TYPE_PERSONAL,
            'owner_userid' => $userId,
            'account_id' => $account->getAccountId(),
            'status' => 'active',
            'is_default' => $hasDefault ? 0 : 1,
            'smtp_mode' => 'crm',
            'sync_from' => $days > 0 ? date('Y-m-d', strtotime('-' . $days . ' days')) : null,
            'createdtime' => $now,
            'modifiedtime' => $now,
        ]);
    }

    /**
     * First retry delay after a failed sync in seconds; it doubles with every further failure.
     */
    public const RETRY_BASE_SECONDS = 300;

    /**
     * Longest retry delay after repeated failures in seconds.
     */
    public const RETRY_MAX_SECONDS = 21600;

    /**
     * Stores a completed sync: last sync time, and clears the failure state.
     *
     * @param int $mailboxId
     *
     * @return void
     */
    public function saveLastSync(int $mailboxId): void
    {
        $this->getMailboxTable()->updateData(
            ['last_sync_at' => date('Y-m-d H:i:s'), 'last_error' => null, 'failed_attempts' => 0, 'next_retry_at' => null],
            ['mailboxid' => $mailboxId]
        );
    }

    /**
     * Stores a failed sync (for example a rejected login) and postpones the next attempt
     * with an exponential backoff, so a wrong password does not lock the account at the
     * mail provider. The error is shown to the user and in Settings.
     *
     * @param array  $mailbox df_mailmanager_mailbox row
     * @param string $error   error message
     *
     * @return void
     */
    public function saveSyncFailure(array $mailbox, string $error): void
    {
        $attempts = (int)($mailbox['failed_attempts'] ?? 0) + 1;
        $delay = min(self::RETRY_BASE_SECONDS * (2 ** min($attempts - 1, 10)), self::RETRY_MAX_SECONDS);

        $this->getMailboxTable()->updateData(
            ['last_error' => mb_substr($error, 0, 1000), 'failed_attempts' => $attempts, 'next_retry_at' => date('Y-m-d H:i:s', time() + $delay)],
            ['mailboxid' => (int)$mailbox['mailboxid']]
        );
    }
}
