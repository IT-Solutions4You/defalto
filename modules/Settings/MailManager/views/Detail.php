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
 * The admin's detail of one mailbox — the owner's My Settings screen
 * (MySettings.tpl) in admin mode: any mailbox, plus the type, the members
 * of a shared one and the trusted certificate
 */
class Settings_MailManager_Detail_View extends Settings_MailManager_ProAbstract_View
{
    /**
     * @param Vtiger_Request $request
     * @return void
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {

        MailManager_ProInbox_Model::useUtf8mb4();
        $mailboxId = (int)$request->get('record');
        if (!Settings_MailManager_Mailbox_Model::getById($mailboxId)) {
            throw new Exception(vtranslate('LBL_RECORD_NOT_FOUND', 'Vtiger'));
        }

        $viewer = $this->getViewer($request)
            ->assign('MODULE', $request->getModule())
            ->assign('QUALIFIED_MODULE', $request->getModule(false));
        $data = MailManager_ProMySettings_Model::getViewData(
            (int)Users_Record_Model::getCurrentUserModel()->getId(),
            $mailboxId,
            true
        );
        foreach ($data as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('Pro/MySettings.tpl', 'MailManager');
    }

    /**
     * The panel's messages are the module's client strings
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function getJSLanguageStrings(Vtiger_Request $request): array
    {
        return array_merge(
            Vtiger_Language_Handler::export('MailManager', 'jsLanguageStrings'),
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
                'modules.MailManager.resources.ProLabelEditor',
                'modules.MailManager.resources.ProSmtpTest',
                'modules.MailManager.resources.ProHistory',
                'modules.MailManager.resources.ProMySettingsPanel',
                // the certificate check and the type toggle of the mailbox wizard
                'modules.Settings.MailManager.resources.MailboxWizard',
                'modules.Settings.MailManager.resources.Detail',
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
                '~layouts/d1/modules/MailManager/resources/ProIndex.css',
                '~layouts/d1/modules/MailManager/resources/ProLabelEditor.css',
                '~layouts/d1/modules/MailManager/resources/ProHistory.css',
            ])
        );
    }
}
