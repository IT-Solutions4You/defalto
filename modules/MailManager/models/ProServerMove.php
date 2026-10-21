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
 * Moves / deletes the messages of a conversation on the mail server. A
 * conversation spans folders and the server works folder by folder — when it
 * fails half way, the caller learns which folders are done and lets the
 * cache follow exactly those (the rest is still where it was). moveThread() and
 * deleteThread() run the whole user action: server first, then the history, the
 * cache and what the move teaches (spam rules, trusted senders).
 */
class MailManager_ProServerMove_Model
{
    /** Folder types whose messages a move leaves in place */
    public const KEEP_FOLDER_TYPES = ['sent', 'drafts'];

    /** Seconds a move waits for the load of a folder the sync does not scan */
    private const ADOPT_SECONDS = 30;

    /** Targets of a conversation move */
    public const MOVE_TARGETS = ['spam', 'archive', 'folder', 'restore'];

    /**
     * Moves a conversation to another folder (Spam / Archive / user-picked / back from
     * Spam and Trash): messages are moved on the mail server first, then the cache
     * follows — a scanned target folder re-imports the thread on its next sync.
     *
     * @param array $mailbox    accessible mailbox row
     * @param array $thread     thread row of the mailbox
     * @param array $moveTarget [remote_id, bool target is the Spam folder, int folderid] of getMoveTarget()
     * @param bool  $restore    "Not spam" / "Restore" – only what was thrown away comes back
     * @param bool  $fromInbox  only the new mail in the Inbox joins the rest of the conversation
     *
     * @return array ['success' => bool] plus 'message' on a failure with a reason
     */
    public static function moveThread(array $mailbox, array $thread, array $moveTarget, bool $restore, bool $fromInbox): array
    {
        [$targetRemoteId, $targetIsSpam, $targetFolderId] = $moveTarget;
        $threadId = (int)$thread['threadid'];
        // mail we moved into a folder the sync does not scan (Trash, Spam) has no UID yet
        self::adoptUnsyncedMail($mailbox, $threadId);
        // Own sent replies and drafts stay where the other clients keep them
        // (Sent) — the conversation still shows them, a thread spans folders.
        // A thread of own mail only moves as a whole
        // part of the conversation still waits for the sync (moved a moment ago) — moving now
        // would split it between folders
        if (MailManager_ProThread_Model::hasPendingMessages($threadId, self::KEEP_FOLDER_TYPES)) {
            return [
                'success' => false,
                'message' => vtranslate('LBL_MOVE_PENDING_SYNC', 'MailManager'),
            ];
        }

        $pointers = MailManager_ProThread_Model::getAllMessagePointers($threadId, self::KEEP_FOLDER_TYPES);
        // "Not spam" / "Restore" — only what was thrown away comes back; mail
        // of the conversation filed elsewhere stays where it is
        $fromSpam = false;

        if ($restore) {
            $fromSpam = (bool)MailManager_ProFolder_Model::filterPointersByTypes($pointers, ['spam']);
            $pointers = MailManager_ProFolder_Model::filterPointersByTypes($pointers, ['spam', 'trash']);
        }

        if ($fromInbox) {
            // new mail of a conversation filed elsewhere joins the rest —
            // only what lies in the Inbox moves
            $pointers = MailManager_ProFolder_Model::filterInboxPointers($pointers);
        }

        // what already is in the target stays put
        $inTarget = isset($pointers[$targetFolderId]);
        unset($pointers[$targetFolderId]);

        if (!$pointers && ($inTarget || $fromInbox) && !$restore) {
            return ['success' => true];
        }

        if (!$pointers) {
            // received mail without a UID yet is on its way after a previous
            // move (the sync adopts it) — moving the own Sent mail instead
            // would be wrong
            if ($restore
                || MailManager_ProThread_Model::hasMessagesOutsideFolderTypes($threadId, self::KEEP_FOLDER_TYPES)) {
                return [
                    'success' => false,
                    'message' => vtranslate('LBL_MOVE_PENDING_SYNC', 'MailManager'),
                ];
            }

            $pointers = MailManager_ProThread_Model::getAllMessagePointers($threadId);
        }

        // server and cache change together — not in the middle of a sync of the
        // mailbox (the lock goes with the request)
        MailManager_ProSync_Model::lockMailbox($mailbox);
        $error = '';

        if ($restore) {
            // our own sent mail and drafts go back to Sent, not into the Inbox
            [$pointers, $sentMoved, $error] = self::restoreSentMail($mailbox, $threadId, $pointers);

            if (!$pointers) {
                return '' === $error || $sentMoved
                    ? self::finishRestore($mailbox, $thread, $fromSpam, $error)
                    : ['success' => false, 'message' => $error];
            }
        }

        if ($pointers) {
            // A driver without CAP_MANAGE throws from moveMessages() — the move
            // then fails visibly instead of dropping the thread from the cache only
            [$done, $moveError] = self::run(
                MailManager_ProTransport_Model::getForMailbox($mailbox),
                $pointers,
                $targetRemoteId
            );
            $error = '' !== $moveError ? $moveError : $error;
            // a failure half way — the cache follows the folders that did move
            $pointers = array_intersect_key($pointers, array_flip($done));

            if (!$pointers) {
                return ['success' => false, 'message' => $error];
            }
        }

        MailManager_ProHistory_Model::logThread(
            $threadId,
            MailManager_ProHistory_Model::ACTION_MOVE,
            MailManager_ProHistory_Model::SOURCE_USER,
            MailManager_ProHistory_Model::getFolderPaths(array_keys($pointers)),
            MailManager_ProHistory_Model::getFolderPath($targetFolderId)
        );

        if ($targetIsSpam) {
            // A manual move into Spam is the learning signal for the auto-spam
            // rules — recorded before the cache (with the sender) disappears
            MailManager_ProSpamGuard_Model::learnFromThread($mailbox, $thread);
            MailManager_ProThread_Model::settleSuggestion($threadId);
        }

        if ($restore) {
            // only the received mail the server moved — our own went to Sent above
            MailManager_ProThread_Model::moveMessagesCache($threadId, $targetFolderId, $pointers);

            return self::finishRestore($mailbox, $thread, $fromSpam, $error);
        }

        // the conversation follows its mail — label and CRM links kept, in
        // Spam as well (the label says why it is there)
        MailManager_ProThread_Model::moveThreadCache($threadId, $targetFolderId, array_keys($pointers));

        return '' === $error ? ['success' => true] : ['success' => false, 'message' => $error];
    }

