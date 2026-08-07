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
 * Handles the second-factor challenge submission: verifies the code and either
 * completes the login (setting AUTHUSERID) or returns to the challenge screen.
 * Also handles re-sending the email passcode.
 */
class TwoFactorAuthentication_Verify_Action extends Core_Controller_Action
{
    /**
     * Redirects back to the challenge screen and stops.
     *
     * @param string $query optional extra query string
     * @return void
     */
    private function backToChallenge(string $query = ''): void
    {
        $url = 'index.php?module=TwoFactorAuthentication&view=Challenge';

        if ($query !== '') {
            $url .= '&' . $query;
        }

        header('Location: ' . $url);
        exit;
    }

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
     * @param Vtiger_Request $request
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        if (!TwoFactorAuthentication_Service_Helper::hasPendingChallenge()) {
            header('Location: ' . TwoFactorAuthentication_Service_Helper::getLoginUrl());
            exit;
        }

        $mode = $request->getMode();

        if ($mode === 'choose') {
            TwoFactorAuthentication_Service_Helper::chooseMethod($request->get('two_factor_method'));
            $this->backToChallenge();
        }

        if ($mode === 'switch') {
            TwoFactorAuthentication_Service_Helper::switchMethod();
            $this->backToChallenge();
        }

        if ($mode === 'resend') {
            $sent = TwoFactorAuthentication_Service_Helper::resendEmailCode();
            $this->backToChallenge($sent ? 'notice=resent' : 'error=delivery');
        }

        if ($mode === 'cancel') {
            TwoFactorAuthentication_Service_Helper::clearChallenge();
            header('Location: ' . TwoFactorAuthentication_Service_Helper::getLoginUrl());
            exit;
        }

        $state = TwoFactorAuthentication_Service_Helper::getState();
        $code = $request->get('code');

        // Confirming the first code during enrollment completes the login directly.
        if (($state['stage'] ?? '') === TwoFactorAuthentication_Service_Helper::STAGE_ENROLL) {
            if (TwoFactorAuthentication_Service_Helper::confirmEnrollment($code)) {
                header('Location: ' . TwoFactorAuthentication_Service_Helper::completeLogin());
                exit;
            }

            $this->backToChallenge('error=invalid');
        }

        if (TwoFactorAuthentication_Service_Helper::verifyCode($code)) {
            header('Location: ' . TwoFactorAuthentication_Service_Helper::completeLogin());
            exit;
        }

        $this->backToChallenge('error=invalid');
    }

    /**
     * @param Vtiger_Request $request
     * @return bool
     * @throws Exception
     */
    public function validateRequest(Vtiger_Request $request): bool
    {
        return $request->validateWriteAccess();
    }
}
