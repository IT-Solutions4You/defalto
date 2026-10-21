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
 * The state of a conversation that has NO driving CRM record — simple
 * support answered by one mail needs no ticket to be "done".
 *
 * Three states: open (the customer wrote last), waiting (we answered and
 * wait) and done. Open and waiting are not stored — they follow who wrote
 * the newest mail; only "done" is (df_mailmanager_thread.assign_status)
 * and new mail from the customer takes it back. A conversation with a
 * driving record (ticket, project, task…) keeps that record's state and owner.
 *
 * Only work mail has a state: conversations under a label with the
 * Workspace flag — newsletters and spam are neither open nor done.
 */
class MailManager_ProThreadState_Model
{
    public const OPEN = 'open';
    public const WAITING = 'waiting';
    public const DONE = 'done';

    /** State => language key */
    public const LABELS = [
        self::OPEN => 'LBL_STATE_OPEN',
        self::WAITING => 'LBL_STATE_WAITING',
        self::DONE => 'LBL_STATE_DONE',
    ];

    /**
     * @param string $alias thread table alias
     * @return string SQL: the conversation is work mail (a Workspace label)
     */
    public static function statefulSql($alias = 't')
    {
        return $alias . '.labelid IN (SELECT sl.labelid FROM df_mailmanager_label sl WHERE sl.workspace = 1)';
    }

    /**
     * @param string $alias thread table alias
     * @return string SQL: no record drives the conversation
     */
    public static function noRecordSql($alias = 't')
    {
        return 'NOT EXISTS (SELECT 1 FROM df_mailmanager_rel sr
                    INNER JOIN vtiger_crmentity sce ON sce.crmid = sr.crmid AND sce.deleted = 0
                    WHERE sr.threadid = ' . $alias . '.threadid AND sce.setype IN ('
            . MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::DRIVING_MODULES) . '))';
    }

