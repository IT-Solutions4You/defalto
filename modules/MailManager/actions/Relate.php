<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/Settings/MailConverter/handlers/MailScannerAction.php';
require_once 'modules/Settings/MailConverter/handlers/MailAttachmentMIME.php';
require_once 'modules/MailManager/MailManager.php';

class MailManager_Relate_Action extends Vtiger_MailScannerAction {

	public function __construct($foractionid = 0) {
	}

	/**
	 * Create new Email record (and link to given record) including attachments
	 * @global Users $current_user
	 * @global PearDataBase $db
	 * @param  MailManager_Message_Model $mailrecord
	 * @param String $module
	 * @param CRMEntity $linkfocus
	 * @return Integer
	 */
	public function __CreateNewEmail($mailrecord, $module, $linkfocus) {
		$site_URL = vglobal('site_URL');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $currentUserModel);
		$meta = $handler->getMeta();

		if ($meta->hasWriteAccess() != true) {
			return false;
		}

		$mailBoxModel = MailManager_Mailbox_Model::activeInstance();
		$username = $mailBoxModel->username();
		$recordModel = Vtiger_Record_Model::getCleanInstance('ITS4YouEmails');
		$recordModel->set('subject', $mailrecord->_subject);

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
		$body = $mailrecord->getBodyHTML();
		$inlineAttachments = $mailrecord->inlineAttachments();
		if (is_array($inlineAttachments)) {
			foreach ($inlineAttachments as $index => $att) {
				$cid = $att['cid'];
				$attch_name = Vtiger_MailRecord::__mime_decode($att['filename']);
				$id = $mailrecord->muid();
				$src = $site_URL . "/index.php?module=MailManager&view=Index&_operation=mail&_operationarg=attachment_dld&_muid=$id&_atname=" . urlencode($attch_name);
				$body = preg_replace('/cid:' . $cid . '/', $src, $body);
				$inline_cid[$attch_name] = $cid;
			}
		}
		$recordModel->set('body', $body);
		$recordModel->set('assigned_user_id', $currentUserModel->get('id'));
		$recordModel->set('email_flag', 'MailManager');

		$from = $mailrecord->_from[0];
		$to = implode(',', $mailrecord->_to);
		$cc = (!empty($mailrecord->_cc))? implode(',', $mailrecord->_cc) : '';
		$bcc= (!empty($mailrecord->_bcc))? implode(',', $mailrecord->_bcc) : '';
		
		//emails field were restructured and to,bcc and cc field are JSON arrays
		$recordModel->set('from_email', $from);
		$recordModel->set('to_email', $to);
		$recordModel->set('cc_email', $cc);
		$recordModel->set('bcc_email', $bcc);
		$recordModel->set('mailboxemail', $username);
		$recordModel->set('mail_manager_id', $mailrecord->muid());
		$recordModel->save();

		// TODO: Handle attachments of the mail (inline/file)
		$this->__SaveAttachements($mailrecord, 'ITS4YouEmails', $recordModel);

		return $recordModel->getId();
	}

	/**
	 * Save attachments from the email and add it to the module record.
	 * @global PearDataBase $db
	 * @global String $root_directory
	 * @param MailManager_Message_Model $mailrecord
	 * @param String $basemodule
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function __SaveAttachements($mailrecord, $basemodule, $recordModel) {
		$db = PearDatabase::getInstance();

		// If there is no attachments return
		if(!$mailrecord->_attachments) return;

		$userid = $recordModel->get('assigned_user_id');
		$recordId = $recordModel->getId();
		$setype = "$basemodule Attachment";

		$date_var = $db->formatDate(date('YmdHis'), true);

		foreach($mailrecord->_attachments as $attachmentData) {
			$filename = $attachmentData['filename'];
			$filecontent = $attachmentData['data'];
			
			if(empty($filecontent)) continue;

			$attachid = $db->getUniqueId('vtiger_crmentity');
			$description = $filename;
			$usetime = $db->formatDate($date_var, true);

			$db->pquery("INSERT INTO vtiger_crmentity(crmid, smcreatorid, smownerid,
				modifiedby, setype, description, createdtime, modifiedtime, presence, deleted)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					Array($attachid, $userid, $userid, $userid, $setype, $description, $usetime, $usetime, 1, 0));

			$issaved = $this->__SaveAttachmentFile($attachid, $filename, $filecontent);

			if($issaved) {
				// To compute file size & type
				$attachRes = $db->pquery("SELECT * FROM vtiger_attachments WHERE attachmentsid = ?", array($attachid));
				if($db->num_rows($attachRes)) {
					$filePath = $db->query_result($attachRes, 0, 'path');
					$completeFilePath = vglobal('root_directory').$filePath. $attachid.'_'. $filename;
					if(file_exists($completeFilePath)) {
						$fileSize = filesize($completeFilePath);
						$mimetype = MailAttachmentMIME::detect($completeFilePath);
					}
				}

				// Link file attached to emails also, for it to appear on email's page
				if(!empty($recordId) && !empty($attachid)) {
					$this->relateAttachment($recordId, $attachid);
				}
			}
		}
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

		$modulename = getSalesEntityType($linkto);
		$linkfocus = CRMEntity::getInstance($modulename);
		$linkfocus->retrieve_entity_info($linkto, $modulename);
		$linkfocus->id = $linkto;

		$emailid = $instance->__CreateNewEmail($mailrecord, $modulename, $linkfocus);

		if (!empty($emailid)) {
			MailManager::updateMailAssociation($mailrecord->uniqueid(), $emailid, $linkfocus->id);
			// To add entry in ModTracker for email relation
			relateEntities($linkfocus, $modulename, $linkto, 'ITS4YouEmails', $emailid);
		}

		$name = getEntityName($modulename, $linkto);

        return self::buildDetailViewLink($modulename, $linkfocus->id, $name[$linkto]);
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