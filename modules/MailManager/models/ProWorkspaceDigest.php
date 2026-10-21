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
 * The Workspace digest (I19, ITS4YouMailManager 4.84): the user's whole Workspace —
 * shared mailboxes included — in four sections, sorted without AI: who waits on whom
 * (the newest non-draft mail decides), whose the conversation is, the records still in
 * progress. The AI lines (one per conversation, written by the cron) and the summary on
 * top come from MailManager_ProWorkspaceDigestAi_Model.
 */
class MailManager_ProWorkspaceDigest_Model
{
    public const SECTION_MINE = 'mine';
    public const SECTION_UNASSIGNED = 'unassigned';
    public const SECTION_CUSTOMER = 'customer';
    public const SECTIONS = [self::SECTION_MINE, self::SECTION_UNASSIGNED, self::SECTION_CUSTOMER];

    /** Days without an answer from which the date shows red with ⚠ (not for unassigned mail) */
    public const LATE_DAYS = 5;

    /** Conversations the digest reads, newest activity first */
    public const MAX_THREADS = 150;

    /** A ticket in this state waits for the customer */
    private const TICKET_WAITS = 'Wait For Response';

    private const PRIORITY_RANK = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'low' => 3];

    /** The line state of a row: '' (a line or no AI), pending (the cron writes it), suspicious */
    public const LINE_PENDING = 'pending';
    public const LINE_SUSPICIOUS = 'suspicious';

    /**
     * The digest of the user, as the view shows it.
     *
     * @param int  $userId
     * @param bool $refresh the user clicked Refresh — the summary is computed again
     *
     * @return array ['sections' => section => items, 'records' => records in progress,
     *               'summary' => string, 'ai' => bool, 'generated' => display time, 'stamp' => getStamp()]
     */
    public static function build(int $userId, bool $refresh = false): array
    {
        MailManager_ProUserSetting_Model::set($userId, MailManager_ProUserSetting_Model::DIGEST_SEEN, date('Y-m-d H:i:s'));
        $digest = self::getSections($userId);
        $digest['stamp'] = self::buildStamp($digest);
        $ai = MailManager_ProWorkspaceDigestAi_Model::isEnabled();
        $digest['summary'] = $ai ? MailManager_ProWorkspaceDigestAi_Model::getSummary($userId, $digest['sections'], $refresh) : '';
        $digest['ai'] = $ai;
        $digest['generated'] = Vtiger_Util_Helper::convertDateTimeIntoUsersDisplayFormat(date('Y-m-d H:i:s'));

        return $digest;
    }

    /**
     * The sections without AI calls — the view and the cron (which lines are missing) use it.
     *
     * @param int $userId
     *
     * @return array ['sections' => section => items (sorted), 'records' => records in progress]
     */
    public static function getSections(int $userId): array
    {
        [$rows, $mailboxes] = MailManager_ProThreadList_Model::getWorkspaceDigestRows($userId, self::MAX_THREADS);
        MailManager_ProThreadList_Model::attachDrivingRecords($rows);
        MailManager_ProThreadState_Model::attach($rows);

        $answered = array_flip(MailManager_ProHistory_Model::getThreadIdsSentBy(array_column($rows, 'threadid'), $userId));
        $ownerIds = MailManager_ProThreadList_Model::getOwnerIds($userId);
        $language = MailManager_ProWorkspaceDigestAi_Model::getLanguage();
        $ai = MailManager_ProWorkspaceDigestAi_Model::isEnabled();
        $sections = array_fill_keys(self::SECTIONS, []);
        $records = [];

        foreach ($rows as $row) {
            $driving = $row['driving'] ?? null;

            // done conversations leave the digest — the record's state or the own one
            if (($driving && $driving['is_done']) || (!empty($row['state']) && $row['state']['is_done'])) {
                continue;
            }

            $mailbox = $mailboxes[(int)$row['mailboxid']] ?? ['email' => '', 'name' => '', 'owner_userid' => 0];
            $isMine = (int)$mailbox['owner_userid'] === $userId
                || (int)$row['assigned_userid'] === $userId
                || ($driving && in_array((int)$driving['owner_id'], $ownerIds, true))
                || isset($answered[(int)$row['threadid']]);
            $waitsOnCustomer = 'out' === $row['last_direction']
                || ($driving && 'HelpDesk' === $driving['module'] && self::TICKET_WAITS === $driving['status']);

            if ($waitsOnCustomer) {
                $section = self::SECTION_CUSTOMER;
            } else {
                $section = $isMine ? self::SECTION_MINE : self::SECTION_UNASSIGNED;
            }

            $isOwner = (int)$mailbox['owner_userid'] === $userId;
            $sections[$section][] = self::buildItem($row, $mailbox, $section, $language, $ai, $isOwner);

            // the records still in progress of the user's conversations, each once
            if ($isMine && $driving && !isset($records[$driving['crmid']])) {
                $records[$driving['crmid']] = $driving + [
                    'threadid' => (int)$row['threadid'],
                    'mailboxid' => (int)$row['mailboxid'],
                ];
            }
        }

        foreach ($sections as $section => $items) {
            usort($items, [self::class, 'compareItems']);
            $sections[$section] = $items;
        }

        return ['sections' => $sections, 'records' => array_values($records)];
    }

    /**
     * A short fingerprint of what the digest shows — a sync compares it with the open digest
     * and reloads it when it differs (a reply from another client, a colleague taking the
     * conversation, a record done, a new line from the cron)
     *
     * @param int $userId
     *
     * @return string md5
     */
    public static function getStamp(int $userId): string
    {
        return self::buildStamp(self::getSections($userId));
    }

    /**
     * @param array $digest getSections()
     *
     * @return string md5 of the sections (conversation, newest mail, priority, line) and the
     *                records (state, owner) — not the day count, not the unread state
     */
    private static function buildStamp(array $digest): string
    {
        $keys = [];

        foreach ($digest['sections'] as $section => $items) {
            foreach ($items as $item) {
                $keys[] = implode('|', [$section, $item['threadid'], $item['last_msgid'], $item['priority'], $item['line_state'], $item['line']]);
            }
        }

        foreach ($digest['records'] as $record) {
            $keys[] = implode('|', ['record', $record['crmid'], $record['status'], $record['owner_id']]);
        }

        sort($keys);

        return md5(implode("\n", $keys));
    }

    /**
     * One row of a section.
     *
     * @param array  $row      getWorkspaceDigestRows() row with driving / state
     * @param array  $mailbox  mailbox row
     * @param string $section  SECTION_*
     * @param string $language ISO 639-1 code of the reader
     * @param bool   $ai       the AI lines are on
     * @param bool   $isOwner  the reader owns the mailbox — a line in another language is written again for him
     *
     * @return array
     */
    private static function buildItem(array $row, array $mailbox, string $section, string $language, bool $ai, bool $isOwner): array
    {
        $lastAt = (string)$row['last_at'];
        $days = '' !== $lastAt ? max(0, (int)floor((time() - strtotime($lastAt)) / 86400)) : 0;
        $late = self::SECTION_UNASSIGNED !== $section && $days >= self::LATE_DAYS;
        $lastMessageId = (int)$row['last_msgid'];
        $line = '';
        $lineState = '';

        if (MailManager_ProAiSuspicion_Model::STATE_SUSPICIOUS === (int)$row['ai_suspicious']) {
            // never sent to the AI — a fixed line says why
            $line = vtranslate('LBL_DIGEST_SUSPICIOUS', 'MailManager');
            $lineState = self::LINE_SUSPICIOUS;
        } elseif ($ai) {
            // one line per conversation: valid for its newest mail; in another language only
            // the mailbox owner gets it written again (readers of a team mailbox in different
            // languages must not rewrite it in turns); an empty one = the AI gave none for this mail
            $current = (int)$row['ai_digest_msgid'] === $lastMessageId && $lastMessageId > 0;

            if (!$current || ($isOwner && '' !== (string)$row['ai_digest'] && (string)$row['ai_digest_lang'] !== $language)) {
                $lineState = self::LINE_PENDING;
            } else {
                $line = (string)$row['ai_digest'];
            }
        }

        $contact = trim(decode_html((string)$row['contact_name']));

        return [
            'threadid' => (int)$row['threadid'],
            'mailboxid' => (int)$row['mailboxid'],
            'mailbox_email' => (string)$mailbox['email'],
            'mailbox_name' => decode_html((string)$mailbox['name']),
            'subject' => decode_html((string)$row['subject']),
            'contact' => '' !== $contact ? $contact : (string)$row['contact_email'],
            'priority' => (string)$row['priority'],
            'unread' => (int)$row['unread_count'] > 0,
            'last_at' => $lastAt,
            'last_display' => '' !== $lastAt ? (new DateTimeField($lastAt))->getDisplayDate() : '',
            'days' => $days,
            'late' => $late,
            'late_title' => $late
                ? sprintf(vtranslate(self::SECTION_CUSTOMER === $section ? 'LBL_DIGEST_LATE_CUSTOMER' : 'LBL_DIGEST_LATE_MINE', 'MailManager'), $days)
                : '',
            'last_msgid' => $lastMessageId,
            'line' => $line,
            'line_state' => $lineState,
            'waits_on' => self::SECTION_CUSTOMER === $section ? 'customer' : 'us',
            'suspicious_state' => (int)$row['ai_suspicious'],
        ];
    }

    /**
     * Priority first (urgent → low), then the longest waiting.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private static function compareItems(array $a, array $b): int
    {
        $rankA = self::PRIORITY_RANK[$a['priority']] ?? 2;
        $rankB = self::PRIORITY_RANK[$b['priority']] ?? 2;

        return [$rankA, $a['last_at']] <=> [$rankB, $b['last_at']];
    }
}
