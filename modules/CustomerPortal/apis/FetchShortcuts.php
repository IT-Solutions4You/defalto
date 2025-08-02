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

class CustomerPortal_FetchShortcuts extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        global $adb;
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();

        if ($current_user) {
            $shortcuts = [];
            $sql = "SELECT shortcuts FROM vtiger_customerportal_settings LIMIT 1";
            $result = $adb->pquery($sql, []);
            $shortcutsJSON = $adb->query_result($result, 0, 'shortcuts');
            $data = Zend_Json::decode(decode_html($shortcutsJSON));

            foreach ($data as $module => $value) {
                $operations = [];
                if (is_array($value)) {
                    foreach ($value as $key1 => $value1) {
                        if ($value1 != 0) {
                            $operations[] = $key1;
                        }
                    }

                    if (!empty($operations) && CustomerPortal_Utils::isModuleActive($module)) {
                        $shortcuts[] = [$module => $operations];
                    }
                }
            }
            $isHelpDeskRecordCreatable = CustomerPortal_Utils::isModuleRecordCreatable('HelpDesk');
            foreach ($shortcuts as $shortcutArray => $shortcutValues) {
                foreach ($shortcutValues as $module => $values) {
                    if ($module == 'HelpDesk' && !$isHelpDeskRecordCreatable) {
                        $createShortCutKey = array_search('LBL_CREATE_TICKET', $values);
                        unset($values[$createShortCutKey]);
                        $values = array_values($values);
                        $shortcutValues['HelpDesk'] = $values;
                        $shortcuts[$shortcutArray] = $shortcutValues;
                    }
                }
            }
            $response->setResult(['shortcuts' => $shortcuts]);
        }

        return $response;
    }
}