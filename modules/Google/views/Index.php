<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
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

class Google_Index_View extends Vtiger_ExtensionViews_View
{
    function __construct()
    {
        parent::__construct();
        $this->exposeMethod('settings');
    }

    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    function getUserEmail()
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $oauth2 = new Google_Oauth2_Connector('Contacts');
        if ($oauth2->hasStoredToken()) {
            $controller = new Google_Contacts_Controller($user);
            $connector = $controller->getTargetConnector();
            $profileInfo = json_decode($connector->getUserProfileInfo(), true);

            return $profileInfo['email'];
        }

        return '';
    }

    /**
     * Function to check if sync is ready
     * @return <boolean> true/false
     */
    function checkIsSyncReady()
    {
        $oauth2 = new Google_Oauth2_Connector('Contacts');
        $isSyncReady = false;
        if ($oauth2->hasStoredToken()) {
            $isSyncReady = true;
        }

        return $isSyncReady;
    }

    function Settings(Vtiger_Request $request)
    {
        $user = Users_Record_Model::getCurrentUserModel();
        $viewer = $this->getViewer($request);
        $moduleName = $request->get('extensionModule');
        $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
        $contactGroups = [];
        $calendars = [];

        $oauth2 = new Google_Oauth2_Connector('Contacts');
        $isSyncReady = false;
        if ($oauth2->hasStoredToken()) {
            $controller = new Google_Contacts_Controller($user);
            $connector = $controller->getTargetConnector();
            try {
                $contactGroups = $connector->pullGroups();
            } catch (Exception $e) {
                $contactGroups = [];
            }
            $isSyncReady = true;
        }

        $oauth2 = new Google_Oauth2_Connector('Calendar');
        $selectedGoogleCalendar = Google_Utils_Helper::getSelectedCalendarForUser($user);
        if ($oauth2->hasStoredToken()) {
            $controller = new Google_Calendar_Controller($user);
            $connector = $controller->getTargetConnector();
            $validCalendarSelected = false;
            try {
                $calendars = $connector->pullCalendars();
                foreach ($calendars as $calendarsDetails) {
                    if ($calendarsDetails['id'] == $selectedGoogleCalendar || $selectedGoogleCalendar == 'primary') {
                        $validCalendarSelected = true;
                    }
                }
            } catch (Exception $e) {
                $calendars = [];
            }
            if (!$validCalendarSelected) {
                $selectedGoogleCalendar = 'primary';
            }
        }

        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('SOURCEMODULE', $request->getModule());
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CONTACTS_ENABLED', Google_Utils_Helper::checkSyncEnabled('Contacts'));
        $viewer->assign('CALENDAR_ENABLED', Google_Utils_Helper::checkSyncEnabled('Calendar'));
        $viewer->assign('SELECTED_CONTACTS_GROUP', Google_Utils_Helper::getSelectedContactGroupForUser());
        $viewer->assign('GOOGLE_CONTACTS_GROUPS', $contactGroups);
        $viewer->assign('SELECTED_GOOGLE_CALENDAR', $selectedGoogleCalendar);
        $viewer->assign('GOOGLE_CALENDARS', $calendars);
        $viewer->assign('CONTACTS_SYNC_DIRECTION', Google_Utils_Helper::getSyncDirectionForUser());
        $viewer->assign('CALENDAR_SYNC_DIRECTION', Google_Utils_Helper::getSyncDirectionForUser(false, 'Calendar'));
        $viewer->assign('IS_SYNC_READY', $isSyncReady);
        $viewer->assign('USER_EMAIL', $this->getUserEmail());
        $viewer->assign('PARENT', $request->get('parent'));
        $viewer->view('ExtensionSettings.tpl', $moduleName);
    }
}