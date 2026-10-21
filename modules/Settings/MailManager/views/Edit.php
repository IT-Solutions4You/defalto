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
 * Add / edit mailbox wizard in Settings — the steps are built by the
 * WizardView model (shared with the users' own mailbox wizard)
 */
class Settings_MailManager_Edit_View extends Settings_MailManager_ProAbstract_View
{
    /**
     * Renders module setting links in the Settings chrome
     *
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $settingLinks = [];
        foreach (Settings_MailManager_Module_Model::getInstance()->getSettingLinks() as $link) {
            $settingLinks['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($link);
        }
        $this->getViewer($request)->assign('LISTVIEW_LINKS', $settingLinks);

        parent::preProcess($request, $display);
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {

        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request)->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewData = Settings_MailManager_WizardView_Model::getViewData([
            'record' => $request->get('record'),
            'step' => $request->get('step'),
            'email' => $request->get('email'),
            'name' => $request->get('name'),
            'provider' => $request->get('provider'),
        ], false);

        foreach ($viewData as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Edit.tpl', $qualifiedModuleName);
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
                'modules.MailManager.resources.ProSmtpTest',
                'modules.Settings.MailManager.resources.MailboxWizard',
                'modules.Settings.MailManager.resources.Edit',
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
