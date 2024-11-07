<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/MailManager/MailManager.php';

class MailManager_Relate_Action extends Settings_MailConverter_MailScannerAction_Handler {

	public function __construct($foractionid = 0) {
	}

    /**
     * Create new Email record (and link to given record) including attachments
     * @param MailManager_Message_Model $mailrecord
     * @param String $module
     * @param CRMEntity $linkfocus
     * @return Integer
     * @throws AppException
     * @global PearDataBase $db
     * @global Users $current_user
     */
	public function createNewEmail($mailrecord, $module, $linkfocus) {
		$site_URL = vglobal('site_URL');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $currentUserModel);
		$meta = $handler->getMeta();

		if ($meta->hasWriteAccess() != true) {
			return false;
		}

		$mailBoxModel = MailManager_Mailbox_Model::getActiveInstance();
		$username = $mailBoxModel->username();
		$recordModel = Vtiger_Record_Model::getCleanInstance('ITS4YouEmails');
		$recordModel->set('subject', $mailrecord->getSubject());

		if(!empty($module)) {
            $recordModel->set('parent_type', $module);
        }

        if (!empty($linkfocus->id)) {
            $recordModel->set('related_to', $linkfocus->id);
            $fieldName = MailManager_Message_Model::RELATIONS_MAPPING['ITS4YouEmails'][$module];

            if (!empty($fieldName)) {
                $recordModel->set($fieldName, $linkfocus->id);
            }
        }

        //To display inline image in body of email when we go to detail view of email from email related tab of related record.
		$recordModel->set('body', $mailrecord->getBody(false));
		$recordModel->set('assigned_user_id', $currentUserModel->get('id'));
		$recordModel->set('email_flag', 'MailManager');

        $from = $mailrecord->getFrom()[0];
        $to = implode(',', $mailrecord->getTo());
        $cc = (!empty($mailrecord->getCC())) ? implode(',', $mailrecord->getCC()) : '';
        $bcc = (!empty($mailrecord->getBCC())) ? implode(',', $mailrecord->getBCC()) : '';
		
		//emails field were restructured and to,bcc and cc field are JSON arrays
		$recordModel->set('from_email', $from);
		$recordModel->set('to_email', $to);
		$recordModel->set('cc_email', $cc);
		$recordModel->set('bcc_email', $bcc);
		$recordModel->set('mailboxemail', $username);
		$recordModel->set('mail_manager_id', $mailrecord->getUid());
		$recordModel->save();

		// TODO: Handle attachments of the mail (inline/file)
		$this->saveAttachments($mailrecord, 'ITS4YouEmails', $recordModel);

