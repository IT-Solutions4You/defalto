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
 * Automatic cleanup of new mail — a label whose suggested action is "Move
 * to Spam" / "Move to Trash" and that is set to perform it automatically
 * (Edit Label) moves the freshly triaged conversation on the mail server
 * right away. Nothing is deleted for good: the mail stays in the Spam /
 * Trash folder of the account with its label (the reason it is there), and
 * the activity history says who moved it.
 * Only the mail lying in the Inbox moves — what the user filed elsewhere
 * (Archive, own folders) stays there. A conversation the user already works
 * with (own reply, a CRM record he linked) or that carries another label is
 * never touched — it keeps the suggestion
 */
class MailManager_ProAutoCleanup_Model
{
    /** Suggested action of the label => folder type the mail goes to */
    private const TARGETS = ['action:spam' => 'spam', 'action:delete' => 'trash'];

    /** @var array folder type => ['folderid' => int, 'items' => [['subject', 'sender']]] moved during this request */
    private static $moved = [];

    /**
     * What the cleanup moved during this request — the inbox tells the user
     * (SyncNow), mail must not vanish unnoticed
     *
     * @return array folder type (spam | trash) => ['folderid' => int, 'items' => [['subject', 'sender']]]
     */
    public static function getMoved()
    {
        return self::$moved;
    }

    /**
     * @param array $thread thread row just labeled by the triage
     * @param array|null $label its label row (suggested_module, auto_action)
     * @param string $source MailManager_ProHistory_Model::SOURCE_AI / SOURCE_RULE
     * @return string the folder type the conversation went to, '' = left in place
     */
    public static function run(array $thread, $label, $source)
    {
        $targetType = self::TARGETS[(string)($label['suggested_module'] ?? '')] ?? '';
        if ($targetType === '' || (int)($label['auto_action'] ?? 0) !== 1
            || !self::carriesLabel((int)$thread['threadid'], (int)$label['labelid'])
            || !self::isUntouched((int)$thread['threadid'])) {
            return '';
        }

        // "Not spam" outranks the label — the user took this sender out of Spam before
        if (MailManager_ProSpamGuard_Model::isTrusted(
            (int)$thread['mailboxid'],
            MailManager_ProRecordMatch_Model::getLatestSender((int)$thread['threadid'])
        )) {
            return '';
        }

        $adb = PearDatabase::getInstance();
        $mailboxResult = $adb->pquery(
            'SELECT * FROM df_mailmanager_mailbox WHERE mailboxid = ?',
            [(int)$thread['mailboxid']]
        );
        $targetResult = $adb->pquery(
            'SELECT folderid, remote_id FROM df_mailmanager_folder
             WHERE mailboxid = ? AND folder_type = ? ORDER BY folderid LIMIT 1',
            [(int)$thread['mailboxid'], $targetType]
        );
        if (!$adb->num_rows($mailboxResult) || !$adb->num_rows($targetResult)) {
            return '';
        }
        // raw read — query_result() escapes "&" of the modified UTF-7 names
        $target = $adb->raw_query_result_rowdata($targetResult, 0);
        $threadId = (int)$thread['threadid'];
        // new mail only — it arrives in the Inbox; the rest of the conversation
        // stays where the user keeps it
        $pointers = MailManager_ProFolder_Model::filterInboxPointers(
            MailManager_ProThread_Model::getAllMessagePointers($threadId)
        );
        if (!$pointers) {
            return '';
        }

        $mailbox = $adb->raw_query_result_rowdata($mailboxResult, 0);
        // server and cache change together — not in the middle of a sync of the mailbox
        $locked = MailManager_ProSync_Model::lockMailbox($mailbox);
        try {
            return self::move($thread, $target, $targetType, $pointers, $mailbox, $source);
        } finally {
            MailManager_ProSync_Model::unlockMailbox($mailbox, $locked);
        }
    }

