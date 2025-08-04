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

class MailManager_Settings_View extends MailManager_MainUI_View
{
    /**
     * Process the request for Settings Operations
     *
     * @param Vtiger_Request $request
     *
     * @return MailManager_Response
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $response = new MailManager_Response();
        $module = $request->getModule();
        if ('edit' == $this->getOperationArg($request)) {
            $model = $this->getMailBoxModel();
            $connector = $this->getConnector();
            $serverName = $model->serverName();
            $folders = [];

            if ($connector->isConnected()) {
                $folders = $connector->getFolders();
            }

            $viewer = $this->getViewer($request);
            $viewer->assign('MODULE', $module);
            $viewer->assign('MAILBOX', $model);
            $viewer->assign('SERVERNAME', $serverName);
            $viewer->assign('FOLDERS', $folders);
            $response->setResult($viewer->view('SettingsEdit.tpl', $module, true));
        } elseif ('save' == $this->getOperationArg($request)) {
            $model = $this->getMailBoxModel();
            $model->setServer($request->get('_mbox_server'));
            $model->setUsername($request->get('_mbox_user'));
            // MailManager_Request->get($key) is give urldecoded value which is replacing + with space
            $model->setPassword($request->getRaw('_mbox_pwd'));
            $model->setProtocol($request->get('_mbox_protocol', 'IMAP4'));
            $model->setSSLType($request->get('_mbox_ssltype', 'ssl'));
            $model->setCertValidate($request->get('_mbox_certvalidate', 'novalidate-cert'));
            $model->setRefreshTimeOut($request->get('_mbox_refresh_timeout'));

            $model->setProxy($request->get('_mbox_proxy'));
            $model->setClientId($request->get('_mbox_client_id'));
            $model->setClientSecret($request->get('_mbox_client_secret'));
            $model->setClientToken($request->get('_mbox_client_token'));
            $model->setClientAccessToken($request->get('_mbox_client_access_token'));

            $connector = $this->getConnector();
            $sentFolder = $request->get('_mbox_sent_folder');

            if ($connector->isConnected() && empty($sentFolder)) {
                $folderInstances = $connector->getFolders();

                foreach ($folderInstances as $folder) {
                    if (str_contains(strtolower($folder->getName()), 'sent')) {
                        $sentFolder = $folder->getName();
                    }
                }
            }

            $model->setFolder($sentFolder);

            if ($connector->isConnected()) {
                $model->save();

                $request->set('_operation', 'mainui');

                return parent::process($request);
            } elseif ($connector->hasError()) {
                $error = $connector->lastError();
                $response->isJSON(true);
                $response->setError(101, $error);
            }
        } elseif ('remove' == $this->getOperationArg($request)) {
            $model = $this->getMailBoxModel();
            $model->delete();

            $response->isJSON(true);
            $response->setResult(['status' => true]);
        } elseif ('detail' == $this->getOperationArg($request)) {
            $model = $this->getMailBoxModel();
            $serverName = $model->serverName();

            $viewer = $this->getViewer($request);
            $viewer->assign('MODULE', $module);
            $viewer->assign('MAILBOX', $model);
            $viewer->assign('SERVERNAME', $serverName);
            $response->setResult($viewer->view('SettingsDetail.tpl', $module, true));
        }

        return $response;
    }

    public function validateRequest(Vtiger_Request $request)
    {
        return $request->validateWriteAccess();
    }
}