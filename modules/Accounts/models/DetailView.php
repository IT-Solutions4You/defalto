<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();

		$linkModelList = parent::getDetailViewLinks($linkParams);

		//TODO: update the database so that these separate handlings are not required
		$index=0;
		foreach($linkModelList['DETAILVIEW'] as $link) {
			if($link->linklabel == 'View History' || $link->linklabel == 'Send SMS') {
				unset($linkModelList['DETAILVIEW'][$index]);
			} else if($link->linklabel == 'LBL_SHOW_ACCOUNT_HIERARCHY') {
				$linkURL = 'index.php?module=Accounts&view=AccountHierarchy&record='.$recordModel->getId();
				$link->linkurl = 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("'.$linkURL.'");';
				unset($linkModelList['DETAILVIEW'][$index]);
				$linkModelList['DETAILVIEW'][$index] = $link;
			}
			$index++;
		}
		
		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if(!empty($SMSNotifierModuleModel) && $currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSms("index.php?module='.$this->getModule()->getName().
								'&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerTransferOwnership("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $linkModelList;
	}
}
