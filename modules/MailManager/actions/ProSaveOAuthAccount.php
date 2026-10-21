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
 * Add mailbox wizard of a user, Microsoft 365 / Google: stores the mailbox the user signed
 * in to (auth.php) as the personal mailbox
 */
class MailManager_ProSaveOAuthAccount_Action extends MailManager_ProAbstract_Action
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
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $provider = (string)$request->get('provider');
        $email = trim((string)$request->get('email'));
        $clientId = trim((string)$request->get('client_id'));
        $response = new Vtiger_Response();

        if (!MailManager_ProOAuthAccount_Model::isValidAccount($provider, $email, $clientId)) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'Settings:MailManager')]);
            $response->emit();

            return;
        }

        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();
        $mailboxId = MailManager_ProOAuthAccount_Model::saveAccount($userId, $provider, $email, $clientId);

        $response->setResult(['success' => true, 'mailboxid' => $mailboxId]);
        $response->emit();
    }
}
