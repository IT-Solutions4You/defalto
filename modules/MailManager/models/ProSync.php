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
 * Sync engine — pulls changes from the scanned folders of active mailboxes
 * through the transport layer, threads the messages and updates the cache.
 * Runs from the cron service; the inbox UI triggers it on demand as well.
 */
class MailManager_ProSync_Model
{
    /** Name of the Pro background sync task (MailManager_Install_Model::\$registerProCron) */
    public const CRON_NAME = 'MailManagerProSync';

    /**
     * Seconds of one scheduler run (MailManagerProSync, every 900 s): the mailboxes, the
     * triage, the digest lines, the topics and the knowledge share it — 120 was spent by an
     * initial sync of new mailboxes and the triage that followed it
     */
    public const RUN_BUDGET = 300;

    /**
     * Seconds of the run kept for the lines of the Workspace digest: the mailboxes and the
     * triage stop before them, so a busy run still writes the lines users wait for
     */
    private const DIGEST_RESERVE = 60;

    /** Seconds a user move waits for a running sync of the mailbox */
    private const MOVE_LOCK_WAIT = 10;

    /**
     * A scheduler run still marked running after this long has died — a run
     * itself is capped at a few minutes
     */
    private const STALE_CRON_SECONDS = 1800;

    /** Messages compared in one query of the read-state sync */
    private const SEEN_CHUNK = 500;

    /** Older messages fetched by one "load older" click */
    private const HISTORY_BATCH = 50;

    /** Shortest search term worth a full-text scan on the mail server */
    public const SEARCH_MIN_TERM = 3;

    /** Server search: new matches fetched by one click, at most per folder */
    private const SEARCH_BATCH = 100;
    private const SEARCH_FOLDER_BATCH = 30;

