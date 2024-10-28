<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'include/Webservices/Create.php';
include_once 'vtlib/Vtiger/Mailer.php';
include_once 'vtlib/Vtiger/Version.php';
include_once 'modules/MailManager/MailManager.php';
include_once 'modules/MailManager/models/Message.php';
include_once 'include/Webservices/DescribeObject.php';

class MailManager_Mail_View extends MailManager_Abstract_View {

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request) {
        $this->exposeMethod('open');
        $this->exposeMethod('mark');
        $this->exposeMethod('delete');
        $this->exposeMethod('move');
        $this->exposeMethod('attachment_dld');

        /** @var MailManager_Connector_Connector $connector */
		$response = new MailManager_Response();
        $mode = $this->getOperationArg($request);

        if (!empty($mode) && $this->isMethodExposed($mode)) {
            $response = $this->invokeExposedMethod($mode, $request, $response);
        }

        return $response;
	}

    /**
     * @throws AppException
     */
    public function open(Vtiger_Request $request, $response)
    {
        /** @var MailManager_Connector_Connector $connector */
        $moduleName = $request->getModule();
        $folderName = $request->get('_folder');
        $mUid = $request->get('_muid');
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $connector->markMailRead($folder, $mUid);
        $mail = $connector->getMail($folder, $mUid);

        if (!$request->isEmpty('attachments') || $mail->hasRelations() || $mail->hasLookUps($folder)) {
            $mail->saveToDBRecord();
            $mail->saveToDBAttachments();
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('FOLDER', $folder);
        $viewer->assign('MAIL', $mail);
        $viewer->assign('USERNAME', $this->mMailboxModel->mUsername);
        $viewer->assign('ATTACHMENTS', $mail->getAttachments());
        $viewer->assign('ATTACHMENTS_COUNT', php7_count($mail->getAttachments()));
        $viewer->assign('INLINE_ATTACHMENTS', $mail->getInlineAttachments());
        $viewer->assign('MODULE', $moduleName);
        $uiContent = $viewer->view('MailOpen.tpl', 'MailManager', true);
        $metaInfo = [
            'from' => $mail->getFrom(),
            'subject' => $mail->getSubject(),
            'msgno' => $mail->msgNo(),
            'msguid' => $mail->getUniqueId(),
            'folder' => $folderName,
            'to' => $mail->getTo(),
        ];

        $response->isJson(true);
        $response->setResult([
            'folder' => $folderName,
            'ui' => $uiContent,
            'meta' => $metaInfo,
        ]);

        return $response;
    }

    /**
     * @throws AppException
     */
    public function mark(Vtiger_Request $request, $response)
    {
        /** @var MailManager_Connector_Connector $connector */
        $connector = $this->getConnector();
        $folderName = $request->get('_folder');
        $folder = $connector->getFolder($folderName);
        $mUIds = explode(',', $request->get('_muid'));

        if ('unread' == $request->get('_markas')) {
            foreach ($mUIds as $mUId) {
                $connector->markMailUnread($folder, $mUId);
            }
        } elseif ('read' == $request->get('_markas')) {
            foreach ($mUIds as $mUId) {
                $connector->markMailRead($folder, $mUId);
            }
        }

        $response->isJson(true);
        $response->setResult([
            'folder' => $folderName,
            'status' => true,
        ]);

        return $response;
    }

    /**
     * @throws AppException
     * @throws Exception
     */
    public function delete(Vtiger_Request $request, $response)
    {
        /** @var MailManager_Connector_Connector $connector */

        if (!$request->validateWriteAccess()) {
            return $response;
        }

        $mUId = $request->get('_muid');
        $folderName = $request->get('_folder');
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);

        $connector->deleteMail($folder, $mUId);

        $response->isJson(true);
        $response->setResult(['folder' => $folderName, 'status' => true]);

        return $response;
    }

    public function move(Vtiger_Request $request, $response)
    {
        /** @var MailManager_Connector_Connector $connector */
        $mUIds = $request->get('_muid');
        $folderName = $request->get('_folder');

        $moveToFolder = $request->get('_moveFolder');

        $connector = $this->getConnector();
        $folderFrom = $connector->getFolder($folderName);
        $folderTo = $connector->getFolder($moveToFolder);

        $connector->moveMail($mUIds, $folderFrom, $folderTo);

        $response->isJson(true);
        $response->setResult(['folder' => $folderName, 'status' => true]);

        return $response;
    }

    /**
     * @throws AppException
     */
    public function attachment_dld(Vtiger_Request $request, $response)
    {
        $attachmentName = $request->getRaw('_atname');
        $attachmentId = $request->get('_atid');
        $muId = $request->get('_muid');

        if (MailManager_Utils_Helper::allowedFileExtension($attachmentName)) {
            // This is to handle larger uploads
            $memory_limit = MailManager_Config_Model::get('MEMORY_LIMIT');
            ini_set('memory_limit', $memory_limit);

            $mail = new MailManager_Message_Model();
            $mail->setUid($muId);
            $mail->retrieveAttachmentsFromDB(true, $attachmentName, $attachmentId);

            $attachmentData = $mail->getAttachments()[0]['data'] ?? $mail->getInlineAttachments()[0]['data'];

            //As we are sending attachment name, it will return only that attachment details
            if ($attachmentData) {
                header("Content-type: application/octet-stream");
                header("Pragma: public");
                header("Cache-Control: private");
                header("Content-Disposition: attachment; filename=\"$attachmentName\"");
                echo $attachmentData;
            } else {
                header("Content-Disposition: attachment; filename=INVALIDFILE");
                echo "";
            }
        } else {
            header("Content-Disposition: attachment; filename=INVALIDFILE");
            echo "";
        }
        flush();
        exit;
    }

    public function validateRequest(Vtiger_Request $request) {
		return $request->validateReadAccess();
	}
}