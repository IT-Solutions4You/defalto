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

chdir(dirname(__FILE__) . '/../../../');
include_once 'includes/main/WebUI.php';
vimport('includes.http.Request');

class SMSNotifier_ClickATellNew_Callbacks
{
    function process(Vtiger_Request $request)
    {
        if (vtlib_isModuleActive('SMSNotifier')) {
            $providerModel = SMSNotifier_Provider_Model::getInstance('ClickATellNew');
            if ($providerModel->validateRequest($request)) {
                $providerModel->updateMessageStatus($request);
            }
        }
    }
}

$clickATell = new SMSNotifier_ClickATellNew_Callbacks();
$clickATell->process(new Vtiger_Request($_REQUEST));