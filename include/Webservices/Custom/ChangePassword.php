<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * @author Musavir Ahmed Khan<musavir at vtiger.com>
 */

/**
 *
 * @param WebserviceId $id
 * @param String       $oldPassword
 * @param String       $newPassword
 * @param String       $confirmPassword
 * @param Users        $user
 *
 */
function vtws_changePassword($id, $oldPassword, $newPassword, $confirmPassword, $user)
{
    vtws_preserveGlobal('current_user', $user);
    $idComponents = vtws_getIdComponents($id);
    if ($idComponents[1] == $user->id || is_admin($user)) {
        $newUser = new Users();
        $newUser->retrieve_entity_info($idComponents[1], 'Users');
        if (!is_admin($user)) {
            if (empty($oldPassword)) {
                throw new WebServiceException(
                    WebServiceErrorCode::$INVALIDOLDPASSWORD,
                    vtws_getWebserviceTranslatedString(
                        'LBL_' .
                        WebServiceErrorCode::$INVALIDOLDPASSWORD
                    )
                );
            }
            if (!$user->verifyPassword($oldPassword)) {
                throw new WebServiceException(
                    WebServiceErrorCode::$INVALIDOLDPASSWORD,
                    vtws_getWebserviceTranslatedString(
                        'LBL_' .
                        WebServiceErrorCode::$INVALIDOLDPASSWORD
                    )
                );
            }
        }
        if (isPasswordStrong($newPassword)) {
            if (strcmp($newPassword, $confirmPassword) === 0) {
                $db = PearDatabase::getInstance();
                $db->dieOnError = true;
                $db->startTransaction();
                $success = $newUser->change_password($oldPassword, $newPassword, false);
                $error = $db->hasFailedTransaction();
                $db->completeTransaction();
                if ($error) {
                    throw new WebServiceException(
                        WebServiceErrorCode::$DATABASEQUERYERROR,
                        vtws_getWebserviceTranslatedString(
                            'LBL_' .
                            WebServiceErrorCode::$DATABASEQUERYERROR
                        )
                    );
                }
                if (!$success) {
                    throw new WebServiceException(
                        WebServiceErrorCode::$CHANGEPASSWORDFAILURE,
                        vtws_getWebserviceTranslatedString(
                            'LBL_' .
                            WebServiceErrorCode::$CHANGEPASSWORDFAILURE
                        )
                    );
                }
            } else {
                throw new WebServiceException(
                    WebServiceErrorCode::$CHANGEPASSWORDFAILURE,
                    vtws_getWebserviceTranslatedString(
                        'LBL_' .
                        WebServiceErrorCode::$CHANGEPASSWORDFAILURE
                    )
                );
            }
        } else {
            throw new WebServiceException(
                WebServiceErrorCode::$CHANGEPASSWORDFAILURE,
                vtws_getWebserviceTranslatedString(
                    'LBL_' .
                    WebServiceErrorCode::$PASSWORDNOTSTRONG
                )
            );
        }
        VTWS_PreserveGlobal::flush();

        return ['message' => 'Changed password successfully'];
    }
}

function isPasswordStrong($new_password)
{
    $runtime_configs = Vtiger_Runtime_Configs::getInstance();
    $password_regex = $runtime_configs->getValidationRegex('password_regex');
    if (preg_match('/' . $password_regex . '/i', $new_password) == 1) {
        return true;
    }

    return false;
}