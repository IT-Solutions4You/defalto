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
 * Label learning — deterministic rules learned from the user's manual label
 * corrections, applied by the triage before the AI is asked.
 *
 * A correction remembers the sender together with the subject pattern
 * (automated mail repeats its subject); the address alone is not enough —
 * a colleague sends private and work mail from the same address. A rule
 * widens to the whole sender after WIDEN_HITS corrected subjects of that
 * sender, to the whole domain after WIDEN_HITS corrected addresses of that
 * domain — never for our own domains, a domain rule never for freemail.
 * Rules are per mailbox: the most specific one wins. Removing a label a
 * rule set stores an exception (subject rule without a label) that
 * outranks the broader rules.
 */
class MailManager_ProLabelRule_Model
{
    public const TYPE_SUBJECT = 'subject';
    public const TYPE_SENDER = 'sender';
    public const TYPE_DOMAIN = 'domain';

    /** Corrected subjects / addresses with the same label before a rule widens */
    public const WIDEN_HITS = 3;

    /** labelid of an exception rule — such mail stays without a label */
    public const NO_LABEL = 0;

    /** Unlabeled conversations of a sender a fresh rule sends back to the triage at most */
    private const REQUEUE_LIMIT = 100;

    /** Earlier decisions handed to the AI as examples with one mail (getExamples) */
    public const MAX_EXAMPLES = 15;

    /** Reply / forward prefixes of the common mail clients (EN, DE, SK/CZ, Nordic, FR/ES) */
    private const REPLY_PREFIX = '/^(\s*(re|fw|fwd|aw|wg|odp|sv|vs|tr|rv)\s*(\[\d+\])?\s*:\s*)+/iu';

    /** @var string[]|null */
    private static $ownDomains = null;

    /**
     * Learning signal — the user set a thread's label by hand. A removed
     * label becomes an exception when a rule would label such mail again,
     * otherwise the sender + subject rule is just forgotten
     *
     * @param array $mailbox mailbox row
     * @param array $thread thread row
     * @param int $labelId new label, 0 = label removed
     * @return void
     */
    public static function learn(array $mailbox, array $thread, $labelId)
    {
        // subjects with emoji must give the same key as at triage time (utf8mb4)
        MailManager_ProInbox_Model::useUtf8mb4();
        self::clearRuleMark($thread);
        $message = self::getLatestInbound((int)$thread['threadid']);
        if (!$message) {
            return;
        }
        $fromEmail = strtolower(trim((string)$message['from_email']));
        if ($fromEmail === '' || strcasecmp($fromEmail, (string)$mailbox['email']) === 0) {
            return;
        }
        $mailboxId = (int)$mailbox['mailboxid'];
        $domain = self::domainOf($fromEmail);
        $subjectKey = self::subjectKey((string)$message['subject']);

        if ($subjectKey !== '') {
            if ($labelId) {
                self::saveRule($mailboxId, (int)$labelId, self::TYPE_SUBJECT, $fromEmail, $domain, $subjectKey);
            } else {
                self::forget($mailboxId, $fromEmail, $domain, $subjectKey, (string)$message['subject']);
            }
        }

        self::widen($mailboxId, $fromEmail, $domain);
        if ($labelId) {
            self::requeueUnlabeled($mailboxId, $fromEmail, (int)$thread['threadid']);
        }
    }

    /**
     * The rule just learned holds for the mail that is already there too —
     * the sender's other conversations still without a label go back to the
     * triage queue; its next run labels them by the rule (no AI call)
     *
     * @param int $mailboxId
     * @param string $fromEmail
     * @param int $exceptThreadId the conversation the user labeled himself
     * @return void
     */
    private static function requeueUnlabeled($mailboxId, $fromEmail, $exceptThreadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT t.threadid,
                    (SELECT m.subject FROM df_mailmanager_message m
                     WHERE m.threadid = t.threadid AND m.direction = 'in'
                     ORDER BY m.sent_at DESC LIMIT 1) AS subject
             FROM df_mailmanager_thread t
             WHERE t.mailboxid = ? AND t.labelid IS NULL AND t.triage_state = 'labeled' AND t.threadid <> ?
               AND (SELECT m.from_email FROM df_mailmanager_message m
                    WHERE m.threadid = t.threadid AND m.direction = 'in'
                    ORDER BY m.sent_at DESC LIMIT 1) = ?
             ORDER BY t.last_message_at DESC LIMIT " . self::REQUEUE_LIMIT,
            [$mailboxId, $exceptThreadId, $fromEmail]
        );