    /**
     * The end of "Not spam" / "Restore": out of Spam = the sender is trusted from now on;
     * the label that sent the conversation away goes in both cases.
     *
     * @param array  $mailbox
     * @param array  $thread
     * @param bool   $fromSpam mail of it came back from Spam
     * @param string $error    error of the server step, '' = all done
     *
     * @return array ['success' => bool] plus 'message' on a failure with a reason
     */
    private static function finishRestore(array $mailbox, array $thread, bool $fromSpam, string $error): array
    {
        if ($fromSpam) {
            MailManager_ProSpamGuard_Model::trustSender($mailbox, $thread);
        }

        MailManager_ProThread_Model::clearCleanupLabel((int)$thread['threadid']);

        return '' === $error ? ['success' => true] : ['success' => false, 'message' => $error];
    }

    /**
     * Restore of our own mail: a delete sent the Sent copies and drafts to the Trash with
     * the rest; they go back to the Sent folder they came from (by the history of the
     * conversation), otherwise to the mailbox's Sent folder.
     *
     * @param array $mailbox
     * @param int   $threadId
     * @param array $pointers folderid => UIDs being restored
     *
     * @return array [pointers left for the Inbox, bool something moved to Sent, error ('' = none)]
     */
    private static function restoreSentMail(array $mailbox, int $threadId, array $pointers): array
    {
        $outgoing = MailManager_ProThread_Model::getAllMessagePointers($threadId, [], true);
        $sentPointers = [];

        foreach ($pointers as $folderId => $uids) {
            $own = array_values(array_intersect($uids, $outgoing[$folderId] ?? []));

            if ($own) {
                $sentPointers[$folderId] = $own;
            }
        }

        $sentFolder = $sentPointers ? self::getRestoreSentFolder((int)$mailbox['mailboxid'], $threadId) : null;

        if (!$sentFolder) {
            return [$pointers, false, ''];
        }

        foreach ($sentPointers as $folderId => $uids) {
            $pointers[$folderId] = array_values(array_diff($pointers[$folderId], $uids));

            if (!$pointers[$folderId]) {
                unset($pointers[$folderId]);
            }
        }

        [$done, $error] = self::run(
            MailManager_ProTransport_Model::getForMailbox($mailbox),
            $sentPointers,
            (string)$sentFolder['remote_id']
        );
        $sentPointers = array_intersect_key($sentPointers, array_flip($done));

        if ($sentPointers) {
            MailManager_ProHistory_Model::logThread(
                $threadId,
                MailManager_ProHistory_Model::ACTION_MOVE,
                MailManager_ProHistory_Model::SOURCE_USER,
                MailManager_ProHistory_Model::getFolderPaths(array_keys($sentPointers)),
                MailManager_ProHistory_Model::getFolderPath((int)$sentFolder['folderid'])
            );
            MailManager_ProThread_Model::moveMessagesCache($threadId, (int)$sentFolder['folderid'], $sentPointers);
        }

        return [$pointers, (bool)$sentPointers, $error];
    }

