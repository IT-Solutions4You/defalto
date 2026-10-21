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
 * A regular user saves their OWN personal mailbox (name, default, folders,
 * SMTP) — the ownership check replaces the admin check of the Settings area
 */
class MailManager_ProSaveMyMailbox_Action extends MailManager_ProAbstract_Action
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
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $mailbox = Settings_MailManager_Mailbox_Model::getById((int)$request->get('mailboxid'));
        if (!$mailbox || (int)$mailbox['owner_userid'] !== $userId || $mailbox['mailbox_type'] !== 'personal') {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_PERMISSION_DENIED', 'Vtiger')]);
            $response->emit();

            return;
        }

        // as typed — get() html-encodes "&" and the templates escape once more ("R&amp;D")
        $name = trim((string)$request->getRaw('name'));
        if ($name === '') {
            $response->setResult(['success' => false, 'message' => vtranslate('LBL_FILL_REQUIRED', 'MailManager')]);
            $response->emit();

            return;
        }

        try {
            MailManager_ProMySettings_Model::saveOwnMailbox((int)$mailbox['mailboxid'], $name, [
                'folders' => $request->getRaw('folders'),
                'is_default' => $request->get('is_default'),
                'smtp_mode' => $request->get('smtp_mode'),
                'smtp_server' => $request->get('smtp_server'),
                'smtp_port' => $request->get('smtp_port'),
                'smtp_encryption' => $request->get('smtp_encryption'),
                'smtp_username' => $request->get('smtp_username'),
                'smtp_password' => $request->getRaw('smtp_password'),
            ]);
            $response->setResult(['success' => true]);
        } catch (Exception $e) {
            $response->setResult(['success' => false, 'message' => $e->getMessage()]);
        }

        $response->emit();
    }
}
