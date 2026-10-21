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
 * The certificate of an existing IMAP mailbox's server — does it verify, is
 * it the trusted one, and its details for the trust dialog. Read before the
 * login, the stored password is not needed
 */
class Settings_MailManager_CheckCertificate_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $mailbox = Settings_MailManager_Mailbox_Model::getById((int)$request->get('record'));

        if (!$mailbox || $mailbox['provider'] !== 'imap') {
            $response->setResult(['success' => false]);
            $response->emit();

            return;
        }

        $check = Settings_MailManager_Certificate_Model::check(
            (string)$mailbox['imap_server'],
            (int)$mailbox['imap_port'],
            (string)$mailbox['imap_encryption'],
            (string)$mailbox['imap_cert_fingerprint']
        );

        $response->setResult(array_merge(['success' => $check['certificate'] !== null], $check));
        $response->emit();
    }
}
