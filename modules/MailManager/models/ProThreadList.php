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
 * Thread lists and their counters — folder/search/filter pages, the
 * Workspace view, sidebar label and priority counts, the driving CRM
 * record of each row
 */
class MailManager_ProThreadList_Model
{
    /** Tabs above the conversation list, in their order */
    public const LIST_FILTERS = ['all', 'unread', 'unanswered', 'unlinked', 'mine', 'open', 'waiting', 'unassigned', 'done'];

    /**
     * Kind of a thread's newest message => [icon, language key] (list row).
     * A plain new mail has none — the icon marks what is out of the ordinary
     */
    private const KIND_ICONS = [
        'copy' => ['fa-regular fa-copy', 'LBL_KIND_COPY'],
        'reply' => ['fa-solid fa-reply', 'LBL_REPLIED'],
        'forward' => ['fa-solid fa-share', 'LBL_KIND_FORWARD'],
        'sent' => ['fa-regular fa-paper-plane', 'LBL_KIND_SENT'],
    ];

    /** Conversations per page of the list the admin can choose from */
    public const PAGE_SIZES = [25, 50, 100];

    /** Config key of the chosen page size */
    public const CONFIG_PAGE_SIZE = 'list_page_size';

    /**
     * Sidebar label filter data — active labels with the conversations of the
     * mailbox under each: unread_count is the badge (what waits, like the
     * folders show), thread_count the total in the tooltip
     *
     * @param int $mailboxId
     * @return array
     */
    public static function getSidebarLabels($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        [$scopeSql, $scopeParams] = Settings_MailManager_Label_Model::mailboxScopeSql('l', $mailboxId);
        // A cleanup label (Spam, Newsletter…) counts what was thrown away too —
        // its mail ends in Spam / Trash by design; the other labels count live
        // mail only ("cleanup OR (1 = 1 AND outside junk)"). Which labels clean
        // up is the mailbox's own matter (the owner's setting of a global label)
        $cleanupIds = Settings_MailManager_Label_Model::getCleanupLabelIds($mailboxId);
        $cleanupSql = $cleanupIds ? 'l.labelid IN (' . implode(', ', $cleanupIds) . ')' : '1 = 0';
        $result = $adb->pquery(
            'SELECT l.labelid, l.name, l.color, COUNT(t.threadid) AS thread_count,
                    IFNULL(SUM(t.unread_count > 0), 0) AS unread_count
             FROM df_mailmanager_label l
             LEFT JOIN df_mailmanager_thread t ON t.labelid = l.labelid AND t.mailboxid = ?
                AND (' . $cleanupSql . ' OR 1 = 1'
                . MailManager_ProThread_Model::outsideJunkSql() . ')
             WHERE l.active = 1 AND ' . $scopeSql . '
             GROUP BY l.labelid, l.name, l.color, l.sequence, l.owner_userid
             ORDER BY l.owner_userid IS NOT NULL, l.sequence',
            array_merge([$mailboxId], $scopeParams)
        );

        $labels = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $labels[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $labels;
    }

    /**
     * Sidebar priority filter data — conversations of the mailbox by priority
     *
     * @param int $mailboxId
     * @return array priority => ['unread' => int (the badge), 'total' => int (the tooltip)]
     */
    public static function getPriorityCounts($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT t.priority, COUNT(*) AS thread_count, SUM(t.unread_count > 0) AS unread_count
             FROM df_mailmanager_thread t
             WHERE t.mailboxid = ? AND t.priority IS NOT NULL' . MailManager_ProThread_Model::outsideJunkSql() . '
             GROUP BY t.priority',
            [$mailboxId]
        );

        $counts = array_fill_keys(MailManager_ProAiTriage_Model::PRIORITIES, ['unread' => 0, 'total' => 0]);
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $counts[$row['priority']] = ['unread' => (int)$row['unread_count'], 'total' => (int)$row['thread_count']];
        }

        return $counts;
    }

    /**
     * @return int conversations per page of the list (Settings, General)
     */
    public static function getPageSize()
    {
        $size = (int)MailManager_ProAiConfig_Model::get(self::CONFIG_PAGE_SIZE);

        return in_array($size, self::PAGE_SIZES, true) ? $size : self::PAGE_SIZES[0];
    }

    /**
     * The counters of the inbox sidebar refreshed while it is open: unread mail per
     * folder, the Workspace, the labels and the priorities.
     *
     * @param int $mailboxId
     * @param int $userId    current user
     *
     * @return array ['unread', 'workspace_unread', 'labels' => labelid => ['unread', 'total'], 'priorities']
     */
    public static function getSidebarCounts(int $mailboxId, int $userId): array
    {
        $labels = [];

        foreach (self::getSidebarLabels($mailboxId) as $label) {
            $labels[(int)$label['labelid']] = [
                'unread' => (int)$label['unread_count'],
                'total' => (int)$label['thread_count'],
            ];
        }

        return [
            'unread' => MailManager_ProFolder_Model::getUnreadCounts($mailboxId),
            'workspace_unread' => self::getWorkspaceUnreadCount($mailboxId, $userId),
            'labels' => $labels,
            'priorities' => self::getPriorityCounts($mailboxId),
        ];
    }

