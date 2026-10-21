<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class MailManager_ProAbstract_Action extends Core_Controller_Action
{
    /**
     * Pro actions require access to the Mail Manager module.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [['module_parameter' => 'module', 'action' => 'DetailView']];
    }

    /**
     * Checks the standard module permissions and additionally requires the Pro edition
     * (active Membership). Without it the request ends with LBL_PRO_MEMBERSHIP_REQUIRED.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        if (!MailManager_Module_Model::isProEnabled()) {
            throw new Exception(vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager'));
        }

        return true;
    }
}
