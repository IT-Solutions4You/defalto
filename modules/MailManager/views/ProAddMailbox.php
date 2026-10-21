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
 * A user adds an own personal IMAP mailbox — the admin's wizard (Edit.tpl,
 * MailboxWizard.js) in user mode; shared mailboxes stay with the admin
 */
class MailManager_ProAddMailbox_View extends MailManager_ProAbstract_View
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

        MailManager_ProRequired_View::requirePro($request, false);

        return true;
    }

    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $request->set('app', 'TOOLS');

        parent::preProcess($request, $display);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = 'Settings:MailManager';
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewData = Settings_MailManager_WizardView_Model::getViewData([
            'record' => $request->get('record'),
            'step' => $request->get('step'),
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'provider' => $request->get('provider'),
        ], true);

        foreach ($viewData as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Edit.tpl', $qualifiedModuleName);
    }

    /**
     * The wizard's messages live in the Settings language file — merged
     * into the module's client strings
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function getJSLanguageStrings(Vtiger_Request $request): array
    {
        return array_merge(
            Vtiger_Language_Handler::export('Settings:MailManager', 'jsLanguageStrings'),
            parent::getJSLanguageStrings($request)
        );
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderScripts($request),
            $this->checkAndConvertJsScripts([
                'modules.' . $request->getModule() . '.resources.ProSmtpTest',
                'modules.Settings.MailManager.resources.MailboxWizard',
                'modules.' . $request->getModule() . '.resources.ProAddMailbox',
            ])
        );
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        return array_merge(
            parent::getHeaderCss($request),
            $this->checkAndConvertCssStyles([
                '~layouts/d1/modules/Settings/MailManager/resources/Edit.css',
            ])
        );
    }
}
