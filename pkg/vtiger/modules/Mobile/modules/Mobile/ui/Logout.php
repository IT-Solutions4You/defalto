<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/**
 * Portions created by IT-Solutions4You s.r.o. are Copyright (C) IT-Solutions4You s.r.o.
 */

class Mobile_UI_Logout extends Mobile_WS_Controller
{
    public function process(Mobile_API_Request $request): void
    {
        Vtiger_Session::destroy();
        header('Location: index.php');
        exit;
    }
}