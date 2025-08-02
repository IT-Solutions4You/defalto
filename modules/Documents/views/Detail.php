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

class Documents_Detail_View extends Vtiger_Detail_View
{
    function preProcess(Vtiger_Request $request, $display = true)
    {
        $viewer = $this->getViewer($request);
        $viewer->assign('NO_SUMMARY', true);
        parent::preProcess($request);
    }

    /**
     * Function to get Ajax is enabled or not
     *
     * @param Vtiger_Record_Model record model
     *
     * @return <boolean> true/false
     */
    public function isAjaxEnabled($recordModel)
    {
        return true;
    }

    /**
     * Function shows basic detail for the record
     *
     * @param <type> $request
     */
    function showModuleBasicView($request)
    {
        return $this->showModuleDetailView($request);
    }
}