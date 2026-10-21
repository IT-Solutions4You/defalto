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
 * Marks a thread as read — in the cache and on the mail server (\Seen flag),
 * fired from the UI right after a conversation opens
 */
class MailManager_ProMarkThreadRead_Action extends MailManager_ProAbstract_Action
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

        // cache + \Seen push to the mail server
        MailManager_ProThreadUpdate_Model::markRead($mailbox, [(int)$thread['threadid']]);

        $response->setResult(['success' => true]);
        $response->emit();
    }
}
