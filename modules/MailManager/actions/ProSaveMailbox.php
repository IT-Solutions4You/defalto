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
 * The users' own mailbox wizard — saves the mailbox as the user's personal
 * one (the owner is the signed-in user); never shared, never an edit
 */
class MailManager_ProSaveMailbox_Action extends Settings_MailManager_SaveMailbox_Action
{
    /**
     * A user's own mailbox — the module access, not the admin
     *
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
     * A user's own mailbox is personal and never shared — whatever the request says.
     *
     * @param Vtiger_Request $request
     *
     * @return array input of Settings_MailManager_Wizard_Model::saveMailbox()
     */
    protected function getMailboxInput(Vtiger_Request $request): array
    {
        return array_merge(parent::getMailboxInput($request), [
            'mailbox_type' => 'personal',
            'shared_with' => '[]',
        ]);
    }
}
