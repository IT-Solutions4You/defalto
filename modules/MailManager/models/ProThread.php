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
 * One conversation — its messages for the timeline, the server message
 * pointers, read state and removal from the cache
 */
class MailManager_ProThread_Model
{
    /** Seconds a moved message may wait for the sync to adopt it before the cache gives it up */
    private const UNADOPTED_GRACE = 86400;

    /**
     * Conversation timeline — messages ascending with attachments and
     * user-formatted timestamps
     *
     * @param int $threadId
     * @return array
     */
    public static function getThreadMessages($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_message WHERE threadid = ? ORDER BY sent_at, messageid',
            [$threadId]
        );

        $messages = [];
        $messageIds = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $row['time_display'] = MailManager_ProMailFormat_Model::formatDateTime($row['sent_at']);
            $row['to_display'] = MailManager_ProMailFormat_Model::formatAddressList($row['to_addrs']);
            $row['cc_display'] = MailManager_ProMailFormat_Model::formatAddressList($row['cc_addrs']);
            // blind copies exist for own mail only — the sender sees whom he added
            $row['bcc_display'] = MailManager_ProMailFormat_Model::formatAddressList($row['bcc_addrs']);
            $row['from_display'] = $row['from_name'] !== '' ? $row['from_name'] : $row['from_email'];
            // Leading icon of the message head: inbound envelope, outbound
            // reply / forward (forward recognized by the subject prefix)
            $row['is_forward'] = $row['direction'] === 'out'
                && preg_match('/^\s*(fwd|fw)\s*(\[\d+\])?\s*:/iu', (string)$row['subject']) === 1;
            $row['attachments'] = [];
            $messages[$row['messageid']] = $row;
            $messageIds[] = $row['messageid'];
        }

        if ($messageIds) {
            $attachmentResult = $adb->pquery(
                'SELECT * FROM df_mailmanager_attachment
                 WHERE messageid IN (' . generateQuestionMarks($messageIds) . ')',
                $messageIds
            );
            $cidMaps = [];
            $attachmentCount = $adb->num_rows($attachmentResult);
            for ($i = 0; $i < $attachmentCount; $i++) {
                $attachment = $adb->raw_query_result_rowdata($attachmentResult, $i);
                if ((string)$attachment['content_id'] !== '') {
                    $cidMaps[$attachment['messageid']][strtolower($attachment['content_id'])] = (int)$attachment['attachmentid'];
                }
                if ((int)$attachment['is_inline'] === 0) {
                    $attachment['size_display'] = MailManager_ProMailFormat_Model::formatSize($attachment['size_bytes']);
                    // the preview: images and PDFs; mail sent from the CRM has its files on
                    // the server only after the Sent folder sync (the message has no remote uid yet)
                    $attachment['preview_type'] = MailManager_ProAttachment_Model::getPreviewType((string)$attachment['mime_type']);
                    $attachment['is_ready'] = '' !== (string)$messages[$attachment['messageid']]['remote_uid'];
                    $messages[$attachment['messageid']]['attachments'][] = $attachment;
                }
            }

            // cid: images point to the on-demand streaming endpoint. The
            // address is absolute — the message frame carries
            // <base href="about:blank"> (a relative address in a mail must
            // not reach the CRM), a relative one would resolve to nothing.
            // A quoted original keeps the cid of ITS images: what the message
            // itself does not carry is looked up in the whole conversation
            $endpoint = rtrim((string)vglobal('site_URL'), '/')
                . '/index.php?module=MailManager&view=ProAttachment&disposition=inline&attachment=';
            $threadCidMap = [];
            foreach ($cidMaps as $cidMap) {
                $threadCidMap += $cidMap;
            }
            foreach ($messages as $messageId => $message) {
                if (stripos((string)$message['body_html'], 'cid:') === false) {
                    continue;
                }
                $cidMap = ($cidMaps[$messageId] ?? []) + $threadCidMap;
                $messages[$messageId]['body_html'] = preg_replace_callback(
                    '/(["\'\(])\s*cid:([^"\'\)\s>]+)/i',
                    function ($match) use ($cidMap, $endpoint) {
                        $attachmentId = $cidMap[strtolower($match[2])] ?? 0;

                        return $attachmentId ? $match[1] . $endpoint . $attachmentId : $match[0];
                    },
                    $message['body_html']
                );
            }
        }

        self::attachTracking($messages);

        return array_values($messages);
    }

    /**
     * Opens and clicks of the mail we sent from the CRM (ITS4YouEmails tracking: the
     * pixel and the rewritten links of a SENT e-mail record) — 'tracking' => null for
     * every other message. One query for the whole conversation.
     *
     * @param array $messages messageid => message row (by reference)
     *
     * @return void
     */
    private static function attachTracking(array &$messages): void
    {
        $emailIds = [];

        foreach ($messages as $messageId => $message) {
            $messages[$messageId]['tracking'] = null;

            if ('out' === $message['direction'] && 'crm' === $message['source'] && (int)$message['crm_emailid']) {
                $emailIds[$messageId] = (int)$message['crm_emailid'];
            }
        }

        if (!$emailIds || !vtlib_isModuleActive('ITS4YouEmails')) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT e.its4you_emails_id, e.access_count, e.click_count, MAX(a.access_time) AS last_access
             FROM its4you_emails e
             LEFT JOIN its4you_emails_access a ON a.mail_id = e.its4you_emails_id
             WHERE e.its4you_emails_id IN (" . generateQuestionMarks($emailIds) . ") AND e.email_flag = 'SENT'
             GROUP BY e.its4you_emails_id, e.access_count, e.click_count",
            array_values($emailIds)
        );
        $stats = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $stats[(int)$row['its4you_emails_id']] = [
                'opens_display' => sprintf(vtranslate('LBL_TRACKING_OPENS', 'MailManager'), (int)$row['access_count']),
                'clicks_display' => sprintf(vtranslate('LBL_TRACKING_CLICKS', 'MailManager'), (int)$row['click_count']),
                // the tracker writes the server time, which is UTC
                'last_display' => MailManager_ProMailFormat_Model::formatDateTime((string)$row['last_access']),
            ];
        }

        foreach ($emailIds as $messageId => $emailId) {
            $messages[$messageId]['tracking'] = $stats[$emailId] ?? null;
        }
    }

    /**
     * Unread messages of a thread grouped for the server flag push
     *
     * @param int $threadId
     * @return array folderid => int[] remote UIDs
     */
    public static function getUnreadMessagePointers($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folderid, remote_uid FROM df_mailmanager_message
             WHERE threadid = ? AND is_read = 0 AND remote_uid IS NOT NULL',
            [$threadId]
        );

        $pointers = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $pointers[(int)$row['folderid']][] = (int)$row['remote_uid'];
        }

        return $pointers;
    }

    /**
     * All server-known messages of a thread grouped by folder (delete etc.)
     *
     * @param int $threadId
     * @param string[] $excludeFolderTypes messages of these folder types stay out (e.g. sent, drafts)
     * @param bool|null $outgoing true = our sent mail and drafts only, false = received mail only, null = all
     * @return array folderid => int[] remote UIDs
     */
    public static function getAllMessagePointers($threadId, array $excludeFolderTypes = [], ?bool $outgoing = null)
    {
        $excludeSql = $excludeFolderTypes
            ? ' AND f.folder_type NOT IN (' . generateQuestionMarks($excludeFolderTypes) . ')'
            : '';

        // our own mail (sent copies and drafts) or the received one only
        if (null !== $outgoing) {
            $excludeSql .= $outgoing
                ? " AND (m.direction = 'out' OR m.is_draft = 1)"
                : " AND m.direction <> 'out' AND m.is_draft = 0";
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT m.folderid, m.remote_uid FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.threadid = ? AND m.remote_uid IS NOT NULL' . $excludeSql,
            array_merge([$threadId], $excludeFolderTypes)
        );

        $pointers = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $pointers[(int)$row['folderid']][] = (int)$row['remote_uid'];
        }

        return $pointers;
    }

    /**
     * Follows a server move in the cache — the conversation keeps its CRM
     * links, label and assignment (a delete + re-import used to lose them)
     *
     * @param int $threadId
     * @param int $targetFolderId df_mailmanager_folder id of the target
     * @param int[] $sourceFolderIds folders whose messages the server moved
     * @return void
     */
    public static function moveThreadCache($threadId, $targetFolderId, array $sourceFolderIds)
    {
        if (!$sourceFolderIds) {
            return;
        }
        // Only rows the server actually moved (they carry a UID); the server
        // assigns new UIDs in the target, the sync adopts the rows by their
        // Message-ID. Unsynced CRM copies (no UID) stay where their server copy is
        // moved_at: the cleanup of Spam / Trash leaves a row alone until the sync
        // had a fair chance to adopt it (deleteUnadoptedCache)
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_message SET folderid = ?, remote_uid = NULL, moved_at = ?
             WHERE threadid = ? AND remote_uid IS NOT NULL
               AND folderid IN (' . generateQuestionMarks($sourceFolderIds) . ')',
            array_merge([$targetFolderId, date('Y-m-d H:i:s'), $threadId], $sourceFolderIds)
        );
    }

    /**
     * Follows a server move of single messages in the cache — like moveThreadCache(), but
     * only the given UIDs, other mail of the same folders stays.
     *
     * @param int   $threadId
     * @param int   $targetFolderId df_mailmanager_folder id of the target
     * @param array $pointers       folderid => int[] remote UIDs the server moved
     *
     * @return void
     */
    public static function moveMessagesCache(int $threadId, int $targetFolderId, array $pointers): void
    {
        $adb = PearDatabase::getInstance();

        foreach ($pointers as $folderId => $uids) {
            if (!$uids) {
                continue;
            }

            $adb->pquery(
                'UPDATE df_mailmanager_message SET folderid = ?, remote_uid = NULL, moved_at = ?
                 WHERE threadid = ? AND folderid = ? AND remote_uid IN (' . generateQuestionMarks($uids) . ')',
                array_merge([$targetFolderId, date('Y-m-d H:i:s'), $threadId, (int)$folderId], array_map('intval', $uids))
            );
        }
    }

    /**
     * Folders the sync does not scan (Trash, Spam, archives) where mail of the thread
     * waits for its UID — mail we moved there gets it only when the folder is loaded.
     *
     * @param int $threadId
     *
     * @return int[] folder ids
     */
    public static function getUnsyncedFolderIds(int $threadId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT DISTINCT m.folderid FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.threadid = ? AND m.remote_uid IS NULL AND f.sync_enabled = 0',
            [$threadId]
        );
        $folderIds = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $folderIds[] = (int)$row['folderid'];
        }

        return $folderIds;
    }

    /**
     * Thread condition "mail of it lies outside Spam / Trash" — the label and
     * priority views and their counts work with live mail only; what was
     * thrown away keeps its label but shows in its folder alone
     *
     * @param string $alias alias of df_mailmanager_thread in the query
     * @return string SQL fragment (no parameters)
     */
    public static function outsideJunkSql($alias = 't')
    {
        return " AND EXISTS (SELECT 1 FROM df_mailmanager_message jm
                    INNER JOIN df_mailmanager_folder jf ON jf.folderid = jm.folderid
                    WHERE jm.threadid = " . $alias . ".threadid AND jf.folder_type NOT IN ('spam', 'trash'))";
    }

    /**
     * @param int $threadId
     * @return bool all mail of the conversation lies in Spam / Trash
     */
    public static function isJunkOnly($threadId)
    {
        return !self::hasMessagesOutsideFolderTypes($threadId, ['spam', 'trash']);
    }

    /**
     * The conversation went where its label suggested (Spam / Trash) — the
     * suggestion is done, the banner must not offer it again there
     *
     * @param int $threadId
     * @return void
     */
    public static function settleSuggestion($threadId)
    {
        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread SET triage_state = 'accepted'
             WHERE threadid = ? AND triage_state = 'suggested'",
            [$threadId]
        );
    }

    /**
     * The conversation came back from Spam / Trash — a label that throws mail
     * away no longer fits it. The label and its "low" priority go; the triage
     * sorts the conversation again when the assistant is on
     *
     * @param int $threadId
     * @return void
     */
    public static function clearCleanupLabel($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT mailboxid, labelid FROM df_mailmanager_thread WHERE threadid = ?',
            [(int)$threadId]
        );
        if (!$adb->num_rows($result)) {
            return;
        }
        $thread = $adb->raw_query_result_rowdata($result, 0);
        // which labels throw mail away is the mailbox's own matter
        $cleanupIds = Settings_MailManager_Label_Model::getCleanupLabelIds((int)$thread['mailboxid']);
        if (in_array((int)$thread['labelid'], $cleanupIds, true)) {
            $adb->pquery(
                "UPDATE df_mailmanager_thread SET labelid = NULL, priority = NULL, triage_state = 'none'
                 WHERE threadid = ?",
                [(int)$threadId]
            );
        }
    }

    /**
     * Stores the AI line of the Workspace digest (I19) — valid for this newest mail and
     * this reader language.
     *
     * @param int    $threadId
     * @param string $line      plain text, at most 300 characters
     * @param int    $messageId the newest message the line describes
     * @param string $language  ISO 639-1 code of the reader
     *
     * @return void
     */
    public static function saveDigestLine(int $threadId, string $line, int $messageId, string $language): void
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_thread SET ai_digest = ?, ai_digest_msgid = ?, ai_digest_lang = ? WHERE threadid = ?',
            [mb_substr($line, 0, 300), $messageId, mb_substr($language, 0, 5), $threadId]
        );
    }

    /**
     * The newest mails of conversations for the digest prompt (I19).
     *
     * @param int[] $threadIds
     * @param int   $perThread mails per conversation
     *
     * @return array threadid => [['direction', 'from_name', 'body_plain', 'body_html'], …] newest first
     */
    public static function getDigestExcerpts(array $threadIds, int $perThread): array
    {
        $threadIds = array_values(array_filter(array_map('intval', $threadIds)));

        if (!$threadIds) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT threadid, direction, from_name, body_plain, body_html FROM df_mailmanager_message
             WHERE is_draft = 0 AND threadid IN (' . generateQuestionMarks($threadIds) . ')
             ORDER BY threadid, ' . MailManager_ProThreadState_Model::newestMessageOrderSql('df_mailmanager_message'),
            $threadIds
        );
        $excerpts = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            $threadId = (int)$row['threadid'];

            if (count($excerpts[$threadId] ?? []) < $perThread) {
                $excerpts[$threadId][] = $row;
            }
        }

        return $excerpts;
    }

    /**
     * Removes a conversation from the cache — messages, attachments, CRM
     * links and the thread row (mail deleted for good)
     *
     * @param int $threadId
     * @return void
     */
    public static function deleteThreadCache($threadId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'DELETE a FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE m.threadid = ?',
            [$threadId]
        );
        $adb->pquery('DELETE FROM df_mailmanager_message WHERE threadid = ?', [$threadId]);
        $adb->pquery('DELETE FROM df_mailmanager_rel WHERE threadid = ?', [$threadId]);
        $adb->pquery('DELETE FROM df_mailmanager_unlink WHERE threadid = ?', [$threadId]);
        $adb->pquery('DELETE FROM df_mailmanager_note WHERE threadid = ?', [$threadId]);
        $adb->pquery('DELETE FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);
    }

    /**
     * Removes the messages of a thread the server deleted for good from the
     * cache; a thread left without messages goes as a whole
     *
     * @param int $threadId
     * @param int[] $folderIds folders whose messages are gone
     * @return void
     */
    public static function deleteMovedMessagesCache($threadId, array $folderIds)
    {
        if (!$folderIds) {
            return;
        }
        $adb = PearDatabase::getInstance();
        $scope = 'm.threadid = ? AND m.remote_uid IS NOT NULL'
            . ' AND m.folderid IN (' . generateQuestionMarks($folderIds) . ')';
        $params = array_merge([$threadId], $folderIds);
        $adb->pquery(
            'DELETE a FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE ' . $scope,
            $params
        );
        $adb->pquery('DELETE m FROM df_mailmanager_message m WHERE ' . $scope, $params);

        $left = $adb->pquery('SELECT 1 FROM df_mailmanager_message WHERE threadid = ? LIMIT 1', [$threadId]);
        if ($adb->num_rows($left)) {
            self::refreshAggregates([$threadId]);
        } else {
            self::deleteThreadCache($threadId);
        }
    }

    /**
     * Mail of the thread that waits for the sync to learn its server UID again (moved or sent a
     * moment ago). A move or delete now would act on part of the conversation only.
     *
     * @param int   $threadId
     * @param array $excludeFolderTypes folder types left out (the move keeps the Sent mail)
     *
     * @return bool
     */
    public static function hasPendingMessages(int $threadId, array $excludeFolderTypes = []): bool
    {
        $excludeSql = $excludeFolderTypes
            ? ' AND f.folder_type NOT IN (' . generateQuestionMarks($excludeFolderTypes) . ')'
            : '';
        $adb = PearDatabase::getInstance();
        // a row without its UID for a day is given up by deleteUnadoptedCache() — it never blocks
        $result = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.threadid = ? AND m.remote_uid IS NULL
               AND COALESCE(m.moved_at, m.sent_at) > UTC_TIMESTAMP() - INTERVAL 1 DAY' . $excludeSql . '
             LIMIT 1',
            array_merge([$threadId], $excludeFolderTypes)
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * @param int $threadId
     * @return bool the cache holds mail of the conversation
     */
    public static function hasMessages($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT 1 FROM df_mailmanager_message WHERE threadid = ? LIMIT 1', [$threadId]);

        return (bool)$adb->num_rows($result);
    }

    /**
     * Drops the rows of a folder that never got their server identity back
     * (moved to Spam / Trash here, purged on the server before the folder
     * was read again); threads left without messages go as a whole
     *
     * @param int $folderId
     * @return void
     */
    public static function deleteUnadoptedCache($folderId)
    {
        // A row moved a moment ago may simply not be on the server's list yet
        // (the move raced this sync, the server was slow) — only what has
        // waited a full day is given up
        $adb = PearDatabase::getInstance();
        $scope = 'folderid = ? AND remote_uid IS NULL AND (moved_at IS NULL OR moved_at < ?)';
        $scopeParams = [$folderId, date('Y-m-d H:i:s', time() - self::UNADOPTED_GRACE)];
        $result = $adb->pquery(
            'SELECT DISTINCT threadid FROM df_mailmanager_message WHERE ' . $scope,
            $scopeParams
        );
        $threadIds = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $threadIds[] = (int)$adb->query_result($result, $i, 'threadid');
        }
        if (!$threadIds) {
            return;
        }

        $adb->pquery(
            'DELETE a FROM df_mailmanager_attachment a
             INNER JOIN df_mailmanager_message m ON m.messageid = a.messageid
             WHERE m.folderid = ? AND m.remote_uid IS NULL AND (m.moved_at IS NULL OR m.moved_at < ?)',
            $scopeParams
        );
        $adb->pquery('DELETE FROM df_mailmanager_message WHERE ' . $scope, $scopeParams);
        foreach ($threadIds as $threadId) {
            $left = $adb->pquery('SELECT 1 FROM df_mailmanager_message WHERE threadid = ? LIMIT 1', [$threadId]);
            if ($adb->num_rows($left)) {
                self::refreshAggregates([$threadId]);
            } else {
                self::deleteThreadCache($threadId);
            }
        }
    }

    /**
     * @param int $threadId
     * @param string[] $folderTypes
     * @return bool the thread has messages in folders of other types (with or without a UID)
     */
    public static function hasMessagesOutsideFolderTypes($threadId, array $folderTypes)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_message m
             INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
             WHERE m.threadid = ? AND f.folder_type NOT IN (' . generateQuestionMarks($folderTypes) . ') LIMIT 1',
            array_merge([$threadId], $folderTypes)
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Recomputes the counters, preview and date of threads from the messages
     * they have now — after a read-state change or after messages left the
     * cache; a thread left without messages is removed unless it carries CRM
     * links (they stay recorded)
     *
     * @param int[] $threadIds
     * @return void
     */
    public static function refreshAggregates(array $threadIds)
    {
        $adb = PearDatabase::getInstance();
        foreach (array_chunk(array_values(array_unique(array_map('intval', $threadIds))), 500) as $chunk) {
            $marks = generateQuestionMarks($chunk);
            $adb->pquery(
                "UPDATE df_mailmanager_thread t SET
                    t.message_count = (SELECT COUNT(*) FROM df_mailmanager_message m
                        WHERE m.threadid = t.threadid),
                    t.unread_count = (SELECT COUNT(*) FROM df_mailmanager_message m
                        WHERE m.threadid = t.threadid AND m.is_read = 0 AND m.direction = 'in' AND m.is_draft = 0),
                    t.has_attachments = (SELECT COUNT(*) > 0 FROM df_mailmanager_message m
                        WHERE m.threadid = t.threadid AND m.has_attachments = 1),
                    t.snippet = IFNULL((SELECT m.snippet FROM df_mailmanager_message m
                        WHERE m.threadid = t.threadid ORDER BY m.sent_at DESC LIMIT 1), t.snippet),
                    t.last_message_at = IFNULL((SELECT MAX(m.sent_at) FROM df_mailmanager_message m
                        WHERE m.threadid = t.threadid), t.last_message_at)
                 WHERE t.threadid IN (" . $marks . ')',
                $chunk
            );
            $empty = $adb->pquery(
                'SELECT t.threadid FROM df_mailmanager_thread t
                 WHERE t.threadid IN (' . $marks . ')
                   AND NOT EXISTS (SELECT 1 FROM df_mailmanager_message m WHERE m.threadid = t.threadid)
                   AND NOT EXISTS (SELECT 1 FROM df_mailmanager_rel r WHERE r.threadid = t.threadid)',
                $chunk
            );
            for ($i = 0, $count = $adb->num_rows($empty); $i < $count; $i++) {
                self::deleteThreadCache((int)$adb->query_result($empty, $i, 'threadid'));
            }
        }
    }

    /**
     * Marks a whole thread as read in the cache
     *
     * @param int $threadId
     * @return void
     */
    public static function markThreadRead($threadId)
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE df_mailmanager_message SET is_read = 1 WHERE threadid = ?', [$threadId]);
        $adb->pquery('UPDATE df_mailmanager_thread SET unread_count = 0 WHERE threadid = ?', [$threadId]);
    }
}
