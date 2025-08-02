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

class Settings_MailConverter_SaveFolders_Action extends Settings_Vtiger_Index_Action
{
    public function process(Vtiger_Request $request)
    {
        $recordId = $request->get('record');
        $qualifiedModuleName = $request->getModule(false);
        $checkedFolders = $request->get('folders');
        $folders = explode(',', $checkedFolders);
        Settings_MailConverter_Module_Model::updateFolders($recordId, $folders);

        $response = new Vtiger_Response();

        $result = ['message' => vtranslate('LBL_SAVED_SUCCESSFULLY', $qualifiedModuleName)];
        $result['id'] = $recordId;
        $response->setResult($result);

        $response->emit();
    }
}