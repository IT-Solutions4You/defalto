<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'config.php';
require_once 'include/utils/utils.php';
include_once 'include/Webservices/Query.php';
require_once 'include/Webservices/QueryRelated.php';
require_once 'includes/runtime/Cache.php';
include_once 'include/Webservices/DescribeObject.php';
require_once 'modules/Vtiger/helpers/Util.php';
include_once 'modules/Settings/MailConverter/handlers/MailScannerAction.php';
include_once 'modules/Settings/MailConverter/handlers/MailAttachmentMIME.php';
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
	 * @global Users Instance $currentUserModel
	 * @global PearDataBase Instance $adb
	 * @global String $currentModule
	 * @param Vtiger_Request $request
	 * @return boolean
	 */
	public function process(Vtiger_Request $request) {
        /** @var MailManager_Connector_Connector $connector */
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$response = new MailManager_Response(true);
		$viewer = $this->getViewer($request);
        $moduleName = $request->get('_mlinktotype');

		if ('find' == $this->getOperationArg($request)) {
			// Check if the message is already linked.
			$msgUid = $request->get('_msguid');
            $linkedTo = null;

            if (!empty($msgUid)) {
                $linkedTo = MailManager_Relate_Action::associatedLink($msgUid);
            }

            $foldername = $request->get('_folder');
            $messageNo = $request->get('_msgno');
            $connector = $this->getConnector($foldername);
            $folder = $connector->folderInstance($foldername);
            $mail = $connector->openMail($messageNo, $foldername, false);
            $mail->retrieveBody();

			// If the message was not linked, lookup for matching records, using FROM address
            if (!empty($linkedTo)) {
                $viewer->assign('LINKEDTO', $linkedTo);
            } else {
                $mail->retrieveLookUps($request->get('_mfrom'), $request->get('_mto'), $folder->isSentFolder());

                $viewer->assign('LOOKUPS', $mail->getLookUps());
            }

            $this->retrieveRelationship($request, $mail, $linkedTo);

            $response->setResult(['ui' => $viewer->view('Relationship.tpl', $request->getModule(), true)]);
        } elseif ('link' == $this->getOperationArg($request)) {
			$linkto = $request->get('_mlinkto');
			$foldername = $request->get('_folder');
            $messageNo = $request->get('_msgno');
			$connector = $this->getConnector($foldername);

			// This is to handle larger uploads
			ini_set('memory_limit', MailManager_Config_Model::get('MEMORY_LIMIT'));

			$mail = $connector->openMail($messageNo, $foldername);
			$mail->attachments(); // Initialize attachments

			$linkedto = MailManager_Relate_Action::associate($mail, $linkto);

            $this->retrieveRelationship($request, $mail, $linkedto);

            $response->setResult(['ui' => $viewer->view('Relationship.tpl', $request->getModule(), true)]);
        } elseif ('create_wizard' == $this->getOperationArg($request)) {
            if(!vtlib_isModuleActive($moduleName)) {
				$response->setResult(array('error'=>vtranslate('LBL_OPERATION_NOT_PERMITTED', $moduleName)));
				return $response;
			}

			$parent =  $request->get('_mlinkto');
			$foldername = $request->get('_folder');

			$connector = $this->getConnector($foldername);
			$mail = $connector->openMail($request->get('_msgno'), $foldername);
			$folder = $connector->folderInstance($foldername);
			$isSentFolder = $mail->from()[0] == $currentUserModel->get('email1') || $folder->isSentFolder();
			$formData = $this->processFormData($mail, $isSentFolder);
			foreach ($formData as $key => $value) {
				$request->set($key, $value);
			}

			$linkedto = MailManager_Relate_Action::getSalesEntityInfo($parent);
            $referenceFieldName = MailManager_Message_Model::RELATIONS_MAPPING[$moduleName][$linkedto['module']];

            switch ($moduleName) {
                case 'HelpDesk' :
                    $from = $mail->from();

                    if ($parent && $referenceFieldName) {
                        $request->set($referenceFieldName, $this->setParentForHelpDesk($parent, $from));
                    }

                    $request->set('description', $mail->body());
                    break;
                case 'Potentials' :
                    if ($parent && $referenceFieldName) {
                        $request->set($referenceFieldName, $request->get('_mlinkto'));
                    }

                    $request->set('description', Core_CKEditor_UIType::transformEditViewDisplayValue($mail->body()));
                    break;
            }


            $request->set('mail_manager_id', $mail->muid());
			$request->set('module', $moduleName);

			// Delegate QuickCreate FormUI to the target view controller of module.
			$quickCreateviewClassName = $moduleName . '_QuickCreateAjax_View';
			if (!class_exists($quickCreateviewClassName)) {
				$quickCreateviewClassName = 'Vtiger_QuickCreateAjax_View';
			}
			$quickCreateViewController = new $quickCreateviewClassName();
			$quickCreateViewController->process($request);

			// UI already sent
			$response = false;

		} elseif ('create' == $this->getOperationArg($request)) {
            $linkedTo = null;
			$linkModule = $request->get('_mlinktotype');

			if(!vtlib_isModuleActive($linkModule)) {
				$response->setResult(array('ui'=>'', 'error'=>vtranslate('LBL_OPERATION_NOT_PERMITTED', $moduleName)));
				return $response;
			}

			$parent =  $request->get('_mlinkto');
			$foldername = $request->get('_folder');

			if(!empty($foldername)) {
				// This is to handle larger uploads
				ini_set('memory_limit', MailManager_Config_Model::get('MEMORY_LIMIT'));

				$connector = $this->getConnector($foldername);
				$mail = $connector->openMail($request->get('_msgno'), $foldername);
				$attachments = $mail->attachments(); // Initialize attachments
			} else {
                $mail = new MailManager_Message_Model();
            }

			$linkedto = MailManager_Relate_Action::getSalesEntityInfo($parent);
			$recordModel = Vtiger_Record_Model::getCleanInstance($linkModule);

			$fields = $recordModel->getModule()->getFields();
			foreach ($fields as $fieldName => $fieldModel) {
				if ($request->has($fieldName)) {
					$fieldValue = $request->get($fieldName);
					$fieldDataType = $fieldModel->getFieldDataType();
					if($fieldDataType == 'time') {
						$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
			}

			// Newly added field for source of created record
			if($linkModule != "ModComments"){
				$recordModel->set('source','Mail Manager');
			}

            switch ($linkModule) {
                case 'HelpDesk' :
                    $from = $mail->from();
                    $referenceFieldName = MailManager_Message_Model::RELATIONS_MAPPING[$linkModule][$linkedto['module']];

                    if (!empty($referenceFieldName) && !$request->has($referenceFieldName)) {
                        $recordModel->set($referenceFieldName, $this->setParentForHelpDesk($parent, $from));
                    }
                    break;

                case 'ModComments':
                    $recordModel->set('assigned_user_id', $currentUserModel->getId());
                    $recordModel->set('commentcontent', $request->getRaw('commentcontent'));
                    $recordModel->set('userid', $currentUserModel->getId());
                    $recordModel->set('creator', $currentUserModel->getId());
                    $recordModel->set('related_to', $parent);
                    break;
            }

            try {
				$recordModel->save();

				// This condition is added so that emails are not created for Tickets and Todo without Parent,
				// as there is no way to relate them
				if(empty($parent) && $linkModule != 'HelpDesk') {
					$linkedTo = MailManager_Relate_Action::associate($mail, $recordModel->getId());
				}

				// add attachments to the tickets as Documents
				if($linkModule == 'HelpDesk' && !empty($attachments)) {
					$relationController = new MailManager_Relate_Action();
					$relationController->__SaveAttachements($mail, $linkModule, $recordModel);
				}

                $this->retrieveRelationship($request, $mail, $linkedTo);

                $response->setResult(['ui' => $viewer->view('Relationship.tpl', $request->getModule(), true)]);
            } catch (DuplicateException $e) {
				$response->setResult(array('ui' => '', 'error' => $e, 'title' => $e->getMessage(), 'message' => $e->getDuplicationMessage()));
			} catch(Exception $e) {
				$response->setResult( array( 'ui' => '', 'error' => $e ));
			}

		} elseif ('savedraft' == $this->getOperationArg($request)) {
			$connector = $this->getConnector('__vt_drafts');
			$draftResponse = $connector->saveDraft($request);
			$response->setResult($draftResponse);
		} elseif ('saveattachment' == $this->getOperationArg($request)) {
			$connector = $this->getConnector('__vt_drafts');
			$uploadResponse = $connector->saveAttachment($request);
			$response->setResult($uploadResponse);
		} elseif ('commentwidget' == $this->getOperationArg($request)) {
            $foldername = $request->get('_folder');
            $connector = $this->getConnector($foldername);
            $mail = $connector->openMail($request->get('_msgno'), $foldername);

			$viewer->assign('LINKMODULE', $request->get('_mlinktotype'));
			$viewer->assign('PARENT', $request->get('_mlinkto'));
			$viewer->assign('MSGNO', $request->get('_msgno'));
			$viewer->assign('FOLDER', $request->get('_folder'));
			$viewer->assign('MODULE', $request->getModule());
			$viewer->assign('COMMENTCONTENT', Core_CKEditor_UIType::transformEditViewDisplayValue($mail->body()));
			$viewer->view( 'MailManagerCommentWidget.tpl', 'MailManager' );
			$response = false;
		}

		return $response;
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
        $subject = $mail->subject();
        $email = $mail->from();

        if ($isSentFolder) {
            $email = $mail->to();

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