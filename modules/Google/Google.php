<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

require_once 'vtlib/Vtiger/Module.php';
require_once('include/events/include.inc');

class Google {

    const module = 'Google';
	var $LBL_GOOGLE = 'LBL_GOOGLE';

    /**
     * Invoked when special actions are to be performed on the module.
     * @param String Module name
     * @param String Event Type
     */
    function vtlib_handler($moduleName, $eventType)
    {
        $adb = PearDatabase::getInstance();
        $forModules = ['Contacts', 'Leads', 'Accounts'];
        $syncModules = ['Contacts' => 'Google Contacts', 'Calendar' => 'Google Calendar'];

        if ($eventType == 'module.postinstall') {
            $adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$moduleName]);
            $this->addMapWidget($forModules);
            $this->addWidgetforSync($syncModules);
        } elseif ($eventType == 'module.disabled') {
            $this->removeMapWidget($forModules);
            $this->removeWidgetforSync($syncModules);
            $adb->pquery('UPDATE vtiger_settings_field SET active=1 WHERE name=?', [$this->LBL_GOOGLE]);
        } elseif ($eventType == 'module.enabled') {
            $this->addMapWidget($forModules);
            $this->addWidgetforSync($syncModules);
            $adb->pquery('UPDATE vtiger_settings_field SET active=0 WHERE name=?', [$this->LBL_GOOGLE]);
        } elseif ($eventType == 'module.preuninstall') {
            $this->removeMapWidget($forModules);
            $this->removeWidgetforSync($syncModules);
        }
    }

    /**
     * Add widget to other module.
     * @param Array $moduleNames
     * @param String $widgetType
     * @param String $widgetName
     * @return
     */
    function addMapWidget($moduleNames, $widgetType = 'DETAILVIEWSIDEBARWIDGET', $widgetName = 'Google Map') {
        if (empty($moduleNames))
            return;

        if (is_string($moduleNames))
            $moduleNames = array($moduleNames);

        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetName, 'module=Google&view=Map&mode=showMap&viewtype=detail', '', '', '');
            }
        }
    }

    /**
     * Remove widget from other modules.
     * @param Array $moduleNames
     * @param String $widgetType
     * @param String $widgetName
     * @return
     */
    function removeMapWidget($moduleNames, $widgetType = 'DETAILVIEWSIDEBARWIDGET', $widgetName = 'Google Map') {
        if (empty($moduleNames))
            return;

        if (is_string($moduleNames))
            $moduleNames = array($moduleNames);

        foreach ($moduleNames as $moduleName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetType, $widgetName, 'module=Google&view=Map&mode=showMap&viewtype=detail');
            }
        }
    }

    /**
     * Add widget to other module
     * @param String $widgetType
     * @param String $widgetName
     * @return
     */
    function addWidgetforSync($moduleNames, $widgetType = 'LISTVIEWSIDEBARWIDGET') {
        if (empty($moduleNames))
            return;

        if (is_string($moduleNames))
            $moduleNames = array($moduleNames);

        foreach ($moduleNames as $moduleName => $widgetName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->addLink($widgetType, $widgetName, "module=Google&view=List&sourcemodule=$moduleName", '', '', '');
            }
        }
    }

    /**
     * Remove widget from other modules.
     * @param String $widgetType
     * @param String $widgetName
     * @return
     */
    function removeWidgetforSync($moduleNames, $widgetType = 'LISTVIEWSIDEBARWIDGET') {
        if (empty($moduleNames))
            return;

        if (is_string($moduleNames))
            $moduleNames = array($moduleNames);

        foreach ($moduleNames as $moduleName => $widgetName) {
            $module = Vtiger_Module::getInstance($moduleName);
            if ($module) {
                $module->deleteLink($widgetType, $widgetName);
            }
        }
    }

}

?>
