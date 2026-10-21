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
 * "Similar solved cases" layer. The AI normalizes every problem to canonical
 * English topic tags at WRITE time (threads during triage, closed tickets by
 * a cron batch) — the search at draft time is then a plain deterministic tag
 * overlap: no AI cost, synonym- and language-independent
 */
class MailManager_ProAiTopics_Model
{
    /** Closed tickets tagged per one AI call */
    private const TICKET_BATCH = 20;
    /** Batches per cron run — keeps the run inside its time budget */
    private const MAX_BATCHES = 5;
    /** Output budget of one batch call — 20 items of tags, reasoning models think from it too
     *  (the answer grows with the batch, an empty one costs the whole batch) */
    private const MAX_TOKENS = 10000;

    /** Cases offered to the draft prompt */
    private const MAX_REPLIES = 2;
    private const MAX_TICKETS = 2;
    /** Prompt budget per case */
    private const CASE_CHARS = 700;
    /** Text sample per item fed to the tagging batches — the subject alone
     * misses the real problem (users write "Question" and explain inside) */
    private const SNIPPET_CHARS = 700;

    /**
     * Shared tagging vocabulary — every tagging prompt (triage, ticket and
     * thread batches) MUST use the same guidance, otherwise related problems
     * end up with disjoint tags and the overlap search misses them
     */
    public const VOCAB_HINT = 'Return 4-6 broad reusable SINGLE-WORD canonical tags covering: '
        . 'the operation (upload, login, sync, import, save, print), the object (attachment, image, '
        . 'document, invoice, workflow, report), the technical area (storage, permissions, '
        . 'configuration, email, database) and the symptom (error, missing, slow, rejected). '
        . 'Fewer than 4 tags only when the text truly gives nothing more. '
        . 'Example: "cannot save an image to a record" and "file upload does not work" must both '
        . 'get: upload, attachment, storage, error.';

    /**
     * Canonical comma list from an AI topic_tags array
     *
     * @param mixed $tags parsed topic_tags (array of strings expected)
     * @return string 'tag1,tag2' capped to the column size, '' when unusable
     */
    public static function normalize($tags)
    {
        if (!is_array($tags)) {
            return '';
        }
        $clean = [];
        $push = function ($tag) use (&$clean) {
            if ($tag !== '' && mb_strlen($tag) <= 40 && count($clean) < 10 && !in_array($tag, $clean, true)) {
                $clean[] = $tag;
            }
        };
        foreach ($tags as $tag) {
            $tag = strtolower(trim(preg_replace('/[^a-z0-9\- ]/i', '', (string)$tag)));
            $tag = str_replace(' ', '-', $tag);
            $push($tag);
            // Compound tags also match through their words — the model keeps
            // emitting specifics like "image-upload" vs "file-upload" despite
            // the vocabulary hint; expanding both to "upload" makes the
            // overlap deterministic instead of prompt-dependent
            foreach (explode('-', $tag) as $word) {
                if (mb_strlen($word) >= 3) {
                    $push($word);
                }
            }
        }

        return mb_substr(implode(',', $clean), 0, 255);
    }

