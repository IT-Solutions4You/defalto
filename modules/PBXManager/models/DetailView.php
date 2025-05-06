<?php

/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PBXManager_DetailView_Model extends Vtiger_DetailView_Model {
    
     /**
    * Overrided to remove Edit button, Duplicate button
    * To remove related links
    */
    public function getDetailViewLinks($linkParams) {
		$linkTypes = array('DETAILVIEWBASIC','DETAILVIEW');
		$moduleModel = $this->getModule();
		$recordModel = $this->getRecord();

		$moduleName = $moduleModel->getName();
		$recordId = $recordModel->getId();

        $links = [];

        $linkModelListDetails = Vtiger_Link_Model::getAllByType($moduleModel->getId(),$linkTypes,$linkParams);
		//Mark all detail view basic links as detail view links.
		//Since ui will be look ugly if you need many basic links
		$detailViewBasicLinks = $linkModelListDetails['DETAILVIEWBASIC'];

		if(Users_Privileges_Model::isPermitted($moduleName, 'Delete', $recordId)) {
            $links[] = [
                'linktype' => 'DETAILVIEW',
                'linklabel' => vtranslate('LBL_DELETE', $moduleName),
                'linkurl' => 'javascript:Vtiger_Detail_Js.deleteRecord("' . $recordModel->getDeleteUrl() . '")',
                'linkicon' => '<i class="fa fa-trash"></i>',
            ];
        }

        if (!empty($detailViewBasicLinks)) {
            foreach ($detailViewBasicLinks as $linkModel) {
                if ($linkModel->linklabel == 'View History') {
                    continue;
                }

                $links[] = $linkModel;
            }
        }

        foreach($this->getWidgets() as $widgetLinkModel) {
            $links[] = $widgetLinkModel;
		}
		
		return Vtiger_Link_Model::checkAndConvertLinks($links);
	}
}
