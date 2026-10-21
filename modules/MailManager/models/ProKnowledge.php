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
 * Knowledge the AI answers from — ONE store of short pieces, prepared in the
 * background and searched at draft time without an AI call:
 * - company knowledge (the admin): files in the "Mail Manager Knowledge"
 *   document folder, the published FAQ, and the closed tickets distilled to
 *   "problem — proven solution";
 * - personal knowledge (each user): his files in the "Mail Manager Personal
 *   Knowledge" folder — used for his replies only, and they win over the
 *   company knowledge where the two disagree.
 * The pieces carry the same canonical topic tags as the conversations
 * (AiTopics), the draft takes the pieces sharing the most topics
 */
class MailManager_ProKnowledge_Model
{
    /** File types read as text */
    public const EXTENSIONS = ['md', 'markdown', 'txt', 'html', 'htm'];

    /** Document folders the knowledge files live in (created on the first upload) */
    public const FOLDER_GENERAL = 'Mail Manager Knowledge';
    public const FOLDER_PERSONAL = 'Mail Manager Personal Knowledge';

    public const SCOPE_GENERAL = 'general';
    public const SCOPE_PERSONAL = 'personal';

    /** Text read of one file */
    private const MAX_FILE_BYTES = 1048576;

    /** One piece of knowledge — a paragraph group under a heading */
    private const CHUNK_CHARS = 1500;

    /** Pieces of one file at most */
    private const MAX_CHUNKS = 200;

    /** Pieces tagged per AI call */
    private const TAG_BATCH = 15;

    /** Closed tickets distilled per AI call */
    private const TICKET_BATCH = 10;

    /** AI calls per cron run of each kind — keeps the run inside its time budget */
    private const MAX_BATCHES = 3;

    /** Output budget of one batch call — reasoning models think from it too; a batch of solved
     *  tickets writes a problem and a solution for each */
    private const MAX_TOKENS = 10000;

    /** Pieces handed to one draft */
    private const PROMPT_CHUNKS = 5;

    /**
     * @return bool the drafts use the knowledge
     */
    public static function isEnabled()
    {
        return MailManager_ProAiConfig_Model::get('ai_feature_knowledge', '1') === '1';
    }

    /**
     * @return bool the closed tickets are a knowledge source
     */
    public static function usesTickets()
    {
        return self::isEnabled() && MailManager_ProAiConfig_Model::get('ai_knowledge_tickets', '1') === '1';
    }

    /**
     * @param bool $personal
     * @param bool $create make the folder when it does not exist (a user is logged in)
     * @return int document folder id, 0 when there is none
     */
    public static function getFolderId($personal, $create = false)
    {
        $name = $personal ? self::FOLDER_PERSONAL : self::FOLDER_GENERAL;
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT folderid FROM vtiger_attachmentsfolder WHERE foldername = ?', [$name]);
        if ($adb->num_rows($result)) {
            return (int)$adb->query_result($result, 0, 'folderid');
        }
        if (!$create) {
            return 0;
        }

        $folder = Documents_Folder_Model::getInstance();
        $folder->set('foldername', $name);
        $folder->set('description', 'Mail Manager');
        $folder->save();

        return (int)$folder->getId();
    }

