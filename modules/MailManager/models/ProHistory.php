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
 * Activity history of a mailbox — what happened to which mail, when, and
 * who did it: moves, deletions, labels, CRM links and sent mail, manual (the user) and
 * automatic (spam rule, label rule, AI, auto-link). Every entry keeps its own
 * copy of the sender, subject and folder / label / record names, so it stays
 * readable after the mail left the cache. Each mailbox keeps its newest
 * KEEP_PER_MAILBOX entries — a quiet mailbox keeps years, a busy one weeks
 */
class MailManager_ProHistory_Model
{
    public const ACTION_MOVE = 'move';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LABEL = 'label';
    public const ACTION_LINK = 'link';
    public const ACTION_UNLINK = 'unlink';
    public const ACTION_SEND = 'send';
    public const ACTION_PRIORITY = 'priority';
    public const ACTION_STATE = 'state';
    /** the AI noticed mail that tries to instruct it (I16) — never its content */
    public const ACTION_AI_SUSPICIOUS = 'aiflag';

    public const SOURCE_USER = 'user';
    public const SOURCE_RULE = 'rule';
    public const SOURCE_AI = 'ai';
    public const SOURCE_AUTO = 'auto';

    /** Entries kept per mailbox — older ones are pruned */
    public const KEEP_PER_MAILBOX = 10000;

    /** The pruning runs on every n-th entry, not on each insert */
    private const PRUNE_EVERY = 100;

    /** Entries per page of the history list */
    public const PAGE_SIZE = 50;

    /** Action => [icon, language key] */
    public const ACTIONS = [
        self::ACTION_MOVE => ['fa-solid fa-folder-open', 'LBL_HISTORY_MOVE'],
        self::ACTION_DELETE => ['fa-solid fa-trash', 'LBL_HISTORY_DELETE'],
        self::ACTION_LABEL => ['fa-solid fa-tag', 'LBL_HISTORY_LABEL'],
        self::ACTION_LINK => ['fa-solid fa-link', 'LBL_HISTORY_LINK'],
        self::ACTION_UNLINK => ['fa-solid fa-unlink', 'LBL_HISTORY_UNLINK'],
        self::ACTION_SEND => ['fa-solid fa-paper-plane', 'LBL_HISTORY_SEND'],
        self::ACTION_PRIORITY => ['fa-solid fa-flag', 'LBL_HISTORY_PRIORITY'],
        self::ACTION_STATE => ['fa-solid fa-check-circle-o', 'LBL_HISTORY_STATE'],
        self::ACTION_AI_SUSPICIOUS => ['fa-solid fa-shield-halved', 'LBL_AI_SUSPICIOUS'],
    ];

    /** Automatic source => [icon, language key] (the user's entries show the user's name) */
    private const SOURCES = [
        self::SOURCE_RULE => ['fa-solid fa-graduation-cap', 'LBL_HISTORY_BY_RULE'],
        self::SOURCE_AI => ['fa-solid fa-wand-magic-sparkles', 'LBL_HISTORY_BY_AI'],
        self::SOURCE_AUTO => ['fa-solid fa-bolt', 'LBL_HISTORY_BY_AUTOLINK'],
    ];

