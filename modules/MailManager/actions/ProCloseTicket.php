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
 * Accepts the AI close-ticket suggestion: one AI call summarizes the problem
 * and the fix from the FULL conversation into the ticket Solution field, then
 * the ticket is closed. The summary call runs only here — on explicit accept
 */

class MailManager_ProCloseTicket_Action extends MailManager_ProAbstract_Action
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
     * Closes the linked open ticket, with an AI solution summary when AI is configured.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        // AI summary + record save — release the session lock like the other
        // long-running actions
        session_write_close();
        MailManager_ProInbox_Model::useUtf8mb4();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $thread = $mailbox
            ? MailManager_ProInbox_Model::getThread((int)$request->get('thread'), (int)$mailbox['mailboxid'])
            : null;

        // The target is re-derived server-side — the client only points at
        // the thread, never at the ticket
        $ticket = $thread ? MailManager_ProRelate_Model::findOpenLinkedTicket((int)$thread['threadid']) : null;
        if (!$ticket) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_RECORD_NOT_FOUND', 'Vtiger')]);
            $response->emit();

            return;
        }
        $ticketId = (int)$ticket['crmid'];
        if (!Users_Privileges_Model::isPermitted('HelpDesk', 'EditView', $ticketId)) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        $response->setResult(MailManager_ProTicketClose_Model::closeTicket($thread, $ticket));
        $response->emit();
    }
}
