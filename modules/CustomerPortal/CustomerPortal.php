<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class CustomerPortal
{
    public string $moduleName = 'CustomerPortal';
    public string $parentName = '';

    /**
     * Invoked when special actions are performed on the module.
     * @param string $moduleName Module name
     * @param string $eventType Event Type
     * @throws AppException
     */
    public function vtlib_handler($moduleName, $eventType)
    {
        require_once('include/utils/utils.php');
        global $adb, $mod_strings;

        if ($eventType == 'module.postinstall') {
            $portalModules = [
                'HelpDesk',
                'Faq',
                'Invoice',
                'Quotes',
                'Products',
                'Services',
                'Documents',
                'Contacts',
                'Accounts',
                'Project',
                'ProjectTask',
                'ProjectMilestone',
                'Assets',
            ];

            $query = 'SELECT max(sequence) AS max_tabseq FROM vtiger_customerportal_tabs';
            $res = $adb->pquery($query, []);
            $tabseq = $adb->query_result($res, 0, 'max_tabseq');
            $i = ++$tabseq;

            foreach ($portalModules as $module) {
                $tabIdResult = $adb->pquery('SELECT tabid FROM vtiger_tab WHERE name=?', [$module]);
                $tabId = $adb->query_result($tabIdResult, 0, 'tabid');

                if ($tabId) {
                    ++$i;
                    $adb->pquery('INSERT INTO vtiger_customerportal_tabs(tabid,visible,sequence) VALUES (?, ?, ?)', [$tabId, 1, $i]);
                    $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [$tabId, 'showrelatedinfo', 1]);
                }
            }

            $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [0, 'userid', 1]);
            $adb->pquery('INSERT INTO vtiger_customerportal_prefs(tabid,prefkey,prefvalue) VALUES (?, ?, ?)', [0, 'defaultassignee', 1]);

            // Mark the module as Standard module
            $adb->pquery('UPDATE vtiger_tab SET customized=0 WHERE name=?', [$moduleName]);
        }

        Vtiger_Install_Model::getInstance($eventType, $moduleName)->install();
    }
}
