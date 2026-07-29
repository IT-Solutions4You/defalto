<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Users_Authentication_Model extends Vtiger_Base_Model
{
    /**
     * Completes a password-authenticated login after all registered
     * authentication factors have succeeded.
     */
    public static function completeLogin(int $userId, string $username): string
    {
        session_regenerate_id(true);

        Vtiger_Session::set('AUTHUSERID', $userId);

        // Backward compatibility for consumers that still read the raw session.
        $_SESSION['authenticated_user_id'] = $userId;
        $_SESSION['app_unique_key'] = vglobal('application_unique_key');
        $_SESSION['authenticated_user_language'] = vglobal('default_language');

        $_SESSION['KCFINDER'] = [
            'disabled' => false,
            'uploadURL' => '../../../test/upload',
            'uploadDir' => dirname(__DIR__, 3) . '/test/upload',
            'deniedExts' => implode(' ', vglobal('upload_badext')),
        ];

        /** @var Users_Module_Model $moduleModel */
        $moduleModel = Users_Module_Model::getInstance('Users');
        $moduleModel->saveLoginHistory($username);

        if (isset($_SESSION['return_params'])) {
            return 'index.php?' . urldecode((string)$_SESSION['return_params']);
        }

        if ($moduleModel->isFirstLoginHistory($username)) {
            return 'index.php?module=Tour&view=Index';
        }

        return 'index.php';
    }
}
