<?php
/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

include_once 'config.php';
require_once 'include/utils/utils.php';
include_once 'include/Webservices/Query.php';
require_once 'include/Webservices/QueryRelated.php';
require_once 'includes/runtime/Cache.php';
include_once 'include/Webservices/DescribeObject.php';
require_once 'modules/Vtiger/helpers/Util.php';
include_once 'modules/MailManager/MailManager.php';

class MailManager_Relation_View extends MailManager_Abstract_View {

    protected array $ignoredMailDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];

    /**
	 * Used to check the MailBox connection
	 * @var Boolean
	 */
	protected $skipConnection = false;

	/** To avoid working with mailbox */
	protected function getMailboxModel() {
		if ($this->skipConnection) return false;
		return parent::getMailboxModel();
	}

    /**
     * Process the request to perform relationship operations
     * @param Vtiger_Request $request
     * @return void
     * @throws AppException
     * @throws Exception
     * @global Users Instance $currentUserModel
     * @global PearDataBase Instance $adb
     * @global String $currentModule
     */
    public function process(Vtiger_Request $request)
    {
        $this->exposeMethod('find');
        $this->exposeMethod('link');
        $this->exposeMethod('commentwidget');
        $this->exposeMethod('create');
        $this->exposeMethod('create_wizard');
        $response = new MailManager_Response(true);
        $operation = $this->getOperationArg($request);

        if (!empty($operation) && $this->isMethodExposed($operation)) {
            $response = $this->invokeExposedMethod($operation, $request, $response);
        }

        if ($response) {
            $response->emit();
        }
    }

    /**
     * @throws AppException
     */
    public function find(Vtiger_Request $request, $response) {

        // Check if the message is already linked.
        $msgUid = $request->get('_msguid');
        $linkedTo = null;

        if (!empty($msgUid)) {
            $linkedTo = MailManager_Relate_Action::associatedLink($msgUid);
        }

        $folderName = $request->get('_folder');
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $mail = $connector->getMail($folder, $msgUid);
        $viewer = $this->getViewer($request);
        $fromEmail = $request->get('_mfrom') ?? $mail->getFrom()[0];
        $toEmail = $request->get('_mto') ?? $mail->getTo()[0];

        // If the message was not linked, lookup for matching records, using FROM address
        if (!empty($linkedTo)) {
            $viewer->assign('LINKEDTO', $linkedTo);
        } else {
            $mail->retrieveLookUps($fromEmail, $toEmail, $folder->isSentFolder());

            $viewer->assign('LOOKUPS', $mail->getLookUps());
        }

        $this->retrieveRelationship($request, $mail, $linkedTo);

        $response->setResult(['ui' => $viewer->view('Relationship.tpl', $request->getModule(), true)]);

        return $response;
    }

    /**
     * @throws AppException
     */
    public function link(Vtiger_Request $request, $response)
    {
        $linkTo = $request->get('_mlinkto');
        $folderName = $request->get('_folder');
        $uid = (int)$request->get('_msguid');
        // This is to handle larger uploads
        ini_set('memory_limit', MailManager_Config_Model::get('MEMORY_LIMIT'));

        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $mail = $connector->getMail($folder, $uid);

        MailManager_Relate_Action::associate($mail, $linkTo);

        $response->setResult(['success' => true]);

        return $response;
    }

    /**
     * @throws AppException
     */
    public function create_wizard(Vtiger_Request $request, $response)
    {
        $linkModule = $request->get('_mlinktotype');
        $moduleName = $request->getModule();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        if (!vtlib_isModuleActive($linkModule)) {
            $response->setResult(['error' => vtranslate('LBL_OPERATION_NOT_PERMITTED', $moduleName)]);

            return $response;
        }

        $parent = $request->get('_mlinkto');
        $folderName = $request->get('_folder');
        $uid = $request->get('_msguid');

        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $mail = $connector->getMail($folder, $uid);
        $isSentFolder = $mail->getFrom()[0] == $currentUserModel->get('email1') || $folder->isSentFolder();
        $formData = $this->processFormData($mail, $isSentFolder);
        $linkedTo = MailManager_Relate_Action::getSalesEntityInfo($parent);
        $referenceFields = MailManager_Message_Model::RELATIONS_MAPPING[$linkModule];
        $referenceFieldName = $referenceFields[$linkedTo['module']];

        switch ($linkModule) {
            case 'HelpDesk' :
                $from = $mail->getFrom();

                if ($parent && $referenceFieldName) {
                    $formData[$referenceFieldName] = $this->setParentForHelpDesk($parent, $from);
                }

                $formData['description'] = $mail->getBody(false);
                break;
            case 'Potentials' :
                if ($parent && $referenceFieldName) {
                    $formData[$referenceFieldName] = $request->get('_mlinkto');
                }

                $formData['description'] = Core_CKEditor_UIType::transformEditViewDisplayValue($mail->getBody(false));
                break;
        }

        $contactRecordId = $formData[$referenceFields['Contacts']];

        if ($contactRecordId && isRecordExists($contactRecordId)) {
            $contactRecordModel = Vtiger_Record_Model::getInstanceById($contactRecordId, 'Contacts');
            $formData[$referenceFields['Accounts']] = $contactRecordModel->get('account_id');
        }

        $formData['mail_message_key'] = $mail->generateUniqueKeyFromEmail();
        $formData['module'] = $linkModule;

        $request = new Vtiger_Request($formData, $formData);
        // Delegate QuickCreate FormUI to the target view controller of module.
        $viewClassName = Vtiger_Loader::getComponentClassName('View', 'QuickCreateAjax', $linkModule);

        if (!class_exists($viewClassName)) {
            $viewClassName = 'Vtiger_QuickCreateAjax_View';
        }

        $viewController = new $viewClassName();
        $viewController->process($request);

        // UI already sent
        return false;
    }

    /**
     * @throws AppException
     */
    public function create(Vtiger_Request $request, $response)
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $moduleName = $request->getModule();
        $linkModule = $request->get('_mlinktotype');

        if (!vtlib_isModuleActive($linkModule)) {
            $response->setResult(['ui' => '', 'error' => vtranslate('LBL_OPERATION_NOT_PERMITTED', $moduleName)]);

            return $response;
        }

        $parent = $request->get('_mlinkto');
        $folderName = $request->get('_folder');
        $uid = $request->get('_msguid');

        if (!empty($folderName)) {
            // This is to handle larger uploads
            ini_set('memory_limit', MailManager_Config_Model::get('MEMORY_LIMIT'));

            $connector = $this->getConnector();
            $folder = $connector->getFolder($folderName);
            $mail = $connector->getMail($folder, $uid);
        } else {
            $mail = new MailManager_Message_Model();
        }

        $linkedTo = MailManager_Relate_Action::getSalesEntityInfo($parent);
        $recordModel = Vtiger_Record_Model::getCleanInstance($linkModule);
        $fields = $recordModel->getModule()->getFields();

        foreach ($fields as $fieldName => $fieldModel) {
            if ($request->has($fieldName)) {
                $fieldValue = $request->get($fieldName);
                $fieldDataType = $fieldModel->getFieldDataType();

                if ($fieldDataType == 'time') {
                    $fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
                }

                $recordModel->set($fieldName, $fieldValue);
            }
        }

        $recordModel->set('source', 'Mail Manager');

        switch ($linkModule) {
            case 'HelpDesk' :
                $from = $mail->getFrom();
                $referenceFieldName = MailManager_Message_Model::RELATIONS_MAPPING[$linkModule][$linkedTo['module']];

                if (!empty($referenceFieldName) && !$request->has($referenceFieldName)) {
                    $recordModel->set($referenceFieldName, $this->setParentForHelpDesk($parent, $from));
                }
                break;

            case 'ModComments':
                $relatedTo = !$request->isEmpty('related_to') ? $request->get('related_to') : $parent;

                $recordModel->set('assigned_user_id', $currentUserModel->getId());
                $recordModel->set('commentcontent', $request->getRaw('commentcontent'));
                $recordModel->set('userid', $currentUserModel->getId());
                $recordModel->set('creator', $currentUserModel->getId());
                $recordModel->set('related_to', $relatedTo);
                $recordModel->set('mail_attachment_ids', $mail->getAttachmentsIds());
                break;
        }

        try {
            $recordModel->save();

            $linkTo = 'ModComments' === $recordModel->getModuleName() ? $recordModel->get('related_to') : $recordModel->getId();

            if (!empty($linkTo)) {
                $mail->setAttachmentRelationIds($recordModel->getId());
                
                MailManager_Relate_Action::associate($mail, $linkTo, (int)$parent);
            }

            $response->setResult(['ui' => '', 'success' => true]);
        } catch (DuplicateException $e) {
            $response->setResult(['ui' => '', 'error' => $e, 'title' => $e->getMessage(), 'message' => $e->getDuplicationMessage()]);
        } catch (Exception $e) {
            $response->setResult(['ui' => '', 'error' => $e]);
        }

        return $response;
    }

    /**
     * @throws AppException
     */
    public function commentwidget(Vtiger_Request $request, $response)
    {
        $folderName = $request->get('_folder');
        $mUid = (int)$request->get('_msguid');
        $connector = $this->getConnector();
        $folder = $connector->getFolder($folderName);
        $mail = $connector->getMail($folder, $mUid);
        $mailRecordId = $mail->getRecordIdByString($mail->getSubject());

        $recordModel = Vtiger_Record_Model::getCleanInstance('ModComments');
        $moduleModel = $recordModel->getModule();

        $fieldModel = $moduleModel->getField('related_to');
        $fieldModel->set('fieldvalue', $mailRecordId);

        $viewer = $this->getViewer($request);
        $viewer->assign('LINKMODULE', $request->get('_mlinktotype'));
        $viewer->assign('PARENT', $request->get('_mlinkto'));
        $viewer->assign('UID', $mUid);
        $viewer->assign('FOLDER', $request->get('_folder'));
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('RECORD_MODEL', $recordModel);
        $viewer->assign('MODULE_MODEL', $moduleModel);
        $viewer->assign('FIELD_MODEL', $fieldModel);
        $viewer->assign('MAIL', $mail);
        $viewer->assign('COMMENTCONTENT', Core_CKEditor_UIType::transformEditViewDisplayValue($mail->getBody(false)));
        $viewer->view('MailManagerCommentWidget.tpl', 'MailManager');

        return false;
    }

    /**
     * @param Vtiger_Request $request
     * @param MailManager_Message_Model $mail
     * @param $linkedTo
     * @return void
     */
    public function retrieveRelationship(Vtiger_Request $request, $mail, $linkedTo = null)
    {
        $folderName = $request->get('_folder');
        $messageNo = $request->get('_msgno');
        $jsScriptInstances = $this->checkAndConvertJsScripts([
            '~libraries/jquery/instaFilta/instafilta.min.js',
        ]);

        $viewer = $this->getViewer($request);
        $viewer->assign('MSGNO', $messageNo);
        $viewer->assign('LINKEDTO', $linkedTo);
        $viewer->assign('ALLOWED_MODULES', $mail->getCurrentUserMailManagerAllowedModules());
        $viewer->assign('LINK_TO_AVAILABLE_ACTIONS', $this->linkToAvailableActions());
        $viewer->assign('FOLDER', $folderName);
        $viewer->assign('MODULE', $request->getModule());
        $viewer->assign('HEADER_SCRIPTS', $jsScriptInstances);
        $viewer->assign('EMAIL', $mail);
        $viewer->assign('IS_ENABLED_ATTACHMENTS', $mail->isAttachmentsAllowed());
    }

    /**
	 * Returns the Parent for Tickets module
	 * @global Users Instance $currentUserModel
	 * @param Integer $parent - crmid of Parent
	 * @param Email Address $from - Email Address of the received mail
	 * @return Integer - Parent(crmid)
	 */
	public function setParentForHelpDesk($parent, $from) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(empty($parent)) {
			if(!empty($from)) {
				$parentInfo = MailManager::lookupMailInVtiger($from[0], $currentUserModel);
				if(!empty($parentInfo[0]['record'])) {
					$parentId = vtws_getIdComponents($parentInfo[0]['record']);
					return $parentId[1];
				}
			}
		} else {
			return $parent;
		}
	}


	/**
	 * Function used to set the record fields with the information from mail.
	 * @param Array $qcreate_array
	 * @param MailManager_Message_Model $mail
	 * @return Array
	 */
    public function processFormData($mail, $isSentFolder = false)
    {
        $subject = $mail->getSubject();
        $email = $mail->getFrom();

        if ($isSentFolder) {
            $email = $mail->getTo();

            if (!empty($email)) {
                $mail_address = implode(',', $email);
            }
        } elseif (!empty($email)) {
            $mail_address = implode(',', $email);
        }

        if (!empty($mail_address)) {
            $companyName = $mail_address;
            $name = explode('@', $mail_address);
        }

        if (!empty($name[1])) {
            if (in_array($name[1], $this->ignoredMailDomains)) {
                $companyName = $name[0];
            } else {
                $companyName = explode('.', $name[1])[0];
            }
        }

        return [
            'lastname' => $name[0],
            'email' => $email[0],
            'email1' => $email[0],
            'accountname' => $companyName,
            'company' => $companyName,
            'ticket_title' => $subject,
            'potentialname' => $subject,
            'subject' => $subject,
            'title' => $subject,
        ];
    }



	/**
	 * Returns the list of accessible modules on which Actions(Relationship) can be taken.
	 * @return string
	 */
	public function linkToAvailableActions() {
		$moduleListForLinkTo = array('ITS4YouEmails', 'ModComments', 'HelpDesk','Potentials');

		foreach($moduleListForLinkTo as $module) {
			if(MailManager::checkModuleWriteAccessForCurrentUser($module)) {
				$mailManagerAllowedModules[] = $module;
			}
		}
		return $mailManagerAllowedModules;
	}

	public function validateRequest(Vtiger_Request $request) {
		return $request->validateWriteAccess();
	}
}