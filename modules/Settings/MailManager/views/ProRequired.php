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
 * The Settings page of Mail Manager Pro without an active Membership — what Pro brings,
 * the Pro offer, the Installer and the stored Pro mail, inside the CRM instead of an
 * error page. Requires the administrator only.
 */
class Settings_MailManager_ProRequired_View extends Settings_Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request)
            ->assign('QUALIFIED_MODULE', $request->getModule(false));

        foreach (MailManager_ProRequired_Model::getViewData(true) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('ProRequired.tpl', 'MailManager');
    }

    /**
     * The Pro offer of the Installer opens from the page button.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts([
                'modules.Installer.resources.PremiumModal',
                'modules.MailManager.resources.ProButton',
            ])
        );
    }
}
