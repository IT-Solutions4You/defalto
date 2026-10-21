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
 * Answering a ticket-linked conversation with a ticket COMMENT instead of a
 * mail — the CRM's own workflows ("Comment Added From CRM") then mail the
 * customer with the helpdesk template. The comment is kept as a note of the
 * conversation too: the workflow mail never reaches the mailbox's Sent
 * folder, the thread would miss its own answer
 */
class MailManager_ProTicketReply_Model
{
    /**
     * The ticket a comment reply goes to — the newest linked ticket the user
     * may view; null when the conversation has none or the user may not comment
     *
     * @param int $threadId
     * @return array|null ['crmid', 'label', 'ticket_no']
     */
    public static function getReplyTicket($threadId)
    {
        if (!$threadId || !vtlib_isModuleActive('HelpDesk') || !vtlib_isModuleActive('ModComments')
            || !Users_Privileges_Model::isPermitted('ModComments', 'CreateView')) {
            return null;
        }

        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT r.crmid, ce.label, tt.ticket_no FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             INNER JOIN vtiger_troubletickets tt ON tt.ticketid = r.crmid
             WHERE r.threadid = ? ORDER BY r.relid DESC',
            [(int)$threadId]
        );
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            if (Users_Privileges_Model::isPermitted('HelpDesk', 'DetailView', (int)$row['crmid'])) {
                return [
                    'crmid' => (int)$row['crmid'],
                    'label' => decode_html((string)$row['label']),
                    'ticket_no' => (string)$row['ticket_no'],
                ];
            }
        }

        return null;
    }

    /**
     * @return bool an active ticket workflow reacts to an added comment —
     *              without one the customer gets nothing from a comment
     */
    public static function hasCommentWorkflow()
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            "SELECT 1 FROM com_vtiger_workflows
             WHERE module_name = 'HelpDesk' AND status = 1 AND test LIKE '%_VT_add_comment%' LIMIT 1",
            []
        );

        return (bool)$adb->num_rows($result);
    }

    /**
     * Writes the reply as a comment of the ticket (the core save fires the
     * ticket's comment workflows) and keeps it as a note of the conversation.
     * A file waiting in $_FILES['filename'] is attached by the core save
     *
     * @param array $thread thread row
     * @param array $ticket getReplyTicket() row
     * @param string $text plain text
     * @param bool $isPrivate an internal comment — the workflows skip the customer
     * @param int $userId
     * @return int ModComments record id
     */
    public static function send(array $thread, array $ticket, $text, $isPrivate, $userId)
    {
        $record = Vtiger_Record_Model::getCleanInstance('ModComments');
        $record->set('commentcontent', MailManager_ProMailText_Model::toCoreCharset($text));
        $record->set('related_to', (int)$ticket['crmid']);
        $record->set('assigned_user_id', $userId);
        $record->set('userid', $userId);
        $record->set('is_private', $isPrivate ? 1 : 0);
        $record->save();
        $commentId = (int)$record->getId();

        $adb = PearDatabase::getInstance();
        $now = gmdate('Y-m-d H:i:s');
        $adb->pquery(
            'INSERT INTO df_mailmanager_note (threadid, crmid, commentid, userid, body, is_private, createdtime)
             VALUES (?,?,?,?,?,?,?)',
            [(int)$thread['threadid'], (int)$ticket['crmid'], $commentId, $userId, $text, $isPrivate ? 1 : 0, $now]
        );
        // the answer leads the conversation in the list like a sent mail does
        $adb->pquery(
            'UPDATE df_mailmanager_thread
             SET snippet = ?, last_message_at = GREATEST(IFNULL(last_message_at, "1970-01-01"), ?)
             WHERE threadid = ?',
            [mb_substr(trim(preg_replace('/\s+/u', ' ', $text)), 0, 255), $now, (int)$thread['threadid']]
        );

        MailManager_ProHistory_Model::logThread(
            (int)$thread['threadid'],
            MailManager_ProHistory_Model::ACTION_SEND,
            MailManager_ProHistory_Model::SOURCE_USER,
            'HelpDesk',
            $ticket['label'],
            (int)$ticket['crmid']
        );

        return $commentId;
    }

    /**
     * The comment replies of a conversation shaped like its messages — the
     * conversation view shows them among the mail, in time order
     *
     * @param int $threadId
     * @return array rows with the keys Thread::getThreadMessages() gives
     */
    public static function getNotes($threadId)
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery(
            'SELECT * FROM df_mailmanager_note WHERE threadid = ? ORDER BY createdtime, noteid',
            [(int)$threadId]
        );

        $notes = [];
        $count = $adb->num_rows($result);
        for ($i = 0; $i < $count; $i++) {
            $row = $adb->raw_query_result_rowdata($result, $i);
            $notes[] = [
                'messageid' => 0,
                'is_note' => true,
                'is_private' => (int)$row['is_private'] === 1,
                'note_crmid' => (int)$row['crmid'],
                'direction' => 'out',
                'is_draft' => 0,
                'is_forward' => false,
                'from_display' => trim((string)getOwnerName((int)$row['userid'])),
                'from_name' => '',
                'from_email' => '',
                'to_display' => '',
                'cc_display' => '',
                'bcc_display' => '',
                // a CRM time — in UTC like the mail it is sorted among
                'sent_at' => MailManager_ProMailFormat_Model::dbToUtcTime((string)$row['createdtime']),
                'time_display' => MailManager_ProMailFormat_Model::formatDateTime(
                    MailManager_ProMailFormat_Model::dbToUtcTime((string)$row['createdtime'])
                ),
                'body_html' => '',
                'body_plain' => (string)$row['body'],
                'attachments' => [],
            ];
        }

        return $notes;
    }

    /**
     * The text of a ticket comment — the composer editor gives HTML, a comment is plain text.
     *
     * @param string $html composer body
     *
     * @return string '' when nothing was written
     */
    public static function getCommentText(string $html): string
    {
        return trim(MailManager_ProMailText_Model::htmlToText($html));
    }

    /**
     * A comment carries one file — the core save takes it from $_FILES['filename'] in
     * the shape of a multiple-file field (CRMEntity reshapes the uploads itself before
     * it saves, uitype 61).
     *
     * @param array|null $upload the $_FILES entry of the composer attachment
     *
     * @return array the $_FILES the core save of the comment reads
     */
    public static function getCommentFiles(?array $upload): array
    {
        $files = [];

        if ($upload && (int)$upload['error'] === 0 && (int)$upload['size'] > 0) {
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) {
                $files['filename'][$key] = [$upload[$key]];
            }
        }

        return $files;
    }
}
