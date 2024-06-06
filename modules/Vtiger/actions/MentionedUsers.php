<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

class Vtiger_MentionedUsers_Action extends Vtiger_Action_Controller {

	public function requiresPermission(Vtiger_Request $request){
		$permissions = parent::requiresPermission($request);
		$permissions[] = array('module_parameter' => 'module', 'action' => 'DetailView');
		$permissions[] = array('module_parameter' => 'custom_module', 'action' => 'DetailView');
		$request->set('custom_module', 'ModComments');
		
		return $permissions;
	}
	
    public function process(Vtiger_Request $request) {
        $mentionedUsers = [];
        $commentId = $request->get('crmid');
        $commentRecord = Vtiger_Record_Model::getInstanceById($commentId, Vtiger_Module_Model::getInstance('ModComments'));
        $commentOwnerId = $commentRecord->get('creator');
        $commentOwnerName = Users_Record_Model::getInstanceById($commentOwnerId, Users_Module_Model::getInstance('Users'))->getName();
        $commentOwnerName = str_replace(' ', '',$commentOwnerName);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $currentUserId = $currentUser->getId();
        
        if($commentOwnerId !== $currentUserId) {
            $mentionedUsers[] = decode_html($commentOwnerName);
        }
        $currentUserName = decode_html(str_replace(' ', '',$currentUser->getName()));
        //Unset current user from the mentioned users
        if(($key = array_search(strtolower($currentUserName), $mentionedUsers)) !== false) {
            unset($mentionedUsers[$key]);
        }

        $mentionedUsersData = [];

        if(!empty($mentionedUsers)){
            $usersString = '@'.implode(' @', $mentionedUsers);
            $mentionedUsersData['usersString'] = $usersString.' ';
        }
        
        $response = new Vtiger_Response();
        $response->setResult($mentionedUsersData);
        $response->emit();
    }
}