    /**
     * Cron batch — tags closed tickets that have no topics yet (portal and
     * phone tickets never met the mail triage), ~20 tickets per AI call
     *
     * @param int $deadline unix timestamp hard stop
     * @return array log lines
     */
    public static function tagClosedTickets($deadline)
    {
        $provider = self::getTagProvider();
        if (!$provider) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        MailManager_ProInbox_Model::useUtf8mb4();
        $log = [];

        for ($batch = 0; $batch < self::MAX_BATCHES && time() < $deadline; $batch++) {
            $result = $adb->pquery(
                "SELECT tt.ticketid, tt.ticket_title AS title, tt.solution
                 FROM vtiger_troubletickets tt
                 INNER JOIN vtiger_crmentity ce ON ce.crmid = tt.ticketid AND ce.deleted = 0
                 LEFT JOIN df_mailmanager_topics tp ON tp.crmid = tt.ticketid
                 WHERE tt.ticketstatus = 'Closed' AND tp.crmid IS NULL
                 ORDER BY tt.ticketid DESC LIMIT " . self::TICKET_BATCH,
                []
            );
            $count = $adb->num_rows($result);
            if (!$count) {
                break;
            }

            $lines = [];
            $ids = [];
            for ($i = 0; $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $ids[] = (int)$row['ticketid'];
                // description = the problem, solution = the fix — both carry
                // taggable topics (storage, permissions, ...)
                $text = mb_substr(trim(
                    strip_tags((string)self::getTicketDescription((int)$row['ticketid']))
                    . "\n" . trim(strip_tags((string)$row['solution']))
                ), 0, self::SNIPPET_CHARS);
                $lines[] = MailManager_ProAiGuard_Helper::wrap(
                    MailManager_ProAiGuard_Helper::SOURCE_CASE,
                    MailManager_ProAnonymizer_Helper::maskPermanently(
                        $row['ticketid'] . ': ' . $row['title'] . ($text !== '' ? ' — ' . $text : ''),
                        MailManager_ProAiDictionary_Model::getForRecords([(int)$row['ticketid']])
                    ),
                    (string)$row['ticketid']
                );
            }

            try {
                $completion = MailManager_ProAiClient_Model::complete(
                    $provider,
                    'topics',
                    0,
                    'For each support ticket below produce canonical lowercase English topic keywords '
                    . 'of the PROBLEM — always English regardless of the ticket language. ' . self::VOCAB_HINT
                    . ' Respond with a single JSON object only: {"<ticketid>": ["tag", ...], ...}',
                    implode("\n", $lines),
                    self::MAX_TOKENS,
                    $deadline
                );
                $parsed = MailManager_ProAiClient_Model::parseJsonObject($completion['text']);
            } catch (Exception $e) {
                $log[] = 'AI topics: batch failed (' . $e->getMessage() . ')';
                break;
            }

            // A broken answer (cut off, prose) must not store empty tags for
            // the whole batch — those rows would never be tagged again
            if (!is_array($parsed)) {
                $log[] = 'AI topics: unparseable answer for the ticket batch' . (!empty($completion['truncated']) ? ' (cut off at max_tokens)' : '');
                // stored as "no tags": the same batch would otherwise be asked
                // again by every run, for good, and nothing behind it ever tagged
                $parsed = [];
            }

            $now = date('Y-m-d H:i:s');
            foreach ($ids as $ticketId) {
                // an empty result is stored too — the ticket must not be
                // retried forever
                $topics = self::normalize($parsed[$ticketId] ?? ($parsed[(string)$ticketId] ?? []));
                $adb->pquery(
                    'INSERT INTO df_mailmanager_topics (crmid, topics, createdtime) VALUES (?,?,?)
                     ON DUPLICATE KEY UPDATE topics = ?',
                    [$ticketId, $topics, $now, $topics]
                );
            }
            $log[] = 'AI topics: tagged ' . count($ids) . ' closed ticket(s)';
        }

        return $log;
    }

