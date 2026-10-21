<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_DeleteMailbox_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        $mailboxId = (int)$request->get('mailboxid');
        if ($mailboxId < 1) {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        Settings_MailManager_Mailbox_Model::delete($mailboxId);
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
