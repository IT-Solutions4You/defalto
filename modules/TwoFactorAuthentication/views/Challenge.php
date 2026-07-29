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
 * Renders the second-factor challenge screen shown after a valid password but
 * before the session is granted. The page is self-contained because the user
 * is not logged in yet (no application header/menu is available).
 */
class TwoFactorAuthentication_Challenge_View extends Core_Controller_View
{
    /**
     * @return bool
     */
    public function isLoginRequired(): bool
    {
        return false;
    }

    /**
     * @param Vtiger_Request $request
     * @return bool
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * Self-contained page: skip the logged-in pre/post processing.
     *
     * @param Vtiger_Request $request
     * @param bool $display
     * @return void
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function postProcess(Vtiger_Request $request): void
    {
    }

    /**
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        if (!TwoFactorAuthentication_Service_Helper::hasPendingChallenge()) {
            header('Location: ' . TwoFactorAuthentication_Service_Helper::getLoginUrl());
            exit;
        }

        $state = TwoFactorAuthentication_Service_Helper::getState();
        $stage = $state['stage'] ?? TwoFactorAuthentication_Service_Helper::STAGE_CHOOSE;
        $lockRemaining = TwoFactorAuthentication_Service_Helper::getLockRemaining();

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('QUALIFIED_MODULE', $request->getModule());
        $viewer->assign('DEFAULT_LAYOUT', Vtiger_Viewer::getDefaultLayoutName());
        $viewer->assign('STAGE', $stage);
        $viewer->assign('ALLOWED_METHODS', $state['allowed'] ?? [TwoFactorAuthentication_Service_Helper::METHOD_EMAIL]);
        $viewer->assign('MULTIPLE_METHODS', count($state['allowed'] ?? []) > 1);
        $viewer->assign('LOCK_REMAINING', $lockRemaining);
        $error = $request->get('error');

        if (!$error && !empty($state['delivery_failed'])) {
            $error = 'delivery';
        }

        $viewer->assign('ERROR_MESSAGE', $this->resolveError($error, $lockRemaining));
        $viewer->assign('NOTICE_MESSAGE', $this->resolveNotice($request->get('notice')));
        $viewer->assign('PAGETITLE', $this->getPageTitle($request));

        if ($stage === TwoFactorAuthentication_Service_Helper::STAGE_ENROLL) {
            $viewer->assign('ENROLL', TwoFactorAuthentication_Service_Helper::getEnrollmentContext());
        }

        $viewer->view('Challenge.tpl', 'TwoFactorAuthentication');
    }

    /**
     * @param Vtiger_Request $request
     * @return string
     */
    public function getPageTitle(Vtiger_Request $request): string
    {
        $companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();

        return $companyDetails->get('organizationname');
    }

    /**
     * @param string|null $error
     * @param int $lockRemaining
     * @return string
     */
    protected function resolveError($error, int $lockRemaining): string
    {
        if ($lockRemaining > 0) {
            return sprintf(
                vtranslate('LBL_TOO_MANY_ATTEMPTS', 'TwoFactorAuthentication'),
                (int)ceil($lockRemaining / 60)
            );
        }

        if ($error === 'invalid') {
            return vtranslate('LBL_INVALID_CODE', 'TwoFactorAuthentication');
        }

        if ($error === 'delivery') {
            return vtranslate('LBL_EMAIL_CODE_SEND_FAILED', 'TwoFactorAuthentication');
        }

        return '';
    }

    /**
     * @param string|null $notice
     * @return string
     */
    protected function resolveNotice($notice): string
    {
        if ($notice === 'resent') {
            return vtranslate('LBL_CODE_RESENT', 'TwoFactorAuthentication');
        }

        return '';
    }
}
