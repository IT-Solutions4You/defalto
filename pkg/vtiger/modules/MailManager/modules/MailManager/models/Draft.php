<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'include/Webservices/Query.php';
include_once 'include/Webservices/Create.php';
include_once 'include/Webservices/Update.php';
include_once 'modules/MailManager/MailManager.php';

class MailManager_Draft_Model
{

	static $totalDraftCount;

	public static function getInstance()
	{
		return new self();
	}

	public function folderInstance()
	{
		return new MailManager_DraftFolder_Model('Drafts');
	}

	public function searchDraftMails($q, $type, $page, $limit, $folder)
	{
		if ($type == 'all') {
			$where = $this->constructAllClause($q);
		} else {
			$where = $type . " LIKE '%" . $q . "%'";
		}
		$where = ' AND ' . $where;
		$draftMails = $this->getDrafts($page, $limit, $folder, $where);

		return $draftMails;
	}

	public function constructAllClause($query)
	{
		$fields = ['bccmail', 'ccmail', 'subject', 'saved_toid', 'description'];
		for ($i = 0; $i < php7_count($fields); $i++) {
			if ($i == php7_count($fields) - 1) {
				$clause .= $fields[$i] . " LIKE '%" . $query . "%'";
			} else {
				$clause .= $fields[$i] . " LIKE '%" . $query . "%' OR ";
			}
		}

		return $clause;
	}

