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
 * My Settings — deletes a label rule learned in one of the user's personal mailboxes
 */
class MailManager_ProDeleteMyLabelRule_Action extends MailManager_ProAbstract_Action
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

        // a rule of an own personal mailbox, any rule for the admin
        $response->setResult([
            'success' => MailManager_ProLabelRule_Model::deleteManagedRule(
                (int)$request->get('ruleid'),
                MailManager_ProMySettings_Model::getManagedMailboxIds()
            ),
        ]);
        $response->emit();
    }
}
