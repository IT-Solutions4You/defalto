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
 * Detects the mail provider from an email address — known consumer domains first,
 * then the domain MX records (Microsoft 365 / Google Workspace / generic IMAP)
 */
class Settings_MailManager_DetectProvider_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $response->setResult(Settings_MailManager_Wizard_Model::detectProvider($request->get('email')));
        $response->emit();
    }
}
