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
 * Answers a ticket-linked conversation with a comment on the ticket
 * (the composer's "By comment" channel)
 */

class MailManager_ProSendComment_Action extends MailManager_ProAbstract_Action
{
    /**
     * Write action — only a POST with a valid CSRF token is accepted.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }

    /**
     * Answers a ticket-linked conversation with a ticket comment.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProInbox_Model::useUtf8mb4();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $thread = $mailbox
            ? MailManager_ProInbox_Model::getThread((int)$request->get('thread'), (int)$mailbox['mailboxid'])
            : null;
        $ticket = $thread ? MailManager_ProTicketReply_Model::getReplyTicket((int)$thread['threadid']) : null;
        if (!$ticket) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        // the composer editor gives HTML, a comment is plain text
        $text = MailManager_ProTicketReply_Model::getCommentText((string)$request->getRaw('body'));

        if ($text === '') {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'MailManager')]);
            $response->emit();

            return;
        }

        // the core save of the comment reads its one file from $_FILES['filename']
        $_FILES = MailManager_ProTicketReply_Model::getCommentFiles($_FILES['attachment'] ?? null);

        try {
            MailManager_ProTicketReply_Model::send($thread, $ticket, $text, $request->get('is_private') === '1', $userId);
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
