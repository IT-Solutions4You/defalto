<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */


class Settings_TwoFactorAuthentication_Index_View extends Settings_Vtiger_Index_View
{
    /**
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $settingLinks = [];

        foreach ($moduleModel->getSettingLinks() as $settingsLink) {
            $settingsLink['linklabel'] = vtranslate($settingsLink['linklabel'], $moduleName);
            $settingLinks['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
        }

        $this->getViewer($request)->assign('LISTVIEW_LINKS', $settingLinks);
        parent::preProcess($request, false);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * Renders the 2FA settings overview and the administrator backup codes panel.
     *
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $moduleModel = Settings_TwoFactorAuthentication_Module_Model::getInstance();
        $validation = TwoFactorAuthentication_Validation_Model::getInstance()->retrieveValidation();
        $runtimeReady = $validation['schema_ready'] && $validation['config_ready'];

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $allowedMethods = $runtimeReady ? TwoFactorAuthentication_Service_Helper::getAllowedMethods() : [];
        $viewer->assign('IS_2FA_ACTIVE', $runtimeReady && TwoFactorAuthentication_Service_Helper::isActive());
        $viewer->assign('ENFORCE_MODE', $runtimeReady ? TwoFactorAuthentication_Service_Helper::getEnforceMode() : 'off');
        $viewer->assign('ALLOW_EMAIL', in_array(TwoFactorAuthentication_Service_Helper::METHOD_EMAIL, $allowedMethods, true));
        $viewer->assign('ALLOW_TOTP', in_array(TwoFactorAuthentication_Service_Helper::METHOD_TOTP, $allowedMethods, true));
        $viewer->assign('MAIL_CONFIGURED', TwoFactorAuthentication_Service_Helper::isEmailMethodAvailable());
        $viewer->assign('TOTP_AVAILABLE', TwoFactorAuthentication_Service_Helper::isTotpMethodAvailable());
        $viewer->assign('USERS_OVERVIEW', $runtimeReady ? TwoFactorAuthentication_Service_Helper::getUsersOverview() : []);
        $viewer->assign('EMAIL_TEMPLATES', $runtimeReady ? TwoFactorAuthentication_Service_Helper::getEmailTemplates() : []);
        $viewer->assign('VALIDATION', $validation);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);

        $viewer->view('Index.tpl', $qualifiedModuleName);
    }

    /**
     * @param Vtiger_Request $request
     * @return array list of Vtiger_JsScript_Model instances (adds bootstrap-switch)
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);

        $jsFileNames = [
            '~libraries/jquery/ckeditor/ckeditor.js',
            'modules.Vtiger.resources.CkEditor',
            'modules.Settings.TwoFactorAuthentication.resources.Index',
        ];

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return array_merge($headerScriptInstances, $jsScriptInstances);
    }

    /**
     * @param Vtiger_Request $request
     * @return array list of Vtiger_CssScript_Model instances (bootstrap-switch + nowrap fix)
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $headerCssInstances = parent::getHeaderCss($request);
        $layout = Vtiger_Viewer::getDefaultLayoutName();

        $cssFileNames = [
            '~/layouts/' . $layout . '/modules/Settings/TwoFactorAuthentication/resources/Index.css',
        ];

        $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);

        return array_merge($headerCssInstances, $cssInstances);
    }
}
