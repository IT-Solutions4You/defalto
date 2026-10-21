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
 * Checks a reply before it is sent (I15) — warnings only, the composer sends anyway
 * when the user wants to or when the check fails
 */
class MailManager_ProCheckDraft_Action extends MailManager_ProAbstract_Action
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
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // an AI call — the session must not block the UI
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $threadId = (int)$request->get('thread');

        if (!$mailbox || ($threadId && !MailManager_ProInbox_Model::getThread($threadId, (int)$mailbox['mailboxid']))) {
            $response->setResult(['success' => false, 'issues' => []]);
            $response->emit();

            return;
        }

        try {
            MailManager_ProInbox_Model::useUtf8mb4();
            $response->setResult(['success' => true, 'issues' => MailManager_ProDraftCheck_Model::check(
                $threadId,
                trim((string)$request->getRaw('subject')),
                (string)$request->getRaw('body'),
                '1' === $request->get('has_attachments'),
                (string)$request->get('to')
            )]);
        } catch (Exception $e) {
            // the check is an assist — the composer sends anyway
            $response->setResult(['success' => false, 'issues' => [], 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
