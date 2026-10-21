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
 * Sends a reply / forward / new mail from a mailbox — request handling only,
 * the work is done by MailManager_ProOutbox_Model::send()
 */
class MailManager_ProSendMail_Action extends MailManager_ProAbstract_Action
{
    /**
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
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // Long-running request (IMAP / AI / SMTP) — release the PHP
        // session lock so parallel UI requests do not queue behind it
        session_write_close();
        MailManager_ProInbox_Model::useUtf8mb4();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        if (!$mailbox) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        $mail = MailManager_ProOutbox_Model::getMail([
            'to' => $request->get('to'),
            'cc' => $request->get('cc'),
            'bcc' => $request->get('bcc'),
            'mark_done' => $request->get('mark_done'),
            // raw read — the request purifier treats the subject as HTML and
            // mangles plain text ("Price < 100", "... (week 1)")
            'subject' => (string)$request->getRaw('subject'),
            'body' => (string)$request->getRaw('body'),
            'thread' => $request->get('thread'),
            'reply_message' => $request->get('reply_message'),
            'quote_message' => $request->get('quote_message'),
            'draft_message' => $request->get('draft_message'),
            'compose_mode' => $request->get('compose_mode'),
            'to_records' => $request->get('to_records'),
            'forward_attachments' => $request->get('forward_attachments'),
            'document_attachments' => $request->get('document_attachments'),
            'uploads' => $_FILES['attachments'] ?? null,
            'scheduled_at' => $request->isEmpty('scheduled_at') ? '' : (string)$request->get('scheduled_at'),
        ]);
        $error = MailManager_ProOutbox_Model::getMailError($mail);

        if ('' !== $error) {
            $response->setResult(['success' => false, 'message' => $error]);
            $response->emit();

            return;
        }

        try {
            $response->setResult(MailManager_ProOutbox_Model::send($mailbox, $mail));
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
