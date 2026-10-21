<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Settings_MailManager_Index_View extends Settings_MailManager_ProAbstract_View
{
    /**
     * Renders module setting links (Integration/Requirements/License/Upgrade/Uninstall)
     * in the Settings chrome
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
        $moduleModel = Vtiger_Module_Model::getInstance('MailManager');

        $this->getViewer($request)
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName)
            ->assign('MODULE_MODEL', $moduleModel)
            ->assign('MAILBOXES', Settings_MailManager_Mailbox_Model::getAll())
            ->assign('SUCCESS', ['1' => 'LBL_MAILBOX_SAVED', '2' => 'LBL_MAILBOX_UPDATED', '3' => 'LBL_AI_SETTINGS_SAVED'][$request->get('success')] ?? '');

        $this->getViewer($request)->view('Index.tpl', $qualifiedModuleName);
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
                'modules.Settings.MailManager.resources.Index',
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
                '~layouts/d1/modules/Settings/MailManager/resources/Index.css',
            ])
        );
    }
}
