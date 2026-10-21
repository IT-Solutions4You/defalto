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
 * Deletes a conversation: messages are moved to Trash on the mail server
 * (hard-deleted when no Trash exists), then the cache is removed.
 * The server step must succeed first — on failure nothing is deleted.
 */
class MailManager_ProDeleteThread_Action extends MailManager_ProAbstract_Action
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
        // Long-running request (IMAP) — release the PHP session lock so the
        // list and the other UI requests do not queue behind it
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

        $response->setResult(MailManager_ProServerMove_Model::deleteThread($mailbox, $thread));
        $response->emit();
    }
}
