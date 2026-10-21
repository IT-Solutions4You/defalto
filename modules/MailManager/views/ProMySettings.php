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
 * Personal settings of a regular user — the admin Settings area stays
 * admin-only, so the OWN mailboxes (name, folders, SMTP, default) and their
 * reply-style profiles are managed here, inside the module
 */
class MailManager_ProMySettings_View extends MailManager_ProAbstract_View
{
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
        MailManager_ProInbox_Model::useUtf8mb4();
        $moduleName = $request->getModule();
        $userId = (int)Users_Record_Model::getCurrentUserModel()->getId();

        $viewer = $this->getViewer($request)->assign('MODULE', $moduleName);
        $data = MailManager_ProMySettings_Model::getViewData($userId, (int)$request->get('mailbox'));
        foreach ($data as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/MySettings.tpl', $moduleName);
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
                'modules.' . $request->getModule() . '.resources.ProLabelEditor',
                'modules.' . $request->getModule() . '.resources.ProSmtpTest',
                'modules.' . $request->getModule() . '.resources.ProHistory',
                'modules.' . $request->getModule() . '.resources.ProKnowledge',
                'modules.' . $request->getModule() . '.resources.ProMySettingsPanel',
                'modules.' . $request->getModule() . '.resources.ProMySettings',
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
                '~layouts/d1/modules/MailManager/resources/ProIndex.css',
                '~layouts/d1/modules/MailManager/resources/ProLabelEditor.css',
                '~layouts/d1/modules/MailManager/resources/ProHistory.css',
            ])
        );
    }
}