    /**
     * Syncs all active mailboxes within a time budget. Every mailbox and
     * every stage catches Throwable: a PHP Error escaping to vtigercron
     * (which catches Exception only) leaves the scheduler task RUNNING for
     * good and the sync never runs again
     *
     * The mailboxes and the triage end DIGEST_RESERVE seconds before the run (at most a
     * quarter of it) — the digest lines get that time even when an initial sync or a triage
     * backlog would take the whole run; the topics and the knowledge take what is left (they
     * catch up on quiet runs, nobody waits for them)
     *
     * @param int $timeBudget seconds for the whole run
     * @return array log lines
     */
    public static function run($timeBudget = self::RUN_BUDGET)
    {
        $deadline = time() + $timeBudget;
        $mailDeadline = $deadline - min(self::DIGEST_RESERVE, intdiv((int)$timeBudget, 4));
        $log = [];

        foreach ((new MailManager_ProMailbox_Model())->getSyncMailboxes() as $mailbox) {
            if (time() >= $mailDeadline) {
                $log[] = 'Time budget reached, remaining mailboxes postponed to the next run';
                break;
            }

            self::markCronStage('mailbox ' . $mailbox['email']);

            try {
                $log = array_merge($log, self::syncMailbox($mailbox, $mailDeadline));
            } catch (Throwable $e) {
                $log[] = "[{$mailbox['email']}] ERROR: " . $e->getMessage();
                (new MailManager_ProMailbox_Model())->saveSyncFailure($mailbox, $e->getMessage());
            }
        }

        // AI triage of freshly imported inbound conversations (no-op while the assistant or
        // the feature is disabled); topic tags of closed tickets and threads; the knowledge
        // store follows its files, FAQ and closed tickets
        $stages = [
            'AI triage' => function () use ($mailDeadline) {
                return MailManager_ProAiTriage_Model::run($mailDeadline);
            },
            // the lines of the Workspace digest (I19) — opening the digest never waits for them
            'AI digest' => function () use ($deadline) {
                return MailManager_ProWorkspaceDigestAi_Model::prepareLines($deadline);
            },
            'AI topics (tickets)' => function () use ($deadline) {
                return MailManager_ProAiTopics_Model::tagClosedTickets($deadline);
            },
            'AI topics (threads)' => function () use ($deadline) {
                return MailManager_ProAiTopics_Model::tagThreads($deadline);
            },
            'AI knowledge' => function () use ($deadline) {
                return MailManager_ProKnowledge_Model::refresh($deadline);
            },
        ];

        foreach ($stages as $stage => $callback) {
            self::markCronStage($stage);

            try {
                $stageLog = (array)$callback();
                $log = array_merge($log, $stageLog);

                // what went wrong in a stage is kept in the trace — the returned log goes nowhere in a cron run
                self::traceProblems($stage, $stageLog);
            } catch (Throwable $e) {
                $log[] = $stage . ' ERROR: ' . $e->getMessage();
                self::traceCron($stage . ' ERROR: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }
        }

        self::traceCron('stages done');

        return $log;
    }

    /** Config keys of the scheduler run's trace — read when a run did not end */
    public const CONFIG_CRON_STAGE = 'cron_stage';
    public const CONFIG_CRON_ERROR = 'cron_last_error';
    public const CONFIG_CRON_OK = 'cron_last_ok';

    /**
     * Notes what the scheduler run is doing — a process that dies (a fatal
     * error, a kill) cannot say where; the note left behind does
     *
     * @param string $stage
     * @return void
     */
    private static function markCronStage($stage)
    {
        self::traceCron($stage);
        (new MailManager_ProConfig_Model())->saveValue(self::CONFIG_CRON_STAGE, date('Y-m-d H:i:s') . ' ' . $stage);
    }

    /** The scheduler run's trace file — small, restarted when it grows */
    private const CRON_TRACE_FILE = 'logs/mailmanager_pro_cron.log';
    private const CRON_TRACE_MAX_BYTES = 200000;

    /** Log lines of the AI stages worth the trace: errors, refusals, failed or cut calls */
    private const TRACE_LOG_PATTERN = '/error|fail|refus|unparseable|unavailable|empty answer|time budget|stopped/i';

    /**
     * The log lines that report a problem (TRACE_LOG_PATTERN) into the trace — the AI stages of
     * the cron, the triage of a Refresh and of the sorting queue
     *
     * @param string $source what wrote them (stage name)
     * @param array  $lines  log lines
     *
     * @return void
     */
    public static function traceProblems(string $source, array $lines): void
    {
        foreach ($lines as $line) {
            if (preg_match(self::TRACE_LOG_PATTERN, (string)$line)) {
                self::traceCron($source . ': ' . mb_substr((string)$line, 0, 500));
            }
        }
    }

    /**
     * One line into the trace file — independent of the database, so it also
     * tells a run that died from one whose database writes did not last
     *
     * @param string $line
     * @return void
     */
    public static function traceCron($line)
    {
        if (@filesize(self::CRON_TRACE_FILE) > self::CRON_TRACE_MAX_BYTES) {
            @unlink(self::CRON_TRACE_FILE);
        }
        @file_put_contents(
            self::CRON_TRACE_FILE,
            date('Y-m-d H:i:s') . ' [' . getmypid() . '] ' . round(memory_get_usage() / 1048576) . 'MB '
            . $line . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * The scheduler run ended — cleanly, or with what stopped it. A run that
     * did not end cleanly frees its scheduler task too: vtigercron marks a
     * task finished only when its script returns, and catches Exception
     * only — after a fatal error the task would show "running" for good
     *
     * @param string $error '' = a clean end
     * @return void
     */
    public static function finishCronRun($error = '')
    {
        self::traceCron($error === '' ? 'finished' : 'FAILED: ' . $error);
        try {
            if ($error === '') {
                (new MailManager_ProConfig_Model())->saveValue(self::CONFIG_CRON_OK, date('Y-m-d H:i:s'));

                return;
            }
            (new MailManager_ProConfig_Model())->saveValue(
                self::CONFIG_CRON_ERROR,
                date('Y-m-d H:i:s') . ' [' . (new MailManager_ProConfig_Model())->getValue(self::CONFIG_CRON_STAGE) . '] '
                . mb_substr($error, 0, 1000)
            );
            $task = Vtiger_Cron::getInstance(self::CRON_NAME);
            if ($task && $task->isRunning()) {
                $task->markFinished();
            }
        } catch (Throwable $e) {
            // the trace is an assist — it must not raise an error of its own
        }
    }

    /**
     * Frees the scheduler task left RUNNING by a run that died (killed
     * process, fatal error) — vtigercron skips a running task forever, so
     * without this the background sync would never run again. Called from
     * the inbox, which polls while it is open
     *
     * @return bool true when a stale task was released
     */
    public static function releaseStaleCronTask()
    {
        $task = Vtiger_Cron::getInstance(self::CRON_NAME);
        if (!$task || !$task->isRunning() || !$task->getLastStart()
            || time() - $task->getLastStart() < self::STALE_CRON_SECONDS) {
            return false;
        }
        $task->markFinished();

        return true;
    }

    /**
     * On-demand sync of one mailbox (inbox open, the Refresh button) or of one folder,
     * with a short triage pass and everything the inbox refreshes after it.
     *
     * @param array $mailbox  accessible mailbox row
     * @param int   $userId   current user
     * @param int   $folderId one folder loaded on demand, 0 = the mailbox
     * @param bool  $triage   run the short triage pass (a manual Refresh leaves it to the queue)
     * @param bool  $digest   the Workspace digest is open – its fingerprint is returned
     *
     * @return array the response of the inbox
     * @throws Exception when the sync fails
     */
    public static function syncNow(array $mailbox, int $userId, int $folderId, bool $triage, bool $digest): array
    {
        $mailboxId = (int)$mailbox['mailboxid'];

        if ($folderId) {
            // On-demand load of one (typically unscanned) folder — no triage,
            // the user is just browsing Junk / Archive
            $log = self::syncFolderOnDemand($mailbox, $folderId, time() + 45);
        } else {
            $log = self::syncMailbox($mailbox, time() + 30);

            // Short triage pass so a just-arrived mail gets its label before the
            // list refresh; the sorting queue of the inbox finishes whatever
            // does not fit here. A manual Refresh watches the sorting live —
            // it leaves all of it to the queue (triage=0)
            if ($triage) {
                $triageLog = MailManager_ProAiTriage_Model::run(time() + 10, $mailboxId);
                // a failed sorting is kept in the trace like the cron's (it is not shown anywhere else)
                self::traceProblems('Refresh AI triage', $triageLog);
                $log = array_merge($log, $triageLog);
            }
        }

        // Fresh per-folder unread counts — the sidebar badges update
        // without a page reload (Spam / Trash included)
        $unread = MailManager_ProFolder_Model::getUnreadCounts($mailboxId);
        // mail still waiting for the triage (none when a single folder was loaded on demand)
        $pending = $folderId
            ? 0
            : count(MailManager_ProAiTriage_Model::getPending($mailboxId));

        return [
            'success' => true,
            'log' => $log,
            'unread' => $unread,
            'workspace_unread' => MailManager_ProThreadList_Model::getWorkspaceUnreadCount($mailboxId, $userId),
            // new mail a label moved to Spam / Trash by itself — the inbox says so
            'auto_moved' => MailManager_ProAutoCleanup_Model::getMoved(),
            // the provider refuses the account (no credit, rejected key) while
            // mail waits — the inbox says why nothing gets sorted
            'ai_block' => $pending ? MailManager_ProAiBlock_Model::describe() : null,
            // the main AI is down and the backup runs — the bar of the inbox (I17)
            'ai_outage' => MailManager_ProAiFailover_Model::describeOutage(),
            // the fingerprint of the Workspace digest — only while it is open (I19)
            'digest_stamp' => $digest ? MailManager_ProWorkspaceDigest_Model::getStamp($userId) : null,
            // what this pass sorted (label, automatic move) and what still
            // waits — the inbox shows both in its sorting queue
            'triage_done' => $folderId ? [] : MailManager_ProTriageQueue_Model::describe(
                $mailboxId,
                MailManager_ProAiTriage_Model::getProcessed()
            ),
            'triage_pending' => $pending,
        ];
    }

    /**
     * @param array $mailbox df_mailmanager_mailbox row (encrypted secrets)
     * @param int $deadline unix timestamp hard stop
     * @return array log lines
     */
    public static function syncMailbox(array $mailbox, $deadline)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $log = [];

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_SYNC)) {
            // Graph / Gmail drivers land after the OAuth flow is field-tested
            return ["[{$mailbox['email']}] {$mailbox['provider']} sync not implemented yet, skipped"];
        }
        if (!self::acquireSyncLock($mailbox)) {
            return ["[{$mailbox['email']}] sync already running, skipped"];
        }

        // folders created, renamed or deleted in another client (or a changed
        // hierarchy delimiter) — one cheap LIST keeps the stored list current
        try {
            $log = MailManager_ProFolder_Model::reconcile($mailbox, $transport);
        } catch (Throwable $e) {
            $log[] = "[{$mailbox['email']}] folder list not checked (" . $e->getMessage() . ')';
        }

        $folderResult = $adb->pquery(
            'SELECT * FROM df_mailmanager_folder WHERE mailboxid = ? AND sync_enabled = 1',
            [$mailbox['mailboxid']]
        );
        $folderCount = $adb->num_rows($folderResult);

        try {
            $failed = 0;
            $lastError = null;

            for ($i = 0; $i < $folderCount; $i++) {
                $folder = $adb->raw_query_result_rowdata($folderResult, $i);

                // one broken folder must not stop the others
                try {
                    $log = array_merge($log, self::syncFolder($mailbox, $transport, $folder, $deadline));
                } catch (Exception $e) {
                    $failed++;
                    $lastError = $e;
                    $log[] = "[{$mailbox['email']}] {$folder['name']}: ERROR " . $e->getMessage();
                }
            }

            // every folder failed – most likely the login; the caller records it and backs off
            if ($folderCount > 0 && $failed === $folderCount && $lastError) {
                throw $lastError;
            }

            self::refreshUnsyncedBadgeCounts($mailbox, $transport);
            self::backfillReplyTo($mailbox, $transport);
        } finally {
            $transport->close();
            self::releaseSyncLock($mailbox);
        }

        (new MailManager_ProMailbox_Model())->saveLastSync((int)$mailbox['mailboxid']);

        return $log;
    }

    /**
     * Unread badge of the Spam and Trash folders without syncing them — one
     * cheap remote STATUS per folder so the sidebar shows whether something
     * new landed there (learned spam moves, the label cleanup)
     *
     * @param array $mailbox df_mailmanager_mailbox row (secrets stay encrypted)
     * @param MailManager_ProBaseTransport_Helper $transport open transport
     * @return void
     */
    private static function refreshUnsyncedBadgeCounts(array $mailbox, $transport)
    {
        $adb = PearDatabase::getInstance();
        $types = MailManager_ProFolder_Model::BADGE_FOLDER_TYPES;
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_folder
             WHERE mailboxid = ? AND sync_enabled = 0 AND folder_type IN (' . generateQuestionMarks($types) . ')',
            array_merge([$mailbox['mailboxid']], $types)
        );

        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $folder = $adb->raw_query_result_rowdata($result, $i);
            try {
                $counts = $transport->getFolderCounts($folder);
            } catch (Exception $e) {
                // a badge is not worth failing the whole sync
                continue;
            }
            $adb->pquery(
                'UPDATE df_mailmanager_folder SET total_count = ?, unread_count = ? WHERE folderid = ?',
                [$counts['messages'], $counts['unseen'], $folder['folderid']]
            );
            MailManager_ProFolder_Model::storeUnreadBase((int)$folder['folderid']);
        }
    }

    /**
     * On-demand sync of one folder — the sidebar loads unscanned folders
     * (Junk, Archive, ...) on click; the first load respects the mailbox
     * sync_from window, later clicks continue from the stored cursor
     *
     * @param array $mailbox df_mailmanager_mailbox row (encrypted secrets)
     * @param int $folderId
     * @param int $deadline unix timestamp hard stop
     * @return array log lines
     * @throws Exception when the folder does not belong to the mailbox
     */
    public static function syncFolderOnDemand(array $mailbox, $folderId, $deadline)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_SYNC)) {
            return ["[{$mailbox['email']}] {$mailbox['provider']} sync not implemented yet, skipped"];
        }

        $folderResult = $adb->pquery(
            'SELECT * FROM df_mailmanager_folder WHERE mailboxid = ? AND folderid = ?',
            [$mailbox['mailboxid'], $folderId]
        );
        if (!$adb->num_rows($folderResult)) {
            throw new Exception('Folder not found');
        }
        $folder = $adb->raw_query_result_rowdata($folderResult, 0);
        if (!self::acquireSyncLock($mailbox)) {
            return ["[{$mailbox['email']}] sync already running, skipped"];
        }

        try {
            return self::syncFolder($mailbox, $transport, $folder, $deadline);
        } finally {
            $transport->close();
            self::releaseSyncLock($mailbox);
        }
    }

    /**
     * "Load older" of a folder — the next batch of history below the oldest
     * cached message, beyond the mailbox sync window. History is only
     * stored: no auto comment, auto-link, filing or re-triage (old mail is
     * not news), the AI history guard skips its threads
     *
     * @param array $mailbox df_mailmanager_mailbox row (encrypted secrets)
     * @param int $folderId
     * @return array ['imported' => int, 'has_more' => bool]
     * @throws Exception when the folder does not belong to the mailbox
     */
    public static function loadOlder(array $mailbox, $folderId)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_SYNC)) {
            return ['imported' => 0, 'has_more' => false];
        }
        $folderResult = $adb->pquery(
            'SELECT * FROM df_mailmanager_folder WHERE mailboxid = ? AND folderid = ?',
            [$mailbox['mailboxid'], $folderId]
        );
        if (!$adb->num_rows($folderResult)) {
            throw new Exception('Folder not found');
        }
        $folder = $adb->raw_query_result_rowdata($folderResult, 0);
        if (!self::acquireSyncLock($mailbox)) {
            throw new Exception(vtranslate('LBL_PRO_SYNC_RUNNING', 'MailManager'));
        }

        try {
            // scattered server search matches do not count — the history
            // continues below the oldest mail loaded in a row
            $oldest = $adb->pquery(
                "SELECT MIN(remote_uid) AS oldest FROM df_mailmanager_message
                 WHERE folderid = ? AND remote_uid IS NOT NULL AND source != 'search'",
                [$folderId]
            );
            $older = $transport->getOlderMessages(
                $folder,
                (int)$adb->query_result($oldest, 0, 'oldest'),
                self::HISTORY_BATCH
            );
            foreach ($older['messages'] as $message) {
                self::storeMessage($mailbox, $folder, $message, true);
            }
            if (!$older['has_more']) {
                $adb->pquery(
                    'UPDATE df_mailmanager_folder SET history_complete = 1 WHERE folderid = ?',
                    [$folderId]
                );
            }

            return ['imported' => count($older['messages']), 'has_more' => $older['has_more']];
        } finally {
            $transport->close();
            self::releaseSyncLock($mailbox);
        }
    }

    /**
     * Search on the mail server — mail the cache does not hold (unscanned
     * folders, history beyond the sync window). Folder by folder (INBOX,
     * Sent, archives, the rest; Spam and Trash left out) the matches not
     * cached yet are fetched as history, newest first, up to SEARCH_BATCH per
     * call; the local search then lists them. A repeated call resumes at the
     * returned folder, so a slow server still gets through every folder
     *
     * @param array $mailbox df_mailmanager_mailbox row (encrypted secrets)
     * @param string $term
     * @param int $deadline unix timestamp — nothing new starts after it
     * @param int $resumeFolderId folder to start at (0 = the first one)
     * @return array ['imported' => int, 'complete' => bool, 'resume_folder' => int (0 = done),
     *                'skipped' => string[] folders the server did not search in time]
     * @throws Exception when another sync holds the mailbox
     */
    public static function searchOnServer(array $mailbox, $term, $deadline, $resumeFolderId = 0)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $result = ['imported' => 0, 'complete' => true, 'resume_folder' => 0, 'skipped' => []];

        $transport = MailManager_ProTransport_Model::getForMailbox($mailbox);
        if (!$transport->supports(MailManager_ProBaseTransport_Helper::CAP_SYNC)) {
            return $result;
        }
        if (!self::acquireSyncLock($mailbox)) {
            throw new Exception(vtranslate('LBL_PRO_SYNC_RUNNING', 'MailManager'));
        }

        $folderResult = $adb->pquery(
            "SELECT * FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folder_type NOT IN ('spam', 'trash')
             ORDER BY FIELD(folder_type, 'inbox', 'sent', 'archive', 'custom', 'drafts'), name, folderid",
            [$mailbox['mailboxid']]
        );
        $folders = [];
        for ($i = 0, $count = $adb->num_rows($folderResult); $i < $count; $i++) {
            $folders[] = $adb->raw_query_result_rowdata($folderResult, $i);
        }
        $position = 0;
        foreach ($folders as $index => $folder) {
            if ((int)$folder['folderid'] === (int)$resumeFolderId) {
                $position = $index;
            }
        }

        try {
            for (; $position < count($folders); $position++) {
                $folder = $folders[$position];
                if (time() >= $deadline || $result['imported'] >= self::SEARCH_BATCH) {
                    $result['resume_folder'] = (int)$folder['folderid'];
                    break;
                }
                try {
                    $uids = $transport->searchFolder($folder, $term);
                } catch (Exception $e) {
                    // the server did not answer in time — the connection
                    // still carries the unfinished search, a new one starts;
                    // the folder is reported, the next call goes on after it
                    $transport->close();
                    $result['skipped'][] = $folder['name'];
                    $result['resume_folder'] = isset($folders[$position + 1])
                        ? (int)$folders[$position + 1]['folderid'] : 0;
                    break;
                }
                $missing = self::uncachedUids((int)$folder['folderid'], $uids);
                $take = min(self::SEARCH_FOLDER_BATCH, self::SEARCH_BATCH - $result['imported']);
                // newest first, in small steps — the deadline is checked between them
                $cutShort = count($missing) > $take;
                foreach (array_chunk(array_reverse(array_slice($missing, -$take)), 10) as $chunk) {
                    if (time() >= $deadline) {
                        $cutShort = true;
                        break;
                    }
                    foreach ($transport->fetchMessages($folder, $chunk) as $message) {
                        self::storeMessage($mailbox, $folder, $message, true, 'search');
                        $result['imported']++;
                    }
                }
                // matches left in this folder — the next call continues here
                // (a message the server refuses is not waited for)
                if ($cutShort) {
                    $result['resume_folder'] = (int)$folder['folderid'];
                    break;
                }
            }
        } finally {
            $transport->close();
            self::releaseSyncLock($mailbox);
        }
        $result['complete'] = !$result['resume_folder'] && !$result['skipped'];

        return $result;
    }

    /**
     * @param int $folderId
     * @param int[] $uids server matches
     * @return int[] the ones the cache does not hold, ascending
     */
    private static function uncachedUids($folderId, array $uids)
    {
        if (!$uids) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $cached = [];
        foreach (array_chunk($uids, self::SEEN_CHUNK) as $chunk) {
            $result = $adb->pquery(
                'SELECT remote_uid FROM df_mailmanager_message
                 WHERE folderid = ? AND remote_uid IN (' . generateQuestionMarks($chunk) . ')',
                array_merge([$folderId], $chunk)
            );
            for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
                $cached[(int)$adb->query_result($result, $i, 'remote_uid')] = true;
            }
        }

        return array_values(array_filter($uids, function ($uid) use ($cached) {
            return !isset($cached[$uid]);
        }));
    }

    /**
     * One sync per mailbox at a time — the cron and the users' Refresh run in
     * parallel; two runs fetching the same messages collide on the unique
     * (folder, uid) key and the losing insert's attachments would land on a
     * wrong message id. A MySQL named lock dies with the connection, so a
     * killed run never leaves it behind
     *
     * @param array $mailbox
     * @return bool false when another run holds the mailbox
     */
    private static function acquireSyncLock(array $mailbox)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT GET_LOCK(?, 0) AS got', [self::syncLockName($mailbox)]);

        return $result && (int)$adb->query_result($result, 0, 'got') === 1;
    }

    /**
     * @param array $mailbox
     * @return void
     */
    private static function releaseSyncLock(array $mailbox)
    {
        PearDatabase::getInstance()->pquery('SELECT RELEASE_LOCK(?)', [self::syncLockName($mailbox)]);
    }

    /**
     * A move / delete the user (or the label cleanup) makes on the server and
     * in the cache must not interleave with a sync of the same mailbox — the
     * sync would see the mail gone from one folder before the cache followed.
     * Waits a moment for a running sync; when it takes longer the move goes
     * ahead anyway (as it always did) rather than failing
     *
     * @param array $mailbox
     * @return bool the lock is held — release it with unlockMailbox()
     */
    public static function lockMailbox(array $mailbox)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT GET_LOCK(?, ' . self::MOVE_LOCK_WAIT . ') AS got',
            [self::syncLockName($mailbox)]
        );

        return $result && (int)$adb->query_result($result, 0, 'got') === 1;
    }

    /**
     * @param array $mailbox
     * @param bool $locked what lockMailbox() returned
     * @return void
     */
    public static function unlockMailbox(array $mailbox, $locked)
    {
        if ($locked) {
            self::releaseSyncLock($mailbox);
        }
    }

    /**
     * @param array $mailbox
     * @return string lock name unique per database and mailbox (MySQL caps it at 64 chars)
     */
    private static function syncLockName(array $mailbox)
    {
        $database = (string)(vglobal('dbconfig')['db_name'] ?? '');

        return 'df_mm_sync_' . substr(md5($database), 0, 12) . '_' . (int)$mailbox['mailboxid'];
    }

    /**
     * Incremental sync of one folder over an open transport
     *
     * @param array $mailbox df_mailmanager_mailbox row (secrets stay encrypted)
     * @param MailManager_ProBaseTransport_Helper $transport
     * @param array $folder df_mailmanager_folder row
     * @param int $deadline
     * @return array log lines
     */
    private static function syncFolder(array $mailbox, $transport, array $folder, $deadline)
    {
        $adb = PearDatabase::getInstance();
        $log = [];
        $imported = 0;
        $autoSpammed = 0;

        do {
            $changes = $transport->getChangesSince($folder, $folder['delta_cursor']);
            if (!empty($changes['reset'])) {
                // The server renumbered the folder (UIDVALIDITY) — cached UIDs
                // now point at other messages. Forgetting them lets the
                // re-import adopt each cached row by its Message-ID (the same
                // path as mail sent from the CRM): no duplicates, the CRM
                // links of the threads stay
                $adb->pquery(
                    'UPDATE df_mailmanager_message SET remote_uid = NULL WHERE folderid = ?',
                    [$folder['folderid']]
                );
                // loaded history is re-adopted the same way by "load older"
                $adb->pquery(
                    'UPDATE df_mailmanager_folder SET history_complete = 0 WHERE folderid = ?',
                    [$folder['folderid']]
                );
                $log[] = "[{$mailbox['email']}] {$folder['name']}: folder renumbered on the server, re-reading it";
                // the flag mod-sequence belongs to the old numbering too
                $folder['flags_modseq'] = 0;
                $folder['state_signature'] = '';
            }
            // Learned spam senders go straight to Junk on the server —
            // never cached, never sent to the AI triage
            $filtered = MailManager_ProSpamGuard_Model::filterIncoming($mailbox, $transport, $folder, $changes['messages']);
            $autoSpammed += $filtered['moved'];

            foreach ($filtered['messages'] as $message) {
                self::storeMessage($mailbox, $folder, $message);
                $imported++;
            }
            $folder['delta_cursor'] = $changes['cursor'];
            $adb->pquery(
                'UPDATE df_mailmanager_folder SET delta_cursor = ?, total_count = ?, unread_count = ? WHERE folderid = ?',
                [$changes['cursor'], $changes['counts']['messages'], $changes['counts']['unseen'], $folder['folderid']]
            );
        } while ($changes['has_more'] && time() < $deadline);

        if ($imported > 0 || $autoSpammed > 0 || $changes['has_more']) {
            $log[] = "[{$mailbox['email']}] {$folder['name']}: {$imported} message(s) imported"
                . ($autoSpammed ? ", {$autoSpammed} auto-moved to Spam" : '')
                . ($changes['has_more'] ? ' (more pending)' : '');
        }

        // A conversation moved to Spam / Trash waits there without a UID
        // until this import adopts it. The folder is read to its end now —
        // what still has none was emptied on the server meanwhile
        if (!$changes['has_more'] && empty($changes['stopped'])
            && in_array($folder['folder_type'] ?? '', ['spam', 'trash'], true)) {
            MailManager_ProThread_Model::deleteUnadoptedCache((int)$folder['folderid']);
        }

        // Mail read, deleted or moved in another client — Thunderbird, a
        // phone — must show the same here; the import alone never revisits it
        if (time() < $deadline) {
            try {
                $state = self::syncFolderState($transport, $folder);
                if ($state['read'] > 0) {
                    $log[] = "[{$mailbox['email']}] {$folder['name']}: "
                        . "read state of {$state['read']} message(s) taken from the server";
                }
                if ($state['removed'] > 0) {
                    $log[] = "[{$mailbox['email']}] {$folder['name']}: "
                        . "{$state['removed']} message(s) no longer on the server removed";
                }
            } catch (Throwable $e) {
                $log[] = "[{$mailbox['email']}] {$folder['name']}: server state not synced (" . $e->getMessage() . ')';
            }
        }
        // the server's unread count and the cache are both fresh now — the
        // badge keeps following the cache until the next sync
        MailManager_ProFolder_Model::storeUnreadBase((int)$folder['folderid']);

        return $log;
    }

    /**
     * Takes the folder's state on the server into the cache: the \Seen flag
     * of the messages, and the messages that are gone (deleted or moved in
     * another client) leave the folder; the touched threads are recomputed
     *
     * @param MailManager_ProBaseTransport_Helper $transport open transport
     * @param array $folder df_mailmanager_folder row
     * @return array ['read' => messages whose read state changed, 'removed' => messages removed]
     */
    private static function syncFolderState($transport, array $folder)
    {
        $state = $transport->getFolderState($folder);
        if ($state === null) {
            return ['read' => 0, 'removed' => 0];
        }

        $adb = PearDatabase::getInstance();
        $read = [];
        $unread = [];
        $threads = [];
        foreach (array_chunk(array_keys($state['seen']), self::SEEN_CHUNK) as $uids) {
            $result = $adb->pquery(
                'SELECT messageid, threadid, remote_uid, is_read FROM df_mailmanager_message
                 WHERE folderid = ? AND remote_uid IN (' . generateQuestionMarks($uids) . ')',
                array_merge([$folder['folderid']], $uids)
            );
            for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $seen = $state['seen'][(int)$row['remote_uid']];
                if ((bool)(int)$row['is_read'] === $seen) {
                    continue;
                }
                if ($seen) {
                    $read[] = (int)$row['messageid'];
                } else {
                    $unread[] = (int)$row['messageid'];
                }
                $threads[(int)$row['threadid']] = true;
            }
        }
        foreach ([1 => $read, 0 => $unread] as $isRead => $messageIds) {
            foreach (array_chunk($messageIds, self::SEEN_CHUNK) as $chunk) {
                $adb->pquery(
                    'UPDATE df_mailmanager_message SET is_read = ?
                     WHERE messageid IN (' . generateQuestionMarks($chunk) . ')',
                    array_merge([$isRead], $chunk)
                );
            }
        }

        // Cached messages the server no longer lists were deleted or moved
        // elsewhere — a move into a synced folder is imported there again.
        // Rows without a UID (mail just sent from the CRM) are not compared
        $removed = [];
        if ($state['uids'] !== null) {
            $onServer = array_flip($state['uids']);
            $result = $adb->pquery(
                'SELECT messageid, threadid, remote_uid FROM df_mailmanager_message
                 WHERE folderid = ? AND remote_uid IS NOT NULL',
                [$folder['folderid']]
            );
            for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                if (!isset($onServer[(int)$row['remote_uid']])) {
                    $removed[] = (int)$row['messageid'];
                    $threads[(int)$row['threadid']] = true;
                }
            }
            foreach (array_chunk($removed, self::SEEN_CHUNK) as $chunk) {
                $marks = generateQuestionMarks($chunk);
                $adb->pquery('DELETE FROM df_mailmanager_attachment WHERE messageid IN (' . $marks . ')', $chunk);
                $adb->pquery('DELETE FROM df_mailmanager_message WHERE messageid IN (' . $marks . ')', $chunk);
            }
        }

        MailManager_ProThread_Model::refreshAggregates(array_keys($threads));
        // the signature lets the next run skip the UID comparison when
        // nothing was added or removed on the server
        $adb->pquery(
            'UPDATE df_mailmanager_folder SET flags_modseq = ?, state_signature = ? WHERE folderid = ?',
            [$state['modseq'] ?: null, ($state['signature'] ?? '') ?: null, $folder['folderid']]
        );

        return ['read' => count($read) + count($unread), 'removed' => count($removed)];
    }

    /**
     * Stores one unified message: dedupe, thread resolution, aggregates
     *
     * @param array $mailbox
     * @param array $folder
     * @param array $message unified message array from the transport
     * @param bool $isHistory loaded older mail — stored only, no follow-up actions
     * @param string $source sync | search (a server search match — scattered, not history in a row)
     * @return void
     */
    private static function storeMessage(
        array $mailbox,
        array $folder,
        array $message,
        $isHistory = false,
        $source = 'sync'
    )
    {
        $adb = PearDatabase::getInstance();

        $existing = $adb->pquery(
            'SELECT messageid FROM df_mailmanager_message WHERE folderid = ? AND remote_uid = ?',
            [$folder['folderid'], $message['remote_uid']]
        );
        if ($adb->num_rows($existing)) {
            return;
        }

        // Mail sent from the CRM is cached immediately (remote_uid NULL) — when
        // the sync later finds its copy in the Sent folder, adopt the server
        // identity instead of inserting a duplicate
        // The row must wait in a folder of the same kind (the one being read
        // first): a new mail that merely reuses a Message-ID elsewhere must
        // not pull a conversation out of the Trash. Mail without a Message-ID
        // is recognised by sender, time and subject in this very folder
        $message['header_message_id'] = mb_substr((string)$message['header_message_id'], 0, 255);
        $message['header_in_reply_to'] = mb_substr((string)$message['header_in_reply_to'], 0, 255);
        $message['from_email'] = mb_substr((string)$message['from_email'], 0, 150);
        if ($message['header_message_id'] !== '') {
            $cached = $adb->pquery(
                'SELECT m.messageid FROM df_mailmanager_message m
                 INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
                 WHERE m.mailboxid = ? AND m.header_message_id = ? AND m.remote_uid IS NULL AND f.folder_type = ?
                 ORDER BY m.folderid = ? DESC LIMIT 1',
                [
                    $mailbox['mailboxid'], $message['header_message_id'],
                    (string)($folder['folder_type'] ?? ''), $folder['folderid'],
                ]
            );
        } else {
            $cached = $adb->pquery(
                "SELECT messageid FROM df_mailmanager_message
                 WHERE folderid = ? AND remote_uid IS NULL AND header_message_id = ''
                   AND from_email = ? AND sent_at = ? AND subject = ? LIMIT 1",
                [
                    $folder['folderid'], $message['from_email'], $message['sent_at'],
                    mb_substr((string)$message['subject'], 0, 500),
                ]
            );
        }
        if ($cached) {
            if ($adb->num_rows($cached)) {
                $cachedId = (int)$adb->query_result($cached, 0, 'messageid');
                $adb->pquery(
                    'UPDATE df_mailmanager_message SET remote_uid = ?, folderid = ?, moved_at = NULL
                     WHERE messageid = ?',
                    [$message['remote_uid'], $folder['folderid'], $cachedId]
                );
                // Mail sent from the CRM was cached without its attachment
                // list — the server copy carries the parts (open, forward).
                // Rows kept from an earlier identity (renumbered folder) stay
                $known = $adb->pquery(
                    'SELECT 1 FROM df_mailmanager_attachment WHERE messageid = ? LIMIT 1',
                    [$cachedId]
                );
                if (!$adb->num_rows($known)) {
                    self::insertAttachments($cachedId, $message['attachments']);
                }

                return;
            }
        }

        $threadId = self::resolveThread($mailbox, $message);
        $direction = self::resolveDirection($mailbox, $folder, $message);
        $bodyHtml = MailManager_ProMailText_Model::sanitizeHtml($message['body_html']);
        $bodyPlain = MailManager_ProMailText_Model::messageText($message['body_plain'], $bodyHtml);
        $snippet = self::buildSnippet($bodyPlain);

        // The paperclip reflects real (non-inline) attachments only —
        // signature logos and inline images do not count
        $hasRealAttachments = 0;
        foreach ($message['attachments'] as $attachment) {
            if ((int)$attachment['is_inline'] === 0) {
                $hasRealAttachments = 1;
                break;
            }
        }
        $message['has_real_attachments'] = $hasRealAttachments;

        $adb->pquery(
            'INSERT INTO df_mailmanager_message
                (threadid, mailboxid, folderid, remote_uid, header_message_id, header_in_reply_to, header_references,
                 reply_to, direction, is_draft, is_read, from_email, from_name, to_addrs, cc_addrs, bcc_addrs,
                 subject, body_html, body_plain, snippet, sent_at, size_bytes, has_attachments, source, mail_message_key)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $threadId, $mailbox['mailboxid'], $folder['folderid'], $message['remote_uid'],
                $message['header_message_id'], $message['header_in_reply_to'], $message['header_references'],
                mb_substr((string)($message['reply_to'] ?? ''), 0, 150),
                $direction, $message['is_draft'], $message['is_read'],
                $message['from_email'], mb_substr($message['from_name'], 0, 150),
                json_encode($message['to_addrs'], JSON_UNESCAPED_UNICODE),
                json_encode($message['cc_addrs'], JSON_UNESCAPED_UNICODE),
                json_encode($message['bcc_addrs'], JSON_UNESCAPED_UNICODE),
                mb_substr($message['subject'], 0, 500), $bodyHtml, $bodyPlain, $snippet,
                $message['sent_at'], $message['size_bytes'], $hasRealAttachments, $source,
                MailManager_ProMimeParser_Helper::getMessageKey((string)$message['header_message_id']),
            ]
        );
        $messageId = (int)$adb->getLastInsertID();

        self::insertAttachments($messageId, $message['attachments']);

        self::updateThreadAggregates($threadId, $message, $snippet, $direction);

        // old mail loaded on request is history, not news; so is a folder the
        // mailbox does not scan (opened on demand — its whole content comes
        // at once) and mail browsed in Spam / Trash: shown only, no CRM
        // link, comment or filing for it
        if ($isHistory || empty($folder['sync_enabled'])
            || in_array($folder['folder_type'] ?? '', ['spam', 'trash'], true)) {
            return;
        }

        // the customer wrote again – a conversation marked done is open again
        if ('in' === $direction && !$message['is_draft']) {
            MailManager_ProThreadState_Model::onIncoming($threadId);
        }

        // New inbound mail of a thread with a linked case is logged on the record as a
        // comment; certain sender matches link their CRM records (no AI call)
        if ('in' === $direction && !$message['is_draft']) {
            MailManager_ProCrmLog_Model::autoCommentIncoming($threadId, $messageId, $message, $bodyPlain);
            MailManager_ProAutoLink_Model::autoLink($threadId);

            // A new customer message changes the situation – re-triage the thread;
            // an explicit dismiss stays muted
            $adb->pquery(
                "UPDATE df_mailmanager_thread SET triage_state = 'none', triage_attempts = 0
                 WHERE threadid = ?
                   AND triage_state IN ('labeled', 'suggested', 'accepted', 'created', 'skipped', 'failed')",
                [$threadId]
            );
        }

        // A message of a linked conversation is filed as an ITS4YouEmails record on its
        // record (after the auto-link above) – the same record Basic shows as linked
        if (!$message['is_draft']) {
            MailManager_ProEmailFiler_Model::fileMessage($messageId);
        }
    }

    /**
     * The attachment list of a cached message — parts stay on the server,
     * fetched on demand by their part path
     *
     * @param int $messageId
     * @param array $attachments unified attachment arrays from the transport
     * @return void
     */
    private static function insertAttachments($messageId, array $attachments)
    {
        $adb = PearDatabase::getInstance();
        foreach ($attachments as $attachment) {
            $adb->pquery(
                'INSERT INTO df_mailmanager_attachment
                    (messageid, remote_part, filename, mime_type, size_bytes, is_inline, content_id)
                 VALUES (?,?,?,?,?,?,?)',
                [
                    $messageId, $attachment['remote_part'], mb_substr($attachment['filename'], 0, 255),
                    $attachment['mime_type'], $attachment['size_bytes'],
                    $attachment['is_inline'], $attachment['content_id'],
                ]
            );
        }
    }

    /**
     * Finds the conversation for a message via References / In-Reply-To /
     * Message-ID, or creates a new thread
     *
     * @param array $mailbox
     * @param array $message
     * @return int threadid
     */
    private static function resolveThread(array $mailbox, array $message)
    {
        $adb = PearDatabase::getInstance();

        $relatedIds = [];
        if (preg_match_all('/<[^<>]+>/', $message['header_references'], $m)) {
            $relatedIds = $m[0];
        }
        if ($message['header_in_reply_to'] !== '') {
            $relatedIds[] = $message['header_in_reply_to'];
        }
        if ($message['header_message_id'] !== '') {
            $relatedIds[] = $message['header_message_id'];
        }
        $relatedIds = array_values(array_unique(array_filter($relatedIds)));

        if ($relatedIds) {
            $result = $adb->pquery(
                'SELECT threadid FROM df_mailmanager_message
                 WHERE mailboxid = ? AND header_message_id IN (' . generateQuestionMarks($relatedIds) . ') LIMIT 1',
                array_merge([$mailbox['mailboxid']], $relatedIds)
            );
            if ($adb->num_rows($result)) {
                return (int)$adb->query_result($result, 0, 'threadid');
            }
        }

        // Thread key = conversation root (first reference) or own Message-ID
        $threadKey = $relatedIds[0] ?? ('no-message-id-' . $message['remote_uid'] . '@' . $mailbox['mailboxid']);
        $threadKey = mb_substr($threadKey, 0, 255);

        $result = $adb->pquery(
            'SELECT threadid FROM df_mailmanager_thread WHERE mailboxid = ? AND thread_key = ?',
            [$mailbox['mailboxid'], $threadKey]
        );
        if ($adb->num_rows($result)) {
            return (int)$adb->query_result($result, 0, 'threadid');
        }

        $adb->pquery(
            'INSERT INTO df_mailmanager_thread (mailboxid, thread_key, subject, last_message_at)
             VALUES (?,?,?,?)',
            [
                $mailbox['mailboxid'], $threadKey,
                mb_substr(self::normalizeSubject($message['subject']), 0, 500), $message['sent_at'],
            ]
        );

        return (int)$adb->getLastInsertID();
    }

    /**
     * @param int $threadId
     * @param array $message needs is_read / attachments / sent_at keys
     * @param string $snippet
     * @param string $direction
     * @return void
     */
    public static function updateThreadAggregates($threadId, array $message, $snippet, $direction)
    {
        $adb = PearDatabase::getInstance();
        $unreadIncrement = (!$message['is_read'] && $direction === 'in') ? 1 : 0;

        $realAttachments = $message['has_real_attachments'] ?? ($message['attachments'] ? 1 : 0);
        $adb->pquery(
            'UPDATE df_mailmanager_thread SET
                message_count = message_count + 1,
                unread_count = unread_count + ?,
                has_attachments = has_attachments OR ?,
                snippet = IF(? >= IFNULL(last_message_at, \'1970-01-01\'), ?, snippet),
                last_message_at = GREATEST(IFNULL(last_message_at, \'1970-01-01\'), ?)
             WHERE threadid = ?',
            [
                $unreadIncrement, $realAttachments,
                $message['sent_at'], mb_substr($snippet, 0, 255),
                $message['sent_at'], $threadId,
            ]
        );
    }

    /**
     * @param array $mailbox
     * @param array $folder
     * @param array $message
     * @return string in | out
     */
    private static function resolveDirection(array $mailbox, array $folder, array $message)
    {
        if (($folder['folder_type'] ?? '') === 'sent' || $message['is_draft']) {
            return 'out';
        }
        if (strcasecmp($message['from_email'], $mailbox['email']) !== 0) {
            return 'in';
        }

        // Mail from the mailbox's own address outside Sent is our own copy —
        // unless somebody else speaks through it: a web form mails from the
        // site's address and names the visitor in Reply-To, a notification
        // goes to the mailbox alone. That is received mail
        $replyTo = (string)($message['reply_to'] ?? '');
        if ($replyTo !== '' && strcasecmp($replyTo, $mailbox['email']) !== 0) {
            return 'in';
        }
        $recipients = array_merge((array)($message['to_addrs'] ?? []), (array)($message['cc_addrs'] ?? []));
        foreach ($recipients as $recipient) {
            if (strcasecmp((string)($recipient['email'] ?? ''), $mailbox['email']) !== 0) {
                return 'out';
            }
        }

        return $recipients ? 'in' : 'out';
    }

    /**
     * Reads the Reply-To of mail cached before the address was kept — only
     * received mail from the mailbox's own address (web forms), a few per
     * run. Best effort: what cannot be read is marked as having none
     *
     * @param array $mailbox df_mailmanager_mailbox row
     * @param MailManager_ProBaseTransport_Helper $transport open transport
     * @return void
     */
    private static function backfillReplyTo(array $mailbox, $transport)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT m.messageid, m.folderid, m.remote_uid FROM df_mailmanager_message m
             WHERE m.mailboxid = ? AND m.reply_to IS NULL AND m.direction = 'in' AND m.remote_uid IS NOT NULL
               AND LOWER(m.from_email) = LOWER(?) ORDER BY m.messageid DESC LIMIT 20",
            [$mailbox['mailboxid'], $mailbox['email']]
        );
        $byFolder = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $byFolder[(int)$row['folderid']][(int)$row['remote_uid']] = (int)$row['messageid'];
        }

        foreach ($byFolder as $folderId => $messageIds) {
            $replyTo = array_fill_keys(array_keys($messageIds), '');
            $folderResult = $adb->pquery('SELECT * FROM df_mailmanager_folder WHERE folderid = ?', [$folderId]);
            if ($adb->num_rows($folderResult)) {
                try {
                    $fetched = $transport->fetchMessages(
                        $adb->raw_query_result_rowdata($folderResult, 0),
                        array_keys($messageIds)
                    );
                    foreach ($fetched as $message) {
                        $replyTo[(int)$message['remote_uid']] = (string)($message['reply_to'] ?? '');
                    }
                } catch (Throwable $e) {
                    // the folder cannot be read now — try again in a later run
                    continue;
                }
            }
            foreach ($replyTo as $uid => $address) {
                $adb->pquery(
                    'UPDATE df_mailmanager_message SET reply_to = ? WHERE messageid = ?',
                    [mb_substr($address, 0, 150), $messageIds[$uid]]
                );
            }
        }
    }

    /**
     * @param string $plain
     * @return string
     */
    private static function buildSnippet($plain)
    {
        $flat = trim(preg_replace('/\s+/u', ' ', $plain));

        return mb_substr($flat, 0, 200);
    }

    /**
     * Strips Re:/Fwd:/FW: style prefixes for the thread subject
     *
     * @param string $subject
     * @return string
     */
    private static function normalizeSubject($subject)
    {
        return trim(preg_replace('/^(\s*(re|fw|fwd|aw|odp|sv)\s*(\[\d+\])?\s*:\s*)+/iu', '', $subject));
    }
}
