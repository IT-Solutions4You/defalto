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
 * The owner (or the admin) lets a deputy work in a personal mailbox
 * (mode=grant, optional last day) or ends it (mode=revoke)
 */
class MailManager_ProSaveMySharing_Action extends MailManager_ProAbstract_Action
{
    /**
     * @param Vtiger_Request $request
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        if (!Users_Privileges_Model::isPermitted($request->getModule(), 'DetailView')) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        if (!MailManager_Module_Model::isProEnabled()) {
            throw new Exception(vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager'));
        }

        return true;
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        $request->validateWriteAccess();
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $response = new Vtiger_Response();
        $mailboxId = (int)$request->get('mailboxid');

        // the owner shares the own personal mailbox; the admin any personal one
        $mailbox = MailManager_ProMySettings_Model::getManagedMailbox($mailboxId);
        if (!$mailbox || $mailbox['mailbox_type'] !== 'personal') {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        try {
            if ($request->get('mode') === 'revoke') {
                MailManager_ProSharing_Model::revoke($mailboxId, (int)$request->get('userid'));
            } else {
                MailManager_ProSharing_Model::grant(
                    $mailbox,
                    (int)$request->get('userid'),
                    (string)$request->get('valid_until')
                );
            }
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
