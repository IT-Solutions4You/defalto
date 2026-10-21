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
 * Test of the outgoing server (SMTP) — connects and signs in without
 * sending. The wizard remembers passed settings for the save; editing a
 * mailbox tests with its stored password when the field stays empty
 */
class Settings_MailManager_TestSmtp_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        $server = trim((string)$request->get('smtp_server'));
        if ($server === '') {
            $response->setResult([
                'success' => false,
                'message' => vtranslate('LBL_SMTP_SERVER_REQUIRED', 'Settings:MailManager'),
            ]);
            $response->emit();

            return;
        }
        $recordId = (int)$request->get('record');
        $password = (string)$request->getRaw('smtp_password');

        if ($password === '') {
            $password = $request->get('same_password') === '1'
                ? Settings_MailManager_Wizard_Model::getImapPassword()
                : $this->getStoredPassword($recordId);
        }

        $response->setResult(Settings_MailManager_Wizard_Model::testSmtp(
            $server,
            $request->get('smtp_port'),
            $request->get('smtp_encryption'),
            $request->get('smtp_username'),
            $password,
            $recordId
        ));
        $response->emit();
    }

    /**
     * @param int $recordId mailbox being edited (0 = none)
     * @return string the stored SMTP password, plain text
     */
    protected function getStoredPassword($recordId)
    {
        return Settings_MailManager_Mailbox_Model::getSmtpPassword((int)$recordId);
    }
}
