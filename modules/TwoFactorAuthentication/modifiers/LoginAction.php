<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class TwoFactorAuthentication_LoginAction_Modifier
{
    public function modifyAuthentication(
        array &$authentication,
        int $userId,
        string $username,
        Vtiger_Request $request
    ): void {
        if (
            !TwoFactorAuthentication_Module_Model::isActiveLogin()
            || !TwoFactorAuthentication_Service_Helper::isRequiredForUser($userId)
        ) {
            return;
        }

        TwoFactorAuthentication_Service_Helper::beginChallenge($userId, $username);
        $authentication['complete'] = false;
        $authentication['redirect_url'] = TwoFactorAuthentication_Service_Helper::getChallengeUrl();
    }
}
