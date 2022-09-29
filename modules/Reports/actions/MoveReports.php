<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Reports_MoveReports_Action extends Vtiger_Mass_Action {

	public function requiresPermission(\Vtiger_Request $request) {
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		return $permissions;
	}

	public function process(Vtiger_Request $request) {
		$parentModule = 'Reports';
		$reportIdsList = $this->getRecordsListFromRequest($request);
		$folderId = $request->get('folderid');
                $viewname=$request->get('viewname');
                if($folderId==$viewname){
                    $sameTargetFolder=1;
                }
		if (!empty ($reportIdsList)) {
			foreach ($reportIdsList as $reportId) {
				$reportModel = Reports_Record_Model::getInstanceById($reportId);
				if (!$reportModel->isDefault() && $reportModel->isEditable() && $reportModel->isEditableBySharing()) {
					$reportModel->move($folderId);
				} else {
					$reportsMoveDenied[] = vtranslate($reportModel->getName(), $parentModule);
				}
			}
		}
		$response = new Vtiger_Response();
		if($sameTargetFolder){
                    $result=array('success'=>false, 'message'=>vtranslate('LBL_SAME_SOURCE_AND_TARGET_FOLDER', $parentModule));
                } 
                else if(empty ($reportsMoveDenied)) {
                    $result=array('success'=>true, 'message'=>vtranslate('LBL_REPORTS_MOVED_SUCCESSFULLY', $parentModule));
                }else {
                    $result = array('success'=>false, 'message'=>vtranslate('LBL_DENIED_REPORTS', $parentModule),'denied'=>$reportsMoveDenied);
		}
                $response->setResult($result);
		$response->emit();
	}
}