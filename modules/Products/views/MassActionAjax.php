<?php
/**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
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

class Products_MassActionAjax_View extends Vtiger_MassActionAjax_View
{
    public function initMassEditViewContents(Vtiger_Request $request)
    {
        parent::initMassEditViewContents($request);

        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);

        $fieldInfo = [];
        $fieldList = $moduleModel->getFields();
        foreach ($fieldList as $fieldName => $fieldModel) {
            $fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $additionalFieldsList = $moduleModel->getAdditionalImportFields();
        foreach ($additionalFieldsList as $fieldName => $fieldModel) {
            $fieldInfo[$fieldName] = $fieldModel->getFieldInfo();
        }

        $viewer->assign('MASS_EDIT_FIELD_DETAILS', $fieldInfo);
    }
}