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
 * The history of the records a conversation is about (I20): the driving record of the
 * thread (ticket, project task, project, opportunity, lead), the project of a task and
 * other linked records of these modules — at most RECORD_LIMIT, the driving one first.
 * For each: the head (name, status, dates, assignee), the description, the solution of a
 * ticket and the whole comment history in time order. The AI prompts (draft, assistant,
 * summary, reply check) get it inside the untrusted wrappers (I16).
 *
 * The history fits the input budget of the task's agent: when it is too long, the newest
 * comments go whole up to half of the budget and the older ones are replaced by a summary
 * the fast agent writes once (task record_history) and df_mailmanager_record_summary keeps
 * until a comment, the description or the solution changes.
 */
class MailManager_ProRecordHistory_Model extends Core_DatabaseData_Model
{
    protected string $table = 'df_mailmanager_record_summary';
    protected string $tableId = 'summaryid';

    /** The AI settings switch (ai_feature_recordhistory) */
    public const SWITCH = 'ai_feature_recordhistory';

    /** The optional token cap of the history (ai_recordhistory_tokens, '' = by the agent) */
    public const TOKEN_CAP = 'ai_recordhistory_tokens';

    /** Usage-log feature and agent task of the summary of older comments (a fast task) */
    public const FEATURE = 'record_history';

    /** Comment types */
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_TEAM = 'team';
    public const TYPE_INTERNAL = 'internal';

    /** Records of a conversation the history covers, the driving one first */
    private const RECORD_LIMIT = 3;

    /** Share of the agent's input the history may take */
    private const BUDGET_SHARE = 0.6;

    /** Share of the agent's input the whole prompt may take — the rest is our instructions and the answer */
    private const PROMPT_SHARE = 0.9;

    /** The fewest characters a record gets in a prompt — less is no help */
    private const MIN_RECORD_CHARS = 1500;

    /** Stored summaries of a record and language kept (one per budget the tasks cut the history at) */
    private const SUMMARIES_KEPT = 3;

    /**
     * Output budget of the summary of older comments — a fast reasoning model (Haiku 5.5)
     * thinks within it too: 2500 ended empty (stop reason max_tokens) on 40 comments, 6000 on
     * 80; 16000 took 5.6k (40), 7.1k (80) and 3.1k (120, input capped)
     */
    private const SUMMARY_MAX_TOKENS = 16000;

    /** Characters of a comment the dedup fingerprint is taken from (words) */
    private const FINGERPRINT_WORDS = 12;

    /** A comment this short is never taken for a copy of a mail */
    private const FINGERPRINT_MIN_WORDS = 6;

    /** Status, date and text fields of the modules (field names of the CRM) */
    private const FIELDS = [
        'HelpDesk' => [
            'status' => 'ticketstatus',
            'dates' => ['createdtime', 'modifiedtime'],
            'texts' => ['description', 'solution'],
        ],
        'ProjectTask' => [
            'status' => 'projecttaskstatus',
            'dates' => ['startdate', 'enddate'],
            'texts' => ['description'],
        ],
        'Project' => [
            'status' => 'projectstatus',
            'dates' => ['startdate', 'targetenddate', 'actualenddate'],
            'texts' => ['description'],
        ],
        'Potentials' => [
            'status' => 'sales_stage',
            'dates' => ['closingdate'],
            'texts' => ['description'],
        ],
        'Leads' => [
            'status' => 'leadstatus',
            'dates' => ['createdtime'],
            'texts' => ['description'],
        ],
    ];

    /** Lines that start a signature or a mobile footer of a pasted mail */
    private const SIGNATURE_PATTERNS = [
        '/^-- ?$/m',
        '/^[ \t]*(Sent from my|Odoslané z|Odesláno z|Gesendet von|Von meinem)\b.*$/imu',
    ];

    /** The markup a comment or a description is written in (CKEditor, a pasted mail) */
    private const HTML_TAG = '/<\/?(p|br|div|span|b|i|u|strong|em|ul|ol|li|table|tr|td|th|blockquote|a|img|h[1-6]|font|hr|pre)\b[^>]*>/i';

    /** @var array "threadid:crmid" => cleaned records of this request (dedup depends on the thread) */
    private static array $records = [];

    /**
     * @return self table accessor of df_mailmanager_record_summary
     */
    public function getSummaryTable(): self
    {
        return $this->getTable($this->table, $this->tableId);
    }

