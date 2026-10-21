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
 * What a user changes on a conversation by hand — its label, its priority
 * and the read state. One conversation (the conversation head) and a bulk
 * selection (the list bar) go through the same code
 */
class MailManager_ProThreadUpdate_Model
{
    /** Fields one conversation update reads – a missing one stays as it is */
    public const THREAD_FIELDS = ['labelid', 'priority', 'state', 'assignee', 'ai_safe', 'triage_dismiss', 'skip_step'];

    /** Operations of a bulk update */
    public const BULK_OPERATIONS = ['label', 'priority', 'read', 'unread', 'state'];

    /** Conversations one bulk update changes at most */
    private const MAX_BULK_THREADS = 500;

    /**
     * Applies what the user changed on one conversation (the conversation head, the
     * triage banner): the label, the priority, the own state and owner, the AI safety
     * check and the triage suggestion.
     *
     * @param array $mailbox accessible mailbox row
     * @param array $thread  thread row of the mailbox
     * @param array $changes THREAD_FIELDS sent by the user, a missing key = no change
     * @param int   $userId  current user
     *
     * @return bool false when a change was refused (the later ones are not applied)
     */
    public static function updateThread(array $mailbox, array $thread, array $changes, int $userId): bool
    {
        // the same code serves the bulk selection (BulkUpdate action)
        if ((isset($changes['labelid'])
                && !self::setLabel($mailbox, $thread, (int)$changes['labelid']))
            || (isset($changes['priority'])
                && !self::setPriority($thread, (string)$changes['priority']))) {
            return false;
        }

        // the conversation's own state and owner — where no record drives it
        $threadId = (int)$thread['threadid'];

        if ((isset($changes['state']) || isset($changes['assignee']))
            && !MailManager_ProThreadState_Model::hasOwnState($threadId)) {
            return false;
        }

        if (isset($changes['state'])) {
            MailManager_ProThreadState_Model::setDone(
                $threadId,
                MailManager_ProThreadState_Model::DONE === $changes['state'],
                $userId
            );
        }

        if (isset($changes['assignee'])
            && !MailManager_ProThreadState_Model::assign($mailbox, $threadId, (int)$changes['assignee'])) {
            return false;
        }

        // the user checked a conversation flagged as suspicious for the AI (I16)
        if ('1' === ($changes['ai_safe'] ?? null)) {
            MailManager_ProAiSuspicion_Model::markSafe($threadId);
        }

        $skipStep = (string)($changes['skip_step'] ?? '');
        $triageDismiss = '1' === ($changes['triage_dismiss'] ?? null);

        if ($triageDismiss && '' !== $skipStep) {
            // a creation step of the chain — only this step is skipped
            MailManager_ProTriageBanner_Model::skipStep($threadId, $skipStep);
        } elseif ($triageDismiss) {
            self::dismissTriage($threadId);
        }

        return true;
    }

    /**
     * The conversations of a bulk selection that belong to the mailbox.
     *
     * @param array $threadIds selected thread ids
     * @param int   $mailboxId
     *
     * @return array thread rows of the mailbox (foreign ids are dropped)
     */
    public static function getBulkThreads(array $threadIds, int $mailboxId): array
    {
        $threads = [];
        $threadIds = array_unique(array_map('intval', $threadIds));

        // the list ticks what it shows — more than this is no selection made by hand
        foreach (array_slice($threadIds, 0, self::MAX_BULK_THREADS) as $threadId) {
            $thread = MailManager_ProInbox_Model::getThread($threadId, $mailboxId);

            if ($thread) {
                $threads[] = $thread;
            }
        }

        return $threads;
    }

    /**
     * Applies one bulk operation of the list bar to the selected conversations.
     *
     * @param array  $mailbox   accessible mailbox row
     * @param array  $threads   thread rows of getBulkThreads()
     * @param string $operation one of BULK_OPERATIONS
     * @param array  $values    labelid, priority and state sent by the user
     * @param int    $userId    current user
     *
     * @return int conversations updated
     */
    public static function updateThreads(array $mailbox, array $threads, string $operation, array $values, int $userId): int
    {
        $updated = 0;

        if ('read' === $operation) {
            self::markRead($mailbox, array_column($threads, 'threadid'));
            $updated = count($threads);
        } elseif ('unread' === $operation) {
            // a colleague is to notice the mail — unread again, here and on the mail server
            $updated = self::markUnread($mailbox, array_column($threads, 'threadid'));
        } elseif ('state' === $operation) {
            // only the conversations with a state of their own — a ticket's is the ticket's
            $done = MailManager_ProThreadState_Model::DONE === $values['state'];

            foreach ($threads as $thread) {
                if (MailManager_ProThreadState_Model::hasOwnState((int)$thread['threadid'])) {
                    MailManager_ProThreadState_Model::setDone((int)$thread['threadid'], $done, $userId);
                    $updated++;
                }
            }
        } else {
            foreach ($threads as $thread) {
                $done = 'label' === $operation
                    ? self::setLabel($mailbox, $thread, (int)$values['labelid'])
                    : self::setPriority($thread, (string)$values['priority']);
                $updated += $done ? 1 : 0;
            }
        }

        return $updated;
    }

