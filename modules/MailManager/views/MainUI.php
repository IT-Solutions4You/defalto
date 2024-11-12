<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_MainUI_View extends MailManager_Abstract_View
{

    /**
     * Process the request for displaying UI
     * @param Vtiger_Request $request
     */
    public function process(Vtiger_Request $request)
    {
    }

    public function validateRequest(Vtiger_Request $request)
    {
        return $request->validateReadAccess();
    }
}