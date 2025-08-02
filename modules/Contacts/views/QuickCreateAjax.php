<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Contacts_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $moduleModel);
        $viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);
        parent::process($request);
    }
}