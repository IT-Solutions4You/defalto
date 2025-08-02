<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Leads_LeadsCreated_Dashboard extends Vtiger_IndexAjax_View
{
    /**
     * Function to get the list of Script models to be included
     *
     * @param Vtiger_Request $request
     *
     * @return <Array> - List of Vtiger_JsScript_Model instances
     */
    function getHeaderScripts(Vtiger_Request $request)
    {
        $jsFileNames = [
//			'~/libraries/jquery/jqplot/plugins/jqplot.cursor.min.js',
//			'~/libraries/jquery/jqplot/plugins/jqplot.dateAxisRenderer.min.js',
//			'~/libraries/jquery/jqplot/plugins/jqplot.logAxisRenderer.min.js',
//			'~/libraries/jquery/jqplot/plugins/jqplot.canvasTextRenderer.min.js',
//			'~/libraries/jquery/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js'
        ];

        $headerScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);

        return $headerScriptInstances;
    }

    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');
        $dates = $request->get('createdtime');
        $owner = $request->get('owner');

        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $data = $moduleModel->getLeadsCreated($owner, $dates);

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        //Include special script and css needed for this widget
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('MODULE_NAME', $moduleName);
        $viewer->assign('DATA', $data);
        $viewer->assign('CURRENTUSER', $currentUser);

        $accessibleUsers = $currentUser->getAccessibleUsersForModule('Leads');
        $viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/DashBoardWidgetContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/LeadsCreated.tpl', $moduleName);
        }
    }
}