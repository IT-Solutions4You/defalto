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

class CustomerPortal_SearchFaqs extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $response = new CustomerPortal_API_Response();
        $current_user = $this->getActiveUser();
        $module = "Faq";
        global $adb;

        if ($current_user) {
            if (!CustomerPortal_Utils::isModuleActive($module)) {
                throw new Exception($module . " not accessible", 1412);
                exit;
            }

            $searchKey = $request->get('searchKey');
            $searchKey = decode_html(htmlspecialchars_decode($searchKey));
            $searchKey = addslashes(addslashes($searchKey));
            $searchFields = ['question', 'answer'];

            $sql = sprintf('SELECT id,question,faq_answer,faqstatus FROM vtiger_faq WHERE faqstatus=? AND (');
            $sql .= implode(" LIKE '%$searchKey%' OR ", $searchFields);
            $sql .= " LIKE '%" . $searchKey . "%') ;";
            $sqlResult = $adb->pquery($sql, ["Published"]);
            $num_rows = $adb->num_rows($sqlResult);
            $data = [];
            for ($i = 0; $i < $num_rows; $i++) {
                $record = [];
                $record['question'] = decode_html($adb->query_result($sqlResult, $i, 'question'));
                $record['faq_answer'] = decode_html($adb->query_result($sqlResult, $i, 'faq_answer'));
                $record['faqstatus'] = decode_html($adb->query_result($sqlResult, $i, 'faqstatus'));
                $record['id'] = vtws_getWebserviceEntityId("Faq", $adb->query_result($sqlResult, $i, 'id'));
                $data[] = $record;
            }

            $response->setResult($data);

            return $response;
        }
    }
}