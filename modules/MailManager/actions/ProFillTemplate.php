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
 * Fills an EMAIL Maker template for the composer — subject, body and the
 * template's Documents — from the conversation's record and the recipient
 */
class MailManager_ProFillTemplate_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailbox = MailManager_ProInbox_Model::getAccessibleMailbox((int)$request->get('mailbox'), $userId);
        $threadId = (int)$request->get('thread');

        if (!$mailbox || ($threadId && !MailManager_ProInbox_Model::getThread($threadId, (int)$mailbox['mailboxid']))) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        try {
            $filled = MailManager_ProTemplate_Model::fill(
                (int)$request->get('template'),
                $threadId,
                (string)(MailManager_ProOutbox_Model::parseAddresses($request->get('to'))[0] ?? '')
            );
            $response->setResult($filled
                ? ['success' => true] + $filled
                : ['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
