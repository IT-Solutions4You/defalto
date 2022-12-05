<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

include_once 'modules/Users/Users.php';
require_once 'include/events/include.inc';
vimport('includes.runtime.LanguageHandler');

class ITS4YouEmails_Tracker_Handler
{

    public function process($data = array())
    {
        global $current_user;
        $current_user = Users::getActiveAdminUser();

        $type = $data['method'];
        if ($type == 'click') {
            $this->clickHandler($data);
        } else {
            if ($type == 'open') {
                $this->openHandler($data);
            }
        }
    }

    protected function clickHandler($data = [])
    {
        $redirectUrl = rawurldecode($data['redirectUrl']);
        $redirectLinkName = rawurldecode($data['linkName']);

        if ((strpos($_SERVER['HTTP_REFERER'], vglobal('site_URL')) !== false) || (empty($_SERVER['HTTP_REFERER']) && $_REQUEST['fromcrm'])) {
            if (!empty($redirectUrl)) {
                Vtiger_Functions::redirectUrl($redirectUrl);
            }
            exit;
        }

        $parentId = $data['parentId'];
        $recordId = $data['record'];

        if ($recordId && $parentId && isRecordExists($recordId)) {
            /** @var ITS4YouEmails_Record_Model $recordModel */
            $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);
            $recordModel->set('click_count', intval($recordModel->get('click_count')) + 1);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
            $recordModel->saveAccess($parentId, $_REQUEST['id']);
        }

        if (!empty($redirectUrl)) {
            Vtiger_Functions::redirectUrl($redirectUrl);
        }
    }

    protected function openHandler($data = array())
    {
        $recordId = $data['record'];
        $parentId = $data['parentId'];

        if ($recordId && $parentId && isRecordExists($recordId)) {
            if ((strpos($_SERVER['HTTP_REFERER'], vglobal('site_URL')) !== false) || (empty($_SERVER['HTTP_REFERER']) && $_REQUEST['fromcrm'])) {
                // If a email is opened from CRM then we no need to track but need to be redirected
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            /** @var ITS4YouEmails_Record_Model $recordModel */
            $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);

            //If email is opened in last 1 hr, not tracking email open again.
            if ($recordModel->isEmailOpenedRecently($_REQUEST['id'])) {
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            $recordModel->set('access_count', intval($recordModel->get('access_count')) + 1);
            $recordModel->set('mode', 'edit');
            $recordModel->save();
            $recordModel->saveAccess($parentId, $_REQUEST['id']);

            Vtiger_ShortURL_Helper::sendTrackerImage();
        }
    }
}