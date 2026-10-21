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
 * Traces of the mail flow on CRM records — inbound and outgoing mail logged
 * as ticket/opportunity comments (ITS4YouEmails records: EmailFiler)
 */
class MailManager_ProCrmLog_Model
{
    /**
     * Logs a new inbound message on the thread's case record (Ticket/Opp) as
     * a comment — the deterministic sibling of the manual Add Comment action.
     * Best-effort: a failure never breaks the sync
     *
     * @param int $threadId
     * @param int $messageId freshly stored message
     * @param array $message unified message array from the transport
     * @param string $bodyPlain already extracted mail text
     * @return void
     */
    public static function autoCommentIncoming($threadId, $messageId, array $message, $bodyPlain)
    {
        if (MailManager_ProAiConfig_Model::get('ai_feature_autocomment') !== '1' || trim($bodyPlain) === '') {
            return;
        }

        $adb = PearDatabase::getInstance();
        $caseModules = MailManager_ProRelate_Model::quoteList(MailManager_ProRelate_Model::CASE_MODULES);
        $case = $adb->pquery(
            'SELECT r.crmid FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ? AND ce.setype IN (' . $caseModules . ')
             ORDER BY FIELD(ce.setype, ' . $caseModules . '), r.relid DESC
             LIMIT 1',
            [$threadId]
        );
        if (!$adb->num_rows($case)) {
            return;
        }
        $caseId = (int)$adb->query_result($case, 0, 'crmid');

        try {
            $from = trim((string)$message['from_name']) !== ''
                ? $message['from_name'] . ' <' . $message['from_email'] . '>'
                : $message['from_email'];
            $record = Vtiger_Record_Model::getCleanInstance('ModComments');
            $record->set(
                'commentcontent',
                MailManager_ProMailText_Model::toCoreCharset(
                    sprintf(vtranslate('LBL_PRO_COMMENT_EMAIL_FROM', 'MailManager'), $from, $message['sent_at']) . "\n\n"
                    . MailManager_ProReplyText_Model::forMessage($messageId, $bodyPlain)
                )
            );
            $record->set('related_to', $caseId);
            $record->set('assigned_user_id', Users_Record_Model::getCurrentUserModel()->getId());
            $record->set('userid', Users_Record_Model::getCurrentUserModel()->getId());
            $record->save();

            // The AI card shows "logged as a comment" while this stays the
            // newest inbound message of the conversation
            $adb->pquery(
                'UPDATE df_mailmanager_thread SET autocomment_msgid = ? WHERE threadid = ?',
                [$messageId, $threadId]
            );
        } catch (Exception $e) {
            // comment logging must never break the mailbox sync
        }
    }

    /**
     * Logs an outgoing mail (reply or forward) on the thread's linked ticket
     * as a comment — the ticket keeps the full story: what was sent and to
     * whom. Best-effort: a failure never breaks the send
     *
     * @param int $threadId
     * @param array $to recipient addresses
     * @param array $cc
     * @param string $subject
     * @param string $bodyHtml reply = own text only, forward = incl. quote
     * @param bool $isForward changes the comment lead-in
     * @return void
     */
    public static function commentOutgoingMail($threadId, array $to, array $cc, $subject, $bodyHtml, $isForward)
    {
        $bodyPlain = trim(MailManager_ProMailText_Model::htmlToText((string)$bodyHtml));
        if (MailManager_ProAiConfig_Model::get('ai_feature_autocomment') !== '1' || $bodyPlain === '') {
            return;
        }

        $adb = PearDatabase::getInstance();
        $case = $adb->pquery(
            "SELECT r.crmid FROM df_mailmanager_rel r
             INNER JOIN vtiger_crmentity ce ON ce.crmid = r.crmid AND ce.deleted = 0
             WHERE r.threadid = ? AND ce.setype = 'HelpDesk'
             ORDER BY r.relid DESC LIMIT 1",
            [$threadId]
        );
        if (!$adb->num_rows($case)) {
            return;
        }
        $caseId = (int)$adb->query_result($case, 0, 'crmid');

        try {
            $recipients = implode(', ', array_merge($to, $cc));
            $record = Vtiger_Record_Model::getCleanInstance('ModComments');
            $record->set(
                'commentcontent',
                MailManager_ProMailText_Model::toCoreCharset(
                    sprintf(
                        vtranslate($isForward ? 'LBL_PRO_COMMENT_FORWARDED_TO' : 'LBL_PRO_COMMENT_REPLY_SENT_TO', 'MailManager'),
                        $recipients
                    )
                    . "\n" . sprintf(vtranslate('LBL_PRO_COMMENT_SUBJECT', 'MailManager'), $subject) . "\n\n" . $bodyPlain
                )
            );
            $record->set('related_to', $caseId);
            $record->set('assigned_user_id', Users_Record_Model::getCurrentUserModel()->getId());
            $record->set('userid', Users_Record_Model::getCurrentUserModel()->getId());
            $record->save();
        } catch (Exception $e) {
            // comment logging must never break the send
        }
    }
}
