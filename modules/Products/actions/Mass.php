<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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

class Products_Mass_Action extends Vtiger_Mass_Action
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('isChildProduct');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();

        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
        }
    }

    public function isChildProduct(Vtiger_Request $request)
    {
        $moduleName = $request->getModule();
        $recordIdsList = $this->getRecordsListFromRequest($request);
        $response = new Vtiger_Response();

        if ($moduleName && $recordIdsList) {
            $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
            $areChildProducts = $moduleModel->areChildProducts($recordIdsList);

            $response->setResult($areChildProducts);
        }

        $response->emit();
    }
}