<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

include_once 'modules/Users/Users.php';
require_once 'include/events/include.inc';
vimport('includes.runtime.LanguageHandler');

class ITS4YouEmails_Tracker_Handler
{
    public function process($data = [])
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

        // an e-mail without a related record (sent to an address unknown to the CRM) counts too
        $parentId = (int)($data['parentId'] ?? 0);
        $recordId = $data['record'];

        if ($recordId && isRecordExists($recordId)) {
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

    protected function openHandler($data = [])
    {
        $recordId = $data['record'];
        // an e-mail without a related record (sent to an address unknown to the CRM) counts too
        $parentId = (int)($data['parentId'] ?? 0);

        if ($recordId && isRecordExists($recordId)) {
            if ($this->isTrackingAllowed()) {
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            /** @var ITS4YouEmails_Record_Model $recordModel */
            $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);

            // the access rows keep the related record in record_id (saveAccess() below)
            if ($recordModel->isEmailOpenedRecently($parentId)) {
                Vtiger_ShortURL_Helper::sendTrackerImage();
                exit;
            }

            $recordModel->saveAccessCount(intval($recordModel->get('access_count')) + 1);
            $recordModel->saveAccess($parentId, $_REQUEST['id']);

            Vtiger_ShortURL_Helper::sendTrackerImage();
        }
    }
}