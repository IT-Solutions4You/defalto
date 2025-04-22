<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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