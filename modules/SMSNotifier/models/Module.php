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

class SMSNotifier_Module_Model extends Vtiger_Module_Model
{
    /**
     * Function to check whether the module is an entity type module or not
     * @return <Boolean> true/false
     */
    public function isQuickCreateSupported()
    {
        //SMSNotifier module is not enabled for quick create
        return false;
    }

    /**
     * Function to get the module is permitted to specific action
     *
     * @param <String> $actionName
     *
     * @return <boolean>
     */
    public function isPermitted($actionName)
    {
        if ($actionName === 'EditView' || $actionName === 'CreateView') {
            return false;
        }

        return Users_Privileges_Model::isPermitted($this->getName(), $actionName);
    }

    /**
     * Function to get Settings links
     * @return <Array>
     */
    public function getSettingLinks()
    {
        vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

        $editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
        $settingsLinks = [];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if ($currentUserModel->isAdminUser()) {
            if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
                $settingsLinks[] = [
                    'linktype'  => 'LISTVIEWSETTING',
                    'linklabel' => 'LBL_EDIT_WORKFLOWS',
                    'linkurl'   => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
                    'linkicon'  => $editWorkflowsImagePath
                ];
            }

            $settingsLinks[] = [
                'linktype'  => 'LISTVIEWSETTING',
                'linklabel' => vtranslate('LBL_SERVER_CONFIG', $this->getName()),
                'linkurl'   => 'index.php?module=SMSNotifier&parent=Settings&view=List',
                'linkicon'  => ''
            ];
        }

        return $settingsLinks;
    }

    /**
     * Function to check if duplicate option is allowed in DetailView
     *
     * @param <string> $action , $recordId
     *
     * @return <boolean>
     */
    public function isDuplicateOptionAllowed($action, $recordId)
    {
        return false;
    }

    /**
     * Function is used to give links in the All menu bar
     */
    public function getQuickMenuModels()
    {
        return;
    }

    /*
     * Function to get supported utility actions for a module
     */
    function getUtilityActionsNames()
    {
        return [];
    }

    public function getModuleBasicLinks()
    {
        return [];
    }

    function isStarredEnabled()
    {
        return false;
    }

    function isTagsEnabled()
    {
        return false;
    }
}