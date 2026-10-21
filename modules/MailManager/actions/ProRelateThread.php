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
 * Links / unlinks a conversation to a CRM record (op = link | unlink)
 */
class MailManager_ProRelateThread_Action extends MailManager_ProAbstract_Action
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
     * Links, unlinks or dismisses a CRM record for the conversation (op = link | unlink | dismiss).
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
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

        $crmid = (int)$request->get('crmid');
        if ($request->get('op') === 'unlink') {
            MailManager_ProRelate_Model::unlink((int)$thread['threadid'], $crmid);
            $response->setResult(['success' => true]);
        } elseif ($request->get('op') === 'dismiss') {
            // a suggestion the user does not want for this conversation
            MailManager_ProRelate_Model::dismissSuggestion((int)$thread['threadid'], $crmid);
            $response->setResult(['success' => true]);
        } else {
            // curates=0 for by-products (project task) — linked and shown,
            // but without labeling the thread or resolving the AI suggestion
            $response->setResult([
                'success' => MailManager_ProRelate_Model::linkWithReferences(
                    (int)$thread['threadid'],
                    $crmid,
                    $userId,
                    $request->get('curates') !== '0',
                    json_decode((string)$request->getRaw('enrich'), true) ?: []
                ),
            ]);
        }

        $response->emit();
    }
}
