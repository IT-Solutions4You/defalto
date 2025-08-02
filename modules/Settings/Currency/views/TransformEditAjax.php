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

class Settings_Currency_TransformEditAjax_View extends Settings_Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $record = $request->get('record');

        $currencyList = Settings_Currency_Record_Model::getAll($record);

        $qualifiedName = $request->getModule(false);
        $viewer = $this->getViewer($request);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedName);
        $viewer->assign('CURRENCY_LIST', $currencyList);
        $viewer->assign('RECORD_MODEL', Settings_Currency_Record_Model::getInstance($record));
        echo $viewer->view('TransformEdit.tpl', $qualifiedName, true);
    }
}