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
 * Saves the composer content as a draft — request handling only, the work
 * is done by MailManager_ProOutbox_Model::saveDraft()
 */
class MailManager_ProSaveDraft_Action extends MailManager_ProAbstract_Action
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
        // Long-running request (IMAP) — release the PHP session lock
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

        $mail = MailManager_ProOutbox_Model::getDraftMail([
            'to' => $request->get('to'),
            'cc' => $request->get('cc'),
            'bcc' => $request->get('bcc'),
            // raw read — the request purifier treats the subject as HTML
            'subject' => (string)$request->getRaw('subject'),
            'body' => (string)$request->getRaw('body'),
            'thread' => $request->get('thread'),
            'reply_message' => $request->get('reply_message'),
            'draft_message' => $request->get('draft_message'),
            'compose_mode' => $request->get('compose_mode'),
            'quote_message' => $request->get('quote_message'),
            'forward_attachments' => $request->get('forward_attachments'),
        ]);

        if (MailManager_ProOutbox_Model::isEmptyDraft($mail)) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'MailManager')]);
            $response->emit();

            return;
        }

        $response->setResult(MailManager_ProOutbox_Model::saveDraft($mailbox, $mail));
        $response->emit();
    }
}