    /**
     * @return bool the AI may use the records' history (the switch, on by default)
     */
    public static function isEnabled(): bool
    {
        return MailManager_ProAiConfig_Model::isEnabled() && '1' === MailManager_ProAiConfig_Model::get(self::SWITCH, '1');
    }

    /**
     * The history block of a prompt — '' when the switch is off, nothing is linked or the
     * user may see none of it.
     *
     * @param array  $thread    thread row
     * @param string $feature   usage-log feature of the call the block goes into (its agent sets the budget)
     * @param int    $usedChars characters the rest of the prompt already takes
     *
     * @return array ['prompt' => string, 'facts' => plain text the reply may rely on, 'records' => int,
     *               'tokens' => estimated tokens of the block, 'summarized' => int older comments in a summary]
     */
    public static function buildPrompt(array $thread, string $feature, int $usedChars = 0): array
    {
        $empty = ['prompt' => '', 'facts' => '', 'records' => 0, 'tokens' => 0, 'summarized' => 0];
        $threadId = (int)($thread['threadid'] ?? 0);

        if (!$threadId || !self::isEnabled()) {
            return $empty;
        }

        $records = self::getRecords($threadId);

        if (!$records) {
            return $empty;
        }

        $budget = self::getBudget($feature, $usedChars);

        // the rest of the prompt already fills the agent's input — no block, no summary call
        if ($budget < self::MIN_RECORD_CHARS) {
            return $empty;
        }

        $blocks = [];
        $facts = [];
        $summarized = 0;
        // every record gets its share; what a short one leaves goes to the next
        $left = $budget;

        foreach ($records as $index => $record) {
            $share = (int)floor($left / (count($records) - $index));

            // what the records before left is too little for another one
            if ($share < self::MIN_RECORD_CHARS) {
                break;
            }

            $block = self::buildRecordBlock($record, $share, $threadId);
            $left -= mb_strlen($block['text']);
            $summarized += $block['summarized'];
            $blocks[] = $block['text'];
            $facts[] = $block['facts'];
        }

        $prompt = "\n\nCRM RECORDS OF THIS CONVERSATION — their description, solution and comment history "
            . '(oldest first). "Customer comment" is communication with the customer (part of the conversation); '
            . '"Team comment" and "Internal note" are internal — facts only, never to be quoted to the customer.'
            . "\n\n" . implode("\n\n", $blocks);

        return [
            'prompt' => $prompt,
            'facts' => implode("\n", $facts),
            'records' => count($blocks),
            'tokens' => (int)ceil(mb_strlen($prompt) / MailManager_ProAiClient_Model::CHARS_PER_TOKEN),
            'summarized' => $summarized,
        ];
    }

    /**
     * What the history of a conversation is made of for the current user — the cached thread
     * summary is valid only for the same one (another user may see other records; a new
     * comment or a changed solution makes a new summary). No AI call.
     *
     * @param int $threadId
     *
     * @return string sha1, '' when the summary gets no history
     */
    public static function getContextHash(int $threadId): string
    {
        if (!$threadId || !self::isEnabled()) {
            return '';
        }

        $source = '';

        foreach (self::getRecords($threadId) as $record) {
            $source .= $record['crmid'] . '|' . implode("\n", $record['head']) . '|' . self::getSummaryHash($record, $record['comments']) . "\n";
        }

        return '' === $source ? '' : sha1($source);
    }

    /**
     * The rules of the draft prompt for the history block.
     *
     * @return string
     */
    public static function getDraftRules(): string
    {
        return ' The CRM records block holds the description, solution and comments of the records this '
            . 'conversation is about. Treat customer comments as part of the conversation. Use team comments '
            . 'and internal notes for facts only (cause, solution, dates, status): never quote them, never '
            . 'reveal internal names, evaluations or remarks to the customer, and never invent what is not there. '
            . 'When the older part of a record\'s history is a summary, rely on it as you would on the comments.';
    }

    /**
     * The plain text of the records' history a reply may rely on (dates and amounts of the
     * comments count as verified facts, I16) — not budgeted, never sent anywhere.
     *
     * @param int $threadId
     *
     * @return string
     */
    public static function getFactText(int $threadId): string
    {
        if (!$threadId || !self::isEnabled()) {
            return '';
        }

        $parts = [];

        foreach (self::getRecords($threadId) as $record) {
            foreach ($record['comments'] as $comment) {
                $parts[] = $comment['text'];
            }
        }

        return implode("\n", $parts);
    }

