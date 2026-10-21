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
 * Deletes the stored Pro mail data. Like the page, it requires the administrator and an
 * existing Pro schema, not an active Membership (T13).
 */
class Settings_MailManager_PurgeData_Action extends Settings_Vtiger_Basic_Action
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
     * Deletes the stored mail and returns the deleted row counts.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $counts = (new MailManager_ProPurge_Model())->purgeStoredMail();
        $response = new Vtiger_Response();
        $response->setResult(['success' => true, 'messages' => $counts['df_mailmanager_message'] ?? 0]);
        $response->emit();
    }
}
