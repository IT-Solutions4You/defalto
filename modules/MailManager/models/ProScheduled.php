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
 * "Send later" (I12): df_mailmanager_scheduled rows point to the SAVED ITS4YouEmails
 * record that holds the content and the files; the cron task MailManagerProScheduled
 * sends what is due through MailManager_ProOutbox_Model::deliver() as the user who
 * scheduled it. Times are UTC.
 */
class MailManager_ProScheduled_Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    /** Earliest "later" — seconds from now */
    public const MIN_DELAY = 60;

    /** Latest "later" — one year */
    public const MAX_DELAY = 31536000;

    /** A send that has not finished within this many seconds is reported as failed */
    private const STALE_SENDING = 1800;

    /**
     * @param int    $mailboxId
     * @param int    $userId      who scheduled it (and whose mail it is)
     * @param int    $emailId     the SAVED ITS4YouEmails record
     * @param string $scheduledAt Y-m-d H:i:s UTC
     * @param array  $context     MailManager_ProOutbox_Model::deliver() context
     *
     * @return int scheduledid
     */
    public static function add(int $mailboxId, int $userId, int $emailId, string $scheduledAt, array $context): int
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'INSERT INTO df_mailmanager_scheduled (mailboxid, threadid, userid, crm_emailid, scheduled_at, status, payload, createdtime)
             VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP())',
            [
                $mailboxId,
                (int)$context['thread'] ?: null,
                $userId,
                $emailId,
                $scheduledAt,
                self::STATUS_WAITING,
                // a broken character must not empty the whole send context
                json_encode($context, JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR),
            ]
        );

        return (int)$adb->getLastInsertID();
    }

    /**
     * @param string $scheduledAt Y-m-d H:i:s UTC
     *
     * @return bool the time lies between MIN_DELAY and MAX_DELAY from now
     */
    public static function isValidTime(string $scheduledAt): bool
    {
        $time = strtotime($scheduledAt . ' UTC');

        return false !== $time && $time >= time() + self::MIN_DELAY - 5 && $time <= time() + self::MAX_DELAY;
    }

    /**
     * The user's waiting and failed scheduled mail, the next first
     *
     * @param int $userId
     *
     * @return array rows + subject, to_display, time_display, time_input
     */
    public static function getForUser(int $userId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT s.*, e.subject, e.to_email FROM df_mailmanager_scheduled s
             INNER JOIN its4you_emails e ON e.its4you_emails_id = s.crm_emailid
             INNER JOIN vtiger_crmentity ce ON ce.crmid = s.crm_emailid AND ce.deleted = 0
             WHERE s.userid = ? AND s.status IN (?, ?) ORDER BY s.scheduled_at',
            [$userId, self::STATUS_WAITING, self::STATUS_FAILED]
        );
        $rows = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            // the CRM stores the e-mail fields HTML encoded
            $row['subject'] = decode_html((string)$row['subject']);
            $row['to_email'] = decode_html((string)$row['to_email']);
            $row['time_display'] = MailManager_ProMailFormat_Model::formatDateTime($row['scheduled_at']);
            $row['time_input'] = MailManager_ProMailFormat_Model::utcToUserInput($row['scheduled_at']);
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param int $userId
     *
     * @return int waiting scheduled mail of the user (the sidebar badge)
     */
    public static function countForUser(int $userId): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT COUNT(*) AS waiting FROM df_mailmanager_scheduled WHERE userid = ? AND status IN (?, ?)',
            [$userId, self::STATUS_WAITING, self::STATUS_FAILED]
        );

        return (int)$adb->query_result($result, 0, 'waiting');
    }

    /**
     * Cancels a waiting (or failed) scheduled mail of the user; its saved e-mail
     * record goes to the recycle bin
     *
     * @param int $scheduledId
     * @param int $userId
     *
     * @return bool
     * @throws Exception
     */
    public static function cancel(int $scheduledId, int $userId): bool
    {
        $row = self::getOwnRow($scheduledId, $userId);

        if (!$row || self::isAlreadySent($row)
            || !self::setStatus($scheduledId, [self::STATUS_WAITING, self::STATUS_FAILED], self::STATUS_CANCELLED)) {
            return false;
        }

        if (isRecordExists((int)$row['crm_emailid'])) {
            Vtiger_Record_Model::getInstanceById((int)$row['crm_emailid'], 'ITS4YouEmails')->delete();
        }

        return true;
    }

    /**
     * Moves a scheduled mail to the time the user entered.
     *
     * @param int    $scheduledId
     * @param int    $userId
     * @param string $userInput the user's wall-clock time in the user's CRM time zone
     *
     * @return array ['success' => bool] plus 'message' when the time is not valid
     */
    public static function updateScheduledTime(int $scheduledId, int $userId, string $userInput): array
    {
        $scheduledAt = MailManager_ProMailFormat_Model::userInputToUtc($userInput);

        return self::isValidTime($scheduledAt)
            ? ['success' => self::reschedule($scheduledId, $userId, $scheduledAt)]
            : ['success' => false, 'message' => vtranslate('LBL_SCHEDULE_INVALID', 'MailManager')];
    }

    /**
     * Moves a waiting (or failed) scheduled mail of the user to another time
     *
     * @param int    $scheduledId
     * @param int    $userId
     * @param string $scheduledAt Y-m-d H:i:s UTC
     *
     * @return bool
     */
    public static function reschedule(int $scheduledId, int $userId, string $scheduledAt): bool
    {
        $row = self::getOwnRow($scheduledId, $userId);

        if (!$row || !in_array($row['status'], [self::STATUS_WAITING, self::STATUS_FAILED], true) || self::isAlreadySent($row)) {
            return false;
        }

        // an unchanged time changes no row — the answer is still "done"
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_scheduled SET scheduled_at = ?, status = ?, error = NULL
             WHERE scheduledid = ? AND userid = ? AND status IN (?, ?)',
            [$scheduledAt, self::STATUS_WAITING, $scheduledId, $userId, self::STATUS_WAITING, self::STATUS_FAILED]
        );

        return true;
    }

    /**
     * Sends one scheduled mail of the user right now
     *
     * @param int $scheduledId
     * @param int $userId
     *
     * @return array ['success', 'message']
     */
    public static function sendNow(int $scheduledId, int $userId): array
    {
        $row = self::getOwnRow($scheduledId, $userId);

        if (!$row || self::isAlreadySent($row)
            || !self::setStatus($scheduledId, [self::STATUS_WAITING, self::STATUS_FAILED], self::STATUS_SENDING)) {
            return ['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')];
        }

        return self::deliverRow($row);
    }

    /**
     * Mail that was scheduled goes out also after the Membership ended (for example a user
     * limit reached): the cron sends it while the module is active and the Pro schema is
     * installed. Nothing new can be scheduled without Membership — the Pro pages and
     * actions stay locked.
     *
     * @return bool
     */
    public static function isSendingAllowed(): bool
    {
        return vtlib_isModuleActive('MailManager')
            && MailManager_Install_Model::getInstance('module.postupdate', 'MailManager')->isProSchemaInstalled();
    }

    /**
     * The cron phase: sends the mail that is due, as the user who scheduled it
     *
     * @param int $limit mail per run
     *
     * @return string[] log lines
     */
    public static function processDue(int $limit = 20): array
    {
        $adb = PearDatabase::getInstance();
        $lines = [];

        // a run that died while sending: sent when its e-mail record went out, otherwise
        // failed — never sent again by itself
        $stale = $adb->pquery(
            'SELECT * FROM df_mailmanager_scheduled
             WHERE status = ? AND claimed_at < UTC_TIMESTAMP() - INTERVAL ' . self::STALE_SENDING . ' SECOND',
            [self::STATUS_SENDING]
        );

        while ($staleRow = $adb->fetchByAssoc($stale, -1, false)) {
            self::isAlreadySent($staleRow)
                ? self::finish((int)$staleRow['scheduledid'], self::STATUS_SENT, null)
                : self::finish((int)$staleRow['scheduledid'], self::STATUS_FAILED, 'interrupted');
        }

        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_scheduled WHERE status = ? AND scheduled_at <= UTC_TIMESTAMP()
             ORDER BY scheduled_at LIMIT ' . $limit,
            [self::STATUS_WAITING]
        );
        $rows = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $rows[] = $row;
        }

        foreach ($rows as $row) {
            // another run took it in the meantime
            if (!self::setStatus((int)$row['scheduledid'], [self::STATUS_WAITING], self::STATUS_SENDING)) {
                continue;
            }

            $sent = self::deliverRow($row);
            $lines[] = 'scheduled ' . $row['scheduledid'] . ': ' . ($sent['success'] ? 'sent' : 'failed – ' . $sent['message']);
        }

        return $lines;
    }

    /**
     * Sends a claimed row (status sending) as its user and records the outcome
     *
     * @param array $row df_mailmanager_scheduled row
     *
     * @return array ['success', 'message']
     */
    private static function deliverRow(array $row): array
    {
        $previousUser = vglobal('current_user');
        $scheduledId = (int)$row['scheduledid'];

        try {
            $user = CRMEntity::getInstance('Users');
            $user->retrieveCurrentUserInfoFromFile((int)$row['userid']);
            vglobal('current_user', $user);

            // the user must still work in the mailbox
            $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$row['mailboxid'], (int)$row['userid']);

            if (!$mailbox || !isRecordExists((int)$row['crm_emailid'])) {
                throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
            }

            MailManager_ProInbox_Model::useUtf8mb4();
            MailManager_ProOutbox_Model::deliver($mailbox, (int)$row['crm_emailid'], (array)json_decode((string)$row['payload'], true));
            self::finish($scheduledId, self::STATUS_SENT, null);

            return ['success' => true, 'message' => ''];
        } catch (Throwable $e) {
            // a step after the SMTP send failed (Sent copy, history, …) — the mail is out,
            // it must not be offered for sending again
            if (self::isAlreadySent($row)) {
                self::finish($scheduledId, self::STATUS_SENT, $e->getMessage());

                return ['success' => true, 'message' => $e->getMessage()];
            }

            self::finish($scheduledId, self::STATUS_FAILED, $e->getMessage());

            return ['success' => false, 'message' => $e->getMessage()];
        } finally {
            vglobal('current_user', $previousUser);
        }
    }

    /**
     * @param array $row df_mailmanager_scheduled row
     *
     * @return bool its e-mail record went out already (ITS4YouEmails marks it SENT)
     */
    private static function isAlreadySent(array $row): bool
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT email_flag FROM its4you_emails WHERE its4you_emails_id = ?', [(int)$row['crm_emailid']]);

        return $adb->num_rows($result) && 'SENT' === (string)$adb->query_result($result, 0, 'email_flag');
    }

    /**
     * @param int $scheduledId
     * @param int $userId
     *
     * @return array|null the row when it is the user's
     */
    private static function getOwnRow(int $scheduledId, int $userId): ?array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_scheduled WHERE scheduledid = ? AND userid = ?', [$scheduledId, $userId]);

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * Atomic status change — only one request or run gets a row
     *
     * @param int      $scheduledId
     * @param string[] $from
     * @param string   $to
     *
     * @return bool the row was in one of $from and is $to now
     */
    private static function setStatus(int $scheduledId, array $from, string $to): bool
    {
        $adb = PearDatabase::getInstance();
        // claimed_at tells a send that died from one still running
        $result = $adb->pquery(
            'UPDATE df_mailmanager_scheduled SET status = ?, claimed_at = UTC_TIMESTAMP()
             WHERE scheduledid = ? AND status IN (' . generateQuestionMarks($from) . ')',
            array_merge([$to, $scheduledId], $from)
        );

        return (bool)$adb->getAffectedRowCount($result);
    }

    /**
     * @param int         $scheduledId
     * @param string      $status
     * @param string|null $error
     *
     * @return void
     */
    private static function finish(int $scheduledId, string $status, ?string $error): void
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_scheduled SET status = ?, error = ? WHERE scheduledid = ?',
            [$status, $error, $scheduledId]
        );
    }
}
