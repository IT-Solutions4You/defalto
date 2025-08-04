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

class CustomerPortal_FetchRecentRecords extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        global $adb;

        if ($current_user) {
            $sql = "SELECT widgets FROM vtiger_customerportal_settings LIMIT 1";
            $result = $adb->pquery($sql, []);
            $widgetsJSON = $adb->query_result($result, 0, 'widgets');
            $data = [];
            $data = Zend_Json::decode(decode_html($widgetsJSON));
            $widgets = $data['widgets'];
            $activeModules = [];

            foreach ($widgets as $key => $value) {
                if (CustomerPortal_Utils::isModuleActive($key) && $value == '1') {
                    $activeModules[] = $key;
                }
            }
            $result = [];
            $customerId = $this->getActiveCustomer()->id;
            $contactWebserviceId = vtws_getWebserviceEntityId('Contacts', $customerId);

            foreach ($activeModules as $module) {
                $mode = CustomerPortal_Settings_Utils::getDefaultMode($module);

                if ($mode === 'all') {
                    $parentId = $this->getParent($contactWebserviceId);
                    if (empty($parentId)) {
                        $parentId = $contactWebserviceId;
                    }
                } else {
                    $parentId = $contactWebserviceId;
                }
                $limit = 5;
                $sql = sprintf("SELECT id FROM %s ", $module);
                $filterClause = sprintf('ORDER BY modifiedtime DESC LIMIT %s', $limit);

                if ($module == 'Faq') {
                    $queryResult = vtws_query($sql . "WHERE faqstatus='Published' " . $filterClause . ';', $current_user);
                } elseif ($module == 'HelpDesk') {
                    $fields = ["ticketstatus", "description"];
                    $moduleModel = Vtiger_Module_Model::getInstance($module);
                    $allowedFields = [];

                    foreach ($fields as $field) {
                        $fieldModel = Vtiger_Field_Model::getInstance($field, $moduleModel);
                        if ($fieldModel->isActiveField()) {
                            $allowedFields[] = $field;
                        }
                    }

                    if (!empty($allowedFields)) {
                        $fieldsSql = implode(",", $allowedFields);
                        $sql = sprintf("SELECT id, %s FROM %s", $fieldsSql, $module);
                    }
                    $queryResult = vtws_query_related($sql, $parentId, CustomerPortal_Utils::getRelatedModuleLabel($module), $current_user, $filterClause);
                } elseif ($mode == 'all' && in_array($module, ['Products', 'Services'])) {
                    $sql = sprintf("SELECT id FROM %s", $module);
                    $filterClause = sprintf("ORDER BY modifiedtime DESC LIMIT %s;", $limit);
                    $queryResult = vtws_query($sql . ' ' . $filterClause, $current_user);
                } else {
                    $queryResult = vtws_query_related($sql, $parentId, CustomerPortal_Utils::getRelatedModuleLabel($module), $current_user, $filterClause);
                }
                $num_rows = php7_count($queryResult);
                $records = [];
                $recordIds = [];

                if (!empty($queryResult)) {
                    foreach ($queryResult as $resultRecord) {
                        $recordIds[] = $resultRecord['id'];
                    }
                    $recordLabels = Vtiger_Util_Helper::fetchRecordLabelsForIds($recordIds);
                    for ($i = 0; $i < $num_rows; $i++) {
                        $record = [];
                        $id = $recordIds[$i];
                        foreach ($recordLabels as $key => $value) {
                            if ($key == $id) {
                                $record['label'] = decode_html($value);
                                break;
                            }
                        }

                        if ($module == 'HelpDesk') {
                            $record['status'] = $queryResult[$i]['ticketstatus'];
                            $record['statuslabel'] = decode_html(vtranslate($queryResult[$i]['ticketstatus'], $module));
                            $record['description'] = decode_html($queryResult[$i]['description']);
                        }
                        $record['id'] = $id;
                        $records[] = $record;
                    }
                }
                $result[] = [$module => $records];
            }
            $response->setResult($result);
        }

        return $response;
    }
}