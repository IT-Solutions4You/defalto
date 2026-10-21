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
 * Wizard step 3 save — persists the mailbox from the wizard session
 */
class Settings_MailManager_SaveMailbox_Action extends Settings_MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();

        $response->setResult(Settings_MailManager_Wizard_Model::saveMailbox(
            $this->getMailboxInput($request),
            (int)Users_Record_Model::getCurrentUserModel()->getId()
        ));
        $response->emit();
    }

    /**
     * The mailbox values the wizard sent.
     *
     * @param Vtiger_Request $request
     *
     * @return array input of Settings_MailManager_Wizard_Model::saveMailbox()
     */
    protected function getMailboxInput(Vtiger_Request $request): array
    {
        return [
            'email' => $request->get('email'),
            'provider' => $request->get('provider'),
            'name' => $request->getRaw('name'),
            'mailbox_type' => $request->get('mailbox_type'),
            'shared_with' => $request->getRaw('shared_with'),
            'folders' => $request->getRaw('folders'),
            'sync_from_days' => $request->get('sync_from_days'),
            'is_default' => $request->get('is_default'),
        ];
    }
}
