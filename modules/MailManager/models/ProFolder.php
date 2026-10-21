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
 * Folder tree of a mailbox — hierarchy, display names and counters
 */
class MailManager_ProFolder_Model
{
    /**
     * System folder type => icon. The main folder of each type shows under
     * one translated name (LBL_FOLDER_<TYPE>) whatever the server calls it —
     * "Junk" here, "Spam" there — like the other mail clients do
     */
    private const SYSTEM_ICONS = [
        'inbox' => 'fa-solid fa-inbox',
        'drafts' => 'fa-regular fa-file-lines',
        'sent' => 'fa-regular fa-paper-plane',
        'archive' => 'fa-solid fa-archive',
        'spam' => 'fa-solid fa-ban',
        'trash' => 'fa-regular fa-trash-can',
    ];

    /** Folder types whose unread badge shows although the mailbox does not scan them */
    public const BADGE_FOLDER_TYPES = ['spam', 'trash'];

    /**
     * Folder types synced by default when a mailbox gets its folder list for the first time
     * without a wizard selection (a Basic account taken over by Pro).
     */
    public const DEFAULT_SYNC_TYPES = ['inbox', 'sent'];

    /** Icon of every other folder */
    private const FOLDER_ICON = 'fa-regular fa-folder';

    /** @var array mailboxid => path as the server names it => path as the sidebar names it */
    private static $pathLabels = [];

    /**
     * Fresh per-folder unread counts — the sidebar badges update without a page
     * reload (Spam / Trash included).
     *
     * @param int $mailboxId
     *
     * @return array folderid => unread count of the folders that show one
     */
    public static function getUnreadCounts(int $mailboxId): array
    {
        $unread = [];

        foreach (self::getFolders($mailboxId) as $folder) {
            if ($folder['shows_unread']) {
                $unread[(int)$folder['folderid']] = (int)$folder['unread_count'];
            }
        }

        return $unread;
    }

    /**
     * Returns the folder opened first: the synchronized Inbox, otherwise the first
     * synchronized folder.
     *
     * @param array $folders folder rows of the selected mailbox
     *
     * @return int folder id, 0 when no folder is synchronized
     */
    public static function getDefaultFolderId(array $folders): int
    {
        foreach ($folders as $folder) {
            if ('inbox' === $folder['folder_type'] && 1 === (int)$folder['sync_enabled']) {
                return (int)$folder['folderid'];
            }
        }

        foreach ($folders as $folder) {
            if (1 === (int)$folder['sync_enabled']) {
                return (int)$folder['folderid'];
            }
        }

        return 0;
    }

    /**
     * Sidebar folder list — sorted (INBOX, Drafts, Sent, Archive, Spam, Trash,
     * custom) with depth for indenting; includes unscanned folders (greyed in UI)
     *
     * @param int $mailboxId
     * @return array folder rows + depth/display keys
     */
    public static function getFolders($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE mailboxid = ?', [$mailboxId]);

        $unread = self::getCachedUnread($mailboxId);
        $folders = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $folder = $adb->raw_query_result_rowdata($result, $i);
            // the badge follows the cache at once (read, delete, move) — the
            // server's own count comes only with a sync (storeUnreadBase)
            $folder['unread_count'] = max(0, (int)$folder['unread_base'] + ($unread[(int)$folder['folderid']] ?? 0));
            // Spam and Trash keep their badge even unscanned — mail gets
            // there by itself (spam filter, label cleanup) and must be noticed
            $folder['shows_unread'] = !empty($folder['sync_enabled'])
                || in_array($folder['folder_type'], self::BADGE_FOLDER_TYPES, true);
            $folders[] = $folder;
        }

        $delimiter = self::getDelimiter($mailboxId, array_column($folders, 'name'));

