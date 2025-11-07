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

class Vtiger_TagCloud_Dashboard extends Vtiger_IndexAjax_View
{
    /**
     * @inheritDoc
     */
    public function getHeaderScripts(Vtiger_Request $request): array
    {
        $jsFileNames = [
            '~/libraries/jquery/jquery.tagcloud.js'
        ];

        Core_Modifiers_Model::modifyVariableForClass(get_class($this), 'getHeaderScripts', $request->getModule(), $jsFileNames, $request);

        return $this->checkAndConvertJsScripts($jsFileNames);
    }

    public function process(Vtiger_Request $request)
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();

        $linkId = $request->get('linkid');

        $widget = Vtiger_Widget_Model::getInstance($linkId, $currentUser->getId());

        $tags = Vtiger_Tag_Model::getAllUserTags($currentUser->getId());

        //Include special script and css needed for this widget
        $viewer->assign('SCRIPTS', $this->getHeaderScripts($request));

        $viewer->assign('WIDGET', $widget);
        $viewer->assign('TAGS', $tags);
        $viewer->assign('MODULE_NAME', $moduleName);

        Core_Modifiers_Model::modifyForClass(get_class($this), 'process', $request->getModule(), $viewer, $request);

        $content = $request->get('content');
        if (!empty($content)) {
            $viewer->view('dashboards/TagCloudContents.tpl', $moduleName);
        } else {
            $viewer->view('dashboards/TagCloud.tpl', $moduleName);
        }
    }
}