    /**
     * The knowledge files of a scope with their preparation state
     *
     * @param string $scope general | personal
     * @param int $userId whose personal files
     * @return array of ['id', 'title', 'filename', 'size', 'chunks', 'waiting', 'url']
     */
    public static function getFiles($scope, $userId)
    {
        $folderId = self::getFolderId($scope === self::SCOPE_PERSONAL);
        if (!$folderId) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT n.notesid, n.notes_title AS title, n.filename, n.filesize FROM vtiger_notes n
                INNER JOIN vtiger_crmentity ce ON ce.crmid = n.notesid AND ce.deleted = 0
                WHERE n.folderid = ?';
        $params = [$folderId];
        if ($scope === self::SCOPE_PERSONAL) {
            $sql .= ' AND ce.assigned_user_id = ?';
            $params[] = (int)$userId;
        }
        $result = $adb->pquery($sql . ' ORDER BY n.title', $params);

        $files = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $files[(int)$row['notesid']] = [
                'id' => (int)$row['notesid'],
                'title' => decode_html((string)$row['title']),
                'filename' => decode_html((string)$row['filename']),
                'size' => MailManager_ProMailFormat_Model::formatSize((int)$row['filesize']),
                'chunks' => 0,
                'waiting' => 0,
                'url' => 'index.php?module=Documents&view=Detail&record=' . (int)$row['notesid'],
            ];
        }
        if ($files) {
            $state = $adb->pquery(
                "SELECT source_id, COUNT(*) AS chunks, SUM(topics IS NULL) AS waiting
                 FROM df_mailmanager_knowledge
                 WHERE source_type = 'document' AND source_id IN (" . generateQuestionMarks($files) . ')
                 GROUP BY source_id',
                array_keys($files)
            );
            for ($i = 0, $stateCount = $adb->num_rows($state); $i < $stateCount; $i++) {
                $row = $adb->raw_query_result_rowdata($state, $i);
                $files[(int)$row['source_id']]['chunks'] = (int)$row['chunks'];
                $files[(int)$row['source_id']]['waiting'] = (int)$row['waiting'];
            }
        }

        return array_values($files);
    }

    /**
     * Adds the files of one picking to the knowledge.
     *
     * @param string $scope  general | personal
     * @param int    $userId
     * @param array  $files  the $_FILES entry of a multiple file input
     *
     * @return string '' when all were added, otherwise the error of the last failed file
     */
    public static function addFiles(string $scope, int $userId, array $files): string
    {
        $error = '';
        $names = (array)($files['name'] ?? []);

        foreach (array_keys($names) as $index) {
            $error = self::addFile($scope, $userId, [
                'name' => $files['name'][$index],
                'tmp_name' => $files['tmp_name'][$index],
                'error' => $files['error'][$index],
            ]) ?: $error;
        }

        return $error;
    }

    /**
     * A known reason of a failed upload is named, the rest is the page's "save failed".
     *
     * @param string $error error of addFiles() / deleteFile()
     *
     * @return string the message for the user, '' = none
     */
    public static function getErrorMessage(string $error): string
    {
        return 'LBL_KNOWLEDGE_BAD_TYPE' === $error ? vtranslate($error, 'Settings:MailManager') : '';
    }