        $rankMap = ['inbox' => 0, 'drafts' => 1, 'sent' => 2, 'archive' => 3, 'spam' => 4, 'trash' => 5];
        $typeByName = [];
        foreach ($folders as $folder) {
            $typeByName[mb_strtolower($folder['name'])] = $folder['folder_type'];
        }
        foreach ($folders as $index => $folder) {
            $segments = $delimiter !== '' ? explode($delimiter, $folder['name']) : [$folder['name']];
            $rootType = $typeByName[mb_strtolower($segments[0])] ?? $folder['folder_type'];
            $folders[$index]['depth'] = count($segments) - 1;
            $folders[$index]['display'] = end($segments);
            $folders[$index]['rank'] = $rankMap[$rootType] ?? 6;
            $folders[$index]['sort_path'] = mb_strtolower($folder['name']);
        }
        usort($folders, function ($a, $b) {
            return [$a['rank'], $a['sort_path']] <=> [$b['rank'], $b['sort_path']];
        });
        $folders = self::nameSystemFolders($folders);

        // After path sorting a direct child immediately follows its parent —
        // the sidebar renders children collapsed with a caret toggle
        $total = count($folders);
        foreach ($folders as $index => $folder) {
            $prefix = $folder['sort_path'] . $delimiter;
            $folders[$index]['delimiter'] = $delimiter;
            $folders[$index]['has_children'] = $delimiter !== ''
                && $index + 1 < $total
                && strpos($folders[$index + 1]['sort_path'], $prefix) === 0;
        }

