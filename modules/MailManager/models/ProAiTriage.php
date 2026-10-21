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
 * AI triage — classifies new inbound conversations after a sync run:
 * assigns a label + priority to the thread and, depending on the configured
 * mode, suggests or auto-creates a CRM record (Lead / Ticket).
 *
 * thread.triage_state lifecycle:
 *   none → labeled | suggested | created | failed | skipped
 *   suggested → accepted (record linked) | dismissed (user)
 */
class MailManager_ProAiTriage_Model
{
    /** Usage-log feature of the cascade's second sorting — runs on the fallback agent */
    public const FEATURE_TRIAGE_STRONG = MailManager_ProAiAgent_Model::FEATURE_TRIAGE_FALLBACK;

    /** Below this confidence of the fast model the cascade asks the strong one */
    public const CASCADE_CONFIDENCE = 0.6;

    /** Output budget of a sorting — the JSON is short, but reasoning models (Haiku 5.5) spend their
     *  thinking from it too: 1000 was reached (916–1000 in the usage log), an empty answer followed;
     *  4000 left half of it spare (1192 at most) — 8000, the fast model is cheap */
    private const MAX_TOKENS = 8000;

    /** Max threads classified in one run (cron picks up the rest next time) */
    public const BATCH_LIMIT = 25;

    public const PRIORITIES = ['urgent', 'high', 'normal', 'low'];

    /** Modules safe to create without user review in the Auto-create mode */
    private const AUTO_CREATE_MODULES = ['Leads', 'HelpDesk'];

    /** vtiger_crmentity.source of auto-created records — counted by the daily cap */
    private const RECORD_SOURCE = 'MAILMANAGER_AI';

    /** Tries a conversation gets before a passing error counts as final */
    private const MAX_ATTEMPTS = 3;

    /** Failures in a row that end a batch (the provider is down, not the mail broken) */
    private const MAX_FAILED_IN_ROW = 3;

    /** A 'processing' claim older than this belongs to a run that died */
    private const STALE_CLAIM_MINUTES = 15;

    /**
     * Thread conditions "waits for the triage" (alias t; parameters: the
     * history guard date, the stale claim time). Spam / Trash content
     * (loaded on demand into the cache) never burns triage tokens — only
     * mail living in regular folders qualifies
     */
    private const PENDING_SQL = "t.last_message_at >= ?
               AND (t.triage_state = 'none'
                    OR (t.triage_state = 'processing' AND (t.triage_claimed_at IS NULL OR t.triage_claimed_at < ?)))
               AND EXISTS (SELECT 1 FROM df_mailmanager_message m
                           INNER JOIN df_mailmanager_folder f ON f.folderid = m.folderid
                           WHERE m.threadid = t.threadid AND m.direction = 'in'
                             AND f.sync_enabled = 1 AND f.folder_type NOT IN ('spam', 'trash'))";

    /** @var int[] conversations this request classified (or tried to) */
    private static $processed = [];

    /**
     * @return int[] conversations run() worked on during this request
     */
    public static function getProcessed()
    {
        return self::$processed;
    }

