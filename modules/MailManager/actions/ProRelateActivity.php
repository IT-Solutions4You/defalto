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
 * Relates a freshly quick-created Appointment to the conversation's records.
 * Appointments hold their relations in FIELDS (account_id, parent_id and the
 * multi-value contact_id), so a post-save field update is the reliable way.
 */
class MailManager_ProRelateActivity_Action extends MailManager_ProAbstract_Action
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
     * Relates a freshly quick-created Appointment to the records linked to the conversation.
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

        $eventId = (int)$request->get('event');
        $targetId = (int)$request->get('crmid');
        $eventModule = $eventId ? getSalesEntityType($eventId) : '';

        if (!$thread || MailManager_ProRelate_Model::APPOINTMENT_MODULE !== $eventModule
            || !Users_Privileges_Model::isPermitted($eventModule, 'EditView', $eventId)) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        // Both references must be records linked to the thread. A contact-only
        // conversation sends no target (a contact belongs in contact_id)
        $contactId = MailManager_ProRelate_Model::getAppointmentContactId((int)$thread['threadid'], $targetId);

        if (null === $contactId) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        try {
            MailManager_ProRelate_Model::saveAppointmentLink((int)$thread['threadid'], $eventId, $targetId, $contactId, $userId);
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
