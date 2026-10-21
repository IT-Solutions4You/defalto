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
 * Style profile of the user's OWN mailbox: mode=generate learns it from the
 * sent mail, mode=save stores the hand-edited version. Ownership replaces
 * the admin check of the Settings variant
 */
class MailManager_ProMyStyleProfile_Action extends MailManager_ProAbstract_Action
{
    /**
     * Requires a valid CSRF token and a POST request (the action writes data or spends AI tokens).
     *
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
     * Handles the request and emits the JSON result.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        // The generate mode runs an AI call — do not block parallel UI requests
        session_write_close();
        $response = new Vtiger_Response();

        // the owner's mailbox, any mailbox for the admin
        $mailbox = MailManager_ProMySettings_Model::getManagedMailbox((int)$request->get('mailboxid'));
        if (!$mailbox) {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        if ($request->get('mode') === 'save') {
            MailManager_ProAiAssist_Model::saveStyleProfile((int)$mailbox['mailboxid'], (string)$request->getRaw('profile'));
            $response->setResult(['success' => true]);
        } else {
            $response->setResult(MailManager_ProAiAssist_Model::generateStyleProfile($mailbox));
        }

        $response->emit();
    }
}