    /**
     * Browsing Spam / Trash – the list rows offer "Not spam" / "Restore" instead of
     * "Move to Spam". A search, a label, a priority or the Workspace lists more than
     * the open folder.
     *
     * @param int    $folderId
     * @param string $search
     * @param int    $labelId
     * @param string $priority
     * @param bool   $workspace
     *
     * @return string spam | trash | ''
     */
    public static function getListJunkType(int $folderId, string $search, int $labelId, string $priority, bool $workspace): string
    {
        $folderType = '' === $search && !$labelId && '' === $priority && !$workspace
            ? MailManager_ProFolder_Model::getFolderType($folderId)
            : '';

        return in_array($folderType, ['spam', 'trash'], true) ? $folderType : '';
    }

    /**
     * Threads having messages in the given folder, newest activity first
     *
     * @param int $mailboxId
     * @param int $folderId
     * @param int $page 1-based
     * @param string $filter all | mine | unassigned | done (shared mailbox tabs)
     * @param int $userId for the "mine" filter
     * @param string $search free-text query — searches the whole mailbox
     *                       (subject / participants / body) ignoring the folder;
     *                       Spam and Trash only when that folder is the open one
     * @param int $labelId sidebar label filter — mailbox-wide, ignores the folder
     * @param string $priority sidebar priority filter — mailbox-wide, ignores the folder
     * @param bool $workspace the Workspace view instead of a folder
     * @param int|array|null $offset "Load more": [last_at, threadid] of the last row
     *                         shown — the list continues strictly after it, whatever
     *                         left or joined the result meanwhile (a read mail drops
     *                         out of "Unread", a deleted one out of the folder; a
     *                         row count or a page number would skip conversations);
     *                         an int = rows to skip; null = by the page
     * @return array ['threads' => array[], 'has_more' => bool, 'can_load_older' => bool,
     *               'can_search_server' => bool] — the last two offer the server at the list end
     */
    public static function getThreads($mailboxId, $folderId, $page, $filter = 'all', $userId = 0, $search = '', $labelId = 0, $priority = '', $workspace = false, $offset = null)
    {
        $adb = PearDatabase::getInstance();
        $pageSize = self::getPageSize();
        $cursorSql = '';
        $cursorParams = [];
        if (is_array($offset)) {
            [$cursorAt, $cursorId] = array_pad(array_values($offset), 2, 0);
            // HAVING — the order value is a computed column of this query
            $cursorSql = ' HAVING (folder_last_at < ? OR (folder_last_at = ? AND t.threadid < ?))';
            $cursorParams = [(string)$cursorAt, (string)$cursorAt, (int)$cursorId];
            $offset = 0;
        } else {
            $offset = $offset === null ? (max(1, (int)$page) - 1) * $pageSize : max(0, (int)$offset);
        }
        $search = trim($search);

        // Tabs follow the DRIVING CRM record of the conversation (linked
        // Ticket / Opportunity / Lead) — its owner and its process state.
        // Done: Ticket = Closed, Opportunity = Closed Won/Lost
        $notClosed = "(tt.ticketstatus IS NULL OR tt.ticketstatus != 'Closed')
                      AND (po.sales_stage IS NULL OR po.sales_stage NOT IN ('Closed Won', 'Closed Lost'))";
        $closed = "tt.ticketstatus = 'Closed' OR po.sales_stage IN ('Closed Won', 'Closed Lost')";
        $projectJoin = '';
        if (MailManager_ProRelate_Model::hasProjects()) {
            $projectDone = MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::PROJECT_DONE);
            $taskDone = MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::TASK_DONE);
            $projectJoin = ' LEFT JOIN vtiger_project pr ON pr.projectid = r.crmid
                        LEFT JOIN vtiger_projecttask pt ON pt.projecttaskid = r.crmid';
            $notClosed .= ' AND (pr.projectstatus IS NULL OR pr.projectstatus NOT IN (' . $projectDone . '))
                      AND (pt.projecttaskstatus IS NULL OR pt.projecttaskstatus NOT IN (' . $taskDone . '))';
            $closed .= ' OR pr.projectstatus IN (' . $projectDone . ') OR pt.projecttaskstatus IN (' . $taskDone . ')';
        }
        $closed = '(' . $closed . ')';
        $drivingModules = MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::DRIVING_MODULES);
        // The state is the one of the record that DRIVES the conversation —
        // a won opportunity must not put the conversation of its running
        // project among the done ones (no higher-ranked record is linked)
        $drivingExists = function ($condition) use ($projectJoin, $drivingModules) {
            return "EXISTS (SELECT 1 FROM df_mailmanager_rel r
                        INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
                        LEFT JOIN vtiger_troubletickets tt ON tt.ticketid = r.crmid
                        LEFT JOIN vtiger_potential po ON po.potentialid = r.crmid" . $projectJoin . "
                        WHERE r.threadid = t.threadid
                          AND ce.setype IN (" . $drivingModules . ")
                          AND NOT EXISTS (SELECT 1 FROM df_mailmanager_rel r2
                                INNER JOIN vtiger_crmentity ce2 ON ce2.crmid = r2.crmid AND ce2.deleted = 0
                                WHERE r2.threadid = t.threadid
                                  AND FIELD(ce2.setype, " . $drivingModules . ") > 0
                                  AND FIELD(ce2.setype, " . $drivingModules . ")
                                      < FIELD(ce.setype, " . $drivingModules . "))
                          AND " . $condition . ')';
        };

        $filterSql = '';
        $filterParams = [];
        if ($filter === 'unread') {
            $filterSql = ' AND t.unread_count > 0';
        } elseif ($filter === 'unanswered') {
            // the customer wrote last and the conversation is not finished —
            // neither its driving record nor its own state is done
            $filterSql = ' AND ' . MailManager_ProThreadState_Model::lastInSql('t')
                . " AND t.assign_status <> '" . MailManager_ProThreadState_Model::DONE . "'"
                . ' AND NOT ' . $drivingExists($closed)
                . ' AND ' . MailManager_ProThreadState_Model::notAutomatedSql('t');
        } elseif ($filter === 'unlinked') {
            // newsletters and machine mail need no CRM record either
            $filterSql = ' AND ' . MailManager_ProThreadState_Model::noLinkSql('t')
                . ' AND ' . MailManager_ProThreadState_Model::notAutomatedSql('t');
        } elseif (in_array($filter, ['mine', 'open', 'waiting', 'unassigned', 'done'], true)) {
            // A conversation follows its driving record; one without a record
            // has a state of its own (ThreadState) — a tab lists both kinds.
            // A ticket waiting for the customer belongs to "waiting", not "open"
            $ticketWaits = "tt.ticketstatus = 'Wait For Response'";
            $recordConditions = [
                // the user's own records and those of the groups he is in
                'mine' => 'ce.assigned_user_id IN (' . self::ownerIdsSql($userId) . ') AND ' . $notClosed,
                'open' => $notClosed . ' AND (tt.ticketstatus IS NULL OR NOT ' . $ticketWaits . ')',
                'waiting' => $ticketWaits,
                'done' => $closed,
            ];
            [$ownSql, $ownParams] = MailManager_ProThreadState_Model::filterSql($filter, $userId);
            $parts = [$ownSql];
            if (isset($recordConditions[$filter])) {
                // the record part comes first — so do its parameters
                array_unshift($parts, $drivingExists($recordConditions[$filter]));
            }
            $filterParams = array_merge($filterParams, $ownParams);
            if ($filter === 'mine') {
                // mail of a mailbox the user owns is his whoever owns the
                // record made from it — a colleague works on the opportunity,
                // the customer still writes to this mailbox
                $parts[] = '(' . MailManager_ProThreadState_Model::ownMailboxSql('t')
                    . ' AND ' . $drivingExists($notClosed) . ')';
                $filterParams[] = $userId;
            }
            $filterSql = ' AND (' . implode(' OR ', $parts) . ')';
        }

        // Search and the sidebar label/priority filters span the whole mailbox
        // (any folder) and preview the newest message of the thread; plain
        // folder browsing previews through the folder
        $labelId = (int)$labelId;
        $folderScoped = $search === '' && !$labelId && $priority === '' && !$workspace;
        $previewFolderSql = $folderScoped ? ' AND mx.folderid = ?' : '';
        // Workspace and the label / priority filters are work queues — an
        // own reply is no news: order and preview follow the newest RECEIVED
        // mail (a thread of own mail only falls back to it); search keeps
        // the newest activity
        $inboundFirst = !$folderScoped && $search === '';
        $previewOrderSql = $inboundFirst ? "mx.direction = 'in' DESC, mx.sent_at DESC" : 'mx.sent_at DESC';
        $lastAtSql = $inboundFirst
            ? "IFNULL(MAX(IF(mx.direction = 'in', mx.sent_at, NULL)), MAX(mx.sent_at))"
            : 'MAX(mx.sent_at)';

        $scopeSql = '';
        $scopeParams = [];
        if ($folderScoped) {
            $scopeSql = ' AND EXISTS (SELECT 1 FROM df_mailmanager_message m WHERE m.threadid = t.threadid AND m.folderid = ?)';
            $scopeParams = [$folderId];
        }
        if ($search !== '') {
            $like = '%' . self::escapeLike($search) . '%';
            // the whole mailbox but what was thrown away: Spam and Trash are
            // searched only from inside (the folder that is open)
            $junkFolder = self::isJunkFolder($folderId) && !$workspace;
            $scopeSql .= " AND EXISTS (SELECT 1 FROM df_mailmanager_message m
                            INNER JOIN df_mailmanager_folder sf ON sf.folderid = m.folderid
                            WHERE m.threadid = t.threadid
                              AND " . ($junkFolder ? 'm.folderid = ?' : "sf.folder_type NOT IN ('spam', 'trash')") . "
                              AND (m.subject LIKE ? OR m.from_name LIKE ? OR m.from_email LIKE ?
                                   OR m.to_addrs LIKE ? OR m.body_plain LIKE ?))";
            $scopeParams = array_merge(
                $scopeParams,
                $junkFolder ? [$folderId] : [],
                [$like, $like, $like, $like, $like]
            );
        }
        if ($labelId) {
            $scopeSql .= ' AND t.labelid = ?';
            $scopeParams[] = $labelId;
        }
        if ($priority !== '') {
            $scopeSql .= ' AND t.priority = ?';
            $scopeParams[] = $priority;
        }
        if ($search === '' && ($labelId || $priority !== '')
            && !in_array($labelId, Settings_MailManager_Label_Model::getCleanupLabelIds($mailboxId), true)) {
            // what was thrown away keeps its label but shows in Spam / Trash
            // only — except under a cleanup label, whose mail ends there by design
            $scopeSql .= MailManager_ProThread_Model::outsideJunkSql();
        }
        if ($workspace) {
            // Workspace = work mail only: threads labeled with a workspace
            // label, living outside spam/trash — newsletters never show up
            $scopeSql .= self::workspaceScopeSql();
        }

        // Preview subqueries: folder-scoped when browsing, thread-wide when searching
        $previewParams = $folderScoped
            ? [$folderId, $folderId, $folderId, $folderId]
            : [];

        // The Workspace is ONE — the user's work queue over all the mailboxes
        // he works in, the same whichever mailbox is open (workspaceMailboxSql).
        // A row of another mailbox than the open one opens in place — the page
        // sends every action to the row's own mailbox, so a reply leaves from
        // the right address
        $mailboxSql = 't.mailboxid = ?';
        $mailboxParams = [$mailboxId];
        $workspaceMailboxes = [];
        if ($workspace && $userId) {
            [$mailboxSql, $mailboxParams, $workspaceMailboxes] = self::workspaceMailboxSql($userId);
        }

        $result = $adb->pquery(
            'SELECT t.*, l.name AS label_name, l.color AS label_color,
                    (SELECT IF(mx.direction = "out", "→ ", "")
                            FROM df_mailmanager_message mx
                            WHERE mx.threadid = t.threadid' . $previewFolderSql . '
                            ORDER BY ' . $previewOrderSql . ' LIMIT 1) AS last_direction_marker,
                    (SELECT IFNULL(NULLIF(mx.from_name, ""), mx.from_email)
                            FROM df_mailmanager_message mx
                            WHERE mx.threadid = t.threadid' . $previewFolderSql . '
                            ORDER BY ' . $previewOrderSql . ' LIMIT 1) AS last_from,
                    (SELECT mx.snippet
                            FROM df_mailmanager_message mx
                            WHERE mx.threadid = t.threadid' . $previewFolderSql . '
                            ORDER BY ' . $previewOrderSql . ' LIMIT 1) AS folder_snippet,
                    (SELECT ' . $lastAtSql . '
                            FROM df_mailmanager_message mx
                            WHERE mx.threadid = t.threadid' . ($folderScoped ? ' AND mx.folderid = ?' : '') . ') AS folder_last_at
             FROM df_mailmanager_thread t
             LEFT JOIN df_mailmanager_label l ON l.labelid = t.labelid
             WHERE ' . $mailboxSql
               . $scopeSql . $filterSql . $cursorSql . '
             ORDER BY folder_last_at DESC, t.threadid DESC
             LIMIT ' . $offset . ',' . ($pageSize + 1),
            array_merge($previewParams, $mailboxParams, $scopeParams, $filterParams, $cursorParams)
        );

        $threads = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < min($count, $pageSize); $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            // the snippet is stored HTML encoded (the template escapes it once more)
            $row['snippet'] = decode_html((string)$row['folder_snippet']);
            $row['time_display'] = MailManager_ProMailFormat_Model::formatDateTime($row['folder_last_at']);
            // the Workspace spans mailboxes — every row says which one it is
            // from; it opens in place, its actions go to its own mailbox
            $rowMailbox = count($workspaceMailboxes) > 1 ? ($workspaceMailboxes[(int)$row['mailboxid']] ?? null) : null;
            $row['mailbox_chip'] = $rowMailbox
                ? ['mailboxid' => (int)$rowMailbox['mailboxid'], 'name' => $rowMailbox['name'],
                    'type' => $rowMailbox['mailbox_type']]
                : null;
            $threads[] = $row;
        }
        self::attachDrivingRecords($threads);
        MailManager_ProThreadState_Model::attach($threads);
        self::attachLastMessageKinds($threads, $mailboxId);
        // Mixed views (Workspace, search, label / priority filter) show in
        // which folders outside the Inbox a conversation's mail lies — a
        // folder view needs no hint
        if (!$folderScoped) {
            $locations = MailManager_ProFolder_Model::getThreadLocations(
                $mailboxId,
                array_column($threads, 'threadid')
            );
            foreach ($threads as $index => $thread) {
                $threads[$index]['locations'] = $locations[(int)$thread['threadid']] ?? [];
            }
        }

        $hasMore = $count > $pageSize;

        return [
            'threads' => $threads,
            'has_more' => $hasMore,
            // end of a plain folder's cache — older mail may wait on the server
            'can_load_older' => !$hasMore && $folderScoped && $filter === 'all' && $folderId
                && MailManager_ProFolder_Model::canLoadOlder($mailboxId, $folderId),
            // end of search results — the server may hold more
            'can_search_server' => !$hasMore
                && mb_strlen($search) >= MailManager_ProSync_Model::SEARCH_MIN_TERM,
        ];
    }

    /**
     * What the newest message of each thread is — the leading icon of the
     * list row marks the exceptions: a mail the mailbox only got a copy of,
     * or the own reply / forward / new mail; a plain new mail has no icon (a work queue previews the
     * received mail, the icon tells the own answer is newer)
     *
     * @param array $threads thread rows, by reference
     * @param int $mailboxId
     * @return void
     */
    private static function attachLastMessageKinds(array &$threads, $mailboxId)
    {
        if (!$threads) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $threadIds = array_map('intval', array_column($threads, 'threadid'));
        $marks = generateQuestionMarks($threadIds);

        $mailboxResult = $adb->pquery('SELECT email FROM df_mailmanager_mailbox WHERE mailboxid = ?', [$mailboxId]);
        $mailboxEmail = $adb->num_rows($mailboxResult)
            ? strtolower(trim((string)$adb->query_result($mailboxResult, 0, 'email')))
            : '';

        $result = $adb->pquery(
            'SELECT m.threadid, m.direction, m.subject, m.to_addrs, m.cc_addrs, m.header_in_reply_to
             FROM df_mailmanager_message m
             INNER JOIN (SELECT threadid, MAX(sent_at) AS last_at FROM df_mailmanager_message
                         WHERE threadid IN (' . $marks . ') AND is_draft = 0 GROUP BY threadid) x
                     ON x.threadid = m.threadid AND x.last_at = m.sent_at
             WHERE m.is_draft = 0',
            $threadIds
        );
        $kinds = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $message = $adb->raw_query_result_rowdata($result, $i);
            $kinds[(int)$message['threadid']] = self::messageKind($message, $mailboxEmail);
        }

        foreach ($threads as $index => $thread) {
            $kind = $kinds[(int)$thread['threadid']] ?? '';
            $threads[$index]['kind'] = $kind;
            $threads[$index]['kind_icon'] = self::KIND_ICONS[$kind][0] ?? '';
            $threads[$index]['kind_title'] = isset(self::KIND_ICONS[$kind])
                ? vtranslate(self::KIND_ICONS[$kind][1], 'MailManager')
                : '';
        }
    }

    /**
     * @param array $message message row (direction, subject, to_addrs, cc_addrs, header_in_reply_to)
     * @param string $mailboxEmail lowercase address of the mailbox
     * @return string key of KIND_ICONS
     */
    private static function messageKind(array $message, $mailboxEmail)
    {
        $subject = (string)$message['subject'];
        if ($message['direction'] === 'out') {
            if (preg_match('/^\s*(fwd|fw)\s*(\[\d+\])?\s*:/iu', $subject) === 1) {
                return 'forward';
            }

            $isReply = trim((string)$message['header_in_reply_to']) !== ''
                || preg_match('/^\s*re\s*(\[\d+\])?\s*:/iu', $subject) === 1;

            return $isReply ? 'reply' : 'sent';
        }

        $inList = function ($json) use ($mailboxEmail) {
            foreach (json_decode((string)$json, true) ?: [] as $address) {
                if (strtolower(trim((string)($address['email'] ?? ''))) === $mailboxEmail) {
                    return true;
                }
            }

            return false;
        };

        // only a copy — the mail is addressed to somebody else
        return $mailboxEmail !== '' && !$inList($message['to_addrs']) && $inList($message['cc_addrs'])
            ? 'copy'
            : 'new';
    }

    /**
     * @param int $folderId
     * @return bool the folder is a Spam / Trash folder
     */
    private static function isJunkFolder($folderId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folder_type FROM df_mailmanager_folder WHERE folderid = ?',
            [(int)$folderId]
        );

        return $adb->num_rows($result)
            && in_array($adb->query_result($result, 0, 'folder_type'), ['spam', 'trash'], true);
    }

    /**
     * Thread conditions of the virtual Workspace folder — appended to a
     * query aliasing df_mailmanager_thread as t
     *
     * @return string SQL fragment (no parameters)
     */
    private static function workspaceScopeSql()
    {
        // The Workspace works with the Inbox: a conversation whose received
        // mail was filed away (moved to Archive or any other folder) is done
        // and leaves it — the customer's next mail lands in the Inbox and
        // brings it back. A conversation we started and wait on (own mail
        // only, Sent / Drafts) stays
        return " AND t.labelid IN (SELECT wl.labelid FROM df_mailmanager_label wl WHERE wl.workspace = 1)
                 AND (EXISTS (SELECT 1 FROM df_mailmanager_message m
                              INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
                              WHERE m.threadid = t.threadid AND f.folder_type = 'inbox')
                      OR NOT EXISTS (SELECT 1 FROM df_mailmanager_message m
                              INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
                              WHERE m.threadid = t.threadid AND f.folder_type NOT IN ('sent', 'drafts')))";
    }

    /** @var array userid => [the user's id, his groups' ids], see getOwnerIds() */
    private static $ownerIds = [];

    /**
     * Whose records are the user's — his own and those assigned to a group he
     * belongs to (directly, by his role or through a parent group; the core
     * resolves all three). A ticket of the "Support" group is every member's
     *
     * @param int $userId
     * @return int[] the user's id and the ids of his groups
     */
    public static function getOwnerIds(int $userId): array
    {
        if (!isset(self::$ownerIds[$userId])) {
            $userGroups = new GetUserGroups();
            $userGroups->getAllUserGroups($userId);
            self::$ownerIds[$userId] = array_values(array_unique(array_filter(array_map(
                'intval',
                array_merge([$userId], (array)$userGroups->user_groups)
            ))));
        }

        return self::$ownerIds[$userId];
    }

    /**
     * @param int $userId
     * @return string integers for an SQL IN () list (getOwnerIds())
     */
    private static function ownerIdsSql($userId)
    {
        return implode(', ', self::getOwnerIds((int)$userId) ?: [0]);
    }

    /**
     * Which conversations the user's ONE Workspace takes, by mailbox:
     * - a mailbox the user OWNS (his personal one, a shared one he is
     *   responsible for) — all of its work mail, whoever got the record
     *   made from it;
     * - every other mailbox he works in (a shared one, a colleague's he
     *   stands in for) — what is his (a record he owns, a conversation he
     *   has, a conversation he answered in) and what nobody has taken yet,
     *   so that new mail of a team mailbox shows to every member until
     *   somebody answers it
     *
     * @param int $userId
     * @return array [SQL condition on thread alias t, parameters, mailboxes (mailboxid => row)]
     */
    private static function workspaceMailboxSql($userId)
    {
        $mailboxes = [];
        $own = [];
        $team = [];
        foreach (MailManager_ProInbox_Model::getMailboxesForUser($userId) as $mailbox) {
            $id = (int)$mailbox['mailboxid'];
            $mailboxes[$id] = $mailbox;
            if ((int)$mailbox['owner_userid'] === (int)$userId) {
                $own[] = $id;
            } else {
                $team[] = $id;
            }
        }

        $parts = [];
        $params = [];
        if ($own) {
            $parts[] = 't.mailboxid IN (' . generateQuestionMarks($own) . ')';
            $params = $own;
        }
        if ($team) {
            $drivingModules = MailManager_ProRelate_Model::quoteList(
                MailManager_ProRelate_Model::DRIVING_MODULES
            );
            $parts[] = '(t.mailboxid IN (' . generateQuestionMarks($team) . ')
                AND (EXISTS (SELECT 1 FROM df_mailmanager_rel wr
                        INNER JOIN vtiger_crmentity wce ON wce.crmid = wr.crmid AND wce.deleted = 0
                        WHERE wr.threadid = t.threadid AND wce.setype IN (' . $drivingModules . ')
                          AND wce.assigned_user_id IN (' . self::ownerIdsSql($userId) . '))
                  OR (' . MailManager_ProThreadState_Model::noRecordSql('t')
                . ' AND ' . MailManager_ProThreadState_Model::statefulSql('t') . "
                      AND (t.assigned_userid = ?
                           OR (t.assigned_userid IS NULL AND t.assign_status <> '"
                . MailManager_ProThreadState_Model::DONE . "')))
                  OR EXISTS (SELECT 1 FROM df_mailmanager_history wh
                        WHERE wh.mailboxid = t.mailboxid AND wh.threadid = t.threadid
                          AND wh.action = '" . MailManager_ProHistory_Model::ACTION_SEND . "'
                          AND wh.userid = ?)))";
            // (the last line: who answered in a conversation keeps it in sight
            // although the ticket or the task made from it is a colleague's)
            $params = array_merge($params, $team, [(int)$userId, (int)$userId]);
        }

        return [$parts ? '(' . implode(' OR ', $parts) . ')' : '1 = 0', $params, $mailboxes];
    }

    /**
     * The conversations of the user's Workspace digest (I19): the same Workspace as the
     * list, newest activity first, without automatic mail, with what decides their
     * section — the newest non-draft mail, the newest customer and the stored AI line.
     *
     * @param int $userId
     * @param int $limit
     *
     * @return array [rows, mailboxes (mailboxid => row)]
     */
    public static function getWorkspaceDigestRows(int $userId, int $limit): array
    {
        $adb = PearDatabase::getInstance();
        [$mailboxSql, $mailboxParams, $mailboxes] = self::workspaceMailboxSql($userId);
        $newest = 'SELECT %s FROM df_mailmanager_message dm
                   WHERE dm.threadid = t.threadid AND dm.is_draft = 0%s
                   ORDER BY ' . MailManager_ProThreadState_Model::newestMessageOrderSql('dm') . ' LIMIT 1';
        $result = $adb->pquery(
            'SELECT t.threadid, t.mailboxid, t.subject, t.priority, t.unread_count, t.assigned_userid, t.assign_status,
                    t.ai_suspicious, t.ai_digest, t.ai_digest_msgid, t.ai_digest_lang,
                    (' . sprintf($newest, 'dm.direction', '') . ') AS last_direction,
                    (' . sprintf($newest, 'dm.sent_at', '') . ') AS last_at,
                    (' . sprintf($newest, 'dm.messageid', '') . ') AS last_msgid,
                    (' . sprintf($newest, 'dm.from_name', " AND dm.direction = 'in'") . ') AS contact_name,
                    (' . sprintf($newest, 'dm.from_email', " AND dm.direction = 'in'") . ') AS contact_email
             FROM df_mailmanager_thread t
             WHERE ' . $mailboxSql . self::workspaceScopeSql()
            . ' AND ' . MailManager_ProThreadState_Model::notAutomaticMailSql('t') . '
             ORDER BY t.last_message_at DESC
             LIMIT ' . max(1, $limit),
            $mailboxParams
        );
        $rows = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $rows[] = $row;
        }

        return [$rows, $mailboxes];
    }

    /**
     * @param int $userId
     *
     * @return string the list tab the inbox opens with (LIST_FILTERS), 'all' when none is set
     */
    public static function getDefaultListTab(int $userId): string
    {
        $tab = MailManager_ProUserSetting_Model::get($userId, MailManager_ProUserSetting_Model::LIST_TAB, 'all');

        return in_array($tab, self::LIST_FILTERS, true) ? $tab : 'all';
    }

    /**
     * @param int    $userId
     * @param string $tab one of LIST_FILTERS
     *
     * @return void
     */
    public static function saveDefaultListTab(int $userId, string $tab): void
    {
        if (!in_array($tab, self::LIST_FILTERS, true)) {
            $tab = 'all';
        }

        MailManager_ProUserSetting_Model::set($userId, MailManager_ProUserSetting_Model::LIST_TAB, $tab);
    }

    /**
     * Unread conversations in the Workspace — the sidebar badge
     *
     * @param int $mailboxId the open mailbox (the Workspace of a request without a user)
     * @param int $userId whose Workspace — it spans all the mailboxes the user works in
     * @return int
     */
    public static function getWorkspaceUnreadCount($mailboxId, $userId = 0)
    {
        $adb = PearDatabase::getInstance();
        [$mailboxSql, $mailboxParams] = $userId
            ? self::workspaceMailboxSql($userId)
            : ['t.mailboxid = ?', [$mailboxId]];
        $result = $adb->pquery(
            'SELECT COUNT(*) AS unread FROM df_mailmanager_thread t
             WHERE ' . $mailboxSql . ' AND t.unread_count > 0' . self::workspaceScopeSql(),
            $mailboxParams
        );

        return (int)$adb->query_result($result, 0, 'unread');
    }

    /**
     * Received mail of the user's Workspace newer than a message — the browser
     * notifications. Unread Inbox mail sent within the last day only: the sync
     * caches old mail too (a folder scanned for the first time).
     *
     * @param int $userId         whose Workspace
     * @param int $afterMessageId the newest message the browser already knows
     * @param int $limit
     *
     * @return array message rows (messageid, threadid, from_name, from_email, subject), newest first
     */
    public static function getWorkspaceNewMessages(int $userId, int $afterMessageId, int $limit): array
    {
        $adb = PearDatabase::getInstance();
        [$mailboxSql, $mailboxParams] = self::workspaceMailboxSql($userId);
        $result = $adb->pquery(
            "SELECT m.messageid, m.threadid, m.from_name, m.from_email, m.subject
             FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid AND f.folder_type = 'inbox'
             INNER JOIN df_mailmanager_thread t ON t.threadid = m.threadid
             WHERE m.messageid > ? AND m.direction = 'in' AND m.is_read = 0 AND m.is_draft = 0
               AND m.sent_at >= UTC_TIMESTAMP() - INTERVAL 1 DAY
               AND " . $mailboxSql . self::workspaceScopeSql() . '
             ORDER BY m.messageid DESC
             LIMIT ' . $limit,
            array_merge([$afterMessageId], $mailboxParams)
        );
        $messages = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $messages[] = $row;
        }

        return $messages;
    }

    /**
     * Attaches the DRIVING CRM record of each thread — the linked case record
     * (Ticket first, then Opportunity, then Lead) whose process state and
     * owner represent the conversation in the list and the head. The
     * conversation itself carries no manual workflow state
     *
     * @param array $threads thread rows (by reference)
     * @return void
     */
    public static function attachDrivingRecords(array &$threads)
    {
        foreach ($threads as $index => $thread) {
            $threads[$index]['driving'] = null;
        }
        $threadIds = array_column($threads, 'threadid');
        if (!$threadIds) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $hasProjects = MailManager_ProRelate_Model::hasProjects();
        $drivingModules = MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::DRIVING_MODULES);
        $result = $adb->pquery(
            'SELECT r.threadid, r.crmid, ce.setype, ce.assigned_user_id, ce.label,
                    tt.ticketstatus AS ticket_status, po.sales_stage, ld.leadstatus,
                    ' . ($hasProjects ? 'pr.projectstatus' : 'NULL') . ' AS project_status,
                    ' . ($hasProjects ? 'pt.projecttaskstatus' : 'NULL') . ' AS task_status
             FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             LEFT JOIN vtiger_troubletickets tt ON tt.ticketid = r.crmid
             LEFT JOIN vtiger_potential po ON po.potentialid = r.crmid
             LEFT JOIN vtiger_leaddetails ld ON ld.leadid = r.crmid'
            . ($hasProjects ? ' LEFT JOIN vtiger_project pr ON pr.projectid = r.crmid
             LEFT JOIN vtiger_projecttask pt ON pt.projecttaskid = r.crmid' : '') . '
             WHERE r.threadid IN (' . generateQuestionMarks($threadIds) . ')
               AND ce.setype IN (' . $drivingModules . ')
             ORDER BY r.threadid, FIELD(ce.setype, ' . $drivingModules . '), r.relid DESC',
            $threadIds
        );

        $driving = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $threadId = (int)$row['threadid'];
            if (isset($driving[$threadId])) {
                continue;   // the first row per thread wins (module priority order)
            }
            // a record linked by a colleague (or the sync) the user may not open
            // must not show its name, state and owner in a shared mailbox
            if (!Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', (int)$row['crmid'])) {
                continue;
            }

            $status = '';
            $isDone = false;
            if ($row['setype'] === 'HelpDesk') {
                $status = (string)$row['ticket_status'];
                $isDone = $status === 'Closed';
            } elseif ($row['setype'] === 'ProjectTask') {
                $status = (string)$row['task_status'];
                $isDone = in_array($status, MailManager_ProRelate_Model::TASK_DONE, true);
            } elseif ($row['setype'] === 'Project') {
                $status = (string)$row['project_status'];
                $isDone = in_array($status, MailManager_ProRelate_Model::PROJECT_DONE, true);
            } elseif ($row['setype'] === 'Potentials') {
                $status = (string)$row['sales_stage'];
                $isDone = in_array($status, ['Closed Won', 'Closed Lost'], true);
            } else {
                $status = (string)$row['leadstatus'];
            }

            $ownerName = trim((string)getOwnerName((int)$row['assigned_user_id']));
            $driving[$threadId] = [
                'crmid' => (int)$row['crmid'],
                'module' => $row['setype'],
                'module_label' => vtranslate('SINGLE_' . $row['setype'], $row['setype']),
                'icon' => Vtiger_Module_Model::getInstance($row['setype'])->getModuleIcon(),
                'record_label' => decode_html((string)$row['label']),
                'status' => $status,
                'status_display' => $status !== '' ? vtranslate($status, $row['setype']) : '',
                'is_done' => $isDone,
                'owner_id' => (int)$row['assigned_user_id'],
                'owner_name' => $ownerName,
                'owner_initials' => self::buildInitials($ownerName),
            ];
        }

        foreach ($threads as $index => $thread) {
            $threads[$index]['driving'] = $driving[(int)$thread['threadid']] ?? null;
        }
    }

    /**
     * Initials for the assignee avatar in the thread list (max 2 letters)
     *
     * @param string $fullName
     * @return string
     */
    private static function buildInitials($fullName)
    {
        $initials = '';
        foreach (array_slice(preg_split('/\s+/u', trim($fullName)) ?: [], 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }

        return $initials;
    }

    /**
     * Escapes LIKE wildcards in a user search term
     *
     * @param string $value
     * @return string
     */
    private static function escapeLike($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    /**
     * "Logged as a comment" note of the CRM panel – true only while the auto-commented
     * message is still the newest received message of the conversation
     *
     * @param int $threadId
     * @param int $autoCommentMessageId messageid stored in the thread's autocomment_msgid
     * @return bool
     */
    public static function isAutoCommentCurrent($threadId, $autoCommentMessageId)
    {
        if ((int)$autoCommentMessageId <= 0) {
            return false;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT MAX(messageid) AS latest FROM df_mailmanager_message WHERE threadid = ? AND direction = 'in'",
            [(int)$threadId]
        );

        return (int)$adb->query_result($result, 0, 'latest') === (int)$autoCommentMessageId;
    }
}
