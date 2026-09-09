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

class Project_DetailView_Model extends Vtiger_DetailView_Model
{
    /**
     * Function to get the detail view related links
     * @return <array> - list of links parameters
     */
    public function getDetailViewRelatedLinks()
    {
        $relatedLinks = parent::getDetailViewRelatedLinks();
        $recordModel = $this->getRecord();
        $moduleName = $recordModel->getModuleName();
        $relatedLinks[] = [
            'linktype'  => 'DETAILVIEWTAB',
            'linklabel' => vtranslate('LBL_CHART', $moduleName),
            'linkurl'   => $recordModel->getDetailViewUrl() . '&mode=showChart',
            'linkicon'  => '<i class="fa-solid fa-chart-gantt"></i>'
        ];

        return $relatedLinks;
    }
}
