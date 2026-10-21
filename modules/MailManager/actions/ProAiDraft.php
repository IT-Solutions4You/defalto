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
 * AI reply draft for the composer (editable — never sent automatically)
 */
class MailManager_ProAiDraft_Action extends MailManager_ProAbstract_Action
{
    /**
     * Requires a valid CSRF token and a POST request (the action writes data or spends AI tokens).
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
     * Handles the request and emits the JSON result.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // Long-running request (IMAP / AI / SMTP) — release the PHP
        // session lock so parallel UI requests do not queue behind it
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $thread = $mailbox
            ? MailManager_ProInbox_Model::getThread((int)$request->get('thread'), (int)$mailbox['mailboxid'])
            : null;

        if (!$thread) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        $response->setResult(MailManager_ProAiAssist_Model::draft(
            $thread,
            $mailbox,
            $request->get('tone'),
            $request->get('language'),
            (string)$request->getRaw('current_html')
        ));
        $response->emit();
    }
}
