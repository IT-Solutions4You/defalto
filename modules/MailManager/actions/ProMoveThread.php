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
 * Moves a conversation to another folder (Spam / Archive / user-picked):
 * messages are moved on the mail server first, then the cache is removed —
 * a scanned target folder re-imports the thread on its next sync.
 * Used by the toolbar actions and the AI suggestion card.
 */
class MailManager_ProMoveThread_Action extends MailManager_ProAbstract_Action
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
        $target = $request->get('target');

        if (!$thread || !in_array($target, MailManager_ProServerMove_Model::MOVE_TARGETS, true)) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        try {
            $moveTarget = MailManager_ProServerMove_Model::getMoveTarget(
                (int)$mailbox['mailboxid'],
                (int)$thread['threadid'],
                $target,
                (int)$request->get('folderid')
            );
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
            $response->emit();

            return;
        }

        $response->setResult(MailManager_ProServerMove_Model::moveThread(
            $mailbox,
            $thread,
            $moveTarget,
            'restore' === $target,
            'inbox' === $request->get('from')
        ));
        $response->emit();
    }
}
