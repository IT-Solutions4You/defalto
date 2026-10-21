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
 * Wizard step 2 (IMAP) connection test — plain socket IMAP LOGIN + LIST,
 * no ext-imap dependency. On success the credentials are stored in the
 * wizard session for step 3.
 */
class Settings_MailManager_TestImap_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setResult(Settings_MailManager_Wizard_Model::testImap([
            'server' => $request->get('server'),
            'port' => $request->get('port'),
            'encryption' => $request->get('encryption'),
            'username' => $request->get('username'),
            'password' => $request->getRaw('password'),
            'email' => $request->get('email'),
            'cert_fingerprint' => $request->get('cert_fingerprint'),
        ]));
        $response->emit();
    }
}
