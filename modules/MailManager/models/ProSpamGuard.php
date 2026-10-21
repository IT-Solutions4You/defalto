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
 * Spam learning — deterministic rules learned from the user's manual
 * "Move to Spam" actions. A sender moved to Spam twice is auto-moved to the
 * Junk folder on the server during future syncs (never deleted, no AI cost).
 * Every automatic move is logged in the mailbox's activity history.
 */
class MailManager_ProSpamGuard_Model
{
    /** Manual moves of the same sender needed before the rule fires */
    public const ACTIVATION_HITS = 2;

    /**
     * The learning is deterministic — enabled independently of the AI
     * assistant / API key (own toggle in the AI Settings)
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return MailManager_ProAiConfig_Model::get('ai_feature_spamlearn') === '1';
    }

    /**
     * Learning signal — the user moved a thread to Spam by hand; remembers
     * the sender of its last inbound message
     *
     * @param array $mailbox mailbox row
     * @param array $thread thread row
     * @return void
     */
    public static function learnFromThread(array $mailbox, array $thread)
    {
        if (!self::isEnabled()) {
            return;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$thread['threadid']]
        );
        if (!$adb->num_rows($result)) {
            return;
        }
        // raw read — query_result() html-encodes "&" and quotes, the rule would never match
        $fromEmail = strtolower(trim((string)$adb->raw_query_result_rowdata($result, 0)['from_email']));
        if ($fromEmail === '' || strcasecmp($fromEmail, $mailbox['email']) === 0) {
            return;
        }

