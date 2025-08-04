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

class Settings_PBXManager_Gateway_Action extends Settings_Vtiger_IndexAjax_View
{
    function __construct()
    {
        $this->exposeMethod('getSecretKey');
    }

    public function process(Vtiger_Request $request)
    {
        $this->getSecretKey($request);
    }

    public function getSecretKey(Vtiger_Request $request)
    {
        $serverModel = PBXManager_Server_Model::getInstance();
        $response = new Vtiger_Response();
        $vtigersecretkey = $serverModel->get('vtigersecretkey');
        if ($vtigersecretkey) {
            $connector = $serverModel->getConnector();
            $vtigersecretkey = $connector->getVtigerSecretKey();
            $response->setResult($vtigersecretkey);
        } else {
            $vtigersecretkey = PBXManager_Server_Model::generateVtigerSecretKey();
            $response->setResult($vtigersecretkey);
        }
        $response->emit();
    }
}