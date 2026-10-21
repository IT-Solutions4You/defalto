<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class MailManager_ProAbstract_View extends Vtiger_Index_View
{
    /**
     * Pro views require access to the Mail Manager module.
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
     * (active Membership). Without it a page load opens the Pro required page, an AJAX call
     * ends with LBL_PRO_MEMBERSHIP_REQUIRED.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        MailManager_ProRequired_View::requirePro($request, false);

        return true;
    }

    /**
     * Every Pro page uses the Pro page frame (module header without the Basic folder sidebar,
     * whose template needs the Basic mailbox).
     *
     * @param Vtiger_Request $request
     *
     * @return string
     */
    protected function preProcessTplName(Vtiger_Request $request): string
    {
        return 'Pro/IndexViewPreProcess.tpl';
    }
}