        return $folders;
    }

    /**
     * Gives every folder its icon and the main folder of each system type
     * (the top-level one; of two the plainer-named, then the older) its
     * unified translated name; a second folder of the type keeps the
     * server's name
     *
     * @param array $folders folder rows with depth / display
     * @return array
     */
    private static function nameSystemFolders(array $folders)
    {
        $main = [];
        foreach ($folders as $index => $folder) {
            $type = $folder['folder_type'];
            if (!isset(self::SYSTEM_ICONS[$type]) || (int)$folder['depth'] !== 0) {
                continue;
            }
            // of two ("Sent" and "Sent Messages") the plainer name is the main one
            $rank = [mb_strlen($folder['name']), (int)$folder['folderid']];
            if (!isset($main[$type]) || $rank < $main[$type]['rank']) {
                $main[$type] = ['index' => $index, 'rank' => $rank];
            }
        }

        foreach ($folders as $index => $folder) {
            $folders[$index]['icon'] = self::FOLDER_ICON;
        }
        foreach ($main as $type => $found) {
            $folders[$found['index']]['icon'] = self::SYSTEM_ICONS[$type];
            $folders[$found['index']]['display'] = vtranslate('LBL_FOLDER_' . strtoupper($type), 'MailManager');
        }

        return $folders;
    }

    /**
     * @param int $mailboxId
     * @return array folderid => unread messages the cache holds in the folder
     */
    private static function getCachedUnread($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folderid, COUNT(*) AS unread FROM df_mailmanager_message
             WHERE mailboxid = ? AND is_read = 0 GROUP BY folderid',
            [$mailboxId]
        );
        $unread = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $unread[(int)$adb->query_result($result, $i, 'folderid')] = (int)$adb->query_result($result, $i, 'unread');
        }

        return $unread;
    }

    /**
     * Called when a sync stored the server's unread count of a folder:
     * remembers how much of it the cache does NOT hold (older than the sync
     * window). From then on the badge = this base + the unread mail cached
     * in the folder, so reading, deleting or moving shows at once
     *
     * @param int $folderId
     * @return void
     */
    public static function storeUnreadBase($folderId)
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_folder f SET f.unread_base = f.unread_count
                - (SELECT COUNT(*) FROM df_mailmanager_message m WHERE m.folderid = f.folderid AND m.is_read = 0)
             WHERE f.folderid = ?',
            [$folderId]
        );
    }

    /**
     * A folder path the way the sidebar names it — the system folders under
     * their unified names ("Junk" → "Spam", "Archives / 2026" → "Archive /
     * 2026"), so the chips and the activity history speak the same language
     *
     * @param int $mailboxId
     * @param string $path segments joined by " / ", named as the server names them
     * @return string
     */
    public static function unifyPath($mailboxId, $path)
    {
        $mailboxId = (int)$mailboxId;
        if (!isset(self::$pathLabels[$mailboxId])) {
            $folders = self::getFolders($mailboxId);
            $roots = [];
            foreach ($folders as $folder) {
                if ((int)$folder['depth'] === 0) {
                    $roots[$folder['name']] = $folder['display'];
                }
            }
            self::$pathLabels[$mailboxId] = [];
            foreach ($folders as $folder) {
                $segments = $folder['delimiter'] !== ''
                    ? explode($folder['delimiter'], $folder['name'])
                    : [$folder['name']];
                $raw = implode(' / ', $segments);
                $segments[0] = $roots[$segments[0]] ?? $segments[0];
                self::$pathLabels[$mailboxId][$raw] = implode(' / ', $segments);
            }
        }

        return self::$pathLabels[$mailboxId][$path] ?? $path;
    }

    /**
     * Where the received mail of conversations lies OUTSIDE the Inbox — the
     * Inbox is where mail is expected, only the other folders say something
     * (own mail in Sent / Drafts does not count; a move leaves it there).
     * Spam and Trash are named only for a conversation that lies there as a
     * whole: older mail thrown away must not mark a conversation with new,
     * live mail as "Trash". Newest mail first
     *
     * @param int $mailboxId
     * @param int[] $threadIds
     * @return array threadid => list of ['folderid', 'folder_type',
     *                           'display' (last segment), 'path' (segments joined by " / ")]
     */
    public static function getThreadLocations($mailboxId, array $threadIds)
    {
        if (!$threadIds) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT m.threadid, m.folderid, f.name, f.folder_type, MAX(m.sent_at) AS last_at
             FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.mailboxid = ? AND f.folder_type NOT IN ('inbox', 'sent', 'drafts')
               AND m.threadid IN (" . generateQuestionMarks($threadIds) . ")
               AND (f.folder_type NOT IN ('spam', 'trash')
                    OR NOT EXISTS (SELECT 1 FROM df_mailmanager_message lm
                            INNER JOIN df_mailmanager_folder lf ON lf.folderid = lm.folderid
                            WHERE lm.threadid = m.threadid AND lf.folder_type NOT IN ('spam', 'trash')))
             GROUP BY m.threadid, m.folderid, f.name, f.folder_type
             ORDER BY last_at DESC",
            array_merge([$mailboxId], array_map('intval', $threadIds))
        );

        $rows = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $rows[] = $adb->raw_query_result_rowdata($result, $i);
        }
        $delimiter = self::getDelimiter($mailboxId, array_column($rows, 'name'));

        $locations = [];
        foreach ($rows as $row) {
            $segments = $delimiter !== '' ? explode($delimiter, $row['name']) : [$row['name']];
            // named as in the sidebar ("Spam", not the server's "Junk")
            $path = self::unifyPath($mailboxId, implode(' / ', $segments));
            $unified = explode(' / ', $path);
            $locations[(int)$row['threadid']][] = [
                'folderid' => (int)$row['folderid'],
                'folder_type' => $row['folder_type'],
                'display' => end($unified),
                'path' => $path,
            ];
        }

        return $locations;
    }

    /**
     * Where Archive puts a conversation: the archive folder of the mailbox,
     * or — when it has a subfolder named by the year of the conversation's
     * newest received mail (Thunderbird keeps Archives/2026) — that one.
     * No folder is created; without the year's subfolder the archive folder
     * itself is the target
     *
     * @param int $mailboxId
     * @param int $threadId
     * @return array|null folder row (folderid, remote_id, name), null = the mailbox has no archive folder
     */
    public static function findArchiveFolder($mailboxId, $threadId)
    {
        $adb = PearDatabase::getInstance();
        // the root — the shortest name among the archive folders
        $rootResult = $adb->pquery(
            "SELECT folderid, remote_id, name FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folder_type = 'archive' ORDER BY CHAR_LENGTH(name), folderid LIMIT 1",
            [$mailboxId]
        );
        if (!$adb->num_rows($rootResult)) {
            return null;
        }
        // raw read — query_result() escapes "&" of the modified UTF-7 names
        $root = $adb->raw_query_result_rowdata($rootResult, 0);

        $yearResult = $adb->pquery(
            "SELECT YEAR(MAX(sent_at)) AS mail_year FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in'",
            [$threadId]
        );
        $year = (int)$adb->query_result($yearResult, 0, 'mail_year');
        if (!$year) {
            // own mail only — the year of its newest message
            $yearResult = $adb->pquery(
                'SELECT YEAR(MAX(sent_at)) AS mail_year FROM df_mailmanager_message WHERE threadid = ?',
                [$threadId]
            );
            $year = (int)$adb->query_result($yearResult, 0, 'mail_year');
        }
        if (!$year) {
            return $root;
        }

        // "<root><delimiter><year>" — the delimiter is one character of the server's choice
        $subResult = $adb->pquery(
            'SELECT folderid, remote_id, name FROM df_mailmanager_folder
             WHERE mailboxid = ? AND CHAR_LENGTH(name) = ? AND name LIKE ? AND RIGHT(name, 4) = ?
             ORDER BY folderid LIMIT 1',
            [$mailboxId, mb_strlen($root['name']) + 5, addcslashes($root['name'], '%_\\') . '_' . $year, (string)$year]
        );

        return $adb->num_rows($subResult) ? $adb->raw_query_result_rowdata($subResult, 0) : $root;
    }

    /**
     * @param array $pointers folderid => remote UIDs
     * @return array the pointers of the Inbox folder(s) only
     */
    public static function filterInboxPointers(array $pointers)
    {
        return self::filterPointersByTypes($pointers, ['inbox']);
    }

    /**
     * @param array $pointers folderid => message pointers
     * @param string[] $folderTypes
     * @return array the pointers lying in folders of these types
     */
    public static function filterPointersByTypes(array $pointers, array $folderTypes)
    {
        if (!$pointers || !$folderTypes) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $folderIds = array_map('intval', array_keys($pointers));
        $result = $adb->pquery(
            'SELECT folderid FROM df_mailmanager_folder
             WHERE folder_type IN (' . generateQuestionMarks($folderTypes) . ')
               AND folderid IN (' . generateQuestionMarks($folderIds) . ')',
            array_merge($folderTypes, $folderIds)
        );
        $filtered = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $folderId = (int)$adb->query_result($result, $i, 'folderid');
            $filtered[$folderId] = $pointers[$folderId];
        }

        return $filtered;
    }

    /**
     * @param int $folderId
     * @return string folder type (inbox, sent, spam, trash, custom…), '' for an unknown folder
     */
    public static function getFolderType($folderId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folder_type FROM df_mailmanager_folder WHERE folderid = ?',
            [(int)$folderId]
        );

        return $adb->num_rows($result) ? (string)$adb->query_result($result, 0, 'folder_type') : '';
    }

    /**
     * @param int $threadId
     * @return bool received mail of the conversation lies in the Inbox
     */
    public static function hasInboxMail($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.threadid = ? AND f.folder_type = 'inbox' LIMIT 1",
            [$threadId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Can the folder load older mail from the server? Only a folder read at
     * least once (it has a cursor) whose history the server has not run out of
     *
     * @param int $mailboxId
     * @param int $folderId
     * @return bool
     */
    public static function canLoadOlder($mailboxId, $folderId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folderid = ? AND history_complete = 0
               AND delta_cursor IS NOT NULL AND delta_cursor != ''",
            [$mailboxId, $folderId]
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Hierarchy delimiter of the mailbox's folder names — the one the server
     * announced when the names use it, otherwise read from the names
     *
     * @param int $mailboxId
     * @param array $names folder names
     * @return string '' = flat list
     */
    public static function getDelimiter($mailboxId, array $names)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folder_delimiter FROM df_mailmanager_mailbox WHERE mailboxid = ?',
            [$mailboxId]
        );
        $stored = $adb->num_rows($result) ? (string)$adb->raw_query_result_rowdata($result, 0)['folder_delimiter'] : '';
        $allNames = implode("\n", $names);
        // The announced delimiter counts only when the names really use it —
        // names stored before the server changed its delimiter keep their
        // tree until reconcile() renames them
        if ($stored !== '' && strpos($allNames, $stored) !== false) {
            return $stored;
        }

        return strpos($allNames, '/') !== false ? '/' : (strpos($allNames, '.') !== false ? '.' : '');
    }

    /**
     * Brings the stored folder list in line with the server — the list is
     * taken once when the mailbox is created. A folder renamed by a changed
     * hierarchy delimiter ("INBOX.Offers" → "INBOX/Offers") keeps its row,
     * cache and sync choice; a new folder is added unsynced; a folder gone
     * from the server is dropped, or only unsynced while it holds cached mail
     *
     * @param array $mailbox mailbox row
     * @param MailManager_ProBaseTransport_Helper $transport open transport
     * @return array log lines
     */
    public static function reconcile(array $mailbox, $transport)
    {
        $serverFolders = [];
        foreach ($transport->listFolders() as $folder) {
            $serverFolders[(string)$folder['remote_id']] = $folder;
        }
        // a reply without INBOX is a misread one — nothing is changed on it
        if (!array_filter(array_keys($serverFolders), function ($remoteId) {
            return strcasecmp($remoteId, 'INBOX') === 0;
        })) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $mailboxId = (int)$mailbox['mailboxid'];
        $result = $adb->pquery(
            'SELECT folderid, remote_id, sync_enabled FROM df_mailmanager_folder WHERE mailboxid = ?',
            [$mailboxId]
        );
        $stored = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $stored[(string)$row['remote_id']] = $row;
        }

        // same path under another delimiter — unique matches only
        $byPath = [];
        foreach (array_diff_key($serverFolders, $stored) as $remoteId => $folder) {
            $byPath[self::pathKey($remoteId)][] = $remoteId;
        }

        $log = [];
        $vanished = [];
        foreach (array_diff_key($stored, $serverFolders) as $remoteId => $row) {
            $candidates = $byPath[self::pathKey($remoteId)] ?? [];
            if (count($candidates) === 1) {
                $newId = $candidates[0];
                $adb->pquery(
                    'UPDATE df_mailmanager_folder SET remote_id = ?, name = ? WHERE folderid = ?',
                    [$newId, $serverFolders[$newId]['name'], $row['folderid']]
                );
                $stored[$newId] = $row;
                unset($byPath[self::pathKey($remoteId)]);
                $log[] = "[{$mailbox['email']}] folder {$remoteId} is {$newId} on the server now";
                continue;
            }
            $vanished[$remoteId] = $row;
        }
        // a folder is dropped only when a second LIST confirms it — one
        // incomplete reply must not cost the user's folder choices
        if ($vanished) {
            foreach ($transport->listFolders() as $folder) {
                unset($vanished[(string)$folder['remote_id']]);
            }
        }
        foreach ($vanished as $remoteId => $row) {
            $cached = $adb->pquery(
                'SELECT 1 FROM df_mailmanager_message WHERE folderid = ? LIMIT 1',
                [$row['folderid']]
            );
            if ($adb->num_rows($cached)) {
                $adb->pquery(
                    'UPDATE df_mailmanager_folder SET sync_enabled = 0 WHERE folderid = ?',
                    [$row['folderid']]
                );
            } else {
                $adb->pquery('DELETE FROM df_mailmanager_folder WHERE folderid = ?', [$row['folderid']]);
            }
            $log[] = "[{$mailbox['email']}] folder {$remoteId} no longer on the server";
        }

        // the very first folder list of a mailbox without a wizard selection (a taken-over
        // Basic account) starts with the default folders synced
        $isFirstList = !$stored;

        foreach (array_diff_key($serverFolders, $stored) as $remoteId => $folder) {
            $folderType = $folder['folder_type']
                ?? MailManager_ProImapClient_Helper::folderType((string)($folder['flags'] ?? ''), $folder['name']);
            $adb->pquery(
                'INSERT INTO df_mailmanager_folder (mailboxid, remote_id, name, folder_type, sync_enabled)
                 VALUES (?,?,?,?,?)',
                [
                    $mailboxId,
                    $remoteId,
                    $folder['name'],
                    $folderType,
                    $isFirstList && in_array($folderType, self::DEFAULT_SYNC_TYPES, true) ? 1 : 0,
                ]
            );
            $log[] = "[{$mailbox['email']}] new folder {$remoteId}";
        }

        $delimiter = $transport->getFolderDelimiter();
        if ($delimiter !== '' && $delimiter !== (string)$mailbox['folder_delimiter']) {
            $adb->pquery(
                'UPDATE df_mailmanager_mailbox SET folder_delimiter = ? WHERE mailboxid = ?',
                [$delimiter, $mailboxId]
            );
        }

        return $log;
    }

    /**
     * @param string $remoteId
     * @return string the folder path independent of the hierarchy delimiter
     */
    private static function pathKey($remoteId)
    {
        return strtolower(strtr($remoteId, ['.' => "\x1f", '/' => "\x1f"]));
    }
}