    /**
     * Logs an action on a cached conversation — the sender and subject come
     * from the cache (call it before the cache drops the thread)
     *
     * @param int $threadId
     * @param string $action ACTION_*
     * @param string $source SOURCE_*
     * @param string|null $fromValue the state before: folder path / label name / record module
     * @param string|null $toValue the state after: folder path / label name / record name / recipients
     * @param int|null $crmid the linked / unlinked record
     * @return void
     */
    public static function logThread($threadId, $action, $source, $fromValue = null, $toValue = null, $crmid = null)
    {
        $adb = PearDatabase::getInstance();
        $threadResult = $adb->pquery(
            'SELECT mailboxid, subject FROM df_mailmanager_thread WHERE threadid = ?',
            [$threadId]
        );
        if (!$adb->num_rows($threadResult)) {
            return;
        }
        $thread = $adb->raw_query_result_rowdata($threadResult, 0);

        // the other party and when its newest mail arrived — own mail only
        // when nobody else wrote
        $senderResult = $adb->pquery(
            "SELECT from_email, sent_at FROM df_mailmanager_message
             WHERE threadid = ? ORDER BY direction = 'in' DESC, sent_at DESC LIMIT 1",
            [$threadId]
        );
        $hasMessage = (bool)$adb->num_rows($senderResult);

        self::add([
            'mailboxid' => (int)$thread['mailboxid'],
            'threadid' => (int)$threadId,
            'action' => $action,
            'source' => $source,
            'from_email' => $hasMessage ? $adb->query_result($senderResult, 0, 'from_email') : null,
            'mail_date' => $hasMessage ? $adb->query_result($senderResult, 0, 'sent_at') : null,
            'subject' => $thread['subject'],
            'from_value' => $fromValue,
            'to_value' => $toValue,
            'crmid' => $crmid,
        ]);
    }

    /**
     * Logs one entry — also for mail never cached (the spam filter moves it
     * during the sync)
     *
     * @param array $entry mailboxid, action, source + optional threadid,
     *                     from_email, subject, from_value, to_value, crmid,
     *                     mail_date (UTC, when the mail itself arrived)
     * @return void
     */
    public static function add(array $entry)
    {
        // subjects carry emoji — the 4-byte connection charset
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $userId = $entry['source'] === self::SOURCE_USER
            ? (int)Users_Record_Model::getCurrentUserModel()->getId()
            : null;

        $adb->pquery(
            'INSERT INTO df_mailmanager_history
             (mailboxid, threadid, action, source, userid, from_email, subject, from_value, to_value, crmid,
              mail_date, createdtime)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                (int)$entry['mailboxid'],
                !empty($entry['threadid']) ? (int)$entry['threadid'] : null,
                $entry['action'],
                $entry['source'],
                $userId ?: null,
                self::cut($entry['from_email'] ?? null, 150),
                self::cut($entry['subject'] ?? null, 500),
                self::cut($entry['from_value'] ?? null, 255),
                self::cut($entry['to_value'] ?? null, 255),
                !empty($entry['crmid']) ? (int)$entry['crmid'] : null,
                !empty($entry['mail_date']) ? $entry['mail_date'] : null,
                date('Y-m-d H:i:s'),
            ]
        );