    /**
     * The records the history covers: the driving record first, the project of a task
     * after it, then the other linked records of the driving modules — only the ones the
     * user may see, at most RECORD_LIMIT.
     *
     * @param int $threadId
     *
     * @return array of ['crmid', 'module', 'label', 'head' => string[], 'texts' => label => text,
     *               'comments' => [['id', 'time', 'author', 'type', 'text']], 'changed' => string]
     */
    public static function getRecords(int $threadId): array
    {
        $candidates = self::getCandidates($threadId);
        $byModule = [];

        foreach ($candidates as $candidate) {
            $byModule[$candidate['module']][] = $candidate['crmid'];
        }

        $visible = [];

        foreach ($byModule as $module => $recordIds) {
            foreach (MailManager_ProAiAccess_Model::getVisibleIds($module, $recordIds) as $recordId) {
                $visible[$recordId] = true;
            }
        }

        $records = [];
        $skip = self::getConversationMails($threadId);

        foreach ($candidates as $candidate) {
            if (!isset($visible[$candidate['crmid']])) {
                continue;
            }

            $record = self::getRecord($threadId, $candidate['crmid'], $candidate['module'], $skip);

            if ($record) {
                $records[] = $record;
            }

            if (count($records) >= self::RECORD_LIMIT) {
                break;
            }
        }

        return $records;
    }

    /**
     * The linked records of the driving modules in their order: by rank of the module
     * (ProRelate DRIVING_MODULES), the newest link first; a task brings its project.
     *
     * @param int $threadId
     *
     * @return array of ['crmid', 'module']
     */
    private static function getCandidates(int $threadId): array
    {
        $ranked = [];

        foreach (MailManager_ProRelate_Model::getLinks($threadId) as $position => $link) {
            $rank = array_search($link['module'], MailManager_ProRelate_Model::DRIVING_MODULES, true);

            if (false !== $rank && vtlib_isModuleActive($link['module'])) {
                $ranked[] = ['crmid' => (int)$link['crmid'], 'module' => $link['module'], 'rank' => $rank, 'position' => $position];
            }
        }

        usort($ranked, function ($a, $b) {
            return [$a['rank'], -$a['position']] <=> [$b['rank'], -$b['position']];
        });

        $candidates = [];
        $seen = [];

        foreach ($ranked as $row) {
            $add = [['crmid' => $row['crmid'], 'module' => $row['module']]];

            if ('ProjectTask' === $row['module']) {
                $projectId = self::getTaskProjectId($row['crmid']);

                if ($projectId) {
                    $add[] = ['crmid' => $projectId, 'module' => 'Project'];
                }
            }

            foreach ($add as $candidate) {
                if (!isset($seen[$candidate['crmid']])) {
                    $seen[$candidate['crmid']] = true;
                    $candidates[] = $candidate;
                }
            }
        }

        return $candidates;
    }

