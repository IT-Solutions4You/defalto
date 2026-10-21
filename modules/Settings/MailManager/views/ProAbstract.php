<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

abstract class Settings_MailManager_ProAbstract_View extends Settings_Vtiger_Index_View
{
    /**
     * Checks the administrator permission of Settings and additionally requires the Pro
     * edition (active Membership).
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
}
