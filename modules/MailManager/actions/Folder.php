<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Folder_Action extends Vtiger_Action_Controller
{

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request): void
    {
        $mode = $request->getMode();

        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
        }
    }
}
