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
 * My Settings — deletes a learned spam rule of one of the user's personal
 * mailboxes (the AI unlearns the sender)
 */
class MailManager_ProDeleteMySpamRule_Action extends MailManager_ProAbstract_Action
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
        // a rule of an own personal mailbox, any rule for the admin
        $deleted = MailManager_ProSpamGuard_Model::deleteRule(
            (int)$request->get('ruleid'),
            MailManager_ProMySettings_Model::getManagedMailboxIds()
        );

        $response = new Vtiger_Response();
        $response->setResult(['success' => $deleted]);
        $response->emit();
    }
}
