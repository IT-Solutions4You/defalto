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
 * Deleting stored personal data must not depend on a license, so this page requires the
 * administrator and an existing Pro schema only – not an active Membership (T13).
 */
class Settings_MailManager_PurgeData_View extends Settings_Vtiger_Index_View
{
    /**
     * Requires the administrator (parent) and an installation that used Pro before.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        if (!MailManager_Install_Model::getInstance('module.postupdate', 'MailManager')->isProSchemaInstalled()) {
            throw new Exception(vtranslate('LBL_PERMISSION_DENIED'));
        }

        return true;
    }

    /**
     * Renders the confirmation page with the number of stored messages.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);

        $this->getViewer($request)
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName)
            ->assign('STORED_MESSAGES', (new MailManager_ProPurge_Model())->countStoredMessages())
            ->view('PurgeData.tpl', $qualifiedModuleName);
    }

    /**
     * Adds the page script after the parent scripts.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts(['modules.Settings.MailManager.resources.PurgeData'])
        );
    }
}
