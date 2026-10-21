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
 * Saves the mailbox settings edit (name, type, members, default, scanned folders)
 */
class Settings_MailManager_UpdateMailbox_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        $response->setResult(Settings_MailManager_Mailbox_Model::saveSettings((int)$request->get('record'), [
            'name' => $request->get('name'),
            'mailbox_type' => $request->get('mailbox_type'),
            'shared_with' => $request->getRaw('shared_with'),
            'folders' => $request->getRaw('folders'),
            'is_default' => $request->get('is_default'),
            'imap_cert_fingerprint' => $request->get('imap_cert_fingerprint'),
            'smtp_mode' => $request->get('smtp_mode'),
            'smtp_server' => $request->get('smtp_server'),
            'smtp_port' => $request->get('smtp_port'),
            'smtp_encryption' => $request->get('smtp_encryption'),
            'smtp_username' => $request->get('smtp_username'),
            'smtp_password' => $request->getRaw('smtp_password'),
        ]));
        $response->emit();
    }
}
