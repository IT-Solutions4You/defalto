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
 * The admin ends a deputy's access to a user's personal mailbox
 */
class Settings_MailManager_RevokeDeputy_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProSharing_Model::revoke((int)$request->get('mailboxid'), (int)$request->get('userid'));

        $response = new Vtiger_Response();
        $response->setResult(['success' => true]);
        $response->emit();
    }
}
