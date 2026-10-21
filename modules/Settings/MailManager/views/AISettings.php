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
 * AI Assistant settings of Mail Manager Pro: provider, API key, model, feature toggles,
 * knowledge files and e-mail labels. Needs only the Pro edition (Membership); the
 * Installer "AI" extension entitlement is unrelated (decision D22).
 */
class Settings_MailManager_AISettings_View extends Settings_Vtiger_Index_View
{
    /**
     * Requires an administrator and the Pro edition.
     *
     * @param Vtiger_Request $request
     *
     * @return bool
     * @throws Exception
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        parent::checkPermission($request);

        MailManager_ProRequired_View::requirePro($request, true);

        return true;
    }

    /**
     * Renders the AI settings page.
     *
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $viewer = $this->getViewer($request)
            ->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewData = MailManager_ProAiSettings_Model::getSettingsViewData(
            (int)$request->get('radar_days'),
            !$request->isEmpty('radar_days')
        );

        foreach ($viewData as $key => $value) {
            $viewer->assign($key, $value);
        }

        $viewer->view('AISettings.tpl', $qualifiedModuleName);
    }

    /**
     * Adds the colour picker, label editor, knowledge and AI settings scripts.
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
                'modules.MailManager.resources.ProLabelEditor',
                'modules.MailManager.resources.ProKnowledge',
                'modules.Settings.MailManager.resources.AISettings',
            ])
        );
    }

    /**
     * Adds the colour picker, label editor and AI settings styles.
     *
     * @param Vtiger_Request $request
     *
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();

        return array_merge(
            parent::getHeaderCss($request),
            $this->checkAndConvertCssStyles([
                "~layouts/$layout/modules/MailManager/resources/ProLabelEditor.css",
                "~layouts/$layout/modules/Settings/MailManager/resources/AISettings.css",
            ])
        );
    }
}
