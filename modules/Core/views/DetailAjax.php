<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Core_DetailAjax_View extends Vtiger_BasicAjax_View
{
    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('searchAssignedUsers');
    }

    public function searchAssignedUsers(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $search = (string)$request->get('search');
        $users = $currentUser->searchAccessibleUsersForModule($request->getModule(), $search);
        $groups = $currentUser->searchAccessibleGroupForModule($request->getModule(), $search);

        $viewer = $this->getViewer($request);
        $viewer->assign('ACCESSIBLE_USER_LIST', $users);
        $viewer->assign('ACCESSIBLE_GROUP_LIST', $groups);
        $viewer->view('HeaderAssignedUsers.tpl', $request->getModule());
    }
}