    /**
     * Cron backfill — tags cached threads that predate the topics feature
     * (triage tags only NEWLY synced mail, history would stay invisible to
     * the similar-cases search forever), ~20 threads per AI call
     *
     * @param int $deadline unix timestamp hard stop
     * @return array log lines
     */
    public static function tagThreads($deadline)
    {
        $provider = self::getTagProvider();
        if (!$provider) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        MailManager_ProInbox_Model::useUtf8mb4();
        $log = [];
        $since = MailManager_ProAiConfig_Model::get('ai_triage_since');
        if ($since === '') {
            // the triage never ran — nothing is "handled" yet
            return [];
        }

        for ($batch = 0; $batch < self::MAX_BATCHES && time() < $deadline; $batch++) {
            // Only what the triage itself handled: mail since the triage was
            // switched on (the history guard protects the token budget here
            // too), sorted already, and not thrown away to Spam / Trash
            $result = $adb->pquery(
                "SELECT t.threadid, t.subject FROM df_mailmanager_thread t
                 WHERE t.ai_topics IS NULL AND t.last_message_at >= ?
                   AND t.triage_state IN ('labeled', 'suggested', 'accepted', 'created', 'dismissed')"
                   . MailManager_ProThread_Model::outsideJunkSql('t') . '
                 ORDER BY t.last_message_at DESC LIMIT ' . self::TICKET_BATCH,
                [$since]
            );
            $count = $adb->num_rows($result);
            if (!$count) {
                break;
            }

            $lines = [];
            $ids = [];
            for ($i = 0; $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $ids[] = (int)$row['threadid'];
                $snippet = self::getThreadSnippet((int)$row['threadid']);
                $line = $row['threadid'] . ': ' . $row['subject'] . ($snippet !== '' ? ' — ' . $snippet : '');
                // a batch of many conversations — the full level masks each with its own parties
                if (MailManager_ProAnonymizer_Helper::LEVEL_FULL === MailManager_ProAnonymizer_Helper::getLevel()) {
                    $line = MailManager_ProAnonymizer_Helper::maskPermanently(
                        $line,
                        MailManager_ProAiDictionary_Model::getForThread((int)$row['threadid'])
                    );
                }
                $lines[] = MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_MAIL, $line, (string)$row['threadid']);
            }

            try {
                $completion = MailManager_ProAiClient_Model::complete(
                    $provider,
                    'topics',
                    0,
                    'For each email conversation below produce canonical lowercase English topic '
                    . 'keywords of the PROBLEM — always English regardless of the mail language. '
                    . 'Newsletters and notifications may return an empty list. ' . self::VOCAB_HINT
                    . ' Respond with a single JSON object only: {"<id>": ["tag", ...], ...}',
                    implode("\n", $lines),
                    self::MAX_TOKENS,
                    $deadline
                );
                $parsed = MailManager_ProAiClient_Model::parseJsonObject($completion['text']);
            } catch (Exception $e) {
                $log[] = 'AI topics: thread batch failed (' . $e->getMessage() . ')';
                break;
            }

            // A broken answer (cut off, prose) must not store empty tags for
            // the whole batch — those rows would never be tagged again
            if (!is_array($parsed)) {
                $log[] = 'AI topics: unparseable answer for the thread batch' . (!empty($completion['truncated']) ? ' (cut off at max_tokens)' : '');
                // stored as "no tags": the same batch would otherwise be asked
                // again by every run, for good, and nothing behind it ever tagged
                $parsed = [];
            }

            foreach ($ids as $threadId) {
                // an empty result is stored too ('' != NULL) — the thread
                // must not be retried forever
                $topics = self::normalize($parsed[$threadId] ?? ($parsed[(string)$threadId] ?? []));
                $adb->pquery(
                    'UPDATE df_mailmanager_thread SET ai_topics = ? WHERE threadid = ?',
                    [$topics, $threadId]
                );
            }
            $log[] = 'AI topics: tagged ' . count($ids) . ' thread(s)';
        }

