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
 * Self-service "My two-factor authentication" page. It renders inside the
 * standard settings chrome (so it gets the My Preferences sidebar for regular
 * users, like the Calendar Settings page) but - unlike normal settings pages -
 * is available to every logged-in user, not just admins.
 */
class Settings_TwoFactorAuthentication_Manage_View extends Settings_Vtiger_Index_View
{
    /**
     * Available to any logged-in user (this is a per-user self-service page).
     *
     * @param Vtiger_Request $request
     * @return bool
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * Keeps the "My Preferences" sidebar block expanded (the default active
     * block for this module would be User Management, leaving it collapsed).
     *
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        parent::preProcess($request, false);
        $this->getViewer($request)->assign('ACTIVE_BLOCK', ['block' => 'LBL_MY_PREFERENCES', 'menu' => '']);

        if ($display) {
            $this->preProcessDisplay($request);
        }
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        $qualifiedModuleName = $request->getModule(false);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userId = (int)$currentUser->getId();
        $allowed = TwoFactorAuthentication_Service_Helper::getAllowedMethods();

        $currentMethod = TwoFactorAuthentication_Service_Helper::getPreferredMethod($userId);
        if (!in_array($currentMethod, $allowed, true)) {
            $currentMethod = $allowed[0] ?? '';
        }

        // Freshly generated backup codes are shown inside the Backup codes tab.
        $newBackupCodes = TwoFactorAuthentication_Service_Helper::getSelfBackupCodes();
        $stage = TwoFactorAuthentication_Service_Helper::hasSelfEnrollment() ? 'enroll' : 'status';
        $activeTab = ($request->get('tab') === 'backup' || !empty($newBackupCodes)) ? 'backup' : 'method';

        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->assign('LANGUAGE_MODULE', 'TwoFactorAuthentication');
        $viewer->assign('STAGE', $stage);
        $viewer->assign('ACTIVE_TAB', $activeTab);
        $viewer->assign('CURRENT_METHOD', $currentMethod);
        $viewer->assign('ENROLLED', TwoFactorAuthentication_Service_Helper::isEnrolled($userId));
        $viewer->assign('BACKUP_COUNT', TwoFactorAuthentication_Service_Helper::countUnusedBackupCodes($userId));
        $viewer->assign('BACKUP_CODES', $newBackupCodes);
        $viewer->assign('ALLOW_EMAIL', in_array(TwoFactorAuthentication_Service_Helper::METHOD_EMAIL, $allowed, true));
        $viewer->assign('ALLOW_TOTP', in_array(TwoFactorAuthentication_Service_Helper::METHOD_TOTP, $allowed, true));
        $viewer->assign(
            'ERROR_MESSAGE',
            $request->get('error') === 'invalid'
                ? vtranslate('LBL_INVALID_CODE', 'TwoFactorAuthentication') : ''
        );
        $viewer->assign(
            'NOTICE_MESSAGE',
            $request->get('notice') === 'saved'
                ? vtranslate('LBL_METHOD_SAVED', 'TwoFactorAuthentication') : ''
        );

        if ($stage === 'enroll') {
            $viewer->assign('ENROLL', TwoFactorAuthentication_Service_Helper::getSelfEnrollmentContext($currentUser->get('user_name')));
        }

        $viewer->view('Manage.tpl', $qualifiedModuleName);
    }

    /**
     * Loads the Manage controller so the inherited Settings events, including
     * filtering links in the Settings menu, are registered on this view.
     *
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = [
            'modules.Settings.TwoFactorAuthentication.resources.Manage',
        ];

        return array_merge(
            $headerScriptInstances,
            $this->checkAndConvertJsScripts($jsFileNames)
        );
    }

    /**
     * @param Vtiger_Request $request
     * @return array
     */
    public function getHeaderCss(Vtiger_Request $request): array
    {
        $layout = Vtiger_Viewer::getDefaultLayoutName();
        $cssInstances = [
            '~/layouts/' . $layout . '/modules/Settings/TwoFactorAuthentication/resources/Index.css',
        ];

        return array_merge(
            parent::getHeaderCss($request),
            $this->checkAndConvertCssStyles($cssInstances)
        );
    }

}