        $threadIds = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $rule = self::match($mailboxId, $fromEmail, (string)$row['subject']);
            if ($rule && (int)$rule['labelid'] !== self::NO_LABEL && self::sortsWithoutAi((int)$rule['labelid'])) {
                $threadIds[] = (int)$row['threadid'];
            }
        }
        if ($threadIds) {
            $adb->pquery(
                "UPDATE df_mailmanager_thread SET triage_state = 'none'
                 WHERE threadid IN (" . generateQuestionMarks($threadIds) . ')',
                $threadIds
            );
        }
    }

    /**
     * The label is the user's own choice now — the conversation stops
     * showing it as set by a rule
     *
     * @param array $thread thread row
     * @return void
     */
    private static function clearRuleMark(array $thread)
    {
        $payload = json_decode((string)($thread['triage_payload'] ?? ''), true);
        if (!is_array($payload) || !isset($payload['label_rule'])) {
            return;
        }
        unset($payload['label_rule']);
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_thread SET triage_payload = ? WHERE threadid = ?',
            [json_encode($payload, JSON_UNESCAPED_UNICODE), $thread['threadid']]
        );
    }

    /**
     * A removed label — drops the sender + subject rule; when a broader rule
     * would still label such mail, an exception takes its place
     *
     * @param int $mailboxId
     * @param string $fromEmail
     * @param string $domain
     * @param string $subjectKey
     * @param string $subject
     * @return void
     */
    private static function forget($mailboxId, $fromEmail, $domain, $subjectKey, $subject)
    {
        PearDatabase::getInstance()->pquery(
            'DELETE FROM df_mailmanager_labelrule
             WHERE mailboxid = ? AND rule_type = ? AND from_email = ? AND subject_key = ?',
            [$mailboxId, self::TYPE_SUBJECT, $fromEmail, $subjectKey]
        );
        $broader = self::match($mailboxId, $fromEmail, $subject);
        if ($broader && (int)$broader['labelid'] !== self::NO_LABEL) {
            self::saveRule($mailboxId, self::NO_LABEL, self::TYPE_SUBJECT, $fromEmail, $domain, $subjectKey);
        }
    }

    /**
     * A label that suggests a CRM record (Ticket, Opportunity) still sends the
     * mail to the AI for the record's details, and in the auto mode creates the
     * record — fine for new mail, not for a pile of old conversations at once
     *
     * @param int $labelId
     * @return bool the rule alone settles such mail
     */
    private static function sortsWithoutAi($labelId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT suggested_module FROM df_mailmanager_label WHERE labelid = ?', [$labelId]);
        $suggested = $adb->num_rows($result) ? (string)$adb->query_result($result, 0, 'suggested_module') : '';

        return $suggested === '' || strpos($suggested, 'action:') === 0;
    }

    /**
     * The most specific rule for an incoming mail
     *
     * @param int $mailboxId
     * @param string $fromEmail
     * @param string $subject
     * @return array|null ['ruleid', 'labelid', 'rule_type'] — labelid NO_LABEL = exception
     */
    public static function match($mailboxId, $fromEmail, $subject)
    {
        $fromEmail = strtolower(trim((string)$fromEmail));
        if ($fromEmail === '') {
            return null;
        }

        // the label must still be active and belong to this mailbox (an exception has none)
        list($scopeSql, $scopeParams) = Settings_MailManager_Label_Model::mailboxScopeSql('l', $mailboxId);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT r.ruleid, r.labelid, r.rule_type FROM df_mailmanager_labelrule r
             LEFT JOIN df_mailmanager_label l ON l.labelid = r.labelid
             WHERE r.mailboxid = ?
               AND (r.labelid = ? OR (l.active = 1 AND ' . $scopeSql . '))
               AND ((r.rule_type = ? AND r.from_email = ? AND r.subject_key = ?)
                    OR (r.rule_type = ? AND r.from_email = ?)
                    OR (r.rule_type = ? AND r.domain = ?))
             ORDER BY FIELD(r.rule_type, ?, ?, ?) LIMIT 1',
            array_merge([$mailboxId, self::NO_LABEL], $scopeParams, [
                self::TYPE_SUBJECT, $fromEmail, self::subjectKey((string)$subject),
                self::TYPE_SENDER, $fromEmail,
                self::TYPE_DOMAIN, self::domainOf($fromEmail),
                self::TYPE_SUBJECT, self::TYPE_SENDER, self::TYPE_DOMAIN,
            ])
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * @param int $ruleId
     * @return void
     */
    public static function recordApplied($ruleId)
    {
        PearDatabase::getInstance()->pquery(
            'UPDATE df_mailmanager_labelrule SET applied_count = applied_count + 1, last_applied_at = ?
             WHERE ruleid = ?',
            [date('Y-m-d H:i:s'), $ruleId]
        );
    }

    /**
     * Subject pattern of a mail — reply prefixes, numbers and dates removed
     * ("Re: [eToro] skupina traderov 28.9." → "[etoro] skupina traderov")
     *
     * @param string $subject
     * @return string
     */
    public static function subjectKey($subject)
    {
        $key = mb_strtolower(trim($subject), 'UTF-8');
        $key = preg_replace(self::REPLY_PREFIX, '', $key);
        $key = preg_replace('/\d+/u', ' ', $key);
        // punctuation left alone by the removed numbers ("28.9." → ". .")
        $key = preg_replace('/(^|\s)[\p{P}\p{S}]+(?=\s|$)/u', '$1', $key);
        $key = trim(preg_replace('/\s+/u', ' ', $key));

        return mb_substr($key, 0, 191, 'UTF-8');
    }

    /**
     * Rules of the user's personal mailboxes — My Settings
     *
     * @param int $userId
     * @param int[]|null $mailboxIds only these of them, null = all
     * @return array
     */
    public static function getUserRules($userId, $mailboxIds = null)
    {
        $ownIds = self::getUserMailboxIds($userId);

        return self::getRules($mailboxIds === null ? $ownIds : array_intersect($ownIds, array_map('intval', $mailboxIds)));
    }

    /**
     * The user's earlier label decisions as examples for the AI — it follows
     * them for mail no rule matches exactly (another subject of the same
     * sender, another address of the same company). The rules closest to the
     * mail come first (its sender, then its domain), the newest after them
     *
     * @param int $mailboxId
     * @param string $fromEmail sender of the mail being triaged
     * @return array of ['rule_type', 'from_email', 'domain', 'subject_key', 'label_name' (null = no label)]
     */
    public static function getExamples($mailboxId, $fromEmail)
    {
        $fromEmail = strtolower(trim((string)$fromEmail));
        // a rule of a label that is gone or switched off teaches nothing
        list($scopeSql, $scopeParams) = Settings_MailManager_Label_Model::mailboxScopeSql('l', $mailboxId);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT r.rule_type, r.from_email, r.domain, r.subject_key, l.name AS label_name
             FROM df_mailmanager_labelrule r
             LEFT JOIN df_mailmanager_label l
                    ON l.labelid = r.labelid AND l.active = 1 AND ' . $scopeSql . '
             WHERE r.mailboxid = ? AND (r.labelid = ? OR l.labelid IS NOT NULL)
             ORDER BY r.from_email = ? DESC, r.domain = ? DESC, r.createdtime DESC, r.ruleid DESC
             LIMIT ' . self::MAX_EXAMPLES,
            array_merge($scopeParams, [$mailboxId, self::NO_LABEL, $fromEmail, self::domainOf($fromEmail)])
        );

        $examples = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $examples[] = $adb->raw_query_result_rowdata($result, $i);
        }

        return $examples;
    }

    /**
     * Rules of the given mailboxes — the admin's mailbox detail
     *
     * @param int[] $mailboxIds
     * @return array
     */
    public static function getMailboxRules(array $mailboxIds)
    {
        return self::getRules(array_map('intval', $mailboxIds));
    }

    /**
     * Deletes a rule the caller may manage
     *
     * @param int $ruleId
     * @param int[]|null $mailboxIds the manageable mailboxes, null = any (the admin)
     * @return bool false when the rule is not in them
     */
    public static function deleteManagedRule($ruleId, $mailboxIds)
    {
        if ($mailboxIds !== null) {
            return self::deleteInMailboxes($ruleId, $mailboxIds);
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('DELETE FROM df_mailmanager_labelrule WHERE ruleid = ?', [$ruleId]);

        return $adb->getAffectedRowCount($result) > 0;
    }

    /**
     * @param int $ruleId
     * @param int[] $mailboxIds mailboxes the caller may manage
     * @return bool
     */
    private static function deleteInMailboxes($ruleId, array $mailboxIds)
    {
        if (!$mailboxIds) {
            return false;
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'DELETE FROM df_mailmanager_labelrule
             WHERE ruleid = ? AND mailboxid IN (' . generateQuestionMarks($mailboxIds) . ')',
            array_merge([$ruleId], $mailboxIds)
        );

        return $adb->getAffectedRowCount($result) > 0;
    }

    /**
     * @param int $userId
     * @return int[]
     */
    private static function getUserMailboxIds($userId)
    {
        return self::getMailboxIds(
            "SELECT mailboxid FROM df_mailmanager_mailbox WHERE owner_userid = ? AND mailbox_type = 'personal'",
            [$userId]
        );
    }

    /**
     * @param string $query
     * @param array $params
     * @return int[]
     */
    private static function getMailboxIds($query, array $params)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($query, $params);
        $ids = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $ids[] = (int)$adb->query_result($result, $i, 'mailboxid');
        }

        return $ids;
    }

    /**
     * @param int[] $mailboxIds
     * @return array rule rows + label_name, label_color, mailbox_email, last_applied_display
     */
    private static function getRules(array $mailboxIds)
    {
        if (!$mailboxIds) {
            return [];
        }
        MailManager_ProInbox_Model::useUtf8mb4();
        $adb = PearDatabase::getInstance();
        // exceptions have no label row
        $result = $adb->pquery(
            'SELECT r.*, l.name AS label_name, l.color AS label_color, m.email AS mailbox_email
             FROM df_mailmanager_labelrule r
             LEFT JOIN df_mailmanager_label l ON l.labelid = r.labelid
             INNER JOIN df_mailmanager_mailbox m ON m.mailboxid = r.mailboxid
             WHERE r.mailboxid IN (' . generateQuestionMarks($mailboxIds) . ')
             ORDER BY FIELD(r.rule_type, ?, ?, ?), r.createdtime DESC',
            array_merge($mailboxIds, [self::TYPE_DOMAIN, self::TYPE_SENDER, self::TYPE_SUBJECT])
        );
        $rules = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $rule = $adb->raw_query_result_rowdata($result, $i);
            $dateTimeField = new DateTimeField($rule['last_applied_at']);
            $rule['last_applied_display'] = $rule['last_applied_at'] ? $dateTimeField->getDisplayDateTimeValue() : '';
            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * Re-evaluates the broader rules after a correction — the sender rule
     * follows the label of most of its corrected subjects, the domain rule
     * the label of most of its corrected addresses; below WIDEN_HITS the
     * broader rule goes away
     *
     * @param int $mailboxId
     * @param string $fromEmail
     * @param string $domain
     * @return void
     */
    private static function widen($mailboxId, $fromEmail, $domain)
    {
        // our own people send work and private mail alike — only their
        // subject rules are reliable
        if ($domain === '' || in_array($domain, self::getOwnDomains(), true)) {
            return;
        }

        $senderLabel = self::dominantLabel($mailboxId, 'from_email', $fromEmail, 'subject_key');
        self::replaceBroadRule($mailboxId, self::TYPE_SENDER, $fromEmail, $domain, $senderLabel);

        if (!in_array($domain, MailManager_ProRecordMatch_Model::FREEMAIL_DOMAINS, true)) {
            $domainLabel = self::dominantLabel($mailboxId, 'domain', $domain, 'from_email');
            self::replaceBroadRule($mailboxId, self::TYPE_DOMAIN, '', $domain, $domainLabel);
        }
    }

    /**
     * @param int $mailboxId
     * @param string $column from_email | domain
     * @param string $value
     * @param string $distinctColumn what must differ between the corrections
     * @return int label with at least WIDEN_HITS corrections, 0 = none or a tie
     */
    private static function dominantLabel($mailboxId, $column, $value, $distinctColumn)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT labelid, COUNT(DISTINCT ' . $distinctColumn . ') AS hits FROM df_mailmanager_labelrule
             WHERE mailboxid = ? AND rule_type = ? AND ' . $column . ' = ? AND labelid != ?
             GROUP BY labelid ORDER BY hits DESC LIMIT 2',
            [$mailboxId, self::TYPE_SUBJECT, $value, self::NO_LABEL]
        );
        $count = $adb->num_rows($result);
        $hits = $count ? (int)$adb->query_result($result, 0, 'hits') : 0;
        // two labels equally often — no clear rule for the whole sender / domain
        if ($hits < self::WIDEN_HITS || ($count > 1 && (int)$adb->query_result($result, 1, 'hits') === $hits)) {
            return 0;
        }

        return (int)$adb->query_result($result, 0, 'labelid');
    }

    /**
     * @param int $mailboxId
     * @param string $type sender | domain
     * @param string $fromEmail '' for a domain rule
     * @param string $domain
     * @param int $labelId 0 = the rule goes away
     * @return void
     */
    private static function replaceBroadRule($mailboxId, $type, $fromEmail, $domain, $labelId)
    {
        if ($labelId) {
            self::saveRule($mailboxId, $labelId, $type, $fromEmail, $domain, '');

            return;
        }
        PearDatabase::getInstance()->pquery(
            'DELETE FROM df_mailmanager_labelrule
             WHERE mailboxid = ? AND rule_type = ? AND from_email = ? AND domain = ?',
            [$mailboxId, $type, $fromEmail, $domain]
        );
    }

    /**
     * Inserts the rule or moves it to the new label
     *
     * @param int $mailboxId
     * @param int $labelId
     * @param string $type
     * @param string $fromEmail
     * @param string $domain
     * @param string $subjectKey
     * @return void
     */
    private static function saveRule($mailboxId, $labelId, $type, $fromEmail, $domain, $subjectKey)
    {
        PearDatabase::getInstance()->pquery(
            'INSERT INTO df_mailmanager_labelrule
                (mailboxid, labelid, rule_type, from_email, domain, subject_key, createdtime)
             VALUES (?,?,?,?,?,?,?)
             ON DUPLICATE KEY UPDATE labelid = VALUES(labelid)',
            [$mailboxId, $labelId, $type, $fromEmail, $domain, $subjectKey, date('Y-m-d H:i:s')]
        );
    }

    /**
     * @param int $threadId
     * @return array|null newest inbound message (from_email, subject)
     */
    private static function getLatestInbound($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email, subject FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$threadId]
        );

        return $adb->num_rows($result) ? $adb->raw_query_result_rowdata($result, 0) : null;
    }

    /**
     * @param string $email
     * @return string lowercase domain, '' when none
     */
    private static function domainOf($email)
    {
        $at = strrpos($email, '@');

        return $at === false ? '' : strtolower(substr($email, $at + 1));
    }

    /**
     * Our own domains — every mailbox address plus the company website;
     * a freemail domain of a personal mailbox is nobody's own
     *
     * @return string[]
     */
    private static function getOwnDomains()
    {
        if (self::$ownDomains !== null) {
            return self::$ownDomains;
        }

        $domains = [];
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT DISTINCT email FROM df_mailmanager_mailbox', []);
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $domains[] = self::domainOf((string)$adb->query_result($result, $i, 'email'));
        }

        $website = decode_html((string)Vtiger_CompanyDetails_Model::getInstanceById()->get('website'));
        if ($website !== '' && strpos($website, '//') === false) {
            $website = 'http://' . $website;
        }
        $host = strtolower((string)parse_url($website, PHP_URL_HOST));
        if ($host !== '') {
            $domains[] = preg_replace('/^www\./', '', $host);
        }

        self::$ownDomains = array_values(array_diff(
            array_unique(array_filter($domains)),
            MailManager_ProRecordMatch_Model::FREEMAIL_DOMAINS
        ));

        return self::$ownDomains;
    }
}
