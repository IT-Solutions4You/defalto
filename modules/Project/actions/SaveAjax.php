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

class Project_SaveAjax_Action extends Vtiger_SaveAjax_Action
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('saveColor');
    }

    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);

            return;
        } else {
            parent::process($request);
        }
    }

    function saveColor(Vtiger_Request $request)
    {
        $db = PearDatabase::getInstance();
        $color = $request->get('color');
        $status = $request->get('status');

        $db->pquery('INSERT INTO vtiger_projecttask_status_color(status,color) VALUES(?,?) ON DUPLICATE KEY UPDATE color = ?', [$status, $color, $color]);
        $response = new Vtiger_Response();
        $response->setEmitType(Vtiger_Response::$EMIT_JSON);
        $response->setResult(true);
        $response->emit();
    }
}