    /**
     * What still waits for the triage in a mailbox, newest first — the
     * order run() takes it in (the sorting queue of the inbox shows it)
     *
     * @param int $mailboxId
     * @param int $limit
     * @return array of ['threadid', 'subject', 'sender']
     */
    public static function getPending($mailboxId, $limit = 200)
    {
        $since = MailManager_ProAiConfig_Model::get('ai_triage_since');
        if ($since === ''
            || !MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRIAGE)
            || !self::getActiveLabels((int)$mailboxId)) {
            return [];
        }

        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT t.threadid, t.subject,
                    (SELECT IFNULL(NULLIF(mx.from_name, ''), mx.from_email) FROM df_mailmanager_message mx
                     WHERE mx.threadid = t.threadid AND mx.direction = 'in'
                     ORDER BY mx.sent_at DESC LIMIT 1) AS sender
             FROM df_mailmanager_thread t
             WHERE " . self::PENDING_SQL . ' AND t.mailboxid = ?
             ORDER BY t.last_message_at DESC
             LIMIT ' . (int)$limit,
            [$since, self::staleClaimTime(), (int)$mailboxId]
        );

        $pending = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $pending[] = [
                'threadid' => (int)$row['threadid'],
                'subject' => (string)$row['subject'],
                'sender' => (string)$row['sender'],
            ];
        }

        return $pending;
    }

    /**
     * Classifies pending threads within the time budget
     *
     * @param int $deadline unix timestamp hard stop
     * @param int $mailboxId 0 = all mailboxes
     * @param int $limit conversations at most (the sorting queue takes small steps)
     * @return array log lines
     */
    public static function run($deadline, $mailboxId = 0, $limit = self::BATCH_LIMIT)
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRIAGE)) {
            return [];
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
        } catch (Exception $e) {
            return ['AI triage: ' . $e->getMessage()];
        }
        if (!$provider) {
            return [];
        }

        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $log = [];

        // History guard — mail older than the moment triage was enabled is never
        // classified (protects the customer's token budget on initial syncs)
        $since = MailManager_ProAiConfig_Model::get('ai_triage_since');
        if ($since === '') {
            $since = date('Y-m-d H:i:s');
            MailManager_ProAiConfig_Model::set('ai_triage_since', $since);
        }
        $adb->pquery(
            "UPDATE df_mailmanager_thread SET triage_state = 'skipped'
             WHERE triage_state = 'none' AND last_message_at < ?",
            [$since]
        );

        // Only mailboxes that have labels to classify into — the threads of
        // the others stay 'none' until labels exist (skipping them for good
        // would leave them unclassified; selecting them would crowd the batch)
        $labelsByMailbox = [];
        $mailboxResult = $adb->pquery(
            'SELECT mailboxid FROM df_mailmanager_mailbox' . ($mailboxId ? ' WHERE mailboxid = ?' : ''),
            $mailboxId ? [$mailboxId] : []
        );
        for ($i = 0, $mailboxCount = $adb->num_rows($mailboxResult); $i < $mailboxCount; $i++) {
            $labeledId = (int)$adb->query_result($mailboxResult, $i, 'mailboxid');
            $labels = self::getActiveLabels($labeledId);
            if ($labels) {
                $labelsByMailbox[$labeledId] = $labels;
            }
        }
        if (!$labelsByMailbox) {
            return $log;
        }

        $params = array_merge([$since, self::staleClaimTime()], array_keys($labelsByMailbox));
        $mailboxCondition = ' AND t.mailboxid IN (' . generateQuestionMarks(array_keys($labelsByMailbox)) . ')';
        $result = $adb->pquery(
            'SELECT t.* FROM df_mailmanager_thread t
             WHERE ' . self::PENDING_SQL . $mailboxCondition . '
             ORDER BY t.last_message_at DESC
             LIMIT ' . max(1, (int)$limit),
            $params
        );

        // the labels differ per mailbox — a personal one adds its owner's own
        $accountRefused = false;
        $failedInRow = 0;
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            if (time() >= $deadline) {
                $log[] = 'AI triage: time budget reached, remaining threads postponed';
                break;
            }
            $thread = $adb->raw_query_result_rowdata($result, $i);
            $labels = $labelsByMailbox[(int)$thread['mailboxid']];
            // Claim the thread first — cron and the users' SyncNow runs select
            // the same rows in parallel; without the claim both classify it
            // and auto mode creates the case record twice
            if (!self::claimThread((int)$thread['threadid'], ['none'])) {
                continue;
            }
            try {
                $log[] = self::triageThread($provider, $labels, $thread, $deadline);
                self::$processed[] = (int)$thread['threadid'];
                $failedInRow = 0;
            } catch (MailManager_ProAiAccountException_Helper $e) {
                // No credit / rejected key / limit: this mail goes back to the
                // queue. The batch goes on — mail a learned rule labels needs no
                // AI, and every further AI call gets the remembered refusal at
                // once, without knocking on the provider again
                self::releaseThread((int)$thread['threadid']);
                MailManager_ProAiBlock_Model::force(false);
                if (!$accountRefused) {
                    $log[] = 'AI triage: the provider refuses the account — ' . $e->getMessage();
                }
                $accountRefused = true;
            } catch (Throwable $e) {
                // a passing error (provider overloaded, timeout) is retried by a
                // later run; only a mail that keeps failing is marked for good
                if (self::markFailed((int)$thread['threadid'], $e)) {
                    self::$processed[] = (int)$thread['threadid'];
                }
                $log[] = 'AI triage: thread ' . $thread['threadid'] . ' FAILED: ' . $e->getMessage();
                // an outage: stop instead of walking the whole batch into the wall
                if (++$failedInRow >= self::MAX_FAILED_IN_ROW) {
                    $log[] = 'AI triage: stopped after ' . $failedInRow . ' failures in a row';
                    break;
                }
            }
        }

        return $log;
    }

    /**
     * Atomically moves a thread into the 'processing' state — only one
     * caller wins, the others skip the thread. A claim older than
     * STALE_CLAIM_MINUTES belongs to a run that died (killed process) and
     * may be taken over, a thread must never stay 'processing' for good
     *
     * @param int $threadId
     * @param array $fromStates states the claim may start from
     * @return bool true when this caller owns the thread now
     */
    private static function claimThread($threadId, array $fromStates)
    {
        $adb = PearDatabase::getInstance();
        $claim = $adb->pquery(
            "UPDATE df_mailmanager_thread SET triage_state = 'processing', triage_claimed_at = ?
             WHERE threadid = ?
               AND (triage_state IN (" . generateQuestionMarks($fromStates) . ")
                    OR (triage_state = 'processing' AND (triage_claimed_at IS NULL OR triage_claimed_at < ?)))",
            array_merge([date('Y-m-d H:i:s'), $threadId], $fromStates, [self::staleClaimTime()])
        );

        return $adb->getAffectedRowCount($claim) > 0;
    }

    /**
     * Gives a claimed thread back to the queue untouched
     *
     * @param int $threadId
     * @return void
     */
    private static function releaseThread($threadId)
    {
        PearDatabase::getInstance()->pquery(
            "UPDATE df_mailmanager_thread SET triage_state = 'none', triage_claimed_at = NULL
             WHERE threadid = ? AND triage_state = 'processing'",
            [$threadId]
        );
    }

    /**
     * @return string datetime before which a 'processing' claim is dead
     */
    private static function staleClaimTime()
    {
        return date('Y-m-d H:i:s', time() - self::STALE_CLAIM_MINUTES * 60);
    }

    /**
     * Releases a claimed thread after an error — 'failed' lets an explicit
     * open retry it, a thread must never stay in 'processing'
     *
     * @param int $threadId
     * @param Throwable $error
     * @return bool true = failed for good; false = back in the queue for another try
     */
    private static function markFailed($threadId, Throwable $error)
    {
        $adb = PearDatabase::getInstance();
        // MySQL applies the assignments left to right — the later ones see the raised counter
        $adb->pquery(
            "UPDATE df_mailmanager_thread
             SET triage_attempts = triage_attempts + 1,
                 triage_state = IF(triage_attempts >= ?, 'failed', 'none'),
                 triage_claimed_at = NULL,
                 triage_payload = IF(triage_attempts >= ?, ?, triage_payload)
             WHERE threadid = ?",
            [
                self::MAX_ATTEMPTS, self::MAX_ATTEMPTS,
                json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_UNICODE), $threadId,
            ]
        );
        $result = $adb->pquery('SELECT triage_state FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);

        return $adb->num_rows($result) && $adb->query_result($result, 0, 'triage_state') === 'failed';
    }

    /**
     * The activity history entry of an automatic label — only when the
     * thread had none (the updates never overwrite a label)
     *
     * @param array $thread thread row as it was before the triage
     * @param int $labelId the label given, 0 = none
     * @param string $source MailManager_ProHistory_Model::SOURCE_RULE / SOURCE_AI
     * @return void
     */
    private static function logLabel(array $thread, $labelId, $source)
    {
        if (!$labelId || !empty($thread['labelid'])) {
            return;
        }
        MailManager_ProHistory_Model::logThread(
            (int)$thread['threadid'],
            MailManager_ProHistory_Model::ACTION_LABEL,
            $source,
            null,
            MailManager_ProHistory_Model::getLabelName($labelId)
        );
    }

    /**
     * Classifies one thread and applies the result
     *
     * @param MailManager_ProAiProvider_Helper $provider
     * @param array $labels active label rows
     * @param array $thread thread row
     * @param int|null $deadline cron budget end (null = interactive call)
     * @return string log line
     * @throws Exception on AI error / unparseable response
     */
    private static function triageThread($provider, array $labels, array $thread, $deadline = null)
    {
        $adb = PearDatabase::getInstance();
        $threadId = (int)$thread['threadid'];

        $messageResult = $adb->pquery(
            "SELECT from_email, from_name, subject, body_plain, body_html FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$threadId]
        );
        if (!$adb->num_rows($messageResult)) {
            $adb->pquery("UPDATE df_mailmanager_thread SET triage_state = 'skipped' WHERE threadid = ?", [$threadId]);

            return 'AI triage: thread ' . $threadId . ' skipped (no inbound message)';
        }
        $message = $adb->raw_query_result_rowdata($messageResult, 0);

        // A rule learned from the user's corrections decides the label — a
        // label without a CRM step (or an exception = no label) needs
        // nothing more from the AI
        $rule = MailManager_ProLabelRule_Model::match(
            (int)$thread['mailboxid'],
            (string)$message['from_email'],
            (string)$message['subject']
        );
        $ruleLabel = null;
        foreach ($rule ? $labels : [] as $label) {
            if ((int)$label['labelid'] === (int)$rule['labelid']) {
                $ruleLabel = $label;
            }
        }
        $isException = $rule && (int)$rule['labelid'] === MailManager_ProLabelRule_Model::NO_LABEL;
        if ($isException || ($ruleLabel && !self::isCrmModuleLabel($ruleLabel))) {
            return self::applyRuleLabel($thread, $message, $ruleLabel, (int)$rule['ruleid'], $deadline !== null);
        }
        if ($ruleLabel) {
            // the label is certain — stored before the AI call, a failed
            // call must not lose it; the AI only adds the CRM step
            $adb->pquery(
                'UPDATE df_mailmanager_thread SET labelid = IFNULL(labelid, ?) WHERE threadid = ?',
                [(int)$ruleLabel['labelid'], $threadId]
            );
            MailManager_ProLabelRule_Model::recordApplied((int)$rule['ruleid']);
            self::logLabel($thread, (int)$ruleLabel['labelid'], MailManager_ProHistory_Model::SOURCE_RULE);
        }

        // The JSON answer is ~30 tokens, but reasoning models (Claude Sonnet 5,
        // Gemini 2.5, o-series) spend thinking tokens from the same budget —
        // a tight cap would return an empty text
        $withEntities = MailManager_ProAiConfig_Model::isFeatureEnabled(
            MailManager_ProAiConfig_Model::FEATURE_ENTITY
        );
        $systemPrompt = MailManager_ProTriagePrompt_Model::buildSystemPrompt($withEntities);
        $emailPrompt = MailManager_ProTriagePrompt_Model::buildEmailPrompt(
            $labels,
            $message,
            MailManager_ProLabelRule_Model::getExamples(
                (int)$thread['mailboxid'],
                (string)$message['from_email']
            )
        );
        $result = MailManager_ProAiClient_Model::complete(
            $provider,
            MailManager_ProAiConfig_Model::FEATURE_TRIAGE,
            $threadId,
            $systemPrompt,
            $emailPrompt,
            self::MAX_TOKENS,
            $deadline
        );

        $parsed = MailManager_ProAiClient_Model::parseJsonObject($result['text']);
        if (!is_array($parsed)) {
            throw new Exception('unparseable AI response' . (!empty($result['truncated']) ? ' (cut off at max_tokens)' : ''));
        }
        // the mail tried to instruct the model — the sorting stands, nothing happens by itself (I16)
        $suspicious = !empty($result['suspicious']);

        // Cascade (AI settings): the triage agent is unsure — the fallback agent sorts the mail again
        // (a failing second call keeps the first answer — the cascade only improves)
        if (self::isCascadeDue($parsed) && (null === $deadline || $deadline - time() > MailManager_ProAiProvider_Helper::MIN_TIMEOUT)) {
            try {
                $strong = MailManager_ProAiClient_Model::complete(
                    $provider,
                    self::FEATURE_TRIAGE_STRONG,
                    $threadId,
                    $systemPrompt,
                    $emailPrompt,
                    self::MAX_TOKENS,
                    $deadline
                );
                $strongParsed = MailManager_ProAiClient_Model::parseJsonObject($strong['text']);

                if (is_array($strongParsed)) {
                    $parsed = $strongParsed;
                }
                $suspicious = $suspicious || !empty($strong['suspicious']);
            } catch (MailManager_ProAiAccountException_Helper $e) {
                // the fallback agent's account was refused — that must not stop the triage
                // agent, which has just answered
                MailManager_ProAiBlock_Model::clear();
            } catch (Exception $e) {
                // the fallback agent's provider failed — the first sorting stands
            }
        }

        // the language of the conversation — the first one told stays
        MailManager_ProThreadLanguage_Model::store($threadId, $parsed['language'] ?? '');

        $labelRow = null;
        // the prompt marks the labels that throw mail away — the model may echo the mark
        $pickedName = trim(preg_replace('/\s*\(discards the mail\)\s*$/i', '', (string)($parsed['label'] ?? '')));
        foreach ($labels as $label) {
            // a label without an AI description is manual-only (e.g. "Later")
            // — the prompt never offers it, a crafted mail must not reach it either
            if (trim((string)$label['ai_description']) === '') {
                continue;
            }
            if (strcasecmp($label['name'], $pickedName) === 0) {
                $labelRow = $label;
                break;
            }
        }
        // the learned rule outranks the model's pick (manual-only labels included)
        if ($ruleLabel) {
            $labelRow = Settings_MailManager_Label_Model::applyMailboxOverride(
                $ruleLabel,
                (int)$thread['mailboxid']
            );
        }
        $labelRow = self::keepTrustedSender($thread, $message, $labelRow);
        $priority = strtolower(trim((string)($parsed['priority'] ?? '')));
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = null;
        }
        // mail the label sends to Spam / Delete is never urgent, whatever the
        // model read into it ("urgent: your account will be closed")
        if (strpos((string)($labelRow['suggested_module'] ?? ''), 'action:') === 0) {
            $priority = 'low';
        }

        // the answer is checked by our code (I16): a confidence outside 0..1 is none at all
        // a confidence the model did not give allows; one outside 0..1 does not (I16 review)
        $confidence = null;
        $confidenceValid = true;
        if (isset($parsed['confidence'])) {
            $confidenceValid = is_numeric($parsed['confidence']) && $parsed['confidence'] >= 0 && $parsed['confidence'] <= 1;
            $confidence = $confidenceValid ? (float)$parsed['confidence'] : null;
        }
        $autoAllowed = !$suspicious && $confidenceValid
            && (null === $confidence || $confidence >= self::CASCADE_CONFIDENCE)
            && !MailManager_ProAiSuspicion_Model::isFlagged($threadId);

        $payload = [
            'label' => $labelRow['name'] ?? null,
            'priority' => $priority,
            'from_email' => (string)$message['from_email'],
            'from_name' => (string)$message['from_name'],
        ];
        // the model may answer the string "false" — that is not a confirmation
        if (filter_var($parsed['case_resolved'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $payload['case_resolved'] = true;
        }
        if ($withEntities) {
            $payload['entities'] = self::cleanEntities($parsed);
            // Our own company means the model read OUR quoted signature — the
            // whole identity is ours then, none of it describes the sender
            if (MailManager_ProRecordMatch_Model::isOwnCompany((string)($payload['entities']['company'] ?? ''))) {
                unset($payload['entities']);
            }
        }
        if ($ruleLabel) {
            $payload['label_rule'] = ['ruleid' => (int)$rule['ruleid'], 'labelid' => (int)$ruleLabel['labelid']];
        }
        $payload = self::carryOverPayload($thread, $payload);
        $state = 'labeled';
        $capped = false;

        $suggestedModule = (string)($labelRow['suggested_module'] ?? '');
        if (strpos($suggestedModule, 'action:') === 0) {
            // Cleanup suggestions (Move to Spam / Delete) are always suggest-only —
            // the AI never touches mail on the server without a user click
            $state = 'suggested';
            $payload['suggested_action'] = substr($suggestedModule, strlen('action:'));
        } elseif ($suggestedModule !== '' && !self::threadHasLinks($threadId)) {
            $senderMatch = MailManager_ProRecordMatch_Model::findSenderRecord((string)$message['from_email']);
            // A Lead only makes sense for a sender the CRM does not know yet
            if (!($suggestedModule === 'Leads' && $senderMatch)) {
                $state = 'suggested';
                $payload['suggested_module'] = $suggestedModule;
                $payload['known_sender'] = $senderMatch !== null;

                // only the background run creates records by itself — opening an
                // old conversation must not turn it into a Lead or a Ticket
                // never for suspicious mail or an unsure sorting (I16) — it stays a suggestion
                if ($deadline !== null && $autoAllowed && MailManager_ProAiConfig_Model::getTriageMode() === 'auto'
                    && in_array($suggestedModule, self::AUTO_CREATE_MODULES, true)) {
                    // A spam wave or a mail loop must not flood the CRM — over
                    // the daily cap the record stays a suggestion
                    if (self::countAutoCreated((int)$thread['mailboxid'])
                        >= MailManager_ProAiConfig_Model::getAutoCreateLimit()) {
                        $capped = true;
                    } else {
                        $created = self::createRecord($thread, $message, $suggestedModule, $senderMatch);
                        if ($created) {
                            $state = 'created';
                            $payload['crmid'] = $created['crmid'];
                            $payload['record_label'] = $created['record_label'];
                        }
                    }
                }
            }
        }
        $topics = MailManager_ProAiTopics_Model::normalize($parsed['topic_tags'] ?? []);

        $adb->pquery(
            'UPDATE df_mailmanager_thread
             SET labelid = IFNULL(labelid, ?), priority = IFNULL(priority, ?), triage_state = ?,
                 triage_attempts = 0, triage_payload = ?, ai_topics = ?, request_type = IFNULL(?, request_type)
             WHERE threadid = ?',
            [
                // topics: '' = looked at, none found — the topics backfill asks
                // the AI only about NULL, a second call for the same mail is waste
                $labelRow['labelid'] ?? null, $priority, $state,
                json_encode($payload, JSON_UNESCAPED_UNICODE), (string)$topics,
                MailManager_ProRadar_Model::normalize($parsed['request_type'] ?? null), $threadId,
            ]
        );
        // a rule label was logged before the AI call
        if (!$ruleLabel) {
            self::logLabel($thread, (int)($labelRow['labelid'] ?? 0), MailManager_ProHistory_Model::SOURCE_AI);
        }
        // a label set to clean up by itself moves the new mail right away —
        // the background run only, never the conversation a user has open
        $cleaned = $deadline !== null
            ? MailManager_ProAutoCleanup_Model::run($thread, $labelRow, MailManager_ProHistory_Model::SOURCE_AI)
            : '';
        if ($cleaned !== '') {
            return 'AI triage: thread ' . $threadId . ' → ' . $labelRow['name']
                . ' (moved to ' . $cleaned . ' automatically)';
        }

        // Certain matches (exact email / company domain) link the CRM records
        // right away — logged in the AI card
        MailManager_ProAutoLink_Model::autoLink($threadId);

        return 'AI triage: thread ' . $threadId
            . ' → ' . ($labelRow['name'] ?? 'no label')
            . ' / ' . ($priority ?: 'no priority')
            . ' (' . $state . ')'
            . ($capped ? ' — auto-create daily limit reached, left as a suggestion' : '');
    }

    /**
     * The sender details of the answer that fit their field (I16): short texts, a phone
     * of digits, a website of a domain — anything else is dropped.
     *
     * @param array $parsed the triage JSON
     *
     * @return array field => value
     */
    private static function cleanEntities(array $parsed): array
    {
        $patterns = [
            'phone' => '/^\+?[\d][\d \-\/().]{5,30}$/',
            'website' => '/^(https?:\/\/)?[\w.-]+\.[a-z]{2,}(\/\S*)?$/i',
            'zip' => '/^[\w \-]{2,12}$/u',
        ];
        $entities = [];

        foreach (['company', 'firstname', 'lastname', 'phone', 'position', 'website', 'street', 'city', 'zip', 'country'] as $field) {
            $value = $parsed['sender_' . $field] ?? '';
            $value = is_scalar($value) ? trim((string)$value) : '';

            if ('' === $value || 'null' === strtolower($value) || mb_strlen($value) > 100
                || (isset($patterns[$field]) && !preg_match($patterns[$field], $value))) {
                continue;
            }

            $entities[$field] = $value;
        }

        return $entities;
    }

    /**
     * @param array $parsed the fast model's answer
     *
     * @return bool the cascade is on, its fallback agent is another active agent and the answer is unsure
     */
    private static function isCascadeDue(array $parsed): bool
    {
        $fallback = MailManager_ProAiAgent_Model::getById((int)MailManager_ProAiConfig_Model::get('ai_triage_fallback'));
        $triageAgent = MailManager_ProAiAgent_Model::getForFeature(MailManager_ProAiConfig_Model::FEATURE_TRIAGE);

        if ('1' !== MailManager_ProAiConfig_Model::get('ai_triage_cascade') || !$fallback || !$fallback['active']
            || (int)$fallback['agentid'] === (int)($triageAgent['agentid'] ?? 0)) {
            return false;
        }

        return isset($parsed['confidence']) && is_numeric($parsed['confidence'])
            && (float)$parsed['confidence'] < self::CASCADE_CONFIDENCE;
    }

    /**
     * On-demand triage of a single thread — runs when the user opens a
     * conversation the batch has not classified (old mail, budget leftovers).
     * The history guard does not apply here: an explicit open is the signal
     * that this one thread is worth one AI call
     *
     * @param array $thread thread row
     * @return bool true when the thread was (re)classified
     */
    public static function triageOne(array $thread)
    {
        if (!MailManager_ProAiConfig_Model::isFeatureEnabled(MailManager_ProAiConfig_Model::FEATURE_TRIAGE)) {
            return false;
        }

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
        } catch (Exception $e) {
            return false;
        }
        $labels = self::getActiveLabels((int)$thread['mailboxid']);
        if (!$provider || !$labels) {
            return false;
        }
        // mail browsed in Spam / Trash is shown only — no second opinion on
        // what was thrown away (the background run skips these folders too)
        if (MailManager_ProThread_Model::isJunkOnly((int)$thread['threadid'])) {
            return false;
        }

        // Optimistic lock — a parallel cron batch must not classify it twice.
        // A previously failed thread gets another chance on an explicit open
        MailManager_ProInbox_Model::useUtf8mb4();
        if (!self::claimThread((int)$thread['threadid'], ['none', 'skipped', 'failed'])) {
            return false;
        }

        try {
            self::triageThread($provider, $labels, $thread);

            return true;
        } catch (MailManager_ProAiAccountException_Helper $e) {
            // not this mail's fault — it waits for the account to work again
            self::releaseThread((int)$thread['threadid']);

            return false;
        } catch (Throwable $e) {
            self::markFailed((int)$thread['threadid'], $e);

            return false;
        }
    }

    /**
     * Auto-create mode — creates the suggested record server-side (like the
     * Mail Converter, but AI-classified) and links it to the thread
     *
     * @param array $thread
     * @param array $message last inbound message row
     * @param string $module Leads|HelpDesk
     * @param array|null $senderMatch ['module', 'crmid'] CRM record matching the sender
     * @return array|null ['crmid', 'record_label'], null = fall back to suggestion
     */
    private static function createRecord(array $thread, array $message, $module, $senderMatch)
    {
        $adb = PearDatabase::getInstance();
        $mailboxResult = $adb->pquery(
            'SELECT owner_userid, email FROM df_mailmanager_mailbox WHERE mailboxid = ?',
            [$thread['mailboxid']]
        );
        if (!$adb->num_rows($mailboxResult)) {
            return null;
        }
        $mailbox = $adb->raw_query_result_rowdata($mailboxResult, 0);

        try {
            if ($module === 'Leads') {
                $prefills = MailManager_ProCreatePrefill_Model::getCreatePrefills($thread, $mailbox['email'], []);
                $fields = $prefills['Leads']['params'] ?? [];
                if (empty($fields['lastname'])) {
                    return null;
                }
            } else {
                $fields = array_filter([
                    'ticket_title' => (string)$thread['subject'] !== '' ? (string)$thread['subject'] : '(no subject)',
                    'description' => mb_substr(MailManager_ProMailText_Model::messageText($message['body_plain'], $message['body_html'] ?? ''), 0, 1000),
                    'ticketstatus' => 'Open',
                    'contact_id' => ($senderMatch['module'] ?? '') === 'Contacts' ? $senderMatch['crmid'] : null,
                    'parent_id' => ($senderMatch['module'] ?? '') === 'Accounts' ? $senderMatch['crmid'] : null,
                ]);
            }

            $record = Vtiger_Record_Model::getCleanInstance($module);
            foreach ($fields as $field => $value) {
                $record->set($field, $value);
            }
            $record->set('assigned_user_id', (int)$mailbox['owner_userid']);
            $record->getEntity()->recordSource = self::RECORD_SOURCE;
            $record->save();

            $crmid = (int)$record->getId();
            if (!$crmid) {
                return null;
            }
            MailManager_ProRelate_Model::link(
                (int)$thread['threadid'],
                $crmid,
                (int)$mailbox['owner_userid'],
                true,
                MailManager_ProHistory_Model::SOURCE_AI
            );

            return ['crmid' => $crmid, 'record_label' => decode_html((string)$record->getName())];
        } catch (Exception $e) {
            // Creation problems degrade gracefully to a suggestion banner
            return null;
        }
    }

    /**
     * Records the Auto-create mode created for the mailbox in the last 24 hours
     *
     * @param int $mailboxId
     * @return int
     */
    private static function countAutoCreated($mailboxId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT COUNT(DISTINCT ce.crmid) AS created FROM df_mailmanager_rel r
             INNER JOIN df_mailmanager_thread t ON t.threadid = r.threadid
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid
             WHERE t.mailboxid = ? AND ce.source = ? AND ce.createdtime >= ?',
            [$mailboxId, self::RECORD_SOURCE, date('Y-m-d H:i:s', time() - 86400)]
        );

        return (int)$adb->query_result($result, 0, 'created');
    }

    /**
     * A re-triage (new mail in the thread) rebuilds the payload — the log of
     * what was linked automatically and the steps the user skipped survive it
     *
     * @param array $thread thread row
     * @param array $payload new payload
     * @return array
     */
    private static function carryOverPayload(array $thread, array $payload)
    {
        $previous = json_decode((string)($thread['triage_payload'] ?? ''), true);
        foreach (['autolink', 'skipped_steps'] as $key) {
            if (!empty($previous[$key])) {
                $payload[$key] = $previous[$key];
            }
        }

        return $payload;
    }

    /**
     * @param array $label label row
     * @return bool the label leads to a CRM record (the AI reads the sender's details for it)
     */
    private static function isCrmModuleLabel(array $label)
    {
        $suggested = (string)$label['suggested_module'];

        return $suggested !== '' && strpos($suggested, 'action:') !== 0;
    }

    /**
     * "Not spam" outranks the sorting — a sender the user took out of Spam
     * gets no label that throws mail away, whoever picked it (the model, a
     * learned rule); the conversation stays unlabeled instead
     *
     * @param array $thread thread row
     * @param array $message newest inbound message
     * @param array|null $label the picked label row
     * @return array|null the label, null when it was a cleanup label for a trusted sender
     */
    private static function keepTrustedSender(array $thread, array $message, $label)
    {
        if ($label && strpos((string)($label['suggested_module'] ?? ''), 'action:') === 0
            && MailManager_ProSpamGuard_Model::isTrusted(
                (int)$thread['mailboxid'],
                (string)$message['from_email']
            )) {
            return null;
        }

        return $label;
    }

    /**
     * Labels a thread by a learned rule without asking the AI
     *
     * @param array $thread thread row
     * @param array $message newest inbound message
     * @param array|null $label the rule's label row, null for an exception (no label)
     * @param int $ruleId
     * @param bool $background the cron run — the label's automatic cleanup may move the mail
     * @return string log line
     */
    private static function applyRuleLabel(array $thread, array $message, $label, $ruleId, $background = false)
    {
        // the rule names the label — what the label DOES is the mailbox owner's setting
        $label = $label
            ? Settings_MailManager_Label_Model::applyMailboxOverride($label, (int)$thread['mailboxid'])
            : null;
        $label = self::keepTrustedSender($thread, $message, $label);
        $threadId = (int)$thread['threadid'];
        $suggested = (string)($label['suggested_module'] ?? '');
        $isCleanup = strpos($suggested, 'action:') === 0;
        $payload = self::carryOverPayload($thread, [
            'label' => $label['name'] ?? null,
            // mail the label sends to Spam / Delete is never urgent
            'priority' => $isCleanup ? 'low' : null,
            'from_email' => (string)$message['from_email'],
            'from_name' => (string)$message['from_name'],
        ]);
        if ($label) {
            $payload['label_rule'] = ['ruleid' => (int)$ruleId, 'labelid' => (int)$label['labelid']];
        }
        $state = 'labeled';
        if ($isCleanup) {
            $state = 'suggested';
            $payload['suggested_action'] = substr($suggested, strlen('action:'));
        }

        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_thread
             SET labelid = IFNULL(labelid, ?), priority = IFNULL(priority, ?), triage_state = ?,
                 triage_attempts = 0, triage_payload = ?, ai_topics = IFNULL(ai_topics, \'\')
             WHERE threadid = ?',
            [
                $label ? (int)$label['labelid'] : null, $payload['priority'], $state,
                json_encode($payload, JSON_UNESCAPED_UNICODE), $threadId,
            ]
        );
        MailManager_ProLabelRule_Model::recordApplied($ruleId);
        self::logLabel($thread, $label ? (int)$label['labelid'] : 0, MailManager_ProHistory_Model::SOURCE_RULE);
        $cleaned = $background
            ? MailManager_ProAutoCleanup_Model::run($thread, $label, MailManager_ProHistory_Model::SOURCE_RULE)
            : '';
        if ($cleaned !== '') {
            return 'AI triage: thread ' . $threadId . ' → ' . $label['name']
                . ' (learned rule, moved to ' . $cleaned . ' automatically)';
        }
        MailManager_ProAutoLink_Model::autoLink($threadId);

        return 'AI triage: thread ' . $threadId . ' → ' . ($label['name'] ?? 'no label (exception)')
            . ' (learned rule, no AI call)';
    }

    /**
     * @param int $mailboxId
     * @return array active label rows the mailbox works with (global + its owner's)
     */
    private static function getActiveLabels($mailboxId)
    {
        return Settings_MailManager_Label_Model::getForMailbox($mailboxId);
    }

    /**
     * @param int $threadId
     * @return bool
     */
    private static function threadHasLinks($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT relid FROM df_mailmanager_rel WHERE threadid = ? LIMIT 1', [$threadId]);

        return (bool)$adb->num_rows($result);
    }
}