        // a sender trusted before starts over — the user changed his mind
        // (hits reads the OLD trusted flag, the assignments run left to right)
        $now = date('Y-m-d H:i:s');
        $adb->pquery(
            'INSERT INTO df_mailmanager_spamrule (mailboxid, from_email, hits, createdtime, last_hit_at)
             VALUES (?,?,1,?,?)
             ON DUPLICATE KEY UPDATE hits = IF(trusted = 1, 1, hits + 1), trusted = 0, last_hit_at = ?',
            [$mailbox['mailboxid'], $fromEmail, $now, $now, $now]
        );
        self::$trusted = null;
    }

    /** @var array|null mailboxid => [lowercased address => true], see isTrusted() */
    private static $trusted;

    /**
     * "Not spam" — the user took the conversation out of Spam: its sender is
     * trusted from now on. The learned rule stops, and nothing moves the
     * sender's mail to Spam / Trash by itself again (the learned rules, the
     * label cleanup, the AI picking a cleanup label). The address only —
     * a whole domain would trust everybody on gmail.com
     *
     * @param array $mailbox mailbox row
     * @param array $thread thread row
     * @return string the trusted address, '' when the conversation has no sender to trust
     */
    public static function trustSender(array $mailbox, array $thread)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_email FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$thread['threadid']]
        );
        // raw read — query_result() html-encodes "&" and quotes, the address would never match
        $fromEmail = $adb->num_rows($result)
            ? strtolower(trim((string)$adb->raw_query_result_rowdata($result, 0)['from_email']))
            : '';
        if ($fromEmail === '' || strcasecmp($fromEmail, $mailbox['email']) === 0) {
            return '';
        }

        $now = date('Y-m-d H:i:s');
        $adb->pquery(
            'INSERT INTO df_mailmanager_spamrule (mailboxid, from_email, hits, trusted, createdtime, last_hit_at)
             VALUES (?,?,0,1,?,?)
             ON DUPLICATE KEY UPDATE hits = 0, trusted = 1, last_hit_at = ?',
            [$mailbox['mailboxid'], $fromEmail, $now, $now, $now]
        );
        self::$trusted = null;

        return $fromEmail;
    }

    /**
     * @param int $mailboxId
     * @param string $email
     * @return bool the user said this sender is not spam
     */
    public static function isTrusted($mailboxId, $email)
    {
        $mailboxId = (int)$mailboxId;
        if (!isset(self::$trusted[$mailboxId])) {
            self::$trusted[$mailboxId] = [];
            $adb = PearDatabase::getInstance();
            $result = $adb->pquery(
                'SELECT from_email FROM df_mailmanager_spamrule WHERE mailboxid = ? AND trusted = 1',
                [$mailboxId]
            );
            $count = $adb->num_rows($result);
            for ($i = 0; $i < $count; $i++) {
                $address = strtolower((string)$adb->raw_query_result_rowdata($result, $i)['from_email']);
                self::$trusted[$mailboxId][$address] = true;
            }
        }

        return isset(self::$trusted[$mailboxId][strtolower(trim((string)$email))]);
    }

    /**
     * Sync-time filter: freshly fetched messages from learned senders are
     * moved to the Junk folder on the server and never cached (also skips
     * their AI triage cost). The mail always survives in Junk — no deletion
     *
     * @param array $mailbox mailbox row
     * @param MailManager_ProBaseTransport_Helper $transport open transport
     * @param array $folder source folder row
     * @param array $messages unified messages from getChangesSince
     * @return array ['messages' => array to store, 'moved' => int]
     */
    public static function filterIncoming(array $mailbox, $transport, array $folder, array $messages)
    {
        if (!$messages || !self::isEnabled()
            || in_array($folder['folder_type'], ['sent', 'drafts', 'spam', 'trash'], true)) {
            return ['messages' => $messages, 'moved' => 0];
        }

        $adb = PearDatabase::getInstance();

        $junkResult = $adb->pquery(
            "SELECT remote_id, name FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folder_type = 'spam' ORDER BY folderid LIMIT 1",
            [$mailbox['mailboxid']]
        );
        if (!$adb->num_rows($junkResult)) {
            return ['messages' => $messages, 'moved' => 0];
        }
        // raw read — query_result() escapes "&" of the modified UTF-7 names
        $junk = $adb->raw_query_result_rowdata($junkResult, 0);
        $junkRemoteId = (string)$junk['remote_id'];

        $ruleResult = $adb->pquery(
            'SELECT ruleid, from_email FROM df_mailmanager_spamrule
             WHERE mailboxid = ? AND active = 1 AND trusted = 0 AND hits >= ' . self::ACTIVATION_HITS,
            [$mailbox['mailboxid']]
        );
        $rules = [];
        $ruleCount = $adb->num_rows($ruleResult);
        for ($i = 0; $i < $ruleCount; $i++) {
            $rule = $adb->raw_query_result_rowdata($ruleResult, $i);
            $rules[strtolower($rule['from_email'])] = (int)$rule['ruleid'];
        }
        if (!$rules) {
            return ['messages' => $messages, 'moved' => 0];
        }

        $keep = [];
        $matched = [];
        foreach ($messages as $message) {
            $sender = strtolower(trim((string)$message['from_email']));
            if (!$message['is_draft'] && isset($rules[$sender])
                && strcasecmp($sender, $mailbox['email']) !== 0) {
                $matched[] = $message;
            } else {
                $keep[] = $message;
            }
        }
        if (!$matched) {
            return ['messages' => $messages, 'moved' => 0];
        }

        try {
            $transport->moveMessages($folder, array_column($matched, 'remote_uid'), $junkRemoteId);
        } catch (Exception $e) {
            // server move failed (or transport lacks it) — store everything normally
            return ['messages' => $messages, 'moved' => 0];
        }

        $mailboxId = (int)$mailbox['mailboxid'];
        $fromPath = MailManager_ProHistory_Model::getFolderPathByName($mailboxId, (string)$folder['name']);
        $toPath = MailManager_ProHistory_Model::getFolderPathByName($mailboxId, (string)$junk['name']);
        $movedPerRule = [];
        foreach ($matched as $message) {
            $ruleId = $rules[strtolower(trim((string)$message['from_email']))];
            $movedPerRule[$ruleId] = ($movedPerRule[$ruleId] ?? 0) + 1;
            MailManager_ProHistory_Model::add([
                'mailboxid' => $mailboxId,
                'action' => MailManager_ProHistory_Model::ACTION_MOVE,
                'source' => MailManager_ProHistory_Model::SOURCE_RULE,
                'from_email' => $message['from_email'],
                'subject' => $message['subject'],
                'mail_date' => $message['sent_at'] ?? null,
                'from_value' => $fromPath,
                'to_value' => $toPath,
            ]);
        }
        foreach ($movedPerRule as $ruleId => $movedCount) {
            $adb->pquery(
                'UPDATE df_mailmanager_spamrule SET moved_count = moved_count + ? WHERE ruleid = ?',
                [$movedCount, $ruleId]
            );
        }

        return ['messages' => $keep, 'moved' => count($matched)];
    }

    /**
     * Learned rules — the AI Settings overview (all mailboxes) or a user's
     * My Settings (the own mailboxes)
     *
     * @param int[]|null $mailboxIds null = all mailboxes
     * @return array
     */
    public static function getRules($mailboxIds = null)
    {
        list($scopeSql, $params) = self::mailboxScope('r', $mailboxIds);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT r.*, m.email AS mailbox_email FROM df_mailmanager_spamrule r
             INNER JOIN df_mailmanager_mailbox m ON m.mailboxid = r.mailboxid
             WHERE ' . $scopeSql . '
             ORDER BY r.last_hit_at DESC',
            $params
        );
        $rules = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $rule = $adb->raw_query_result_rowdata($result, $i);
            $dateTimeField = new DateTimeField($rule['last_hit_at']);
            $rule['last_hit_display'] = $rule['last_hit_at'] ? $dateTimeField->getDisplayDateTimeValue() : '';
            $rules[] = $rule;
        }

        return $rules;
    }

    /**
     * @param int $ruleId
     * @param int[]|null $mailboxIds the mailboxes the caller may manage, null = any
     * @return bool false when the rule is not in them
     */
    public static function deleteRule($ruleId, $mailboxIds = null)
    {
        list($scopeSql, $params) = self::mailboxScope('r', $mailboxIds);
        $adb = PearDatabase::getInstance();
        $found = $adb->pquery(
            'SELECT 1 FROM df_mailmanager_spamrule r WHERE r.ruleid = ? AND ' . $scopeSql,
            array_merge([$ruleId], $params)
        );
        if (!$adb->num_rows($found)) {
            return false;
        }
        $adb->pquery('DELETE FROM df_mailmanager_spamrule WHERE ruleid = ?', [$ruleId]);

        return true;
    }

    /**
     * @param int[]|null $mailboxIds null = all mailboxes
     * @return int total messages auto-moved to Spam
     */
    public static function getMovedTotal($mailboxIds = null)
    {
        // the rules count their moves — the history keeps only the newest entries
        list($scopeSql, $params) = self::mailboxScope('r', $mailboxIds);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT COALESCE(SUM(r.moved_count), 0) AS total FROM df_mailmanager_spamrule r WHERE ' . $scopeSql,
            $params
        );

        return (int)$adb->query_result($result, 0, 'total');
    }

    /**
     * @param string $alias table alias carrying mailboxid
     * @param int[]|null $mailboxIds null = no restriction
     * @return array [SQL condition, parameters] — an empty list matches nothing
     */
    private static function mailboxScope($alias, $mailboxIds)
    {
        if ($mailboxIds === null) {
            return ['1 = 1', []];
        }
        if (!$mailboxIds) {
            return ['1 = 0', []];
        }
        $mailboxIds = array_map('intval', $mailboxIds);

        return [$alias . '.mailboxid IN (' . generateQuestionMarks($mailboxIds) . ')', $mailboxIds];
    }
}
