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
 * The AI assistant of a conversation — its chat (history), a question, the
 * summary chip and a fresh start
 */
class MailManager_ProAssistant_Action extends MailManager_ProAbstract_Action
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
        // an AI answer takes a while — parallel UI requests must not queue behind the session lock
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $thread = $mailbox
            ? MailManager_ProInbox_Model::getThread((int)$request->get('thread'), (int)$mailbox['mailboxid'])
            : null;
        if (!$thread || !MailManager_ProAssistant_Model::isEnabled()) {
            $response->setResult(['success' => false, 'message' => '']);
            $response->emit();

            return;
        }

        $mode = (string)$request->get('mode');
        if ($mode === 'ask') {
            $result = MailManager_ProAssistant_Model::ask($thread, $userId, (string)$request->get('question'));
        } elseif ($mode === 'summary') {
            $result = MailManager_ProAssistant_Model::summary($thread, $userId, (string)$request->get('question'));
        } else {
            if ($mode === 'clear') {
                MailManager_ProAssistant_Model::clear((int)$thread['threadid'], $userId);
            }
            $result = [
                'success' => true,
                'history' => MailManager_ProAssistant_Model::getHistory((int)$thread['threadid'], $userId),
            ];
        }
        $response->setResult($result);
        $response->emit();
    }
}
