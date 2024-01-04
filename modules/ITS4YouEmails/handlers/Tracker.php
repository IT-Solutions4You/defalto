<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
include_once 'modules/Users/Users.php';
require_once 'include/events/include.inc';
vimport('includes.runtime.LanguageHandler');

class ITS4YouEmails_Tracker_Handler
{

    public function process($data = array())
    {
        global $current_user;
        $current_user = Users::getActiveAdminUser();

        switch ($data['method']) {
            case 'click':
                $this->clickHandler($data);
                break;
            case 'open':
                $this->openHandler($data);
                break;
        }
    }

    protected function clickHandler($data = [])
    {
        $redirectUrl = rawurldecode($data['redirectUrl']);
        $redirectLinkName = rawurldecode($data['linkName']);

        if ($this->isTrackingAllowed()) {
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
            $recordModel->saveClickCount(intval($recordModel->get('click_count')) + 1);
            $recordModel->saveAccess($parentId, $_REQUEST['id']);
        }

        if (!empty($redirectUrl)) {
            Vtiger_Functions::redirectUrl($redirectUrl);
        }
    }

    /**
     * @return bool
     */
    public function isTrackingAllowed()
    {
        return (strpos($_SERVER['HTTP_REFERER'], vglobal('site_URL')) !== false) || (empty($_SERVER['HTTP_REFERER']) && $_REQUEST['fromcrm']);
    }

    protected function openHandler($data = array())
    {
        $recordId = $data['record'];
        $parentId = $data['parentId'];

        if ($recordId && $parentId && isRecordExists($recordId)) {
            if ($this->isTrackingAllowed()) {
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            /** @var ITS4YouEmails_Record_Model $recordModel */
            $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);

            if ($recordModel->isEmailOpenedRecently($_REQUEST['id'])) {
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            $recordModel->saveAccessCount(intval($recordModel->get('access_count')) + 1);
            $recordModel->saveAccess($parentId, $_REQUEST['id']);

            Vtiger_ShortURL_Helper::sendTrackerImage();
        }
    }
}