    /**
     * Adds an uploaded file to the knowledge — a document in the folder of
     * the scope, owned by the uploader; its pieces are made right away (the
     * topics follow in the background)
     *
     * @param string $scope general | personal
     * @param int $userId
     * @param array $file one $_FILES entry
     * @return string '' when added, 'LBL_KNOWLEDGE_BAD_TYPE' or 'failed'
     */
    public static function addFile($scope, $userId, array $file)
    {
        $name = (string)($file['name'] ?? '');
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS, true)) {
            return 'LBL_KNOWLEDGE_BAD_TYPE';
        }
        if ((int)($file['error'] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string)$file['tmp_name'])) {
            return 'failed';
        }
        $content = (string)file_get_contents((string)$file['tmp_name'], false, null, 0, self::MAX_FILE_BYTES);

        $documentId = MailManager_ProDocumentFiler_Model::createStandaloneDocument(
            (int)$userId,
            $name,
            $extension === 'html' || $extension === 'htm' ? 'text/html' : 'text/plain',
            $content,
            self::getFolderId($scope === self::SCOPE_PERSONAL, true)
        );
        if (!$documentId) {
            return 'failed';
        }
        self::syncSources();

        return '';
    }

    /**
     * Removes a knowledge file — the document goes to the CRM recycle bin
     *
     * @param string $scope general | personal
     * @param int $userId
     * @param int $documentId
     * @return bool
     */
    public static function deleteFile($scope, $userId, $documentId)
    {
        $ids = array_column(self::getFiles($scope, $userId), 'id');
        if (!in_array((int)$documentId, $ids, true)) {
            return false;
        }
        Vtiger_Record_Model::getInstanceById((int)$documentId, 'Documents')->delete();
        self::deleteSource('document', (int)$documentId);

        return true;
    }

    /**
     * Cron — the store follows its sources: new / changed / removed files
     * and FAQ, the topics of new pieces, the closed tickets distilled
     *
     * @param int $deadline unix timestamp hard stop
     * @return array log lines
     */
    public static function refresh($deadline)
    {
        if (!self::isEnabled()) {
            return [];
        }
        MailManager_ProInbox_Model::useUtf8mb4();
        $log = self::syncSources();

        try {
            $provider = MailManager_ProAiConfig_Model::getProvider();
        } catch (Exception $e) {
            $provider = null;
        }
        if (!$provider) {
            return $log;
        }

        return array_merge($log, self::tagPieces($provider, $deadline), self::distillTickets($provider, $deadline));
    }

    /**
     * Makes the pieces of new and changed files and FAQ, drops those of
     * removed ones (no AI — the topics are added by tagPieces)
     *
     * @return array log lines
     */
    public static function syncSources()
    {
        $adb = PearDatabase::getInstance();
        $sources = [];

        if (MailManager_ProAiConfig_Model::get('ai_knowledge_faq', '1') === '1') {
            $result = $adb->pquery(
                "SELECT f.id, f.question, f.faq_answer AS answer FROM vtiger_faq f
                 INNER JOIN vtiger_crmentity ce ON ce.crmid = f.id AND ce.deleted = 0
                 WHERE f.faqstatus = 'Published'",
                []
            );
            for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $question = trim(MailManager_ProMailText_Model::htmlToText(decode_html((string)$row['question'])));
                $answer = trim(MailManager_ProMailText_Model::htmlToText(decode_html((string)$row['answer'])));
                if ($question === '' || $answer === '') {
                    continue;
                }
                $sources['faq:' . $row['id']] = [
                    'type' => 'faq', 'id' => (int)$row['id'], 'scope' => self::SCOPE_GENERAL, 'owner' => 0,
                    'title' => mb_substr($question, 0, 250), 'hash' => md5($question . "\n" . $answer),
                    'text' => function () use ($question, $answer) {
                        return [['title' => '', 'content' => 'Q: ' . $question . "\nA: " . $answer]];
                    },
                ];
            }
        }

        foreach ([false, true] as $personal) {
            $folderId = self::getFolderId($personal);
            if (!$folderId) {
                continue;
            }
            $result = $adb->pquery(
                "SELECT n.notesid, n.notes_title AS title, n.filename, n.filesize, ce.assigned_user_id, ce.modifiedtime FROM vtiger_notes n
                 INNER JOIN vtiger_crmentity ce ON ce.crmid = n.notesid AND ce.deleted = 0
                 WHERE n.folderid = ? AND n.filelocationtype = 'I'",
                [$folderId]
            );
            for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $documentId = (int)$row['notesid'];
                $title = decode_html((string)$row['title']);
                $filename = decode_html((string)$row['filename']);
                $sources['document:' . $documentId] = [
                    'type' => 'document', 'id' => $documentId,
                    'scope' => $personal ? self::SCOPE_PERSONAL : self::SCOPE_GENERAL,
                    'owner' => $personal ? (int)$row['assigned_user_id'] : 0,
                    'title' => mb_substr($title, 0, 250),
                    'hash' => md5(
                        $row['modifiedtime'] . '|' . $row['filesize'] . '|' . $row['assigned_user_id'] . '|' . $filename
                    ),
                    'text' => function () use ($documentId, $filename, $title) {
                        return self::chunk(self::readDocumentText($documentId, $filename), $title);
                    },
                ];
            }
        }

        $known = [];
        $result = $adb->pquery(
            "SELECT source_type, source_id, MAX(source_hash) AS hash FROM df_mailmanager_knowledge
             WHERE source_type IN ('faq', 'document') GROUP BY source_type, source_id",
            []
        );
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $known[$row['source_type'] . ':' . $row['source_id']] = (string)$row['hash'];
        }

        $changed = 0;
        foreach ($sources as $key => $source) {
            if (($known[$key] ?? null) === $source['hash']) {
                continue;
            }
            self::deleteSource($source['type'], $source['id']);
            $pieces = array_slice(call_user_func($source['text']), 0, self::MAX_CHUNKS);
            foreach ($pieces as $index => $piece) {
                self::insertPiece($source, $index, $piece['title'], $piece['content'], null);
            }
            if (!$pieces) {
                // an empty / unreadable file is remembered as such — not read again until it changes
                self::insertPiece($source, 0, '', '', '');
            }
            $changed++;
        }
        $removed = 0;
        foreach (array_diff_key($known, $sources) as $key => $hash) {
            [$type, $id] = explode(':', $key);
            self::deleteSource($type, (int)$id);
            $removed++;
        }

        return $changed || $removed ? ["AI knowledge: $changed source(s) prepared, $removed removed"] : [];
    }

    /**
     * Gives the new pieces their topic tags — the same vocabulary as the
     * conversations, so the draft finds them by plain tag overlap
     *
     * @param MailManager_ProAiProvider_Helper $provider
     * @param int $deadline
     * @return array log lines
     */
    private static function tagPieces($provider, $deadline)
    {
        $adb = PearDatabase::getInstance();
        $log = [];
        for ($batch = 0; $batch < self::MAX_BATCHES && time() < $deadline; $batch++) {
            $result = $adb->pquery(
                'SELECT knowledgeid, source_title, title, content FROM df_mailmanager_knowledge
                 WHERE topics IS NULL ORDER BY knowledgeid LIMIT ' . self::TAG_BATCH,
                []
            );
            $count = $adb->num_rows($result);
            if (!$count) {
                break;
            }
            $ids = [];
            $lines = [];
            for ($i = 0; $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $ids[] = (int)$row['knowledgeid'];
                $lines[] = $row['knowledgeid'] . ': ' . trim($row['source_title'] . ' ' . $row['title'])
                    . ' — ' . mb_substr(preg_replace('/\s+/u', ' ', (string)$row['content']), 0, 700);
            }

            try {
                $completion = MailManager_ProAiClient_Model::complete(
                    $provider,
                    'knowledge',
                    0,
                    'For each piece of support knowledge below produce canonical lowercase English topic '
                    . 'keywords of WHAT IT IS ABOUT — always English regardless of the text language. '
                    . MailManager_ProAiTopics_Model::VOCAB_HINT
                    . ' Respond with a single JSON object only: {"<id>": ["tag", ...], ...}',
                    implode("\n", $lines),
                    self::MAX_TOKENS,
                    $deadline
                );
                $parsed = MailManager_ProAiClient_Model::parseJsonObject($completion['text']);
            } catch (Exception $e) {
                $log[] = 'AI knowledge: tagging failed (' . $e->getMessage() . ')';
                break;
            }
            // a broken answer stores "no topics" — the batch must not block the queue for good
            $parsed = is_array($parsed) ? $parsed : [];
            foreach ($ids as $id) {
                $topics = MailManager_ProAiTopics_Model::normalize($parsed[$id] ?? ($parsed[(string)$id] ?? []));
                $adb->pquery(
                    'UPDATE df_mailmanager_knowledge SET topics = ? WHERE knowledgeid = ?',
                    [$topics, $id]
                );
            }
            $log[] = 'AI knowledge: tagged ' . count($ids) . ' piece(s)';
        }

        return $log;
    }

    /**
     * Turns closed tickets into knowledge — "problem — proven solution" in a
     * few sentences, so a draft gets the answer, not the whole ticket. A
     * ticket without a usable solution is remembered as such. The topics are
     * those the ticket already got (AiTopics::tagClosedTickets)
     *
     * @param MailManager_ProAiProvider_Helper $provider
     * @param int $deadline
     * @return array log lines
     */
    private static function distillTickets($provider, $deadline)
    {
        $adb = PearDatabase::getInstance();
        // a reopened, deleted or no longer closed ticket is no proven solution
        $adb->pquery(
            "DELETE k FROM df_mailmanager_knowledge k
             LEFT JOIN vtiger_troubletickets tt ON tt.ticketid = k.source_id
             LEFT JOIN vtiger_crmentity ce ON ce.crmid = k.source_id
             WHERE k.source_type = 'ticket' AND (tt.ticketid IS NULL OR tt.ticketstatus <> 'Closed' OR ce.deleted = 1)",
            []
        );
        if (!self::usesTickets()) {
            return [];
        }

        $log = [];
        for ($batch = 0; $batch < self::MAX_BATCHES && time() < $deadline; $batch++) {
            $result = $adb->pquery(
                "SELECT tt.ticketid, tt.ticket_title AS title, tt.solution, tp.topics, ce.description
                 FROM vtiger_troubletickets tt
                 INNER JOIN vtiger_crmentity ce ON ce.crmid = tt.ticketid AND ce.deleted = 0
                 INNER JOIN df_mailmanager_topics tp ON tp.crmid = tt.ticketid AND tp.topics <> ''
                 LEFT JOIN df_mailmanager_knowledge k ON k.source_type = 'ticket' AND k.source_id = tt.ticketid
                 WHERE tt.ticketstatus = 'Closed' AND k.knowledgeid IS NULL
                 ORDER BY tt.ticketid DESC LIMIT " . self::TICKET_BATCH,
                []
            );
            $count = $adb->num_rows($result);
            if (!$count) {
                break;
            }
            $tickets = [];
            $lines = [];
            for ($i = 0; $i < $count; $i++) {
                $row = $adb->raw_query_result_rowdata($result, $i);
                $ticketId = (int)$row['ticketid'];
                $tickets[$ticketId] = $row;
                $toText = function ($html) {
                    return trim(MailManager_ProMailText_Model::htmlToText(decode_html((string)$html)));
                };
                $text = trim(implode("\n", array_filter([
                    'Problem: ' . $toText($row['description']),
                    'Solution: ' . $toText($row['solution']),
                    MailManager_ProAiTopics_Model::getTicketComments($ticketId),
                ])));
                // another customer's case: masked for good on the full level, so the stored
                // knowledge holds no personal data either (I16)
                $lines[] = MailManager_ProAiGuard_Helper::wrap(
                    MailManager_ProAiGuard_Helper::SOURCE_CASE,
                    MailManager_ProAnonymizer_Helper::maskPermanently(
                        '### ' . $ticketId . ': ' . decode_html((string)$row['title']) . "\n"
                        . mb_substr(preg_replace('/[ \t]+/u', ' ', $text), 0, 1500),
                        MailManager_ProAiDictionary_Model::getForRecords([$ticketId])
                    ),
                    (string)$ticketId
                );
            }

            try {
                $completion = MailManager_ProAiClient_Model::complete(
                    $provider,
                    'knowledge',
                    0,
                    'For each closed support ticket below write down the KNOWLEDGE it holds: the problem '
                    . 'in one sentence, then the proven solution — the concrete steps, settings or links '
                    . 'that solved it. At most 80 words, in the language of the ticket, no names of '
                    . 'customers or people. Use null when the ticket holds no usable solution (no answer, '
                    . 'closed without a fix, a duplicate, only "thanks"). Respond with a single JSON '
                    . 'object only: {"<ticketid>": "<problem — solution>" or null, ...}',
                    implode("\n\n", $lines),
                    self::MAX_TOKENS,
                    $deadline
                );
                $parsed = MailManager_ProAiClient_Model::parseJsonObject($completion['text']);
            } catch (Exception $e) {
                $log[] = 'AI knowledge: ticket batch failed (' . $e->getMessage() . ')';
                break;
            }
            $parsed = is_array($parsed) ? $parsed : [];

            $kept = 0;
            foreach ($tickets as $ticketId => $row) {
                $content = trim((string)($parsed[$ticketId] ?? ($parsed[(string)$ticketId] ?? '')));
                $source = [
                    'type' => 'ticket', 'id' => $ticketId, 'scope' => self::SCOPE_GENERAL, 'owner' => 0,
                    'title' => mb_substr(decode_html((string)$row['title']), 0, 250), 'hash' => '',
                ];
                // no solution: stored empty, never asked again
                self::insertPiece($source, 0, '', $content, $content !== '' ? (string)$row['topics'] : '');
                $kept += $content !== '' ? 1 : 0;
            }
            $log[] = 'AI knowledge: ' . count($tickets) . ' closed ticket(s) read, ' . $kept . ' kept';
        }

        return $log;
    }

    /**
     * The knowledge for one draft — the pieces sharing the most topics with
     * the conversation; the writer's own pieces first and preferred
     *
     * @param array $thread thread row (ai_topics)
     * @param int $userId who drafts
     * @return array ['prompt' => string, 'sources' => [['label', 'personal']]]
     */
    public static function buildPrompt(array $thread, $userId)
    {
        $empty = ['prompt' => '', 'sources' => []];
        $topics = array_values(array_filter(explode(',', (string)($thread['ai_topics'] ?? ''))));
        if (!self::isEnabled() || !$topics) {
            return $empty;
        }
        // one generic topic alone would match half the store (see AiTopics)
        $minOverlap = count($topics) >= 3 ? 2 : 1;

        $conditions = [];
        $params = [(int)$userId];
        foreach ($topics as $topic) {
            $conditions[] = 'topics LIKE ?';
            $params[] = '%' . $topic . '%';
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT knowledgeid, source_type, source_id, scope, source_title, title, content, topics
             FROM df_mailmanager_knowledge
             WHERE content <> '' AND topics <> ''
               AND (scope = 'general' OR (scope = 'personal' AND owner_userid = ?))
               AND (" . implode(' OR ', $conditions) . ') LIMIT 300',
            $params
        );

        $pieces = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $overlap = count(array_intersect($topics, explode(',', (string)$row['topics'])));
            if ($overlap < $minOverlap) {
                continue;
            }
            $row['score'] = $overlap + ($row['scope'] === self::SCOPE_PERSONAL ? 0.5 : 0);
            $pieces[] = $row;
        }
        usort($pieces, function ($a, $b) {
            return [$b['score'], (int)$b['knowledgeid']] <=> [$a['score'], (int)$a['knowledgeid']];
        });

        // a ticket the drafting user may not see in the list never feeds the draft (the list
        // access query — isPermitted() alone lets private tickets through, QA PT09359)
        $ticketIds = [];
        foreach ($pieces as $piece) {
            if ($piece['source_type'] === 'ticket') {
                $ticketIds[] = (int)$piece['source_id'];
            }
        }
        $visibleTickets = array_flip(MailManager_ProAiAccess_Model::getVisibleIds('HelpDesk', $ticketIds));

        $personal = [];
        $general = [];
        $sources = [];
        foreach ($pieces as $piece) {
            if (count($personal) + count($general) >= self::PROMPT_CHUNKS) {
                break;
            }
            $isTicket = $piece['source_type'] === 'ticket';
            if ($isTicket && !isset($visibleTickets[(int)$piece['source_id']])) {
                continue;
            }
            $label = trim($piece['source_title'] . ($piece['title'] !== '' ? ' — ' . $piece['title'] : ''));
            $section = $label . ":\n" . mb_substr((string)$piece['content'], 0, self::CHUNK_CHARS);

            // a solved ticket is another customer's case — its personal data never reaches a
            // draft (always the full level, the parties of that ticket — I16)
            if ($isTicket) {
                $section = MailManager_ProAnonymizer_Helper::maskPermanently(
                    $section,
                    MailManager_ProAiDictionary_Model::getForRecords([(int)$piece['source_id']])
                );
            }
            // documents and FAQ are third-party text too (anyone may have written them)
            $section = MailManager_ProAiGuard_Helper::wrap(
                $isTicket ? MailManager_ProAiGuard_Helper::SOURCE_CASE : MailManager_ProAiGuard_Helper::SOURCE_KNOWLEDGE,
                $section
            );
            if ($piece['scope'] === self::SCOPE_PERSONAL) {
                $personal[] = $section;
            } else {
                $general[] = $section;
            }
            $sources[$piece['source_type'] . ':' . $piece['source_id']] = [
                'label' => (string)$piece['source_title'],
                'personal' => $piece['scope'] === self::SCOPE_PERSONAL,
            ];
        }
        if (!$personal && !$general) {
            return $empty;
        }

        $prompt = "\n\nKnowledge to answer from. Use it ONLY where it answers what the customer asks — "
            . 'never invent anything beyond it, and ignore pieces that do not apply. Where the writer\'s '
            . 'own notes and the company knowledge disagree, the WRITER\'S OWN NOTES are right.';
        if ($personal) {
            $prompt .= "\n\nThe writer's own notes:\n\n" . implode("\n", $personal);
        }
        if ($general) {
            $prompt .= "\n\nCompany knowledge (documents, FAQ, solved tickets):\n\n" . implode("\n", $general);
        }

        return ['prompt' => $prompt, 'sources' => array_values($sources)];
    }

    /**
     * Splits a text into pieces under its headings, paragraphs kept whole
     * where they fit
     *
     * @param string $text
     * @param string $documentTitle
     * @return array of ['title' => heading, 'content']
     */
    private static function chunk($text, $documentTitle)
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", (string)$text));
        if ($text === '') {
            return [];
        }
        $pieces = [];
        foreach (preg_split('/^(?=#{1,6}\s)/mu', $text) as $section) {
            $section = trim($section);
            if ($section === '') {
                continue;
            }
            $heading = '';
            if (preg_match('/^#{1,6}\s+(.+)$/mu', strtok($section, "\n"), $match)) {
                $heading = mb_substr(trim($match[1]), 0, 200);
            }
            $current = '';
            foreach (preg_split("/\n{2,}/u", $section) as $paragraph) {
                $paragraph = trim($paragraph);
                // a paragraph longer than a piece is cut on its own
                while (mb_strlen($paragraph) > self::CHUNK_CHARS) {
                    if ($current !== '') {
                        $pieces[] = ['title' => $heading, 'content' => $current];
                        $current = '';
                    }
                    $pieces[] = ['title' => $heading, 'content' => mb_substr($paragraph, 0, self::CHUNK_CHARS)];
                    $paragraph = mb_substr($paragraph, self::CHUNK_CHARS);
                }
                if ($current !== '' && mb_strlen($current) + mb_strlen($paragraph) + 2 > self::CHUNK_CHARS) {
                    $pieces[] = ['title' => $heading, 'content' => $current];
                    $current = '';
                }
                $current = $current === '' ? $paragraph : $current . "\n\n" . $paragraph;
            }
            if (trim($current) !== '') {
                $pieces[] = ['title' => $heading, 'content' => $current];
            }
        }

        return $pieces;
    }

    /**
     * @param int $documentId
     * @param string $filename
     * @return string the text of a stored knowledge file, '' when unreadable
     */
    private static function readDocumentText($documentId, $filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS, true)) {
            return '';
        }
        $row = Vtiger_Record_Model::getInstanceById((int)$documentId, 'Documents')->getFileDetails();

        if (empty($row['attachmentsid'])) {
            return '';
        }

        $stored = (string)($row['storedname'] ?? '') !== ''
            ? $row['storedname']
            : decode_html((string)$row['name']);
        $path = $row['path'] . $row['attachmentsid'] . '_' . $stored;
        if (!is_file($path)) {
            return '';
        }
        $text = (string)file_get_contents($path, false, null, 0, self::MAX_FILE_BYTES);
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1250');
        }

        return in_array($extension, ['html', 'htm'], true)
            ? MailManager_ProMailText_Model::htmlToText($text)
            : $text;
    }

    /**
     * @param array $source ['type', 'id', 'scope', 'owner', 'title', 'hash']
     * @param int $index
     * @param string $title heading of the piece
     * @param string $content
     * @param string|null $topics null = to be tagged
     * @return void
     */
    private static function insertPiece(array $source, $index, $title, $content, $topics)
    {
        PearDatabase::getInstance()->pquery(
            'INSERT INTO df_mailmanager_knowledge
                (source_type, source_id, scope, owner_userid, source_title, title, chunk_no, content, topics,
                 source_hash, createdtime)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [
                $source['type'], $source['id'], $source['scope'], (int)$source['owner'], (string)$source['title'],
                mb_substr((string)$title, 0, 255), (int)$index, (string)$content, $topics, (string)$source['hash'],
                date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * @param string $type faq | document | ticket
     * @param int $id
     * @return void
     */
    private static function deleteSource($type, $id)
    {
        PearDatabase::getInstance()->pquery(
            'DELETE FROM df_mailmanager_knowledge WHERE source_type = ? AND source_id = ?',
            [$type, (int)$id]
        );
    }
}