        if ((int)$adb->getLastInsertID() % self::PRUNE_EVERY === 0) {
            self::prune((int)$entry['mailboxid']);
        }
    }

    /**
     * @param int[] $threadIds
     * @param int   $userId
     *
     * @return int[] the conversations the user answered in (a "send" entry of his)
     */
    public static function getThreadIdsSentBy(array $threadIds, int $userId): array
    {
        $threadIds = array_values(array_filter(array_map('intval', $threadIds)));

        if (!$threadIds) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT DISTINCT threadid FROM df_mailmanager_history
             WHERE action = ? AND userid = ? AND threadid IN (' . generateQuestionMarks($threadIds) . ')',
            array_merge([self::ACTION_SEND, $userId], $threadIds)
        );
        $answered = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $answered[] = (int)$row['threadid'];
        }

        return $answered;
    }

    /**
     * The owner reads the own mailbox's history, the admin any mailbox's
     *
     * @param int $mailboxId
     * @param Users_Record_Model $user
     * @return bool
     */
    public static function canView($mailboxId, $user)
    {
        if ($user->isAdminUser()) {
            return true;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM df_mailmanager_mailbox
             WHERE mailboxid = ? AND owner_userid = ? AND mailbox_type = 'personal'",
            [$mailboxId, (int)$user->getId()]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * One page of a mailbox's history, newest first
     *
     * @param int $mailboxId
     * @param string $search text in the sender, subject, folder, label or record name
     * @param string $action ACTION_* or '' for all
     * @param int $offset
     * @return array ['entries' => array, 'has_more' => bool]
     */
    public static function getPage($mailboxId, $search = '', $action = '', $offset = 0)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $where = ['h.mailboxid = ?'];
        $params = [(int)$mailboxId];
        if (isset(self::ACTIONS[$action])) {
            $where[] = 'h.action = ?';
            $params[] = $action;
        }
        $search = trim((string)$search);
        if ($search !== '') {
            $where[] = '(h.from_email LIKE ? OR h.subject LIKE ? OR h.from_value LIKE ? OR h.to_value LIKE ?)';
            $like = '%' . addcslashes($search, '%_\\') . '%';
            array_push($params, $like, $like, $like, $like);
        }

        $result = $adb->pquery(
            'SELECT h.* FROM df_mailmanager_history h
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY h.historyid DESC
             LIMIT ' . (int)max(0, $offset) . ', ' . (self::PAGE_SIZE + 1),
            $params
        );

        $entries = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $entries[] = self::decorate($adb->raw_query_result_rowdata($result, $i));
        }
        $hasMore = count($entries) > self::PAGE_SIZE;

        return ['entries' => array_slice($entries, 0, self::PAGE_SIZE), 'has_more' => $hasMore];
    }

    /**
     * Folder as the user reads it — "Archive / 2025", not the raw server path
     *
     * @param int $folderId
     * @return string|null
     */
    public static function getFolderPath($folderId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT mailboxid, name FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);
        if (!$adb->num_rows($result)) {
            return null;
        }
        $folder = $adb->raw_query_result_rowdata($result, 0);

        return self::getFolderPathByName((int)$folder['mailboxid'], (string)$folder['name']);
    }

    /**
     * @param int $mailboxId
     * @param string $name folder name as the server lists it
     * @return string
     */
    public static function getFolderPathByName($mailboxId, $name)
    {
        $delimiter = MailManager_ProFolder_Model::getDelimiter($mailboxId, [$name]);

        return $delimiter !== '' ? implode(' / ', explode($delimiter, $name)) : $name;
    }

    /**
     * @param int[] $folderIds folders the conversation was in
     * @return string|null their paths, comma separated
     */
    public static function getFolderPaths(array $folderIds)
    {
        $paths = array_filter(array_map([self::class, 'getFolderPath'], $folderIds));

        return $paths ? implode(', ', array_unique($paths)) : null;
    }

    /**
     * The folders the conversation came from in its last move or delete (newest first).
     *
     * @param int $threadId
     *
     * @return string[] folder paths, [] when the history has none
     */
    public static function getLastSourcePaths(int $threadId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT from_value FROM df_mailmanager_history
             WHERE threadid = ? AND action IN (?, ?) AND from_value IS NOT NULL
             ORDER BY historyid DESC LIMIT 1',
            [$threadId, self::ACTION_MOVE, self::ACTION_DELETE]
        );

        if (!$adb->num_rows($result)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(', ', (string)$adb->raw_query_result_rowdata($result, 0)['from_value']))));
    }

    /**
     * @param int|null $labelId
     * @return string|null
     */
    public static function getLabelName($labelId)
    {
        if (!$labelId) {
            return null;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT name FROM df_mailmanager_label WHERE labelid = ?', [$labelId]);

        return $adb->num_rows($result) ? decode_html((string)$adb->query_result($result, 0, 'name')) : null;
    }

    /**
     * Display values of one entry — the user's date format, who did it,
     * the action and the linked record
     *
     * @param array $entry history row
     * @return array
     */
    private static function decorate(array $entry)
    {
        $qualifiedModule = 'Settings:MailManager';
        $dateTimeField = new DateTimeField($entry['createdtime']);
        $entry['created_display'] = $entry['createdtime'] ? $dateTimeField->getDisplayDateTimeValue() : '';
        // when the mail itself arrived — an action on old mail is no new mail
        // the mail's own time is UTC, the core field expects a CRM time
        $mailDateField = new DateTimeField(MailManager_ProMailFormat_Model::utcToDbTime($entry['mail_date'] ?? ''));
        $entry['mail_date_display'] = !empty($entry['mail_date']) ? $mailDateField->getDisplayDateTimeValue() : '';

        [$icon, $labelKey] = self::ACTIONS[$entry['action']] ?? ['fa-solid fa-circle-o', $entry['action']];
        $entry['action_icon'] = $icon;
        $entry['action_label'] = vtranslate($labelKey, $qualifiedModule);

        if ($entry['source'] === self::SOURCE_USER) {
            $entry['by'] = $entry['userid'] ? getUserFullName((int)$entry['userid']) : '';
            $entry['by_icon'] = 'fa-solid fa-user';
        } else {
            [$byIcon, $byKey] = self::SOURCES[$entry['source']] ?? ['fa-solid fa-cogs', $entry['source']];
            $entry['by'] = vtranslate($byKey, $qualifiedModule);
            $entry['by_icon'] = $byIcon;
        }

        // folders are stored as the server names them — shown as the sidebar
        // names them, in the reader's language ("Junk" → "Spam")
        if (in_array($entry['action'], [self::ACTION_MOVE, self::ACTION_DELETE], true)) {
            foreach (['from_value', 'to_value'] as $key) {
                $entry[$key] = self::unifyFolders((int)$entry['mailboxid'], $entry[$key]);
            }
        }

        // priorities are stored by their English name ("High") — shown in the reader's language
        if ($entry['action'] === self::ACTION_PRIORITY) {
            foreach (['from_value', 'to_value'] as $key) {
                if ((string)$entry[$key] !== '') {
                    $entry[$key] = vtranslate('LBL_PRIORITY_' . strtoupper((string)$entry[$key]), 'MailManager');
                }
            }
        }

        // a reply sent as a ticket comment keeps the module in from_value
        if ($entry['action'] === self::ACTION_SEND && (string)$entry['from_value'] !== '' && vtlib_isModuleActive((string)$entry['from_value'])) {
            $entry['from_value'] = vtranslate('SINGLE_' . $entry['from_value'], (string)$entry['from_value']);
        }

        // a record link shows its module icon; the module sits in from_value
        $entry['record_icon'] = '';
        $entry['record_url'] = '';
        if (in_array($entry['action'], [self::ACTION_LINK, self::ACTION_UNLINK], true) && $entry['from_value']) {
            $moduleModel = Vtiger_Module_Model::getInstance($entry['from_value']);
            $entry['record_icon'] = $moduleModel ? $moduleModel->getModuleIcon() : '';
            if ($entry['crmid'] && isRecordExists((int)$entry['crmid'])) {
                $entry['record_url'] = 'index.php?module=' . urlencode($entry['from_value'])
                    . '&view=Detail&record=' . (int)$entry['crmid'];
            }
        }

        return $entry;
    }

    /**
     * @param int $mailboxId
     * @param string|null $paths folder paths, comma separated (getFolderPaths)
     * @return string|null
     */
    private static function unifyFolders($mailboxId, $paths)
    {
        if ((string)$paths === '') {
            return $paths;
        }
        $unified = [];
        foreach (explode(', ', (string)$paths) as $path) {
            $unified[] = MailManager_ProFolder_Model::unifyPath($mailboxId, $path);
        }

        return implode(', ', array_unique($unified));
    }

    /**
     * Keeps the newest KEEP_PER_MAILBOX entries of the mailbox
     *
     * @param int $mailboxId
     * @return void
     */
    private static function prune($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT historyid FROM df_mailmanager_history WHERE mailboxid = ?
             ORDER BY historyid DESC LIMIT 1 OFFSET ' . self::KEEP_PER_MAILBOX,
            [$mailboxId]
        );
        if ($adb->num_rows($result)) {
            $adb->pquery(
                'DELETE FROM df_mailmanager_history WHERE mailboxid = ? AND historyid <= ?',
                [$mailboxId, (int)$adb->query_result($result, 0, 'historyid')]
            );
        }
    }

    /**
     * @param string|null $value
     * @param int $length column size
     * @return string|null
     */
    private static function cut($value, $length)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_substr(decode_html((string)$value), 0, $length);
    }
}