    /**
     * The Sent folder our own mail of a conversation goes back to: the one its last move
     * or delete took it from, otherwise the mailbox's Sent folder.
     *
     * @param int $mailboxId
     * @param int $threadId
     *
     * @return array|null folderid + remote_id
     */
    private static function getRestoreSentFolder(int $mailboxId, int $threadId): ?array
    {
        $sourcePaths = MailManager_ProHistory_Model::getLastSourcePaths($threadId);

        foreach (MailManager_ProFolder_Model::getFolders($mailboxId) as $folder) {
            if ('sent' === $folder['folder_type']
                && in_array(MailManager_ProHistory_Model::getFolderPath((int)$folder['folderid']), $sourcePaths, true)) {
                return self::getMailboxFolder($mailboxId, (int)$folder['folderid']);
            }
        }

        return self::getFolderByType($mailboxId, 'sent');
    }

    /**
     * Mail we moved into a folder the sync does not scan (Trash, Spam) gets its UID only
     * when that folder is loaded — the same on-demand load as opening the folder. Without
     * the UID the server cannot move it. Best-effort: a failed load leaves the mail
     * waiting and the move reports that.
     *
     * @param array $mailbox
     * @param int   $threadId
     *
     * @return void
     */
    public static function adoptUnsyncedMail(array $mailbox, int $threadId): void
    {
        foreach (MailManager_ProThread_Model::getUnsyncedFolderIds($threadId) as $folderId) {
            try {
                MailManager_ProSync_Model::syncFolderOnDemand($mailbox, $folderId, time() + self::ADOPT_SECONDS);
            } catch (Exception $e) {
                // the mail still waits for its UID — the move says so
            }
        }
    }

    /**
     * The folder a conversation moves to.
     *
     * @param int    $mailboxId
     * @param int    $threadId  the conversation moved (its year picks the archive subfolder)
     * @param string $target    spam | archive | restore | folder
     * @param int    $folderId  the picked folder of the target "folder"
     *
     * @return array [remote_id, bool target is the Spam folder, int folderid]
     * @throws Exception when the target folder does not exist in the mailbox
     */
    public static function getMoveTarget(int $mailboxId, int $threadId, string $target, int $folderId): array
    {
        if ('folder' === $target) {
            $row = self::getMailboxFolder($mailboxId, $folderId);

            if (!$row) {
                throw new Exception(vtranslate('LBL_NO_TARGET_FOLDER', 'MailManager'));
            }

            $isSpam = 'spam' === $row['folder_type'];
        } elseif ('archive' === $target) {
            // the year's subfolder when the mailbox has one (Thunderbird's
            // Archives/2026), the archive folder itself otherwise
            $row = MailManager_ProFolder_Model::findArchiveFolder($mailboxId, $threadId);

            if (!$row) {
                throw new Exception(vtranslate('LBL_NO_ARCHIVE_FOLDER', 'MailManager'));
            }

            $isSpam = false;
        } elseif ('restore' === $target) {
            // back from Spam / Trash — to the Inbox
            $row = self::getFolderByType($mailboxId, 'inbox');

            if (!$row) {
                throw new Exception(vtranslate('LBL_NO_TARGET_FOLDER', 'MailManager'));
            }

            $isSpam = false;
        } else {
            // spam — the folder the spam filter moves to as well
            $row = self::getFolderByType($mailboxId, 'spam');

            if (!$row) {
                throw new Exception(vtranslate('LBL_NO_SPAM_FOLDER', 'MailManager'));
            }

            $isSpam = true;
        }

        return [(string)$row['remote_id'], $isSpam, (int)$row['folderid']];
    }