    /**
     * @param string $alias thread table alias
     * @return string SQL: no live CRM record is linked to the conversation
     */
    public static function noLinkSql($alias = 't')
    {
        return 'NOT EXISTS (SELECT 1 FROM df_mailmanager_rel lr
                    INNER JOIN vtiger_crmentity lce ON lce.crmid = lr.crmid AND lce.deleted = 0
                    WHERE lr.threadid = ' . $alias . '.threadid)';
    }

    /**
     * Senders that never read an answer (local part of the address)
     */
    private const AUTOMATED_SENDER_REGEXP = '^(no-?reply|do-?not-?reply|mailer-daemon|postmaster|bounces?|notifications?|newsletter)([+._-][^@]*)?@';

    /**
     * Mail nobody answers or files: a conversation with a cleanup label (Spam,
     * Newsletter — a label outside the Workspace), whose newest received mail
     * comes from a no-reply address, or is a notice this CRM sent itself (I19 test:
     * the AI outage mails — the support name of config.inc.php as the sender name and a
     * Message-ID of this server)
     *
     * @param string $alias thread table alias
     * @return string SQL (no parameters): the conversation is NOT such mail
     */
    public static function notAutomatedSql($alias = 't')
    {
        $newestIn = 'SELECT %s FROM df_mailmanager_message am
                    WHERE am.threadid = ' . $alias . ".threadid AND am.direction = 'in' AND am.is_draft = 0
                    ORDER BY am.sent_at DESC, am.messageid DESC LIMIT 1";
        $sql = 'NOT EXISTS (SELECT 1 FROM df_mailmanager_label al WHERE al.labelid = ' . $alias . '.labelid AND al.workspace = 0)
            AND IFNULL((' . sprintf($newestIn, 'am.from_email') . "), '') NOT REGEXP '" . self::AUTOMATED_SENDER_REGEXP . "'";
        $ownNotice = self::getOwnNoticeSql();

        if ('' !== $ownNotice) {
            $sql .= ' AND NOT IFNULL((' . sprintf($newestIn, $ownNotice) . '), 0)';
        }

        return $sql;
    }

    /**
     * @return string SQL expression on message alias am: a notice this CRM sent itself, '' when
     *                the server has no host name to tell it by
     */
    private static function getOwnNoticeSql(): string
    {
        global $HELPDESK_SUPPORT_NAME, $site_URL;
        // the same sender name the notices use (MailManager_ProAiFailoverNotice_Model)
        $name = trim((string)($HELPDESK_SUPPORT_NAME ?: 'Defalto CRM'));
        $hosts = array_unique(array_filter([
            strtolower((string)gethostname()),
            strtolower((string)parse_url((string)$site_URL, PHP_URL_HOST)),
        ]));

        if ('' === $name || !$hosts) {
            return '';
        }

        $adb = PearDatabase::getInstance();
        $conditions = [];

        foreach ($hosts as $host) {
            $conditions[] = "LOWER(am.header_message_id) LIKE '%@" . $adb->sql_escape_string(addcslashes($host, '%_\\')) . ">'";
        }

        return "(am.from_name = '" . $adb->sql_escape_string($name) . "' AND (" . implode(' OR ', $conditions) . '))';
    }

    /**
     * The order that makes the newest message of a conversation the first one. At the same
     * time our own message counts as the newer: a mail the mailbox sent to itself is stored as
     * the sent copy and the received one, both with one sent_at — the received copy must not
     * make the customer the one who wrote last.
     *
     * @param string $alias message table alias
     *
     * @return string SQL ORDER BY expression (no parameters)
     */
    public static function newestMessageOrderSql(string $alias): string
    {
        return $alias . '.sent_at DESC, ' . $alias . ".direction = 'out' DESC, " . $alias . '.messageid DESC';
    }

    /** The triage has sorted the conversation (done states) */
    private const TRIAGED_STATES = ['labeled', 'suggested', 'accepted', 'created', 'dismissed'];

    /**
     * Automatic mail kept out of the Workspace digest (I19) — one place for every view that
     * needs it (the Unanswered tab may take it too): notAutomatedSql() (a cleanup label, a
     * no-reply sender) and, while the request radar runs, a conversation the triage sorted
     * since the radar was switched on (its newest received mail) without a request type — the triage gives none to
     * newsletters, notifications and other machine mail. Without the radar the triage has no
     * such signal (request_type stays empty for all mail), only notAutomatedSql() applies.
     *
     * @param string $alias thread table alias
     * @return string SQL (no parameters): the conversation is NOT automatic mail
     */
    public static function notAutomaticMailSql($alias = 't')
    {
        $sql = self::notAutomatedSql($alias);
        $radarSince = MailManager_ProAiConfig_Model::get(MailManager_ProRadar_Model::SINCE_KEY);

        if (MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_RADAR)
            && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $radarSince)) {
            $states = "'" . implode("', '", self::TRIAGED_STATES) . "'";
            // the newest RECEIVED mail decides — our reply after the radar's start does not
            // turn a conversation sorted before it into automatic mail
            $sql .= ' AND NOT (' . $alias . '.triage_state IN (' . $states . ') AND ' . $alias . ".request_type IS NULL
                    AND IFNULL((SELECT MAX(rm.sent_at) FROM df_mailmanager_message rm
                        WHERE rm.threadid = " . $alias . ".threadid AND rm.direction = 'in'), '') >= '" . $radarSince . "')";
        }

        return $sql;
    }

    /**
     * @param string $alias thread table alias
     * @return string SQL: the newest mail of the conversation is a received one — nobody answered yet
     */
    public static function lastInSql($alias = 't')
    {
        return 'NOT (' . self::lastOutSql($alias) . ')';
    }

    /**
     * @param string $alias thread table alias
     * @return string SQL: the newest mail of the conversation is our own
     */
    private static function lastOutSql($alias)
    {
        return "IFNULL((SELECT sm.direction FROM df_mailmanager_message sm
                    WHERE sm.threadid = " . $alias . ".threadid AND sm.is_draft = 0
                    ORDER BY " . self::newestMessageOrderSql('sm') . " LIMIT 1), 'in') = 'out'";
    }

    /**
     * The mailbox is the user's to look after — he is its owner, whether it
     * is his personal one or a shared one (info@) he is responsible for. All
     * of its mail is his: nothing leaves his sight because a colleague got
     * the ticket or the task made from it
     *
     * @param string $alias thread table alias
     * @return string SQL with ONE parameter (the user): the conversation lies
     *                in a mailbox that user owns
     */
    public static function ownMailboxSql($alias = 't')
    {
        return "EXISTS (SELECT 1 FROM df_mailmanager_mailbox sb
                    WHERE sb.mailboxid = " . $alias . ".mailboxid AND sb.owner_userid = ?)";
    }

    /**
     * @param string $alias thread table alias
     * @return string SQL with ONE parameter (the user): the conversation lies
     *                in a PERSONAL mailbox that user owns — nobody else is
     *                there to take anything
     */
    private static function personalMailboxSql($alias = 't')
    {
        return "EXISTS (SELECT 1 FROM df_mailmanager_mailbox sb
                    WHERE sb.mailboxid = " . $alias . ".mailboxid AND sb.mailbox_type = 'personal'
                      AND sb.owner_userid = ?)";
    }

    /**
     * Condition of a list tab for the conversations that have a state of
     * their own (the list adds the ones a record drives itself)
     *
     * @param string $filter mine | open | waiting | unassigned | done
     * @param int $userId
     * @param string $alias thread table alias
     * @return array [SQL condition, parameters], ['', []] for a tab without one
     */
    public static function filterSql($filter, $userId, $alias = 't')
    {
        $notDone = $alias . ".assign_status <> '" . self::DONE . "'";
        $ownMailbox = self::ownMailboxSql($alias);
        $conditions = [
            self::OPEN => [$notDone . ' AND NOT (' . self::lastOutSql($alias) . ')', []],
            self::WAITING => [$notDone . ' AND ' . self::lastOutSql($alias), []],
            self::DONE => [$alias . ".assign_status = '" . self::DONE . "'", []],
            // Mine: what the user took, and everything in a mailbox he owns —
            // his personal one, or a shared one he is responsible for (there
            // also what a colleague took: the owner keeps the overview)
            'mine' => [
                $notDone . ' AND (' . $alias . '.assigned_userid = ? OR ' . $ownMailbox . ')',
                [(int)$userId, (int)$userId],
            ],
            // "nobody has it yet" exists only in a mailbox more people work
            // in — not in the user's own personal one
            'unassigned' => [
                $notDone . ' AND ' . $alias . '.assigned_userid IS NULL AND NOT ' . self::personalMailboxSql($alias),
                [(int)$userId],
            ],
        ];
        if (!isset($conditions[$filter])) {
            return ['', []];
        }

        return [
            '(' . self::noRecordSql($alias) . ' AND ' . self::statefulSql($alias)
                . ' AND ' . $conditions[$filter][0] . ')',
            $conditions[$filter][1],
        ];
    }

    /**
     * Gives the conversations without a driving record their state and owner
     * ('state' => ['key', 'label', 'is_done', 'owner_id', 'owner_name',
     * 'owner_initials'], null where the conversation has none)
     *
     * @param array $threads thread rows carrying 'driving', by reference
     * @return void
     */
    public static function attach(array &$threads)
    {
        $threadIds = [];
        foreach ($threads as $index => $thread) {
            $threads[$index]['state'] = null;
            if (empty($thread['driving'])) {
                $threadIds[] = (int)$thread['threadid'];
            }
        }
        if (!$threadIds) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $marks = generateQuestionMarks($threadIds);
        // the newest mail of each conversation decides between open and waiting
        $result = $adb->pquery(
            'SELECT t.threadid, t.assign_status, t.assigned_userid,
                    (SELECT sm.direction FROM df_mailmanager_message sm
                      WHERE sm.threadid = t.threadid AND sm.is_draft = 0
                      ORDER BY ' . self::newestMessageOrderSql('sm') . ' LIMIT 1) AS last_direction
             FROM df_mailmanager_thread t
             WHERE t.threadid IN (' . $marks . ') AND ' . self::statefulSql('t'),
            $threadIds
        );
        $states = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $key = $row['assign_status'] === self::DONE
                ? self::DONE
                : ($row['last_direction'] === 'out' ? self::WAITING : self::OPEN);
            $ownerId = (int)$row['assigned_userid'];
            $ownerName = $ownerId ? trim((string)getUserFullName($ownerId)) : '';
            $states[(int)$row['threadid']] = [
                'key' => $key,
                'label' => vtranslate(self::LABELS[$key], 'MailManager'),
                'is_done' => $key === self::DONE,
                'owner_id' => $ownerId,
                'owner_name' => $ownerName,
                'owner_initials' => self::initials($ownerName),
            ];
        }

        foreach ($threads as $index => $thread) {
            if (empty($thread['driving'])) {
                $threads[$index]['state'] = $states[(int)$thread['threadid']] ?? null;
            }
        }
    }

    /**
     * @param string $fullName
     * @return string max 2 letters for the avatar of the list row
     */
    private static function initials($fullName)
    {
        $initials = '';
        foreach (array_slice(preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials;
    }

    /**
     * "Done" / "Reopen" by the user. Whoever closes a conversation nobody
     * has is its owner from then on
     *
     * @param int $threadId
     * @param bool $done
     * @param int $userId
     * @return void
     */
    public static function setDone($threadId, $done, $userId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'UPDATE df_mailmanager_thread SET assign_status = ?, assigned_userid = IFNULL(assigned_userid, ?)
             WHERE threadid = ?',
            [$done ? self::DONE : self::OPEN, (int)$userId, (int)$threadId]
        );
        MailManager_ProHistory_Model::logThread(
            (int)$threadId,
            MailManager_ProHistory_Model::ACTION_STATE,
            MailManager_ProHistory_Model::SOURCE_USER,
            null,
            vtranslate(self::LABELS[$done ? self::DONE : self::OPEN], 'MailManager')
        );
    }

    /**
     * Hands the conversation to somebody working in the mailbox
     *
     * @param array $mailbox mailbox row
     * @param int $threadId
     * @param int $assigneeId
     * @return bool false when that user does not work in the mailbox
     */
    public static function assign(array $mailbox, $threadId, $assigneeId)
    {
        if (!isset(self::getAssignees($mailbox)[(int)$assigneeId])) {
            return false;
        }
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_thread SET assigned_userid = ? WHERE threadid = ?',
            [(int)$assigneeId, (int)$threadId]
        );

        return true;
    }

    /**
     * Who a conversation of the mailbox can be handed to — the owner and the
     * members of a shared mailbox; a personal mailbox has nobody to hand to
     *
     * @param array $mailbox mailbox row
     * @return array userid => full name
     */
    public static function getAssignees(array $mailbox)
    {
        if (($mailbox['mailbox_type'] ?? '') !== 'shared') {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT member_id FROM df_mailmanager_mailbox_access
             WHERE mailboxid = ? AND member_type = 'user' AND (valid_until IS NULL OR valid_until >= ?)",
            [(int)$mailbox['mailboxid'], date('Y-m-d')]
        );
        $userIds = [(int)$mailbox['owner_userid']];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $userIds[] = (int)$adb->query_result($result, $i, 'member_id');
        }

        $assignees = [];
        foreach (array_unique(array_filter($userIds)) as $userId) {
            $name = trim((string)getUserFullName($userId));
            if ($name !== '') {
                $assignees[$userId] = $name;
            }
        }
        asort($assignees);

        return $assignees;
    }

    /**
     * We answered — whoever answers a conversation nobody has takes it; the
     * "Send and mark done" choice closes it at once (otherwise the newest
     * mail being ours makes it "waiting" by itself)
     *
     * @param int $threadId
     * @param int $userId
     * @param bool $done
     * @return void
     */
    public static function onReply($threadId, $userId, $done = false)
    {
        if (!$threadId) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'UPDATE df_mailmanager_thread SET assigned_userid = IFNULL(assigned_userid, ?) WHERE threadid = ?',
            [(int)$userId, (int)$threadId]
        );
        // a reply to a closed conversation opens it again — unless it closes it itself
        $adb->pquery(
            'UPDATE df_mailmanager_thread SET assign_status = ? WHERE threadid = ?',
            [$done ? self::DONE : self::OPEN, (int)$threadId]
        );
    }

    /**
     * New mail from the customer — a closed conversation is open again
     *
     * @param int $threadId
     * @return void
     */
    public static function onIncoming($threadId)
    {
        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread SET assign_status = '" . self::OPEN . "'
             WHERE threadid = ? AND assign_status = '" . self::DONE . "'",
            [(int)$threadId]
        );
    }

    /**
     * @param int $threadId
     * @return bool the conversation has a state of its own (work mail no record drives)
     */
    public static function hasOwnState($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_thread t
             WHERE t.threadid = ? AND ' . self::statefulSql('t') . ' AND ' . self::noRecordSql('t'),
            [(int)$threadId]
        );

        return (bool)$adb->num_rows($result);
    }
}
