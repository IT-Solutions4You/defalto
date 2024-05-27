<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_SaveWidgetPositions_Action extends Vtiger_IndexAjax_View {

	public function requiresPermission(Vtiger_Request $request){
		if($request->get('module') != 'Dashboard'){
			$request->set('custom_module', 'Dashboard');
			$permissions[] = array('module_parameter' => 'custom_module', 'action' => 'DetailView');
		}else{
			$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		}
		
		return $permissions;
	}
	
	public function process(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

        $positionsMap = vtlib_array($request->get('positionsmap'));
		
		if ($positionsMap) {
			foreach ($positionsMap as $id => $position) {
				list ($linkid, $widgetid) = explode('-', $id);
				if ($widgetid) {
					Vtiger_Widget_Model::updateWidgetPosition($position, NULL, $widgetid, $currentUser->getId());
				} else {
					Vtiger_Widget_Model::updateWidgetPosition($position, $linkid, NULL, $currentUser->getId());
				}
			}
		}
		
		$response = new Vtiger_Response();
		$response->setResult(array('Save' => 'OK'));
		$response->emit();
	}
}