    /**
     * @param int $taskId
     *
     * @return int the project of a project task (0 = none or deleted)
     */
    private static function getTaskProjectId(int $taskId): int
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT pt.projectid FROM vtiger_projecttask pt
             INNER JOIN vtiger_crmentity ce ON ce.crmid = pt.projectid AND ce.deleted = 0
             WHERE pt.projecttaskid = ?',
            [$taskId]
        );

        return $adb->num_rows($result) ? (int)$adb->query_result($result, 0, 'projectid') : 0;
    }

    /**
     * What the conversation prompt already carries: the CRM e-mails and the text
     * fingerprints of its newest messages (MailManager_ProAiAssist_Model::MAX_MESSAGES).
     *
     * @param int $threadId
     *
     * @return array ['emails' => crm_emailid => true, 'texts' => normalized texts]
     */
    private static function getConversationMails(int $threadId): array
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT crm_emailid, body_plain, body_html FROM df_mailmanager_message
             WHERE threadid = ? AND is_draft = 0 ORDER BY sent_at DESC LIMIT ' . MailManager_ProAiAssist_Model::MAX_MESSAGES,
            [$threadId]
        );
        $mails = ['emails' => [], 'texts' => []];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            if ((int)$row['crm_emailid']) {
                $mails['emails'][(int)$row['crm_emailid']] = true;
            }

            $mails['texts'][] = self::normalize(
                MailManager_ProAiGuard_Helper::visibleMailText((string)$row['body_plain'], (string)$row['body_html'])
            );
        }

        return $mails;
    }

    /**
     * One record with its comments, read once per request and thread.
     *
     * @param int    $threadId
     * @param int    $crmid
     * @param string $module
     * @param array  $skip getConversationMails()
     *
     * @return array|null
     */
    private static function getRecord(int $threadId, int $crmid, string $module, array $skip): ?array
    {
        $key = $threadId . ':' . $crmid;

        if (array_key_exists($key, self::$records)) {
            return self::$records[$key];
        }

        try {
            $recordModel = Vtiger_Record_Model::getInstanceById($crmid, $module);
        } catch (Exception $e) {
            return self::$records[$key] = null;
        }

        $moduleModel = $recordModel->getModule();
        $fields = self::FIELDS[$module];
        $head = [vtranslate('SINGLE_' . $module, $module) . ': ' . decode_html((string)$recordModel->getName())];

        foreach (array_merge([$fields['status']], $fields['dates']) as $fieldName) {
            $value = self::getFieldText($recordModel, $fieldName);
            $fieldModel = $moduleModel->getField($fieldName);

            if ('' !== $value && $fieldModel) {
                $head[] = vtranslate($fieldModel->get('label'), $module) . ': ' . $value;
            }
        }

        $owner = trim((string)getOwnerName((int)$recordModel->get('assigned_user_id')));

        if ('' !== $owner) {
            $head[] = vtranslate('LBL_ASSIGNED_TO', 'Core') . ': ' . $owner;
        }

        $texts = [];

        foreach ($fields['texts'] as $fieldName) {
            $fieldModel = $moduleModel->getField($fieldName);
            $text = self::cleanText((string)$recordModel->get($fieldName));

            if ('' !== $text && $fieldModel) {
                $texts[vtranslate($fieldModel->get('label'), $module)] = $text;
            }
        }

        return self::$records[$key] = [
            'crmid' => $crmid,
            'module' => $module,
            'head' => $head,
            'texts' => $texts,
            'comments' => self::getComments($crmid, $skip),
        ];
    }

    /**
     * The comments of a record in time order — none when the user may not read comments;
     * a comment that only repeats a mail of the conversation prompt is left out.
     *
     * @param int   $crmid
     * @param array $skip getConversationMails()
     *
     * @return array of ['id', 'time', 'changed', 'author', 'type', 'text']
     */
    private static function getComments(int $crmid, array $skip): array
    {
        // the CRM shows a user every comment of a record he sees (private = hidden from the portal only)
        if (!vtlib_isModuleActive('ModComments') || !Users_Privileges_Model::isPermitted('ModComments', 'DetailView')) {
            return [];
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT m.modcommentsid, m.commentcontent, m.customer, m.userid, m.is_private, m.related_email_id,
                    ce.createdtime, ce.modifiedtime
             FROM vtiger_modcomments m
             INNER JOIN vtiger_crmentity ce ON ce.crmid = m.modcommentsid AND ce.deleted = 0
             WHERE m.related_to = ?
             ORDER BY ce.createdtime, m.modcommentsid',
            [$crmid]
        );
        $comments = [];

        while ($row = $adb->fetchByAssoc($result, -1, false)) {
            if ((int)$row['related_email_id'] && isset($skip['emails'][(int)$row['related_email_id']])) {
                continue;
            }

            $text = self::cleanText((string)$row['commentcontent']);

            if ('' === $text || self::isConversationCopy($text, $skip['texts'])) {
                continue;
            }

            $comments[] = [
                'id' => (int)$row['modcommentsid'],
                'time' => (string)$row['createdtime'],
                'changed' => (string)$row['modifiedtime'],
                'author' => self::getAuthor((int)$row['customer'], (int)$row['userid']),
                'type' => self::getType((int)$row['customer'], (int)$row['is_private']),
                'text' => $text,
            ];
        }

        return $comments;
    }

    /**
     * customer = a portal customer wrote it; is_private = an internal note (hidden from
     * the portal); otherwise a comment of the team the customer sees.
     *
     * @param int $customer
     * @param int $isPrivate
     *
     * @return string TYPE_*
     */
    public static function getType(int $customer, int $isPrivate): string
    {
        if ($customer) {
            return self::TYPE_CUSTOMER;
        }

        return $isPrivate ? self::TYPE_INTERNAL : self::TYPE_TEAM;
    }

    /**
     * @param int $customer
     * @param int $userId
     *
     * @return string the name of who wrote the comment
     */
    private static function getAuthor(int $customer, int $userId): string
    {
        if ($customer) {
            return decode_html(trim((string)Vtiger_Functions::getCRMRecordLabel($customer)));
        }

        return $userId ? trim((string)getUserFullName($userId)) : '';
    }

    /**
     * A comment MailManager made of a mail (auto-comment) carries the mail's text — the
     * first words after its lead-in found in a mail of the conversation make it a copy.
     *
     * @param string   $text
     * @param string[] $mailTexts normalized
     *
     * @return bool
     */
    private static function isConversationCopy(string $text, array $mailTexts): bool
    {
        if (!$mailTexts) {
            return false;
        }

        // the lead-in ("E-mail from …", "Reply sent to … / Subject: …") is ours, the mail follows it
        $body = preg_split("/\n\s*\n/", $text, 2);
        $body = isset($body[1]) && '' !== trim($body[1]) ? $body[1] : $text;
        $words = array_slice(explode(' ', self::normalize($body)), 0, self::FINGERPRINT_WORDS);

        if (count($words) < self::FINGERPRINT_MIN_WORDS) {
            return false;
        }

        $fingerprint = implode(' ', $words);

        foreach ($mailTexts as $mailText) {
            if (false !== strpos($mailText, $fingerprint)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $text
     *
     * @return string lower-case words separated by single spaces
     */
    private static function normalize(string $text): string
    {
        $text = mb_strtolower($text);

        // a copy typed without diacritics is the same text
        if (class_exists('Normalizer')) {
            $text = (string)preg_replace('/\p{Mn}+/u', '', (string)Normalizer::normalize($text, Normalizer::FORM_D));
        }

        return trim((string)preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text));
    }

    /**
     * A record text or a comment as the reader sees it: HTML to text, the quoted history,
     * the signature and the mobile footer of a pasted mail removed; otherwise unchanged.
     *
     * @param string $value stored value (html-encoded by the CRM)
     *
     * @return string
     */
    public static function cleanText(string $value): string
    {
        $value = decode_html($value);

        if ('' === trim($value)) {
            return '';
        }

        // "Name <mail@example.com>" of a mail lead-in is no markup
        $isHtml = (bool)preg_match(self::HTML_TAG, $value);
        $text = MailManager_ProReplyText_Model::newText($isHtml ? $value : '', $isHtml ? '' : $value);

        foreach (self::SIGNATURE_PATTERNS as $pattern) {
            // only in the second half — a line like that at the top is the text itself
            if (preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE) && $match[0][1] > strlen($text) / 2) {
                $text = substr($text, 0, $match[0][1]);
            }
        }

        return trim((string)preg_replace("/\n{3,}/", "\n\n", $text));
    }

    /**
     * @param Vtiger_Record_Model $recordModel
     * @param string              $fieldName
     *
     * @return string the display value as plain text
     */
    private static function getFieldText(Vtiger_Record_Model $recordModel, string $fieldName): string
    {
        if ('' === (string)$recordModel->get($fieldName)) {
            return '';
        }

        try {
            $value = $recordModel->getDisplayValue($fieldName);
        } catch (Exception $e) {
            $value = $recordModel->get($fieldName);
        }

        return trim(html_entity_decode(strip_tags((string)$value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * The characters the history may take in a prompt of the feature: a share of its
     * agent's input, less what the rest of the prompt takes, within the admin's cap.
     *
     * @param string $feature
     * @param int    $usedChars
     *
     * @return int
     */
    public static function getBudget(string $feature, int $usedChars): int
    {
        $agent = MailManager_ProAiAgent_Model::getForFeature($feature);
        $inputChars = (int)($agent['max_input_tokens'] ?? 0) ?: MailManager_ProAiAgent_Model::DEFAULT_MAX_INPUT_TOKENS;
        $inputChars *= MailManager_ProAiClient_Model::CHARS_PER_TOKEN;
        $budget = min((int)($inputChars * self::BUDGET_SHARE), (int)($inputChars * self::PROMPT_SHARE) - $usedChars);
        $cap = (int)MailManager_ProAiConfig_Model::get(self::TOKEN_CAP);

        if ($cap > 0) {
            $budget = min($budget, $cap * MailManager_ProAiClient_Model::CHARS_PER_TOKEN);
        }

        return max(0, $budget);
    }

    /**
     * One record of the prompt: the head, the texts and the comments — the older comments
     * as their summary when all of them do not fit.
     *
     * @param array $record   getRecords() item
     * @param int   $budget   characters
     * @param int   $threadId the conversation the prompt is about
     *
     * @return array ['text' => wrapped block, 'facts' => plain text, 'summarized' => int]
     */
    private static function buildRecordBlock(array $record, int $budget, int $threadId): array
    {
        $id = vtranslate('SINGLE_' . $record['module'], $record['module']) . ' ' . $record['crmid'];
        $head = implode("\n", $record['head']);
        // the texts (a pasted mail as the description) take half of the share at most
        $textChars = (int)($budget / 2 / max(1, count($record['texts'])));

        foreach ($record['texts'] as $label => $text) {
            $head .= "\n\n" . $label . ":\n" . (mb_strlen($text) > $textChars
                ? mb_substr($text, 0, $textChars) . "\n[… the rest of this text does not fit the input limit.]"
                : $text);
        }

        $parts = [MailManager_ProAiGuard_Helper::wrap(MailManager_ProAiGuard_Helper::SOURCE_RECORD, $head, $id)];
        $facts = [$head];
        $used = mb_strlen($parts[0]);
        $comments = array_map(function ($comment) use ($id) {
            return [
                'comment' => $comment,
                'text' => self::wrapComment($comment, $id),
            ];
        }, $record['comments']);
        $total = $used + array_sum(array_map(function ($item) {
            return mb_strlen($item['text']) + 1;
        }, $comments));
        $summarized = 0;

        if ($total > $budget && $comments) {
            // the newest comments whole up to half of the budget, the older ones in the summary
            $keep = [];
            $kept = 0;

            foreach (array_reverse($comments) as $item) {
                if ($used + $kept + mb_strlen($item['text']) > $budget / 2) {
                    break;
                }

                $kept += mb_strlen($item['text']) + 1;
                array_unshift($keep, $item);
            }

            $older = array_slice($comments, 0, count($comments) - count($keep));
            $summary = mb_substr(self::getSummary($record, array_column($older, 'comment'), $threadId), 0, max(0, (int)($budget / 2)));
            $summarized = count($older);
            $parts[] = '' !== $summary
                ? MailManager_ProAiGuard_Helper::wrap(
                    MailManager_ProAiGuard_Helper::SOURCE_RECORD,
                    'SUMMARY of the ' . $summarized . ' older comments (written from them, not a quote):' . "\n" . $summary,
                    $id . ' older comments'
                )
                : '[' . $summarized . ' older comments of this record are left out — they do not fit the input limit.]';
            $comments = $keep;
        }

        foreach ($comments as $item) {
            $parts[] = $item['text'];
            $facts[] = $item['comment']['text'];
        }

        return ['text' => implode("\n", $parts), 'facts' => implode("\n", $facts), 'summarized' => $summarized];
    }

    /**
     * @param array  $comment
     * @param string $recordId the wrapper id of the record
     *
     * @return string the comment in its wrapper (a customer's comment is the customer's text)
     */
    private static function wrapComment(array $comment, string $recordId): string
    {
        $labels = [
            self::TYPE_CUSTOMER => 'Customer comment',
            self::TYPE_TEAM => 'Team comment (the customer sees it in the portal)',
            self::TYPE_INTERNAL => 'Internal note',
        ];
        $source = self::TYPE_CUSTOMER === $comment['type']
            ? MailManager_ProAiGuard_Helper::SOURCE_CUSTOMER_COMMENT
            : MailManager_ProAiGuard_Helper::SOURCE_RECORD;

        return MailManager_ProAiGuard_Helper::wrap(
            $source,
            $labels[$comment['type']] . ' — ' . $comment['time'] . ('' !== $comment['author'] ? ' — ' . $comment['author'] : '')
            . "\n" . $comment['text'],
            $recordId . ' comment ' . $comment['id']
        );
    }

    /**
     * The summary of the older comments of a record — the stored one while nothing of it
     * changed, otherwise one call of the fast agent writes it again.
     *
     * @param array $record
     * @param array $comments the older comments
     * @param int   $threadId the conversation it is written for (suspicion flag, anonymization dictionary)
     *
     * @return string '' when the AI cannot write it
     */
    private static function getSummary(array $record, array $comments, int $threadId): string
    {
        $language = MailManager_ProTranslation_Model::getUserLanguage();
        $hash = self::getSummaryHash($record, $comments);
        $stored = self::getStoredSummary($record['crmid'], $language, $hash);

        if ($stored) {
            return (string)$stored['summary'];
        }

        $provider = MailManager_ProAiConfig_Model::getProvider();

        if (!$provider) {
            return '';
        }

        $lines = [];

        foreach ($comments as $comment) {
            $lines[] = self::wrapComment($comment, (string)$record['crmid']);
        }

        $system = 'You summarize the older comment history of a CRM record (' . $record['module'] . ') for the '
            . 'colleague who continues the case. Keep every fact: what the customer reported or asked, the cause, '
            . 'what was done and decided, dates, versions, amounts, promises and who is to act. Say which facts '
            . 'came from the customer and which are internal. Write in '
            . MailManager_ProThreadLanguage_Model::getPromptName($language) . ', plain text, short bullet lines, '
            . 'oldest first, no introduction.';
        $user = "The record:\n" . MailManager_ProAiGuard_Helper::wrap(
            MailManager_ProAiGuard_Helper::SOURCE_RECORD,
            implode("\n", $record['head']) . "\n\n" . implode("\n\n", $record['texts'])
        ) . "\n\nThe older comments, oldest first:\n" . implode("\n", $lines);

        try {
            $result = MailManager_ProAiClient_Model::complete($provider, self::FEATURE, $threadId, $system, $user, self::SUMMARY_MAX_TOKENS);
        } catch (Exception $e) {
            // the client traced why (logs/mailmanager_pro_cron.log) — the older comments are left out
            return '';
        }

        $summary = trim(html_entity_decode((string)$result['text'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        // a summary cut at the output limit misses its newest part — never stored as the whole one
        if (!empty($result['truncated'])) {
            return '';
        }

        if ('' !== $summary) {
            self::saveSummary($record['crmid'], $language, $hash, (int)end($comments)['id'], $summary);
        }

        return $summary;
    }

    /**
     * @param array $record
     * @param array $comments
     *
     * @return string what the summary is made of — a new comment, an edited one, a changed
     *                description or solution changes it
     */
    public static function getSummaryHash(array $record, array $comments): string
    {
        $source = implode("\n", $record['texts']);

        foreach ($comments as $comment) {
            $source .= "\n" . $comment['id'] . '|' . $comment['changed'];
        }

        return sha1($source);
    }

    /**
     * @param int    $crmid
     * @param string $language
     * @param string $hash     getSummaryHash()
     *
     * @return array|null the stored summary of exactly these comments
     */
    public static function getStoredSummary(int $crmid, string $language, string $hash): ?array
    {
        if (!Vtiger_Utils::CheckTable('df_mailmanager_record_summary')) {
            return null;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_record_summary WHERE crmid = ? AND language = ? AND source_hash = ?',
            [$crmid, $language, $hash]
        );

        return $adb->num_rows($result) ? $adb->fetchByAssoc($result, -1, false) : null;
    }

    /**
     * @param int    $crmid
     * @param string $language
     * @param string $hash          getSummaryHash()
     * @param int    $lastCommentId the newest comment the summary covers
     * @param string $summary
     *
     * @return void
     */
    public static function saveSummary(int $crmid, string $language, string $hash, int $lastCommentId, string $summary): void
    {
        if (!Vtiger_Utils::CheckTable('df_mailmanager_record_summary')) {
            return;
        }

        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        $adb->pquery(
            'INSERT INTO df_mailmanager_record_summary (crmid, language, source_hash, last_commentid, summary, modifiedtime)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE last_commentid = VALUES(last_commentid), summary = VALUES(summary),
                modifiedtime = VALUES(modifiedtime)',
            [$crmid, $language, $hash, $lastCommentId, $summary, date('Y-m-d H:i:s')]
        );
        // the tasks cut the history at their own budgets — a few cuts are kept, the older go
        $adb->pquery(
            'DELETE FROM df_mailmanager_record_summary WHERE crmid = ? AND language = ? AND summaryid NOT IN (
                SELECT summaryid FROM (
                    SELECT summaryid FROM df_mailmanager_record_summary WHERE crmid = ? AND language = ?
                    ORDER BY modifiedtime DESC, summaryid DESC LIMIT ' . self::SUMMARIES_KEPT . '
                ) kept
             )',
            [$crmid, $language, $crmid, $language]
        );
    }
}