    /**
     * @param array $thread thread row
     * @param array $target folderid + remote_id of the Spam / Trash folder
     * @param string $targetType spam | trash
     * @param array $pointers folderid => UIDs of the mail to move
     * @param array $mailbox mailbox row
     * @param string $source MailManager_ProHistory_Model::SOURCE_*
     * @return string the folder type the conversation went to, '' = left in place
     */
    private static function move(array $thread, array $target, $targetType, array $pointers, array $mailbox, $source)
    {
        $threadId = (int)$thread['threadid'];
        [$done] = MailManager_ProServerMove_Model::run(
            MailManager_ProTransport_Model::getForMailbox($mailbox),
            $pointers,
            (string)$target['remote_id']
        );
        // the server refused — the conversation stays with its suggestion;
        // a failure half way — the cache follows the folders that did move
        $pointers = array_intersect_key($pointers, array_flip($done));
        if (!$pointers) {
            return '';
        }

        MailManager_ProHistory_Model::logThread(
            $threadId,
            $targetType === 'trash'
                ? MailManager_ProHistory_Model::ACTION_DELETE
                : MailManager_ProHistory_Model::ACTION_MOVE,
            $source,
            MailManager_ProHistory_Model::getFolderPaths(array_keys($pointers)),
            MailManager_ProHistory_Model::getFolderPath((int)$target['folderid'])
        );
        // the conversation follows its mail and keeps the label
        MailManager_ProThread_Model::moveThreadCache($threadId, (int)$target['folderid'], array_keys($pointers));
        MailManager_ProThread_Model::settleSuggestion($threadId);

        self::$moved[$targetType]['folderid'] = (int)$target['folderid'];
        self::$moved[$targetType]['items'][] = [
            'subject' => (string)$thread['subject'],
            'sender' => self::getSender($threadId),
        ];

        return $targetType;
    }

    /**
     * @param int $threadId
     * @return string who wrote the newest received mail of the conversation
     */
    private static function getSender($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT from_name, from_email FROM df_mailmanager_message
             WHERE threadid = ? AND direction = 'in' ORDER BY sent_at DESC LIMIT 1",
            [$threadId]
        );
        if (!$adb->num_rows($result)) {
            return '';
        }
        $row = $adb->raw_query_result_rowdata($result, 0);

        return trim((string)$row['from_name']) !== '' ? (string)$row['from_name'] : (string)$row['from_email'];
    }

    /**
     * The triage keeps a label the conversation already has — the cleanup
     * follows the label the thread really carries, not the fresh classification
     *
     * @param int $threadId
     * @param int $labelId label the triage just picked
     * @return bool
     */
    private static function carriesLabel($threadId, $labelId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT labelid FROM df_mailmanager_thread WHERE threadid = ?', [$threadId]);

        return $adb->num_rows($result) && (int)$adb->query_result($result, 0, 'labelid') === $labelId;
    }

    /**
     * @param int $threadId
     * @return bool nobody answered the conversation and nobody linked a CRM
     *              record to it (a link the sync made by itself does not count)
     */
    private static function isUntouched($threadId)
    {
        // ...and that consists of this one received mail. A bounce or an automatic
        // notice joining a customer's conversation gets its label judged alone,
        // but the move would take the customer's own mail along
        $adb = PearDatabase::getInstance();
        $inbound = $adb->pquery(
            "SELECT COUNT(*) AS mails FROM df_mailmanager_message WHERE threadid = ? AND direction = 'in'",
            [$threadId]
        );
        if ((int)$adb->query_result($inbound, 0, 'mails') !== 1) {
            return false;
        }
        $result = $adb->pquery(
            "SELECT (SELECT COUNT(*) FROM df_mailmanager_rel r WHERE r.threadid = ? AND r.source <> 'auto')
                  + (SELECT COUNT(*) FROM df_mailmanager_message m
                     WHERE m.threadid = ? AND m.direction = 'out') AS touched",
            [$threadId, $threadId]
        );

        return (int)$adb->query_result($result, 0, 'touched') === 0;
    }
}
