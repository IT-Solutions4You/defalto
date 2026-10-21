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
 * Thread curation updates — manual label change and triage suggestion dismissal
 */
class MailManager_ProUpdateThread_Action extends MailManager_ProAbstract_Action
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

        $changes = [];

        foreach (MailManager_ProThreadUpdate_Model::THREAD_FIELDS as $field) {
            if ($request->has($field)) {
                $changes[$field] = $request->get($field);
            }
        }

        $response->setResult(['success' => MailManager_ProThreadUpdate_Model::updateThread($mailbox, $thread, $changes, $userId)]);
        $response->emit();
    }
}
