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

class CustomerPortal_FetchCompanyDetails extends CustomerPortal_API_Abstract
{
    function process(CustomerPortal_API_Request $request)
    {
        $current_user = $this->getActiveUser();
        $response = new CustomerPortal_API_Response();

        if ($current_user) {
            $company_id = vtws_getCompanyId();
            $companyDetails = vtws_retrieve($company_id, $current_user);
            $companyDetailsModel = new Settings_Vtiger_CompanyDetails_Model();
            $companyDetailsModel->set('logoname', $companyDetails['logoname']);
            $filePath = $companyDetailsModel->getLogoPath();
            $imageInfo = getimagesize($filePath);
            $companyDetails['mime'] = $imageInfo['mime'];
            $response->setResult($companyDetails);
        }

        return $response;
    }
}