        return $log;
    }

    /**
     * @return MailManager_ProAiProvider_Helper|null provider when the
     * similar-cases feature is on and the assistant is configured
     */
    private static function getTagProvider()
    {
        // the knowledge needs the same topics (conversations, closed tickets)
        if (MailManager_ProAiConfig_Model::get('ai_feature_similar', '1') !== '1'
            && !MailManager_ProKnowledge_Model::isEnabled()) {
            return null;
        }

        try {
            return MailManager_ProAiConfig_Model::getProvider();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Opening text of a thread — the earliest inbound message when there is
     * one, the earliest message otherwise
     *
     * @param int $threadId
     * @return string
     */
    private static function getThreadSnippet($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT body_plain, body_html FROM df_mailmanager_message
             WHERE threadid = ?
             ORDER BY FIELD(direction, 'in', 'out'), sent_at ASC LIMIT 1",
            [$threadId]
        );

        if (!$adb->num_rows($result)) {
            return '';
        }

        $row = $adb->raw_query_result_rowdata($result, 0);

        // the visible text only (I16) — the stored plain text keeps hidden parts
        return mb_substr(self::cleanText(MailManager_ProAiGuard_Helper::visibleMailText(
            (string)$row['body_plain'],
            (string)$row['body_html']
        )), 0, self::SNIPPET_CHARS);
    }

    /**
     * Similar solved cases for the draft prompt: past sent replies of
     * threads sharing topics + closed tickets sharing topics (their
     * solution/comments). Deterministic — no AI call
     *
     * @param array $thread current thread row (ai_topics used)
     * @return string prompt section, '' when nothing found / disabled
     */
    public static function buildSimilarCasesPrompt(array $thread)
    {
        if (MailManager_ProAiConfig_Model::get('ai_feature_similar', '1') !== '1') {
            return '';
        }
        $topics = array_filter(explode(',', (string)($thread['ai_topics'] ?? '')));
        if (!$topics) {
            return '';
        }
        // one generic topic alone would match half the archive — but a
        // sparsely tagged thread (1-2 tags) cannot ever reach 2, there a
        // single shared tag has to be enough
        $minOverlap = count($topics) >= 3 ? 2 : 1;

        // Only what the drafting user may see — the draft is sent to a
        // customer, other people's mailboxes and hidden tickets must not leak
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailboxIds = array_map('intval', array_column(
            MailManager_ProInbox_Model::getMailboxesForUser($userId),
            'mailboxid'
        ));

        // the closed tickets reach the draft through the knowledge store
        // (distilled to problem — solution) when it uses them
        $cases = array_merge(
            self::findSimilarReplies($thread, $topics, $minOverlap, $mailboxIds),
            MailManager_ProKnowledge_Model::usesTickets() ? [] : self::findSimilarTickets($thread, $topics, $minOverlap)
        );
        if (!$cases) {
            return '';
        }

        $sections = [];
        foreach ($cases as $index => $case) {
            // other customers' conversations — their personal data never reaches a draft
            // (always the full level, whatever the AI settings say — I16)
            $sections[] = MailManager_ProAiGuard_Helper::wrap(
                MailManager_ProAiGuard_Helper::SOURCE_CASE,
                MailManager_ProAnonymizer_Helper::maskPermanently(
                    $case['label'] . ":\n" . mb_substr($case['text'], 0, self::CASE_CHARS),
                    $case['dictionary']
                ),
                'case ' . ($index + 1)
            );
        }

        return "\n\nSimilar solved cases from the past. When one of them clearly matches the "
            . 'current problem, base the reply on its PROVEN SOLUTION — recommend the same fix, '
            . 'steps and links instead of asking generic diagnostic questions. A positive customer '
            . 'reaction after a past reply means that advice really worked — lead with it. Ignore '
            . "cases that do not apply:\n\n"
            . implode("\n", $sections);
    }

    /**
     * Latest own replies of other threads with overlapping topics
     *
     * @param array $thread current thread row
     * @param array $topics current thread topics
     * @param int $minOverlap
     * @param array $mailboxIds mailboxes the drafting user may access
     * @return array of ['label', 'text', 'dictionary' => its parties for the masking]
     */
    private static function findSimilarReplies(array $thread, array $topics, $minOverlap, array $mailboxIds)
    {
        if (!$mailboxIds) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $conditions = [];
        foreach ($topics as $topic) {
            $conditions[] = 'ai_topics LIKE ?';
        }
        $params = array_merge(
            [(int)$thread['threadid']],
            $mailboxIds,
            array_map(function ($topic) {
                return '%' . $topic . '%';
            }, $topics)
        );
        $result = $adb->pquery(
            'SELECT threadid, ai_topics FROM df_mailmanager_thread
             WHERE threadid != ? AND mailboxid IN (' . generateQuestionMarks($mailboxIds) . ')
               AND ai_topics IS NOT NULL AND (' . implode(' OR ', $conditions) . ')
             ORDER BY last_message_at DESC LIMIT 50',
            $params
        );

        $candidates = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $overlap = count(array_intersect($topics, explode(',', (string)$row['ai_topics'])));
            if ($overlap >= $minOverlap) {
                $candidates[(int)$row['threadid']] = $overlap;
            }
        }
        arsort($candidates);

        $cases = [];
        foreach (array_keys($candidates) as $threadId) {
            $reply = $adb->pquery(
                "SELECT sent_at, body_plain FROM df_mailmanager_message
                 WHERE threadid = ? AND direction = 'out' AND is_draft = 0
                 ORDER BY sent_at DESC LIMIT 1",
                [$threadId]
            );
            if (!$adb->num_rows($reply)) {
                continue;
            }
            $replyRow = $adb->raw_query_result_rowdata($reply, 0);
            $text = self::cleanText((string)$replyRow['body_plain']);
            if (mb_strlen($text) < 60) {
                continue;
            }
            // The customer reaction AFTER the reply tells whether the advice
            // actually worked ("thanks, that fixed it") — the strongest
            // signal the archive has
            $text = mb_substr($text, 0, 500);
            $reaction = $adb->pquery(
                "SELECT body_plain FROM df_mailmanager_message
                 WHERE threadid = ? AND direction = 'in' AND sent_at > ?
                 ORDER BY sent_at DESC LIMIT 1",
                [$threadId, $replyRow['sent_at']]
            );
            if ($adb->num_rows($reaction)) {
                $reactionText = self::cleanText(
                    (string)$adb->raw_query_result_rowdata($reaction, 0)['body_plain']
                );
                if ($reactionText !== '') {
                    $text .= "\nCustomer reaction afterwards: " . mb_substr($reactionText, 0, 150);
                }
            }
            $cases[] = [
                'label' => 'Past reply to a similar request',
                'text' => $text,
                // the parties of that conversation are masked in it (I16)
                'dictionary' => MailManager_ProAiDictionary_Model::getForThread((int)$threadId),
            ];
            if (count($cases) >= self::MAX_REPLIES) {
                break;
            }
        }

        return $cases;
    }

    /**
     * Closed tickets with overlapping topics — linked tickets inherit the
     * thread topics, the rest carries its own row in the topics table.
     * The answer often lives in the comments, not only in Solution
     *
     * @param array $thread current thread row
     * @param array $topics current thread topics
     * @param int $minOverlap
     * @return array of ['label', 'text', 'dictionary' => its parties for the masking]
     */
    private static function findSimilarTickets(array $thread, array $topics, $minOverlap)
    {
        $adb = PearDatabase::getInstance();
        $conditions = [];
        $params = [];
        foreach ($topics as $topic) {
            $conditions[] = 'tp.topics LIKE ?';
            $params[] = '%' . $topic . '%';
        }
        $result = $adb->pquery(
            "SELECT tt.ticketid, tt.ticket_title AS title, tt.solution, tp.topics
             FROM df_mailmanager_topics tp
             INNER JOIN vtiger_troubletickets tt ON tt.ticketid = tp.crmid
             INNER JOIN vtiger_crmentity ce ON ce.crmid = tt.ticketid AND ce.deleted = 0
             WHERE tt.ticketstatus = 'Closed' AND (" . implode(' OR ', $conditions) . ')
             ORDER BY tt.ticketid DESC LIMIT 30',
            $params
        );

        $rows = [];
        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            if (count(array_intersect($topics, explode(',', (string)$row['topics']))) >= $minOverlap) {
                $rows[(int)$row['ticketid']] = $row;
            }
        }
        // tickets the drafting user may not see in the list never feed the draft (the list
        // access query — isPermitted() alone lets private tickets through, QA PT09359)
        $visible = array_flip(MailManager_ProAiAccess_Model::getVisibleIds('HelpDesk', array_keys($rows)));

        $cases = [];
        foreach ($rows as $ticketId => $row) {
            if (count($cases) >= self::MAX_TICKETS) {
                break;
            }
            if (!isset($visible[$ticketId])) {
                continue;
            }
            $parts = array_filter([
                self::cleanText(strip_tags((string)$row['solution'])),
                self::getTicketComments((int)$row['ticketid']),
            ]);
            if (!$parts) {
                continue;
            }
            $cases[] = [
                'label' => 'Closed ticket "' . decode_html((string)$row['title']) . '" — how it was solved',
                'text' => implode("\n", $parts),
                'dictionary' => MailManager_ProAiDictionary_Model::getForRecords([(int)$ticketId]),
            ];
        }

        return $cases;
    }

    /**
     * Prompt-safe text: HTML entities decoded (cached bodies carry them —
     * the AI copies "&iacute;" literally into the draft otherwise)
     *
     * @param string $text
     * @return string
     */
    private static function cleanText($text)
    {
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param int $ticketId
     * @return string ticket description (crmentity table)
     */
    private static function getTicketDescription($ticketId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT description FROM vtiger_crmentity WHERE crmid = ?', [$ticketId]);

        return $adb->num_rows($result) ? (string)$adb->query_result($result, 0, 'description') : '';
    }

    /**
     * Newest comments of a ticket — the fix is usually written there
     *
     * @param int $ticketId
     * @return string
     */
    public static function getTicketComments($ticketId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT mc.commentcontent FROM vtiger_modcomments mc
             INNER JOIN vtiger_crmentity ce ON ce.crmid = mc.modcommentsid AND ce.deleted = 0
             WHERE mc.related_to = ?
             ORDER BY mc.modcommentsid DESC LIMIT 3',
            [$ticketId]
        );

        $comments = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $comment = self::cleanText(strip_tags((string)$adb->query_result($result, $i, 'commentcontent')));
            if ($comment !== '') {
                $comments[] = $comment;
            }
        }

        // oldest of the taken ones first — reads like the resolution story
        return implode("\n", array_reverse($comments));
    }
}