    /**
     * Deletes a conversation: its mail goes to the Trash of the server (what already
     * lay there, or a mailbox without one, is gone for good) and the cache follows.
     *
     * @param array $mailbox accessible mailbox row
     * @param array $thread  thread row of the mailbox
     *
     * @return array ['success' => bool] plus 'message' on a failure with a reason
     */
    public static function deleteThread(array $mailbox, array $thread): array
    {
        $threadId = (int)$thread['threadid'];
        // mail we moved into a folder the sync does not scan (Trash, Spam) has no UID yet
        self::adoptUnsyncedMail($mailbox, $threadId);
        $pointers = MailManager_ProThread_Model::getAllMessagePointers($threadId);

        // Mail without a UID was just moved (or just sent) and waits for the sync
        // to find it on the server again. Deleting now would only empty the
        // cache — the server copy would come back with the next sync
        if ((!$pointers && MailManager_ProThread_Model::hasMessages($threadId))
            || MailManager_ProThread_Model::hasPendingMessages($threadId)) {
            return [
                'success' => false,
                'message' => vtranslate('LBL_MOVE_PENDING_SYNC', 'MailManager'),
            ];
        }

        // server and cache change together — not in the middle of a sync of the
        // mailbox (the lock goes with the request)
        MailManager_ProSync_Model::lockMailbox($mailbox);

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        $trash = self::getFolderByType((int)$mailbox['mailboxid'], 'trash');
        $trashId = $trash ? (int)$trash['folderid'] : 0;
        $error = '';
        // a driver that cannot manage mail deletes from the cache only
        $onServer = $transport->supports(MailManager_ProBaseTransport_Helper::CAP_MANAGE);

        if ($pointers && $onServer) {
            [$done, $error] = self::run(
                $transport,
                $pointers,
                $trash ? (string)$trash['remote_id'] : '',
                true
            );
            // a failure half way — the cache follows the folders that are gone
            $pointers = array_intersect_key($pointers, array_flip($done));

            if (!$pointers) {
                return ['success' => false, 'message' => $error];
            }
        }

        // the server keeps deleted mail in the Trash — the conversation
        // follows it there with its label and CRM links; what already lay in
        // the Trash (or a mailbox without one, a cache-only delete) is gone
        $gone = $trashId && $onServer ? array_intersect(array_keys($pointers), [$trashId]) : array_keys($pointers);
        $moved = array_diff(array_keys($pointers), $gone);

        MailManager_ProHistory_Model::logThread(
            $threadId,
            MailManager_ProHistory_Model::ACTION_DELETE,
            MailManager_ProHistory_Model::SOURCE_USER,
            MailManager_ProHistory_Model::getFolderPaths(array_keys($pointers)),
            // no target = deleted for good
            $moved ? MailManager_ProHistory_Model::getFolderPath($trashId) : null
        );
        MailManager_ProThread_Model::moveThreadCache($threadId, $trashId, $moved);
        MailManager_ProThread_Model::settleSuggestion($threadId);

        if ('' === $error && !$moved) {
            // nothing of it is left on the server — unsynced copies included
            MailManager_ProThread_Model::deleteThreadCache($threadId);
        } else {
            MailManager_ProThread_Model::deleteMovedMessagesCache($threadId, $gone);
        }

        return '' === $error ? ['success' => true] : ['success' => false, 'message' => $error];
    }
    /**
     * @param MailManager_ProBaseTransport_Helper $transport closed when done
     * @param array $pointers folderid => remote UIDs
     * @param string $targetRemoteId folder the messages go to (the Trash for a delete, '' = none)
     * @param bool $delete delete instead of move
     * @return array [int[] folder ids the server finished, string error message ('' = all done)]
     */
    public static function run($transport, array $pointers, $targetRemoteId, $delete = false)
    {
        $adb = PearDatabase::getInstance();
        $done = [];
        $error = '';

        try {
            foreach ($pointers as $folderId => $uids) {
                $folderResult = $adb->pquery(
                    'SELECT * FROM df_mailmanager_folder WHERE folderid = ?',
                    [$folderId]
                );
                if ($adb->num_rows($folderResult)) {
                    // raw read — query_result() escapes "&" of the modified UTF-7 names
                    $folder = $adb->raw_query_result_rowdata($folderResult, 0);
                    if ($delete) {
                        $transport->deleteMessages($folder, $uids, $targetRemoteId);
                    } else {
                        $transport->moveMessages($folder, $uids, $targetRemoteId);
                    }
                }
                $done[] = (int)$folderId;
            }
        } catch (Exception $e) {
            $error = $e->getMessage() !== '' ? $e->getMessage() : 'mail server error';
        } finally {
            $transport->close();
        }

        return [$done, $error];
    }

    /**
     * The first folder of a type in a mailbox — raw read: query_result()
     * escapes "&", and IMAP folder names carry it for every non-ASCII letter
     * (modified UTF-7: "Ko&AWE-")
     *
     * @param int $mailboxId
     * @param string $folderType inbox | spam | trash | ...
     * @return array|null folderid + remote_id, null when the mailbox has none
     */
    public static function getFolderByType($mailboxId, $folderType)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folderid, remote_id FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folder_type = ? ORDER BY folderid LIMIT 1',
            [(int)$mailboxId, (string)$folderType]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * @param int $mailboxId safety scope
     * @param int $folderId
     * @return array|null folderid + remote_id + folder_type (raw read, see getFolderByType)
     */
    public static function getMailboxFolder($mailboxId, $folderId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT folderid, remote_id, folder_type FROM df_mailmanager_folder WHERE mailboxid = ? AND folderid = ?',
            [(int)$mailboxId, (int)$folderId]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }
}
