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

require_once dirname(__FILE__) . '/ModTracker.php';
require_once 'data/VTEntityDelta.php';

class ModTrackerHandler extends VTEventHandler
{
    function handleEvent($eventName, $data)
    {
        global $adb;
        if (isset($_SESSION["authenticated_user_id"])) {
            $current_user_id = $_SESSION["authenticated_user_id"];
            $current_user = Users_Record_Model::getInstanceById($current_user_id, 'Users');
            $curid = $current_user->get('id');
        } else {
            //$_SESSION["authenticated_user_id"] is not set when creating/updating via Webservice
            global $current_user;
            $curid = $current_user->id;
        }
        $moduleName = $data->getModuleName();
        $isTrackingEnabled = ModTracker::isTrackingEnabledForModule($moduleName);
        if (!$isTrackingEnabled) {
            return;
        }
        if ($eventName == 'vtiger.entity.aftersave.final') {
            $recordId = $data->getId();
            $columnFields = $data->getData();
            $vtEntityDelta = new VTEntityDelta();
            $delta = $vtEntityDelta->getEntityDelta($moduleName, $recordId, true);

            $newerEntity = $vtEntityDelta->getNewEntity($moduleName, $recordId);
            $newerColumnFields = $newerEntity->getData();

            if (is_array($delta)) {
                $inserted = false;
                foreach ($delta as $fieldName => $values) {
                    if ($fieldName != 'modifiedtime') {
                        if (!$inserted) {
                            $checkRecordPresentResult = $adb->pquery('SELECT * FROM vtiger_modtracker_basic WHERE crmid = ? AND status = ?', [$recordId, ModTracker::$CREATED]);
                            if (!$adb->num_rows($checkRecordPresentResult) && $data->isNew()) {
                                $status = ModTracker::$CREATED;
                            } else {
                                $status = ModTracker::$UPDATED;
                            }
                            $this->id = $adb->getUniqueId('vtiger_modtracker_basic');
                            $changedOn = $newerColumnFields['modifiedtime'];
                            if ($moduleName == 'Users') {
                                $date_var = date("Y-m-d H:i:s");
                                $changedOn = $adb->formatDate($date_var, true);
                            }
                            $adb->pquery(
                                'INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
										VALUES(?,?,?,?,?,?)',
                                [
                                    $this->id,
                                    $recordId,
                                    $moduleName,
                                    $curid,
                                    $changedOn,
                                    $status
                                ]
                            );
                            $inserted = true;
                        }
                        $adb->pquery(
                            'INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue,postvalue) VALUES(?,?,?,?)',
                            [$this->id, $fieldName, $values['oldValue'], $values['currentValue']]
                        );
                    }
                }
            }
        }

        if ($eventName == 'vtiger.entity.beforedelete') {
            $recordId = $data->getId();
            $columnFields = $data->getData();
            $id = $adb->getUniqueId('vtiger_modtracker_basic');
            $adb->pquery(
                'INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)',
                [$id, $recordId, $moduleName, $curid, date('Y-m-d H:i:s', time()), ModTracker::$DELETED]
            );
        }

        if ($eventName == 'vtiger.entity.afterrestore') {
            $recordId = $data->getId();
            $columnFields = $data->getData();
            $id = $adb->getUniqueId('vtiger_modtracker_basic');
            $adb->pquery(
                'INSERT INTO vtiger_modtracker_basic(id, crmid, module, whodid, changedon, status)
				VALUES(?,?,?,?,?,?)',
                [$id, $recordId, $moduleName, $curid, date('Y-m-d H:i:s', time()), ModTracker::$RESTORED]
            );
        }
    }
}