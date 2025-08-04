<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

/**
 * Create and trigger events in vtiger
 */
class VTEventsManager
{
    protected PearDatabase $adb;

    function __construct($adb)
    {
        $this->adb = $adb;
    }

    /**
     * Register an event handler
     *
     * @param        $forEvent  The name of the event to handle
     * @param        $path      The path to the php file containing the handler
     * @param        $className The name of the VTEventHandler class.
     * @param string $depedentOn
     */
    function registerHandler($forEvent, $path, $className, $depedentOn = '[]')
    {
        $adb = $this->adb;
        $result = $adb->pquery(
            "SELECT * FROM vtiger_eventhandlers WHERE event_name=? AND handler_path=? AND handler_class=?",
            [$forEvent, $path, $className]
        );
        if ($adb->num_rows($result) === 0) {
            $handlerId = $adb->getUniqueId('vtiger_eventhandlers');
            $adb->pquery(
                "insert into vtiger_eventhandlers
					(eventhandler_id, event_name, handler_path, handler_class, is_active, dependent_on)
					values (?,?,?,?, true, ?)",
                [$handlerId, $forEvent, $path, $className, $depedentOn]
            );

            $this->clearTriggerCache($forEvent);
        }
    }

    /**
     * Initialize Event Trigger Cache for the required event types.
     *
     * @param Object  $for   Optional String or Array of event_names for initializing.
     * @param Boolean $force Optional Force the initialization of cache?
     */
    function initTriggerCache($for = false, $force = false)
    {
        VTEventTrigger::initCache($for, $force);
    }

    /**
     * Clear the Event Trigger Cache
     *
     * @param Object $forEvent
     */
    function clearTriggerCache($forEvent = false)
    {
        VTEventTrigger::clearCache($forEvent);
    }

    /**
     * Set an event handler as inactive
     *
     * @param The handler class to set as inactive
     *
     */
    function setHandlerInActive($handlerClass)
    {
        $adb = $this->adb;
        $adb->pquery("update vtiger_eventhandlers set is_active=false where handler_class=?", [$handlerClass]);

        $this->clearTriggerCache();
    }

    /**
     * Set an event handler as active
     *
     * @param The handler class to set as active
     */
    function setHandlerActive($handlerClass)
    {
        $adb = $this->adb;
        $adb->pquery("update vtiger_eventhandlers set is_active=true where handler_class=?", [$handlerClass]);

        $this->clearTriggerCache();
    }

    /**
     * Unregister a registered handler
     *
     * @param $className string The name of teh VTEventHandler class to unregister
     */
    function unregisterHandler($className)
    {
        $adb = $this->adb;
        $adb->pquery(
            "delete from vtiger_eventhandlers where handler_class=?",
            [$className]
        );
        $adb->pquery(
            "delete from vtiger_eventhandler_module where handler_class=?",
            [$className]
        );

        $this->clearTriggerCache();
    }

    /**
     * Get an event triger instance
     *
     * @param $triggerName The name of the event.
     *
     * @return The trigger object for the event.
     */
    function getTrigger($triggerName)
    {
        $adb = $this->adb;

        return VTEventTrigger::getInstance($adb, $triggerName);
    }

    /**
     * Trigger an event
     *
     * @param $triggerName The name of the event.
     *
     * @return The trigger object for the event.
     */
    function triggerEvent($triggerName, $data)
    {
        $this->getTrigger($triggerName)->trigger($data);
    }

    /**
     * Set the module the handler belongs to
     *
     * @param moduleName - The name of the module
     * @param handlerClass - The name of the handler class
     */
    function setModuleForHandler($moduleName, $handlerClass)
    {
        $adb = $this->adb;
        $result = $adb->pquery(
            "SELECT * FROM vtiger_eventhandler_module WHERE handler_class=?",
            [$handlerClass]
        );
        if ($adb->num_rows($result) === 0) {
            $handlerModuleId = $adb->getUniqueId('vtiger_eventhandler_module');
            $adb->pquery(
                "insert into vtiger_eventhandler_module
					(eventhandler_module_id, module_name, handler_class)
					values (?,?,?)",
                array($handlerModuleId, $moduleName, $handlerClass)
            );
        }
    }
}