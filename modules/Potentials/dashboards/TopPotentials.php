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

class Potentials_TopPotentials_Dashboard extends Vtiger_IndexAjax_View
{
    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');
        $page = $request->get('page');
        if (empty($page)) {
            $page = 1;
        }
        $pagingModel = new Vtiger_Paging_Model();
        $pagingModel->set('page', $page);

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $models = $moduleModel->getTopPotentials($pagingModel);

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('MODELS', $models);
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userCurrencyInfo = getCurrencySymbolandCRate($currentUser->get('currency_id'));
        $viewer->assign('USER_CURRENCY_SYMBOL', $userCurrencyInfo['symbol']);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/TopPotentialsContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/TopPotentials.tpl', $moduleName);
        }
    }
}