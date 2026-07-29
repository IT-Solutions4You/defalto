<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use JetBrains\PhpStorm\NoReturn;


/**
 * Handles the logged-in self-service actions for the "My two-factor
 * authentication" page (enrollment, switching method, backup codes).
 */
class TwoFactorAuthentication_Manage_Action extends Core_Controller_Action
{
    /**
     * Redirects back to the manage page and stops.
     *
     * @param string $query optional extra query string
     * @return void
     */
    private function back(string $query = ''): void
    {
        $url = 'index.php?module=TwoFactorAuthentication&parent=Settings&view=Manage';

        if ($query !== '') {
            $url .= '&' . $query;
        }

        header('Location: ' . $url);
        exit;
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
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userId = (int)$currentUser->getId();
        $mode = $request->getMode();

        switch ($mode) {
            case 'savemethod':
                $method = $request->get('two_factor_method');
                if (TwoFactorAuthentication_Service_Helper::isMethodAllowed($method)) {
                    TwoFactorAuthentication_Service_Helper::setUserMethod($userId, $method);
                    $this->back('notice=saved');
                }
                break;

            case 'start':
                if (TwoFactorAuthentication_Service_Helper::isMethodAllowed(TwoFactorAuthentication_Service_Helper::METHOD_TOTP)) {
                    TwoFactorAuthentication_Service_Helper::startSelfEnrollment();
                }
                break;

            case 'confirm':
                if (!TwoFactorAuthentication_Service_Helper::confirmSelfEnrollment($userId, $request->get('code'))) {
                    $this->back('error=invalid');
                }
                break;

            case 'cancel':
                TwoFactorAuthentication_Service_Helper::clearSelf();
                break;

            case 'regen':
                TwoFactorAuthentication_Service_Helper::regenSelfBackupCodes($userId);
                $this->back('tab=backup');
                break;

            case 'ack':
                TwoFactorAuthentication_Service_Helper::clearSelf();
                $this->back('tab=backup');
                break;
        }

        $this->back();
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
