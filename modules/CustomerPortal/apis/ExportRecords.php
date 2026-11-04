<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class CustomerPortal_ExportRecords extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $db = PearDatabase::getInstance();
        if ($current_user) {
            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);
            $accountId = $this->getParent($contactWebserviceId);
            $mode = $request->get('mode');
            $module = $request->get('module');
            $fieldsArray = $request->get('fields');
            $fieldsArray = Zend_Json::decode($fieldsArray);

            //validate module with portal settings
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception("Module not accessible", 1412);
                exit;
            }

            $activeFields = CustomerPortal_Utils::getActiveFields($module);
            $filterConditions = CustomerPortal_Utils::getVtqlFilterConditions(
                $fieldsArray,
                $activeFields,
                $request->get('groupConditions')
            );

            $fields = $fields = implode(',', $activeFields);
            if (empty($mode)) {
                $mode = CustomerPortal_Settings_Utils::getDefaultMode($module);
            }
            if ($mode == 'all' && in_array($module, ['Products', 'Services'])) {
                $countSql = sprintf('SELECT count(*) FROM %s;', $module);
                $countResult = vtws_query($countSql, $current_user);
                $count = $countResult[0]['count'];
            } else {
                //setting parentId based on mode
                $parentId = null;
                if ($mode == 'mine') {
                    $parentId = $contactWebserviceId;
                } elseif ($mode == 'all') {
                    if (!empty($accountId)) {
                        if (CustomerPortal_Settings_Utils::getDefaultMode($module) == 'all') {
                            $parentId = $accountId;
                        } else {
                            $parentId = $contactWebserviceId;
                        }
                    } else {
                        $parentId = $contactWebserviceId;
                    }
                }
                $countSql = sprintf('SELECT count(*) FROM %s', $module);

                if (!empty($filterConditions)) {
                    $countSql = sprintf('SELECT count(*) FROM %s WHERE %s', $module, $filterConditions);
                }
                $moduleLabel = CustomerPortal_Utils::getRelatedModuleLabel($module);
                $countResult = vtws_query_related($countSql, $parentId, $moduleLabel, $current_user);
                $count = $countResult[0]['count'];
            }
            //vtws_query gives max of 100 records per request.loop for records if more than 100
            $pageLimit = 100;
            $loopCount = $count / $pageLimit;
            $records = [];

            for ($i = 0; $i < $loopCount; $i++) {
                if (!empty($filterConditions)) {
                    $sql = sprintf('SELECT %s FROM %s WHERE %s', $fields, $module, $filterConditions);
                } else {
                    $sql = sprintf('SELECT %s FROM %s', $fields, $module);
                }
                $filterClause = sprintf(" LIMIT %s,%s", $i * $pageLimit, $pageLimit);
                if ($mode == 'all' && in_array($module, ['Products', 'Services'])) {
                    $result = vtws_query($sql . ' ' . $filterClause . ';', $current_user);
                } else {
                    $result = vtws_query_related($sql, $parentId, $moduleLabel, $current_user, $filterClause);
                }
                // process result
                foreach ($result as $key => $recordValues) {
                    $records[] = CustomerPortal_Utils::resolveRecordValues($recordValues);
                }
            }
            $response->setResult($records);

            return $response;
        }
    }
}
