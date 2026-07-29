<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
class Users_Login_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function isLoginRequired(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function checkPermission(Vtiger_Request $request): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $username = $request->get('username');
        $password = $request->getRaw('password');

        $user = CRMEntity::getInstance('Users');
        $user->column_fields['user_name'] = $username;

        if (!$user->doLogin($password)) {
            header('Location: index.php?module=Users&parent=Settings&view=Login&error=login');
            exit;
        }

        $userId = (int)$user->retrieve_user_id($username);
        $authentication = [
            'complete' => true,
            'redirect_url' => '',
        ];

        Core_Modifiers_Model::modifyVariableForClass(
            get_class($this),
            'authentication',
            'Users',
            $authentication,
            $userId,
            (string)$username,
            $request
        );

        if (empty($authentication['complete'])) {
            session_regenerate_id(true);
            $redirectUrl = (string)($authentication['redirect_url'] ?? '');

            header('Location: ' . ($redirectUrl !== '' ? $redirectUrl : 'index.php'));
            exit;
        }

        header('Location: ' . Users_Authentication_Model::completeLogin($userId, (string)$username));
        exit;
    }
}
