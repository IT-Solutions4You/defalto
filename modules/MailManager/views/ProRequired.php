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
 * A user's Mail Manager Pro page (My settings, Add mailbox) without an active Membership —
 * what Pro brings and the Pro offer, inside the CRM instead of an error page.
 */
class MailManager_ProRequired_View extends MailManager_ProAbstract_View
{
    /**
     * The guard of every Pro page: without an active Membership a page load opens the
     * Pro required page inside the CRM, an AJAX call ends with the error as before.
     *
     * @param Vtiger_Request $request
     * @param bool           $settings a Settings page (administrator) — otherwise a page of the module
     *
     * @return void
     * @throws Exception for an AJAX call without the Pro edition
     */
    public static function requirePro(Vtiger_Request $request, bool $settings): void
    {
        if (MailManager_Module_Model::isProEnabled()) {
            return;
        }

        if (!$request->isAjax()) {
            header('Location: ' . ($settings ? MailManager_ProRequired_Model::SETTINGS_URL : MailManager_ProRequired_Model::MODULE_URL));
            exit;
        }

        throw new Exception(vtranslate('LBL_PRO_MEMBERSHIP_REQUIRED', 'MailManager'));
    }

    /**
     * The module permission only — this page is what shows without the Pro edition.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return Vtiger_Index_View::checkPermission($request);
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $request->getModule());

        foreach (MailManager_ProRequired_Model::getViewData(false) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('ProRequired.tpl', $request->getModule());
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
