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
 * The users' own mailbox wizard — the admin's certificate check
 */
class MailManager_ProCheckCertificate_Action extends Settings_MailManager_CheckCertificate_Action
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
        // the parent loads any mailbox by its id — here only one the user manages
        if (!Users_Privileges_Model::isPermitted($request->getModule(), 'DetailView')
            || !MailManager_ProMySettings_Model::getManagedMailbox((int)$request->get('record'))) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
        }

        if (!MailManager_Module_Model::isProEnabled()) {
            throw new Exception(vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager'));
        }

        return true;
    }
}
