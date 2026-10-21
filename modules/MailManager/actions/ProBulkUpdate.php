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
 * Bulk change of the conversations ticked in the list: op = label
 * (labelid), priority (priority) or read. The label and the priority are
 * cache-only, the read state is pushed to the mail server
 */
class MailManager_ProBulkUpdate_Action extends MailManager_ProAbstract_Action
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
        // the read state goes to the mail server — do not block the UI
        session_write_close();
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $operation = (string)$request->get('op');

        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $threads = $mailbox
            ? MailManager_ProThreadUpdate_Model::getBulkThreads(
                json_decode((string)$request->getRaw('threads'), true) ?: [],
                (int)$mailbox['mailboxid']
            )
            : [];

        if (!$threads || !in_array($operation, MailManager_ProThreadUpdate_Model::BULK_OPERATIONS, true)) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        $updated = MailManager_ProThreadUpdate_Model::updateThreads(
            $mailbox,
            $threads,
            $operation,
            [
                'labelid' => $request->get('labelid'),
                'priority' => $request->get('priority'),
                'state' => $request->get('state'),
            ],
            $userId
        );

        $response->setResult(['success' => $updated > 0, 'updated' => $updated]);
        $response->emit();
    }
}