		return $recordModel->getId();
	}

    /**
     * Save attachments from the email and add it to the module record.
     * @param MailManager_Message_Model $mailRecord
     * @param string $baseModule
     * @param Vtiger_Record_Model $recordModel
     * @throws AppException
     * @global PearDataBase $db
     * @global String $root_directory
     */
    public function saveAttachments(MailManager_Message_Model $mailRecord, string $baseModule, Vtiger_Record_Model $recordModel): void
    {
        $recordId = $recordModel->getId();
        $currentUser = Users_Record_Model::getCurrentUserModel();

        foreach ($mailRecord->getAttachments() as $attachmentInfo) {
            $attachment = $this->saveAttachment($baseModule, $attachmentInfo);

            if ($attachment->getId()) {
                $documentRecord = $mailRecord->saveDocumentFile($attachment->getName(), $attachment->getContent(), $currentUser->getId(), 'MailManager');

                if ($documentRecord->getId()) {
                    $documentRecord->saveAttachmentsRelation($documentRecord->getId(), $attachment->getId());
                    $documentRecord->saveAttachmentsRelation($recordModel->getId(), $attachment->getId());
                    $documentRecord->saveDocumentsRelation($recordModel->getId(), $documentRecord->getId());
                }
            }
        }

        foreach ($mailRecord->getInlineAttachments() as $attachmentInfo) {
            $attachment = $this->saveAttachment($baseModule, $attachmentInfo);

            if ($attachment->getId()) {
                $this->relateAttachment($recordId, $attachment->getId());
            }
        }
    }

    /**
     * @param string $baseModule
     * @param array $attachmentInfo
     * @return Core_Attachment_Model
     * @throws AppException
     */
    public function saveAttachment(string $baseModule, array $attachmentInfo): Core_Attachment_Model
    {
        $attachment = Core_Attachment_Model::getInstance($baseModule);
        $attachment->retrieveDefault($attachmentInfo['filename']);
        $attachment->setType($attachmentInfo['type']);
        $attachment->saveFile($attachmentInfo['data']);

        if ($attachment->validateSaveFile()) {
            $attachment->save();
        }

        return $attachment;
    }

    /**
	 *
	 * @global Users $current_user
	 * @param MailManager_Message_Model $mailrecord
	 * @param Integer $linkto
	 * @return Array
	 */
	public static function associate($mailrecord, $linkto) {
		$instance = new self();

		$moduleName = getSalesEntityType($linkto);
		$linkfocus = CRMEntity::getInstance($moduleName);
		$linkfocus->retrieve_entity_info($linkto, $moduleName);
		$linkfocus->id = $linkto;
		$emailid = $instance->createNewEmail($mailrecord, $moduleName, $linkfocus);

		if (!empty($emailid)) {
			MailManager::updateMailAssociation($mailrecord->getUniqueId(), $emailid, $linkfocus->id);
			// To add entry in ModTracker for email relation
			relateEntities($linkfocus, $moduleName, $linkto, 'ITS4YouEmails', $emailid);
		}

		$name = getEntityName($moduleName, $linkto);

        return self::buildDetailViewLink($moduleName, $linkfocus->id, $name[$linkto]);
	}

	/**
	 * Returns the information about the Parent
	 * @param String $module
	 * @param Integer $record
	 * @param String $label
	 * @return Array
	 */
    public static function buildDetailViewLink($module, $record, $label)
    {
        if (!empty($record) && isRecordExists($record)) {
            $recordModel = Vtiger_Record_Model::getInstanceById($record, $module);

            return [
                'icon' => $recordModel->getModule()->getModuleIcon(),
                'record' => $record,
                'module' => $module,
                'label' => $recordModel->getName(),
                'url' => $recordModel->getDetailViewUrl(),
            ];
        }

        $detailViewUrl = sprintf('index.php?module=%s&view=Detail&record=%s', $module, $record);
        $detailViewLink = sprintf("<a target='_blank' href='%s'>%s</a>", $detailViewUrl, textlength_check($label));

        return [
            'record' => $record,
            'module' => $module,
            'label' => $label,
            'detailviewlink' => $detailViewLink,
            'url' => $detailViewUrl,
        ];
    }

    /**
	 * Returns the related entity for a Mail
	 * @global PearDataBase $db
	 * @param integer $mailuid - Mail Number
	 * @return Array
	 */
	public static function associatedLink($mailuid) {
		$info = MailManager::lookupMailAssociation($mailuid);
		if ($info) {
			return self::getSalesEntityInfo($info['crmid']);
		}
		return false;
	}

	/**
	 * Returns the information about the Parent
	 * @global PearDataBase $db
	 * @param Integer $crmid
	 * @return Array
	 */
	public static function getSalesEntityInfo($crmid) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT setype FROM vtiger_crmentity WHERE crmid=? AND deleted=0", array($crmid));
		if ($db->num_rows($result)) {
			$modulename = $db->query_result($result, 0, 'setype');
			$recordlabels = getEntityName($modulename, array($crmid));
			return self::buildDetailViewLink($modulename, $crmid, $recordlabels[$crmid]);
		}
	}

	/**
	 *
	 * @global PearDataBase $db
	 * @param <type> $modulewsid
	 * @return <type>
	 */
	public static function ws_modulename($modulewsid) {
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT name FROM vtiger_ws_entity WHERE id=?", array($modulewsid));
		if ($db->num_rows($result)) return $db->query_result($result, 0, 'name');
		return false;
	}

	/**
	 * Related an attachment to a Email record
	 * @global PearDataBase $db
	 * @param Integer $crmId
	 * @param Integer $attachId
	 */
	public function relateAttachment($crmId, $attachId) {
		$db = PearDatabase::getInstance();
		$db->pquery("INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)",
				array($crmId, $attachId));
	}

}
?>