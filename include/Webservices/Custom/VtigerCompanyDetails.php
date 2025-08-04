<?php
/*********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *********************************************************************************/

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'include/Webservices/VtigerActorOperation.php';

/**
 * Description of VtigerCompanyDetails
 *
 * @author MAK
 */
class VtigerCompanyDetails extends VtigerActorOperation
{
    public function create($elementType, $element)
    {
        $db = PearDatabase::getInstance();
        $params = [];
        $sql = 'select * from vtiger_organizationdetails';
        $result = $db->pquery($sql, $params);
        $rowCount = $db->num_rows($result);
        if ($rowCount > 0) {
            $id = $db->query_result($result, 0, 'organization_id');
            $meta = $this->getMeta();
            $element['id'] = vtws_getId($meta->getEntityId(), $id);

            return $this->revise($element);
        } else {
            $element = $this->handleFileUpload($element);

            return parent::create($elementType, $element);
        }
    }

    function handleFileUpload($element)
    {
        $fileFieldList = $this->meta->getFieldListByType('file');
        foreach ($fileFieldList as $field) {
            $fieldname = $field->getFieldName();
            if (is_array($_FILES[$fieldname])) {
                $element[$fieldname] = vtws_CreateCompanyLogoFile($fieldname);
            }
        }

        return $element;
    }

    public function update($element)
    {
        $element = $this->handleFileUpload($element);

        return parent::update($element);
    }

    public function revise($element)
    {
        $element = $this->handleFileUpload($element);

        return parent::revise($element);
    }

    public function retrieve($id)
    {
        $element = parent::retrieve($id);
        if (empty($element['logo'])) {
            $element['logo'] = vtws_getCompanyEncodedImage($element['logoname']);
        }

        return $element;
    }
}