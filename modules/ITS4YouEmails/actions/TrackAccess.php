<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);

//Opensource fix for tracking email access count
chdir(__DIR__ . '/../../../');

require_once 'vendorCheck.php';
require_once 'vendor/autoload.php';
require_once 'include/utils/utils.php';

vimport('includes.http.Request');
vimport('includes.runtime.Globals');
vimport('includes.runtime.BaseModel');
vimport('includes.runtime.LanguageHandler');

class ITS4YouEmails_TrackAccess_Action extends Core_Controller_Action
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        $permissions = parent::requiresPermission($request);
        $permissions[] = ['module_parameter' => 'module', 'action' => 'DetailView'];

        return $permissions;
    }

    /**
     * @param Vtiger_Request $request
     *
     * @return void
     */
    public function process(Vtiger_Request $request)
    {
        if (vglobal('application_unique_key') !== $request->get('applicationKey')) {
            exit;
        }

        if ((strpos($_SERVER['HTTP_REFERER'], vglobal('site_URL')) !== false)) {
            exit;
        }

        global $current_user;
        $current_user = Users::getActiveAdminUser();

        if ($request->get('method') === 'click') {
            $this->clickHandler($request);
        } else {
            $parentId = $request->get('parentId');
            $recordId = $request->get('record');

            if ($parentId && $recordId) {
                $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);
                $recordModel->updateTrackDetails($parentId);
                Vtiger_ShortURL_Helper::sendTrackerImage();
            }
        }
    }

    public function clickHandler(Vtiger_Request $request)
    {
        $parentId = $request->get('parentId');
        $recordId = $request->get('record');

        if ($parentId && $recordId) {
            $recordModel = ITS4YouEmails_Record_Model::getInstanceById($recordId);
            $recordModel->trackClicks($parentId);
        }

        $redirectUrl = $request->get('redirectUrl');

        if (!empty($redirectUrl)) {
            return Vtiger_Functions::redirectUrl(rawurldecode($redirectUrl));
        }
    }
}

$track = new ITS4YouEmails_TrackAccess_Action();
$track->process(new Vtiger_Request($_REQUEST));