    /**
     * Manual label — overrides the AI classification, teaches the triage
     * (the next such mail gets the label by rule) and re-opens a dismissed
     * suggestion, because the new label may suggest a next step
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param array $thread thread row
     * @param int $labelId 0 = no label
     * @return bool false when the label does not belong to the mailbox
     */
    public static function setLabel(array $mailbox, array $thread, $labelId)
    {
        $labelId = (int)$labelId;
        $threadId = (int)$thread['threadid'];
        // a global label or, in a personal mailbox, the owner's own
        if ($labelId
            && !Settings_MailManager_Label_Model::isAvailableForMailbox($labelId, (int)$mailbox['mailboxid'])) {
            return false;
        }

        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread
             SET labelid = ?, triage_state = IF(triage_state = 'dismissed', 'labeled', triage_state)
             WHERE threadid = ?",
            [$labelId ?: null, $threadId]
        );
        if ($labelId !== (int)$thread['labelid']) {
            MailManager_ProLabelRule_Model::learn($mailbox, $thread, $labelId);
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_LABEL,
                MailManager_ProHistory_Model::SOURCE_USER,
                MailManager_ProHistory_Model::getLabelName((int)$thread['labelid']),
                MailManager_ProHistory_Model::getLabelName($labelId)
            );
        }

        return true;
    }

    /**
     * Manual priority — overrides the AI's assessment; '' clears it (the
     * next triage may assess it again)
     *
     * @param array $thread thread row
     * @param string $priority one of MailManager_ProAiTriage_Model::PRIORITIES or ''
     * @return bool false on an unknown priority
     */
    public static function setPriority(array $thread, $priority)
    {
        $priority = (string)$priority;
        if ($priority !== '' && !in_array($priority, MailManager_ProAiTriage_Model::PRIORITIES, true)) {
            return false;
        }
        if ($priority === (string)$thread['priority']) {
            return true;
        }

        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_thread SET priority = ? WHERE threadid = ?',
            [$priority !== '' ? $priority : null, (int)$thread['threadid']]
        );
        MailManager_ProHistory_Model::logThread(
            (int)$thread['threadid'],
            MailManager_ProHistory_Model::ACTION_PRIORITY,
            MailManager_ProHistory_Model::SOURCE_USER,
            ucfirst((string)$thread['priority']) ?: null,
            ucfirst($priority) ?: null
        );

        return true;
    }

    /**
     * Marks conversations unread again — the newest received message of each
     * (a colleague notices it in the list, bold, and in his mail program).
     * The read state is the mailbox's, shared by everybody working in it
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param int[] $threadIds conversations of the mailbox
     * @return int conversations marked
     */
    public static function markUnread(array $mailbox, array $threadIds)
    {
        $adb = PearDatabase::getInstance();
        $pointers = [];
        $marked = 0;
        foreach ($threadIds as $threadId) {
            $result = $adb->pquery(
                "SELECT messageid, folderid, remote_uid FROM df_mailmanager_message
                 WHERE threadid = ? AND direction = 'in' AND is_draft = 0 ORDER BY sent_at DESC, messageid DESC LIMIT 1",
                [(int)$threadId]
            );
            if (!$adb->num_rows($result)) {
                continue;
            }
            $message = $adb->raw_query_result_rowdata($result, 0);
            $adb->pquery('UPDATE df_mailmanager_message SET is_read = 0 WHERE messageid = ?', [$message['messageid']]);
            $adb->pquery(
                'UPDATE df_mailmanager_thread SET unread_count = (SELECT COUNT(*) FROM df_mailmanager_message
                     WHERE threadid = ? AND is_read = 0 AND is_draft = 0)
                 WHERE threadid = ?',
                [(int)$threadId, (int)$threadId]
            );
            if ($message['remote_uid'] !== null && $message['remote_uid'] !== '') {
                $pointers[(int)$message['folderid']][] = (int)$message['remote_uid'];
            }
            $marked++;
        }

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$pointers || !$transport->supports(MailManager_ProBaseTransport_Helper::CAP_MANAGE)) {
            return $marked;
        }
        try {
            foreach ($pointers as $folderId => $uids) {
                $folderResult = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);
                if ($adb->num_rows($folderResult)) {
                    $transport->markUnseen($adb->raw_query_result_rowdata($folderResult, 0), $uids);
                }
            }
        } catch (Exception $e) {
            // best effort only — the cache is unread already
        } finally {
            $transport->close();
        }

        return $marked;
    }

    /**
     * Marks conversations read — the cache at once, \Seen on the mail
     * server over one connection (best effort: the cache stays marked even
     * when the server is unreachable)
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param int[] $threadIds conversations of the mailbox
     * @return void
     */
    public static function markRead(array $mailbox, array $threadIds)
    {
        $pointers = [];
        foreach ($threadIds as $threadId) {
            foreach (MailManager_ProThread_Model::getUnreadMessagePointers((int)$threadId) as $folderId => $uids) {
                $pointers[$folderId] = array_merge($pointers[$folderId] ?? [], $uids);
            }
            MailManager_ProThread_Model::markThreadRead((int)$threadId);
        }

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$pointers || !$transport->supports(MailManager_ProBaseTransport_Helper::CAP_MANAGE)) {
            return;
        }

        try {
            $adb = PearDatabase::getInstance();
            foreach ($pointers as $folderId => $uids) {
                $folderResult = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);
                if ($adb->num_rows($folderResult)) {
                    $transport->markSeen($adb->raw_query_result_rowdata($folderResult, 0), $uids);
                }
            }
        } catch (Exception $e) {
            // best effort only
        } finally {
            $transport->close();
        }
    }

    /**
     * Dismisses the AI suggestion banner of a conversation
     *
     * @param int $threadId
     * @return void
     */
    public static function dismissTriage($threadId)
    {
        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread SET triage_state = 'dismissed' WHERE threadid = ? AND triage_state IN ('suggested', 'created', 'labeled')",
            [(int)$threadId]
        );
    }
}
