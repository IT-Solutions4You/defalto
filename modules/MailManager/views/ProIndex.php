<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class MailManager_ProIndex_View extends MailManager_ProAbstract_View
{
    /**
     * Prepares Mail Manager Pro on its first start (Pro tables, default labels, cron) and
     * the page header. The page is rendered under view=List (menu entry), so the view
     * name is set to ProIndex for the client side to start MailManager_ProIndex_Js.
     *
     * @param Vtiger_Request $request
     * @param bool           $display
     *
     * @return void
     * @throws Exception
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $installModel = MailManager_Install_Model::getInstance('module.postupdate', $request->getModule());

        if (!$installModel->isProInitialized()) {
            $installModel->initializePro();
        }

        // an account created in Basic meanwhile, or a user added later, is linked on the next visit
        (new MailManager_ProMailbox_Model())->linkBasicAccount((int)Users_Record_Model::getCurrentUserModel()->getId());

        parent::preProcess($request, false);

        $this->getViewer($request)
            ->assign('VIEW', 'ProIndex')
            ->assign('CURRENT_VIEW', 'ProIndex');

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * Renders the Pro inbox: sidebar (Workspace, mailbox switch, folders, labels,
     * priorities), the thread list frame and the conversation frame. Lists and
     * conversations are loaded afterwards through MailManager_ProIndexAjax_View.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        MailManager_ProInbox_Model::useUtf8mb4();

        $moduleName = $request->getModule();
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request)
            ->assign('MODULE', $moduleName)
            ->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName))
            ->assign('IS_ADMIN', $currentUser->isAdminUser())
            ->assign('SETTINGS_URL', 'index.php?module=MailManager&parent=Settings&view=Index')
            ->assign('OPEN_THREAD', (int)$request->get('thread'));

        foreach (MailManager_ProInbox_Model::getIndexViewData((int)$currentUser->getId(), (int)$request->get('mailbox')) as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/Index.tpl', $moduleName);
    }

    /**
     * Adds CKEditor support and the Pro inbox scripts after the parent scripts. The
     * mixins must load before ProIndex, which composes MailManager_ProIndex_Js from them.
     * CKEditor itself is loaded globally by the d1 layout.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $jsFileNames = [
            'modules.Vtiger.resources.CkEditor',
            'modules.MailManager.resources.ProLabelEditor',
            'modules.MailManager.resources.ProSmtpTest',
            'modules.MailManager.resources.ProHistory',
            'modules.MailManager.resources.ProKnowledge',
            'modules.MailManager.resources.ProMySettingsPanel',
            'modules.MailManager.resources.ProThreadList',
            'modules.MailManager.resources.ProConversation',
            'modules.MailManager.resources.ProCrmPanel',
            'modules.MailManager.resources.ProComposer',
            'modules.MailManager.resources.ProSettingsPane',
            'modules.MailManager.resources.ProWorkspaceDigest',
            'modules.MailManager.resources.ProTriageQueue',
            'modules.MailManager.resources.ProAssistant',
            'modules.MailManager.resources.ProIndex',
        ];

        return array_merge(parent::getHeaderScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
    }

    /**
     * Adds the Pro inbox stylesheets after the parent styles.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $cssFileNames = [
            "layouts.$layout.modules.MailManager.resources.ProIndex",
            "layouts.$layout.modules.MailManager.resources.ProLabelEditor",
            "layouts.$layout.modules.MailManager.resources.ProHistory",
        ];

        return array_merge(parent::getHeaderCss($request), $this->checkAndConvertCssStyles($cssFileNames));
    }
}
