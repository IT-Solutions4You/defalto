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

vimport('~~/modules/MailManager/runtime/Request.php');
vimport('modules/MailManager/MailManager.php');

abstract class MailManager_Abstract_View extends Vtiger_Index_View
{
    /**
     * @inheritDoc
     */
    public function requiresPermission(Vtiger_Request $request): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function preProcess(Vtiger_Request $request, bool $display = true): void
    {
        if (!in_array($this->getOperationArg($request), ['open', 'attachment_dld'])) {
            parent::preProcess($request, $display);
        }
    }

    /**
     * @inheritDoc
     */
    public function postProcess(Vtiger_Request $request): void
    {
        if (!in_array($this->getOperationArg($request), ['open', 'attachment_dld'])) {
            parent::postProcess($request);
        }
    }

    /**
     * Function which gets the template handler
     *
     * @param Vtiger_Request $request
     *
     * @return Vtiger_Viewer
     */
    public function getViewer(Vtiger_Request $request): Vtiger_Viewer
    {
        $viewer = parent::getViewer($request);
        $viewer->assign('MAILBOX', $this->getMailboxModel());
        $viewer->assign('QUALIFIED_MODULE', $request->get('module'));

        return $viewer;
    }

    /**
     * Function to extract operation argument from request.
     *
     * @param Vtiger_Request $request
     *
     * @return type
     */
    public function getOperationArg(Vtiger_Request $request)
    {
        return $request->get('_operationarg');
    }

    /**
     * Mail Manager Connector
     * @var MailManager_Connector
     */
    protected $mConnector = false;

    /**
     * MailBox folder name
     * @var string
     */
    protected $mFolder = false;

    /**
     * Connector to the IMAP server
     * @var MailManager_Mailbox_Model
     */
    protected $mMailboxModel = false;

    /**
     * Returns the active Instance of Current Users MailBox
     * @return MailManager_Mailbox_Model
     */
    protected function getMailboxModel()
    {
        if ($this->mMailboxModel === false) {
            $this->mMailboxModel = MailManager_Mailbox_Model::getActiveInstance();
        }

        return $this->mMailboxModel;
    }

    /**
     * Checks if the current users has provided Mail Server details
     * @return Boolean
     */
    protected function hasMailboxModel()
    {
        $model = $this->getMailboxModel();

        return $model->exists();
    }

    /**
     * Returns a Connector to either MailBox or Internal Drafts
     * @return MailManager_Connector_Connector
     * @throws Exception
     */
    protected function getConnector()
    {
        if (!$this->mConnector) {
            $model = $this->getMailboxModel();
            $this->mConnector = MailManager_Connector_Connector::connectorWithModel($model);
        }

        return $this->mConnector;
    }

    /**
     * Function that closes connection to IMAP server
     */
    public function closeConnector()
    {
        if ($this->mConnector) {
            $this->mConnector->close();
            $this->mConnector = false;
        }
    }

    /**
     * @throws Exception
     */
    public function getMail(string $folderName, int $mUid): MailManager_Message_Model
    {
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);

        return $connector->getMail($folder, $mUid);
    }
}