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
 * Deletes the mail data Mail Manager Pro stored in the database (decision D8, task T13).
 * Mailboxes, credentials, sharing, labels, rules and settings stay; CRM records created
 * from mail (ITS4YouEmails, Documents, comments, tickets) are never touched.
 */
class MailManager_ProPurge_Model
{
    /**
     * Tables whose rows are mail content or depend on it, deleted completely.
     */
    public const PURGED_TABLES = [
        'df_mailmanager_attachment',
        'df_mailmanager_translation',
        'df_mailmanager_message',
        'df_mailmanager_assistant',
        'df_mailmanager_note',
        'df_mailmanager_rel',
        'df_mailmanager_unlink',
        'df_mailmanager_history',
        'df_mailmanager_thread',
    ];

    /**
     * Returns the number of stored messages (shown in the confirmation).
     *
     * @return int
     */
    public function countStoredMessages(): int
    {
        $db = PearDatabase::getInstance();

        return (int)$db->query_result($db->pquery('SELECT COUNT(*) AS cnt FROM df_mailmanager_message', []), 0, 'cnt');
    }

    /**
     * Deletes the stored mail and resets the sync cursors, so a later reactivation
     * synchronizes again from each mailbox's sync_from date.
     *
     * @return array table name => deleted rows
     */
    public function purgeStoredMail(): array
    {
        $db = PearDatabase::getInstance();
        $counts = [];

        foreach (self::PURGED_TABLES as $table) {
            $result = $db->pquery('DELETE FROM ' . $table, []);
            $counts[$table] = (int)$db->getAffectedRowCount($result);
        }

        $db->pquery(
            'UPDATE df_mailmanager_folder SET uidvalidity = NULL, last_uid = NULL, delta_cursor = NULL, flags_modseq = NULL,
                state_signature = NULL, history_complete = 0, total_count = 0, unread_count = 0, unread_base = 0',
            []
        );

        return $counts;
    }
}
