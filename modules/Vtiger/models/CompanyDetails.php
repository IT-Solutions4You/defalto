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

/**
 * CompanyDetails Record Model class
 */
class Vtiger_CompanyDetails_Model extends Vtiger_Base_Model
{
    /**
     * Function to get the Company Logo
     * @return Vtiger_Image_Model instance
     */
    public function getLogo()
    {
        $logoName = decode_html($this->get('logoname'));
        $logoModel = new Vtiger_Image_Model();
        if (!empty($logoName)) {
            $companyLogo = [];
            $companyLogo['imagepath'] = Vtiger_Functions::getLogoPublicURL($logoName);
            $companyLogo['alt'] = $companyLogo['title'] = $companyLogo['imagename'] = $logoName;
            $logoModel->setData($companyLogo);
        }

        return $logoModel;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->get('organizationname');
    }

    /**
     * Function to get the instance of the CompanyDetails model for a given organization id
     *
     * @param <Number> $id
     *
     * @return Vtiger_CompanyDetails_Model instance
     */
    public static function getInstanceById($id = 1)
    {
        $companyDetails = Vtiger_Cache::get('vtiger', 'organization');
        if (!$companyDetails) {
            $db = PearDatabase::getInstance();
            $sql = 'SELECT * FROM vtiger_organizationdetails WHERE organization_id=?';
            $params = [$id];
            $result = $db->pquery($sql, $params);
            $companyDetails = new self();
            if ($result && $db->num_rows($result) > 0) {
                $resultRow = $db->query_result_rowdata($result, 0);
                $companyDetails->setData($resultRow);
            }
            Vtiger_Cache::set('vtiger', 'organization', $companyDetails);
        }

        return $companyDetails;
    }
}