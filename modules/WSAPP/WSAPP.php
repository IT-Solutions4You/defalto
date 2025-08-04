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

require_once('include/events/include.inc');
require_once 'modules/WSAPP/Utils.php';

class WSAPP extends CRMExtension
{
    /**
     * Invoked when special actions are performed on the module.
     *
     * @param String Module name
     * @param String Event Type (module.postinstall, module.disabled, module.enabled, module.preuninstall)
     */
    function vtlib_handler($modulename, $event_type)
    {
        if ($event_type == 'module.postinstall') {
            $this->initCustomWebserviceOperations();
            $this->registerHandlers();
            $this->registerVtigerCRMApp();
            $this->registerWsappWorkflowhandler();
            $this->registerSynclibEventHandler();
        } elseif ($event_type == 'module.postupdate') {
            $this->registerSynclibEventHandler();
        }
    }

    function initCustomWebserviceOperations()
    {
        $operations = [];

        $wsapp_register_parameters = ['type' => 'string', 'synctype' => 'string'];
        $operations['wsapp_register'] = [
            'file'       => 'modules/WSAPP/api/ws/Register.php',
            'handler'    => 'wsapp_register',
            'reqtype'    => 'POST',
            'prelogin'   => '0',
            'parameters' => $wsapp_register_parameters
        ];

        $wsapp_deregister_parameters = ['type' => 'string', 'key' => 'string'];
        $operations['wsapp_deregister'] = [
            'file'       => 'modules/WSAPP/api/ws/DeRegister.php',
            'handler'    => 'wsapp_deregister',
            'reqtype'    => 'POST',
            'prelogin'   => '0',
            'parameters' => $wsapp_deregister_parameters
        ];

        $wsapp_get_parameters = ['key' => 'string', 'module' => 'string', 'token' => 'string'];
        $operations['wsapp_get'] = [
            'file'       => 'modules/WSAPP/api/ws/Get.php',
            'handler'    => 'wsapp_get',
            'reqtype'    => 'POST',
            'prelogin'   => '0',
            'parameters' => $wsapp_get_parameters
        ];

        $wsapp_put_parameters = ['key' => 'string', 'element' => 'encoded'];
        $operations['wsapp_put'] = [
            'file'       => 'modules/WSAPP/api/ws/Put.php',
            'handler'    => 'wsapp_put',
            'reqtype'    => 'POST',
            'prelogin'   => '0',
            'parameters' => $wsapp_put_parameters
        ];

        $wsapp_put_parameters = ['key' => 'string', 'element' => 'encoded'];
        $operations['wsapp_map'] = [
            'file'       => 'modules/WSAPP/api/ws/Map.php',
            'handler'    => 'wsapp_map',
            'reqtype'    => 'POST',
            'prelogin'   => '0',
            'parameters' => $wsapp_put_parameters
        ];

        $this->registerCustomWebservices($operations);
    }

    function registerCustomWebservices($operations)
    {
        global $adb;

        foreach ($operations as $operation_name => $operation_info) {
            $checkres = $adb->pquery("SELECT operationid FROM vtiger_ws_operation WHERE name=?", [$operation_name]);
            if ($checkres && $adb->num_rows($checkres) < 1) {
                $operation_id = $adb->getUniqueId('vtiger_ws_operation');

                $operation_res = $adb->pquery(
                    "INSERT INTO vtiger_ws_operation (operationid, name, handler_path, handler_method, type, prelogin) 
					VALUES (?,?,?,?,?,?)",
                    [
                        $operation_id,
                        $operation_name,
                        $operation_info['file'],
                        $operation_info['handler'],
                        $operation_info['reqtype'],
                        $operation_info['prelogin']
                    ]
                );

                $operation_parameters = $operation_info['parameters'];
                $parameter_index = 0;
                foreach ($operation_parameters as $parameter_name => $parameter_type) {
                    $adb->pquery(
                        "INSERT INTO vtiger_ws_operation_parameters (operationid, name, type, sequence) 
						VALUES(?,?,?,?)",
                        [$operation_id, $parameter_name, $parameter_type, ($parameter_index + 1)]
                    );
                    ++$parameter_index;
                }
                Vtiger_Utils::Log("Opearation $operation_name enabled successfully.");
            } else {
                Vtiger_Utils::Log("Operation $operation_name already exists.");
            }
        }
    }

    function registerHandlers()
    {
        global $adb;

        $handlerDetails = [];

        $appTypehandler = [];
        $appTypehandler['type'] = "Outlook";
        $appTypehandler['handlerclass'] = "OutlookHandler";
        $appTypehandler['handlerpath'] = "modules/WSAPP/Handlers/OutlookHandler.php";
        $handlerDetails[] = $appTypehandler;

        $appTypehandler = [];
        $appTypehandler['type'] = "vtigerCRM";
        $appTypehandler['handlerclass'] = "vtigerCRMHandler";
        $appTypehandler['handlerpath'] = "modules/WSAPP/Handlers/vtigerCRMHandler.php";
        $handlerDetails[] = $appTypehandler;

        foreach ($handlerDetails as $appHandlerDetails) {
            $adb->pquery(
                "INSERT INTO vtiger_wsapp_handlerdetails VALUES(?,?,?)",
                [$appHandlerDetails['type'], $appHandlerDetails['handlerclass'], $appHandlerDetails['handlerpath']]
            );
        }
    }

    function registerVtigerCRMApp()
    {
        $db = PearDatabase::getInstance();
        $appName = "vtigerCRM";
        $type = "user";
        $uid = uniqid();
        $db->pquery("INSERT INTO vtiger_wsapp (name, appkey,type) VALUES(?,?,?)", [$appName, $uid, $type]);
    }

    function registerWsappWorkflowhandler()
    {
        $db = PearDatabase::getInstance();
        $em = new VTEventsManager($db);
        $dependentEventHandlers = ['VTEntityDelta'];
        $dependentEventHandlersJson = Zend_Json::encode($dependentEventHandlers);
        $em->registerHandler('vtiger.entity.aftersave', 'modules/WSAPP/WorkFlowHandlers/WSAPPAssignToTracker.php', 'WSAPPAssignToTracker', $dependentEventHandlersJson);
    }

    function registerSynclibEventHandler()
    {
        $className = 'WSAPP_VtigerSyncEventHandler';
        $path = 'modules/WSAPP/synclib/handlers/VtigerSyncEventHandler.php';
        $type = 'vtigerSyncLib';
        wsapp_RegisterHandler($type, $className, $path);
    }
}