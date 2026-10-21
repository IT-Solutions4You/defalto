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
 * The sorting queue of the inbox: mode "list" = what waits for the AI
 * triage, mode "run" = classify the next few conversations and say what
 * happened to them (label, automatic move)
 */
class MailManager_ProTriageQueue_Action extends MailManager_ProAbstract_Action
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
        // Long-running request (AI, IMAP moves) — release the PHP session lock
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        if (!$mailbox) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }
        $mailboxId = (int)$mailbox['mailboxid'];

        try {
            $result = $request->get('mode') === 'run'
                ? MailManager_ProTriageQueue_Model::runStep($mailboxId, $request->get('force') === '1')
                : ['pending' => MailManager_ProAiTriage_Model::getPending($mailboxId)];
            $response->setResult(['success' => true] + $result);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }
        $response->emit();
    }
}
