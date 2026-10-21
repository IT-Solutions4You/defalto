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
 * The sorting queue of the inbox — after a weekend more new mail waits for
 * the AI triage than one sync classifies. The inbox keeps asking for the
 * next small step and shows every conversation with what happened to it:
 * the label it got and where it was moved
 */
class MailManager_ProTriageQueue_Model
{
    /** Conversations one step classifies — small, the queue shows progress */
    public const STEP = 5;

    /** Seconds one step may take */
    public const STEP_SECONDS = 25;

    /**
     * Classifies the next conversations of the mailbox
     *
     * @param int $mailboxId
     * @param bool $force the user started the sorting himself — ask the provider
     *                    even when it refused the account a moment ago
     * @return array ['done' => describe() rows, 'pending' => conversations still waiting,
     *               'ai_block' => MailManager_ProAiBlock_Model::describe()]
     */
    public static function runStep($mailboxId, $force = false)
    {
        if ($force) {
            MailManager_ProAiBlock_Model::force();
        }
        MailManager_ProSync_Model::traceProblems(
            'Sorting queue AI triage',
            MailManager_ProAiTriage_Model::run(time() + self::STEP_SECONDS, (int)$mailboxId, self::STEP)
        );

        $pending = count(MailManager_ProAiTriage_Model::getPending((int)$mailboxId));

        return [
            'done' => self::describe((int)$mailboxId, MailManager_ProAiTriage_Model::getProcessed()),
            'pending' => $pending,
            // only while mail waits: a refusal nothing waits behind is nobody's problem
            'ai_block' => $pending ? MailManager_ProAiBlock_Model::describe() : null,
        ];
    }

    /**
     * What the triage did with conversations
     *
     * @param int $mailboxId
     * @param int[] $threadIds
     * @return array of ['threadid', 'subject', 'sender', 'label' (null = none), 'color', 'text_color',
     *                  'moved' (folder name, null = stays), 'moved_type' (spam | trash), 'failed',
     *                  'error' (why it failed, '' otherwise)]
     */
    public static function describe($mailboxId, array $threadIds)
    {
        if (!$threadIds) {
            return [];
        }
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT t.threadid, t.subject, t.triage_state, t.triage_payload, l.name, l.color,
                    (SELECT IFNULL(NULLIF(mx.from_name, ''), mx.from_email) FROM df_mailmanager_message mx
                     WHERE mx.threadid = t.threadid AND mx.direction = 'in'
                     ORDER BY mx.sent_at DESC LIMIT 1) AS sender
             FROM df_mailmanager_thread t
             LEFT JOIN df_mailmanager_label l ON l.labelid = t.labelid
             WHERE t.mailboxid = ? AND t.threadid IN (" . generateQuestionMarks($threadIds) . ')',
            array_merge([$mailboxId], $threadIds)
        );
        $locations = MailManager_ProFolder_Model::getThreadLocations($mailboxId, $threadIds);

        $rows = [];
        for ($i = 0, $count = $adb->num_rows($result); $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $threadId = (int)$row['threadid'];
            $moved = null;
            foreach ($locations[$threadId] ?? [] as $location) {
                if (in_array($location['folder_type'], MailManager_ProFolder_Model::BADGE_FOLDER_TYPES, true)) {
                    $moved = $location;
                    break;
                }
            }
            $rows[] = [
                'threadid' => $threadId,
                'subject' => (string)$row['subject'],
                'sender' => (string)$row['sender'],
                'label' => $row['name'] !== null ? decode_html((string)$row['name']) : null,
                'color' => (string)$row['color'],
                'text_color' => $row['name'] !== null
                    ? Settings_MailManager_Label_Model::getTextColor((string)$row['color'])
                    : '',
                'moved' => $moved ? $moved['display'] : null,
                'moved_type' => $moved ? $moved['folder_type'] : null,
                'failed' => $row['triage_state'] === 'failed',
                'error' => $row['triage_state'] === 'failed'
                    ? mb_substr((string)(json_decode((string)$row['triage_payload'], true)['error'] ?? ''), 0, 300)
                    : '',
            ];
        }

        return $rows;
    }
}
