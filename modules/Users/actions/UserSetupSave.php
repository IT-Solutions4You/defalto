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

class Users_UserSetupSave_Action extends Users_Save_Action
{
    public function process(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $userModuleModel = Users_Module_Model::getInstance($moduleName);
        $userRecordModel = Users_Record_Model::getCurrentUserModel();

        //Handling the user preferences
        $userRecordModel->set('mode', 'edit');
        $userRecordModel->set('language', $request->get('lang_name'));
        $userRecordModel->set('time_zone', $request->get('time_zone'));
        $userRecordModel->set('date_format', $request->get('date_format'));
        $userRecordModel->set('tagcloud', 0);
        $userRecordModel->save();
        //End

        //Handling the System Setup
        $currencyName = $request->get('currency_name');
        if (!empty($currencyName)) {
            $userModuleModel->updateBaseCurrency($currencyName);
        }
        $userModuleModel->insertEntryIntoCRMSetup($userRecordModel->getId());
        //End

        header("Location: index.php");
        //End
    }
}