	public function getDrafts($page, $limit, $folder, $where = null)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $currentUserModel);
		$meta = $handler->getMeta();
		if (!$meta->hasReadAccess()) {
			return false;
		}

		if (!empty($page)) {
			$limitClause = 'LIMIT ' . ($limit * $page) . ', ' . $limit;
		} else {
			$limitClause = 'LIMIT 0, ' . $limit;
		}
		$query = "SELECT * FROM ITS4YouEmails where email_flag='SAVED' $where ORDER BY modifiedtime DESC $limitClause;";
		$draftMails = vtws_query($query, $currentUserModel);
		for ($i = 0; $i < php7_count($draftMails); $i++) {
			foreach ($draftMails[$i] as $fieldname => $fieldvalue) {
				if ($fieldname == 'to_email' || $fieldname == 'cc_email' || $fieldname == 'bcc_email') {
					if (!empty($fieldvalue)) {
						$value = implode(',', Zend_Json::decode($fieldvalue));
						if (strlen($value) > 45) {
							$value = substr($value, 0, 45) . '....';
						}
						$draftMails[$i][$fieldname] = $value;
					}
				} elseif ($fieldname == 'date_start') {
					if (!empty($fieldvalue)) {
						$value = Vtiger_Date_UIType::getDisplayDateValue($fieldvalue);
						$draftMails[$i][$fieldname] = $value;
					}
				} elseif ($fieldname == 'id') {
					$emailId = vtws_getIdComponents($fieldvalue);
					$draftMails[$i][$fieldname] = $emailId[1];
				}
			}
		}
		if ($where) {
			$folder->setPaging($limit * $page + 1, $limit * $page + $limit, $limit, php7_count($draftMails), $page);
		} else {
			$total = $this->getTotalDraftCount();
			$folder->setPaging($limit * $page + 1, $limit * $page + $limit, $limit, $total, $page);
		}
		$folder->setMails($draftMails);

		return $draftMails;
	}

	public function getTotalDraftCount()
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (empty(self::$totalDraftCount)) {
			$DraftRes = $query = "SELECT * FROM ITS4YouEmails where email_flag='SAVED';";
			$draftMails = vtws_query($query, $currentUserModel);
			self::$totalDraftCount = php7_count($draftMails);

			return self::$totalDraftCount;
		} else {
			return self::$totalDraftCount;
		}
	}

	public function getDraftMail($request)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $currentUserModel);
		$meta = $handler->getMeta();
		if (!$meta->hasReadAccess()) {
			return false;
		}
		$id = vtws_getWebserviceEntityId('ITS4YouEmails', $request->get('id'));
		$draftMail = vtws_query("SELECT * FROM ITS4YouEmails where id = $id;", $currentUserModel);
		$emailId = vtws_getIdComponents($id);
		$draftMail['attachments'] = $this->getAttachmentDetails($emailId[1]);
		$draftMail[0]['id'] = $request->get('id');

		return $draftMail;
	}

	public function getAttachmentDetails($crmid)
	{
		$db = PearDatabase::getInstance();

		if (empty($crmid)) {
			return false;
		}

		$documentRes = $db->pquery('SELECT * FROM vtiger_senotesrel
									INNER JOIN vtiger_crmentity ON vtiger_senotesrel.notesid = vtiger_crmentity.crmid AND vtiger_senotesrel.crmid = ?
									INNER JOIN vtiger_notes ON vtiger_notes.notesid = vtiger_senotesrel.notesid
									INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.crmid = vtiger_notes.notesid
									INNER JOIN vtiger_attachments ON vtiger_attachments.attachmentsid = vtiger_seattachmentsrel.attachmentsid
									WHERE vtiger_crmentity.deleted = 0', [$crmid]);
		if ($db->num_rows($documentRes)) {
			for ($i = 0; $i < $db->num_rows($documentRes); $i++) {
				$draftMail[$i]['name'] = $db->query_result($documentRes, $i, 'filename');
				$filesize = $db->query_result($documentRes, $i, 'filesize');
				$draftMail[$i]['size'] = $this->getFormattedFileSize($filesize);
				$draftMail[$i]['docid'] = $db->query_result($documentRes, $i, 'notesid');
				$draftMail[$i]['path'] = $db->query_result($documentRes, $i, 'path');
				$draftMail[$i]['fileid'] = $db->query_result($documentRes, $i, 'attachmentsid');
				$draftMail[$i]['attachment'] = $db->query_result($documentRes, $i, 'name');
			}
		}

		return $draftMail;
	}

	public function saveDraft($request)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if (!MailManager::checkModuleWriteAccessForCurrentUser('ITS4YouEmails')) {
			return false;
		}

		$email = CRMEntity::getInstance('ITS4YouEmails');

		$to_string = rtrim($request->get('to'), ',');
		$cc_string = rtrim($request->get('cc'), ',');
		$bcc_string = rtrim($request->get('bcc'), ',');

		$parentIds = $this->getParentFromEmails($to_string);

		$emailId = $request->get('emailid');
		$subject = $request->get('subject');

		$email = CRMEntity::getInstance('ITS4YouEmails');
		$email->column_fields['assigned_user_id'] = $currentUserModel->getId();
		$email->column_fields['date_start'] = date('Y-m-d');
		$email->column_fields['time_start'] = date('H:i');
		$email->column_fields['related_to'] = $this->retrieveFirstParent($parentIds);
		$email->column_fields['subject'] = (!empty($subject)) ? $subject : 'No Subject';
		$email->column_fields['body'] = $request->get('body');
		$email->column_fields['from_email'] = $fromEmail;
		$email->column_fields['to_email'] = $to_string;
		$email->column_fields['cc_email'] = $cc_string;
		$email->column_fields['bcc_email'] = $bcc_string;
		$email->column_fields['email_flag'] = 'SAVED';

		if (empty($emailId)) {
			$email->save('ITS4YouEmails');
		} else {
			$email->id = $emailId;
			$email->mode = 'edit';
			$email->save('ITS4YouEmails');
		}

		return $email->id;
	}

	/**
	 * Retrieves the first parent id from a string (2@71|...)
	 *
	 * @param string $parentIds
	 *
	 * @return int|string
	 */
	public function retrieveFirstParent(string $parentIds): int|string
	{
		$myIds = explode('|', $parentIds);

		if (php7_count($myIds)) {
			$realId = explode('@', $myIds[0]);

			if (!empty($realId[0]) && !empty($emailId)) {
				return $realId[0];
			}
		}

		return '';
	}

	public function getFromEmailAddress()
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$fromEmail = Settings_Vtiger_Systems_Model::getFromEmailField();

		if (empty($fromEmail)) {
			$fromEmail = $currentUserModel->get('email1');
		}

		return $fromEmail;
	}

	public function saveAttachment($request)
	{
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		//need to handle earlier as Emails save will save the uploaded files from $_FILES
		$uploadResponse = $this->handleUpload();

		$emailId = $this->saveDraft($request);

		if ($emailId != false) {

			if ($uploadResponse && $uploadResponse['success'] == true) {
				// Link document to base record
				if (!empty($uploadResponse['docid'])) {
					$this->saveEmailDocumentRel($emailId, $uploadResponse['docid']);
				}
				if (!empty($uploadResponse['attachid'])) {
					$this->saveAttachmentRel($emailId, $uploadResponse['attachid']);
				}
			}
			$uploadResponse['emailid'] = $emailId;
		} else {
			$uploadResponse['error'] = true;
		}

		return $uploadResponse;
	}

	public function getParentFromEmails($to_string)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if (!empty($to_string)) {
			$toArray = explode(',', $to_string);
			foreach ($toArray as $to) {
				$relatedtos = MailManager::lookupMailInVtiger(trim($to), $currentUserModel);
				if (!empty($relatedtos) && is_array($relatedtos)) {
					for ($i = 0; $i < php7_count($relatedtos); $i++) {
						$relateto = vtws_getIdComponents($relatedtos[$i]['record']);
						$parentIds .= $relateto[1] . '@1|';
					}
				}
			}
		}

		return $parentIds;
	}

	public function handleUpload()
	{
		$allowedFileExtension = [];

		$uploadLimit = MailManager_Config_Model::get('MAXUPLOADLIMIT', Vtiger_Util_Helper::getMaxUploadSizeInBytes());
		$filePath = decideFilePath();

		$upload = new MailManager_Upload_Action($allowedFileExtension, $uploadLimit);

		return $upload->handleUpload($filePath, false);
	}

	public function saveEmailDocumentRel($emailId, $documentId)
	{
		$db = PearDatabase::getInstance();
		if (!empty($emailId) && !empty($documentId)) {
			$db->pquery('INSERT INTO vtiger_senotesrel(crmid, notesid) VALUES(?,?)',
				[$emailId, $documentId]);
		}
	}

	public function saveAttachmentRel($crmid, $attachId)
	{
		$db = PearDatabase::getInstance();
		if (!empty($crmid) && !empty($attachId)) {
			$db->pquery('INSERT INTO vtiger_seattachmentsrel(crmid, attachmentsid) VALUES(?,?)',
				[$crmid, $attachId]);
		}
	}

	public function deleteMail($ids)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$focus = CRMEntity::getInstance('ITS4YouEmails');
		$idList = explode(',', $ids);
		foreach ($idList as $id) {
			$focus->trash('ITS4YouEmails', $id);
		}
	}

	public function deleteAttachment($request)
	{
		$db = PearDatabase::getInstance();
		$emailid = $request->get('emailid');
		$docid = $request->get('docid');
		if (!empty($docid) && !empty($emailid)) {
			$db->pquery('DELETE FROM vtiger_senotesrel WHERE crmid = ? AND notesid = ?', [$emailid, $docid]);

			return true;
		}

		return false;
	}

	public function getFormattedFileSize($filesize)
	{
		if ($filesize < 1024) {
			$filesize = sprintf('%0.2f', round($filesize, 2)) . 'b';
		} elseif ($filesize > 1024 && $filesize < 1048576) {
			$filesize = sprintf('%0.2f', round($filesize / 1024, 2)) . 'kB';
		} elseif ($filesize > 1048576) {
			$filesize = sprintf('%0.2f', round($filesize / (1024 * 1024), 2)) . 'MB';
		}

		return $filesize;
	}
}