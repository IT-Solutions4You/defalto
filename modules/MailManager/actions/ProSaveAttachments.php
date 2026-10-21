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
 * Saves a conversation's attachments as Documents linked to a CRM record —
 * fired after create-from-mail for record types where the mail's attachments
 * belong to the case (Ticket / Opportunity)
 */
class MailManager_ProSaveAttachments_Action extends MailManager_ProAbstract_Action
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
     * Saves the conversation's attachments as Documents of a CRM record.
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
        $crmid = (int)$request->get('crmid');

        $module = $crmid > 0 && isRecordExists($crmid) ? (string)getSalesEntityType($crmid) : '';

        if (!$thread || '' === $module || !Users_Privileges_Model::isPermitted($module, 'EditView', $crmid)) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        try {
            // latest=1: only the files of the newest received mail (a task made
            // from it) — not everything the conversation ever carried
            $saved = MailManager_ProDocumentFiler_Model::saveAttachmentsToRecords(
                $mailbox,
                (int)$thread['threadid'],
                $crmid,
                $userId,
                '1' === $request->get('latest')
            );
            $response->setResult(['success' => true, 'saved' => $saved]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
