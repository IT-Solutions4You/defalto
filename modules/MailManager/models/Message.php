<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class MailManager_Message_Model extends Settings_MailConverter_MailRecord_Handler  {

    protected $moduleName = 'MailManager';
	protected $lookUps = [];
	protected $attachmentsAllowed = null;

    public const RELATIONS_MAPPING = [
        'HelpDesk' => [
            'Contacts' => 'contact_id',
            'Accounts' => 'parent_id',
        ],
        'ITS4YouEmails' => [
            'Vendors' => 'vendor_id',
            'Contacts' => 'contact_id',
            'Accounts' => 'account_id',
            'Leads' => 'lead_id',
        ],
        'Potentials' => [
            'Contacts' => 'contact_id',
            'Accounts' => 'related_to',
        ],
    ];

    /**
     * array values [ModuleName, TableId, TableName] for field module_manager_id
     */
    public const RELATIONS_TABLES = [
        ['HelpDesk', 'ticketid', 'vtiger_troubletickets'],
        ['Potentials', 'potentialid', 'vtiger_potential'],
        ['ITS4YouEmails', 'its4you_emails_id', 'its4you_emails'],
    ];

    /**
     * List of modules used to match the Email address
     * @var Array
     */
    public const EMAIL_ADDRESS_MODULES = array ('Accounts', 'Contacts', 'Leads', 'HelpDesk', 'Potentials');

    public array $displayedRecords = [];

	/**
	 * Clears the cache data
	 * @global PearDataBase Instance $db
	 * @global Users Instance $currentUserModel
	 * @param Integer $waybacktime
	 */
	public static function pruneOlderInDB($waybacktime) {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		//remove the saved attachments
		self::removeSavedAttachmentFiles($waybacktime);

		$db->pquery("DELETE FROM vtiger_mailmanager_mailrecord
		WHERE userid=? AND lastsavedtime < ?", array($currentUserModel->getId(), $waybacktime));
		$db->pquery("DELETE FROM vtiger_mailmanager_mailattachments
		WHERE userid=? AND lastsavedtime < ?", array($currentUserModel->getId(), $waybacktime));
	}

	/**
	 * Used to remove the saved attachments
	 * @global Users Instance $currentUserModel
	 * @global PearDataBase Instance $db
	 * @param Integer $waybacktime - timestamp
	 */
	public static function removeSavedAttachmentFiles($waybacktime) {
		$db = PearDatabase::getInstance();
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$mailManagerAttachments = $db->pquery("SELECT attachid, aname, path FROM vtiger_mailmanager_mailattachments
			WHERE userid=? AND lastsavedtime < ?", array($currentUserModel->getId(), $waybacktime));

		for($i=0; $i<$db->num_rows($mailManagerAttachments); $i++) {
			$atResultRow = $db->raw_query_result_rowdata($mailManagerAttachments, $i);

			$db->pquery("UPDATE vtiger_crmentity set deleted = 1 WHERE crmid = ?", array($atResultRow['attachid']));

			$filepath = $atResultRow['path'] ."/". $atResultRow['attachid'] ."_". $atResultRow['aname'];
			if(file_exists($filepath)) {
				unlink($filepath);
			}
		}
	}

    /**
     * Reads the Mail information from the Database
     * @return void
     * @throws AppException
     */

    public function retrieveRecordFromDB(): void
    {
        if (!$this->validateUid()) {
            return;
        }

        $uid = $this->getUid();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $data = $this->getRecordTable()->selectData([], ['userid' => $currentUserModel->getId(), 'muid' => $uid]);

        if (!empty($data['muid'])) {
            $this->setFrom(Zend_Json::decode(decode_html($data['mfrom'])));
            $this->setTo(Zend_Json::decode(decode_html($data['mto'])));
            $this->setCC(Zend_Json::decode(decode_html($data['mcc'])));
            $this->setBCC(Zend_Json::decode(decode_html($data['mbcc'])));
            $this->setDate(intval($data['mdate']));
            $this->setSubject(decode_html($data['msubject']));
            $this->setBody(decode_html($data['mbody']));
            $this->setUniqueId(decode_html($data['muniqueid']));
        }
    }

    /**
     * @param bool $withContent
     * @param string|null $aName
     * @param int|null $aId
     * @return void
     * @throws AppException
     */
    public function retrieveAttachmentsFromDB(bool $withContent, string|null $aName = null, int|null $aId = null): void
    {
        if (!empty($this->_attachments)) {
            return;
        }

        $db = PearDatabase::getInstance();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $this->_attachments = [];
        $data = ['aname', 'attachid', 'path', 'cid'];
        $search = ['userid' => $currentUserModel->getId(), 'muid' => $this->getUid()];

        if (!empty($aName)) {
            $search['aname'] = $aName;
        }

        if (!empty($aId)) {
            $search['attachid'] = $aId;
        }

        $result = $this->getAttachmentTable()->selectResult($data, $search);

        while ($row = $db->fetchByAssoc($result)) {
            $fileName = sanitizeUploadFileName($row['aname'], vglobal('upload_badext'));
            $filePath = $row['path'] . $row['attachid'] . '_' . $fileName;
            $fileSize = filesize($filePath);
            $fileContent = '';

            if ($withContent) {
                $fileContent = file_get_contents($filePath);
            }

            $fileInfo = [
                'filename' => $row['aname'],
                'data' => $fileContent,
                'size' => $fileSize,
                'path' => $filePath,
                'type' => mime_content_type($filePath),
                'attachment_id' => $row['attachid'],
                'attachment_url' => $this->getAttachmentUrl($row['attachid'], $row['aname']),
                'cid' => $row['cid'],
            ];

            if (!empty($row['cid'])) {
                $this->_inline_attachments[] = $fileInfo;
            } else {
                $this->_attachments[] = $fileInfo;
            }
        }
    }

    public function getAttachmentUrl($attachmentId, $attachmentName): string
    {
        return sprintf(
            '%s/index.php?module=MailManager&view=Index&_operation=mail&_operationarg=attachment_dld&_muid=%s&_atid=%s&_atname=%s',
            rtrim(vglobal('site_URL'), '/'),
            $this->getUid(),
            $attachmentId,
            urlencode($attachmentName),
        );
    }

    public function saveRecordToDB(): void
    {
        $mUid = $this->getUid();
        $record = $this->getRecordTable()->selectData(['muid'], ['muid' => $mUid]);

        if (!empty($record['muid'])) {
            return;
        }

        $this->saveRecord([
            'userid' => Users_Record_Model::getCurrentUserModel()->getId(),
            'muid' => $mUid,
            'mfrom' => Zend_Json::encode($this->_from),
            'mto' => Zend_Json::encode($this->_to),
            'mcc' => Zend_Json::encode($this->_cc),
            'mbcc' => Zend_Json::encode($this->_bcc),
            'mdate' => $this->_date,
            'msubject' => $this->_subject,
            'mbody' => $this->_body,
            'lastsavedtime' => strtotime("now"),
            'mfolder' => $this->getFolderName(),
        ]);
    }

    /**
     * @throws AppException
     */
    public function saveAttachmentsToDB(): void
    {
        $uid = $this->getUid();
        $currentUserId = Users_Record_Model::getCurrentUserModel()->getId();
        $attachment = $this->getAttachmentTable()->selectData(['muid'], ['muid' => $uid]);

        if (!empty($attachment['muid'])) {
            return;
        }

        if (!empty($this->_attachments)) {
            foreach ($this->_attachments as $index => $info) {
                $attachInfo = $this->saveAttachmentFile($info['filename'], $info['data'], $info['type']);

                if (is_array($attachInfo) && !empty($attachInfo)) {
                    $this->saveAttachment([
                        'userid' => $currentUserId,
                        'muid' => $uid,
                        'attachid' => $attachInfo['attachmentsid'],
                        'aname' => $attachInfo['storedname'],
                        'path' => $attachInfo['path'],
                        'lastsavedtime' => strtotime('now'),
                    ]);
                    $this->_attachments[$index]['attachment_id'] = $attachInfo['attachmentsid'];
                    $this->_attachments[$index]['attachment_url'] = $this->getAttachmentUrl($attachInfo['attachmentsid'], $attachInfo['storedname']);
                } else {
                    unset($this->_attachments[$index]);
                }

                unset($info['data']);
            }
        }

        if (!empty($this->_inline_attachments)) {
            foreach ($this->_inline_attachments as $index => $info) {
                $attachInfo = $this->saveAttachmentFile($info['filename'], $info['data'], $info['type']);

                if (is_array($attachInfo) && !empty($attachInfo)) {
                    $this->saveAttachment([
                        'userid' => $currentUserId,
                        'muid' => $uid,
                        'attachid' => $attachInfo['attachmentsid'],
                        'aname' => $attachInfo['storedname'],
                        'path' => $attachInfo['path'],
                        'lastsavedtime' => strtotime('now'),
                        'cid' => $info['cid'],
                    ]);
                    $this->_attachments[$index]['attachment_id'] = $attachInfo['attachmentsid'];
                    $this->_attachments[$index]['attachment_url'] = $this->getAttachmentUrl($attachInfo['attachmentsid'], $attachInfo['storedname']);
                } else {
                    unset($this->_inline_attachments[$index]);
                }

                unset($info['data']);
            }
        }
    }

    public function saveRecord($data)
    {
        $this->getRecordTable()->insertData($data);
    }

    public function getRecordTable() {
        return (new Core_DatabaseData_Model())->getTable('vtiger_mailmanager_mailrecord', null);
    }
    
    public function saveAttachment($data)
    {
        $this->getAttachmentTable()->insertData($data);
    }

    public function getAttachmentTable()
    {
        return (new Core_DatabaseData_Model())->getTable('vtiger_mailmanager_mailattachments', null);
    }




    /**
     * Sets the Mail Headers
     * @param object $mMessage
     * @param object|bool $mBox
     * @param MailManager_Folder_Model|bool $mFolder
     * @return self
     * @throws AppException
     */
    public static function parseOverview(object $mMessage, MailManager_Folder_Model|bool $mFolder = false, object|bool $mBox = false): self
    {
        if ($mMessage) {
            $instance = self::getInstanceByBoxMessage($mMessage, $mFolder, $mBox);
        } else {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param object $mMessage
     * @param object $mBox
     * @return MailManager_Message_Model
     * @throws AppException
     * @var MailManager_Folder_Model $mFolder
     */
    public static function getInstanceByBoxMessage(object $mMessage, MailManager_Folder_Model $mFolder, object $mBox): self
    {
        $instance = new self();
        $instance->setBoxMessage($mMessage);
        $instance->setFolder($mFolder);
        $instance->setBox($mBox);
        $instance->setUid(intval($mMessage->getUid()));
        $instance->retrieveRecord();

        return $instance;
    }

    public function getAttachmentIcon($fileName) {
		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
		$icon = '';
		switch(strtolower($ext)) {
			case 'txt' : $icon = 'fa-file-text';
				break;
			case 'doc' :
			case 'docx' : $icon = 'fa-file-word-o';
				break;
			case 'zip' :
			case 'tar' :
			case '7z' :
			case 'apk' :
			case 'bin' :
			case 'bzip' :
			case 'bzip2' :
			case 'gz' :
			case 'jar' :
			case 'rar' :
			case 'xz' : $icon = 'fa-file-archive-o';
				break;
			case 'jpeg' :
			case 'jfif' :
			case 'rif' :
			case 'gif' :
			case 'bmp' :
			case 'jpg' :
			case 'png' : $icon = 'fa-file-image-o';
				break;
			case 'pdf' : $icon = 'fa-file-pdf-o';
				break;
			case 'mp3' :
			case 'wma' :
			case 'wav' :
			case 'ogg' : $icon = 'fa-file-audio-o';
				break;
			case 'xls' :
			case 'xlsx' : $icon = 'fa-file-excel-o';
				break;
			case 'webm' :
			case 'mkv' :
			case 'flv' :
			case 'vob' :
			case 'ogv' :
			case 'ogg' :
			case 'avi' :
			case 'mov' :
			case 'mp4' :
			case 'mpg' :
			case 'mpeg' :
			case '3gp' : $icon = 'fa-file-video-o';
				break;
			default : $icon = 'fa-file-o';
				break;
		}
		
		return $icon;
	}

    public array $mUidRelations = [];
    public array $mUidRelationRecords = [];

    public function getRelationIds(): array
    {
        if (empty($this->mUid) || !empty($this->mUidRelations)) {
            return $this->mUidRelations;
        }

        $this->retrieveRelationsMailManager();

        foreach (self::RELATIONS_TABLES as $relation) {
            $this->retrieveRelations($relation[0], $relation[1], $relation[2]);
        }

        return $this->mUidRelations;
    }

    public function retrieveRelationsMailManager(): void
    {
        $adb = PearDatabase::getInstance();
        $uid = $this->getUid();
        $result = $adb->pquery(
            'SELECT vtiger_mailmanager_mailrel.* FROM vtiger_mailmanager_mailrel 
            INNER JOIN vtiger_mailmanager_mailrecord ON vtiger_mailmanager_mailrecord.muniqueid=vtiger_mailmanager_mailrel.mailuid
            WHERE vtiger_mailmanager_mailrecord.muid=?',
            [$uid],
        );

        while ($row = $adb->fetchByAssoc($result)) {
            $this->mUidRelations[(int)$row['emailid']] = 'ITS4YouEmails';
            $this->mUidRelations[(int)$row['crmid']] = getSalesEntityType((int)$row['crmid']);
        }
    }

    public function retrieveRelations($module, $tableId, $tableName)
    {
        $adb = PearDatabase::getInstance();
        $sql = sprintf('SELECT %s as id FROM %s WHERE mail_manager_id=?', $tableId, $tableName);
        $result = $adb->pquery($sql, [$this->getUid()]);

        while ($row = $adb->fetchByAssoc($result)) {
            if (!isRecordExists($row['id'])) {
                continue;
            }

            $this->mUidRelations[(int)$row['id']] = $module;
        }
    }


    public function getRelations(): array
    {
        if (!empty($this->mUidRelationRecords)) {
            return $this->mUidRelationRecords;
        }

        foreach ($this->getRelationIds() as $relationId => $relationModule) {
            if (!isRecordExists($relationId)) {
                continue;
            }

            $this->mUidRelationRecords[$relationId] = Vtiger_Record_Model::getInstanceById($relationId, $relationModule);
        }

        return $this->mUidRelationRecords;
    }

    public function getEmailRelations()
    {
        $relation = [];

        foreach ($this->getRelations() as $recordModel) {
            if ('ITS4YouEmails' !== $recordModel->getModuleName()) {
                continue;
            }

            $relation[$recordModel->getId()] = $recordModel;
        }

        return $relation;
    }

    public function retrieveLookUps(string $lookupEmail, string $toEmail, bool $isSentFolder = false)
    {
        if (!empty($this->lookUps)) {
            return $this->lookUps;
        }

        $allowedModules = $this->getCurrentUserMailManagerAllowedModules();
        $currentUserModel = Users_Record_Model::getCurrentUserModel();

        foreach (self::EMAIL_ADDRESS_MODULES as $MODULE) {
            if (!in_array($MODULE, $allowedModules)) {
                continue;
            }

            //lookup will be from email other than sent mail folder
            //if its sent folder, lookup email will be first TO email
            if ($lookupEmail == $currentUserModel->get('email1') || $isSentFolder) {
                $lookupEmail = explode(',', $toEmail)[0];
            }

            if (empty($lookupEmail)) {
                continue;
            }

            $lookupResults = $this->lookupModuleRecordsWithEmail($MODULE, $lookupEmail);

            foreach ($lookupResults as $lookupResult) {
                if (array_key_exists('parent', $lookupResult)) {
                    $this->lookUps[getSalesEntityType($lookupResult['id'])][] = $lookupResult;
                } else {
                    $this->lookUps[$MODULE][] = $lookupResult;
                }
            }
        }

        return $this->lookUps;
    }

    public function getLookUps()
    {
        return $this->lookUps;
    }

    /**
     * Returns the List of Matching records with the Email Address
     * @global Users Instance $currentUserModel
     * @param String $module
     * @param Email $email Address $email
     * @return Array
     */
    public function lookupModuleRecordsWithEmail($module, $email)
    {
        $currentUserModel = vglobal('current_user');
        $results = [];
        $activeEmailFields = null;

        $handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
        $meta = $handler->getMeta();
        $emailFields = $meta->getEmailFields();
        $moduleFields = $meta->getModuleFields();

        foreach ($emailFields as $emailFieldName) {
            $emailFieldInstance = $moduleFields[$emailFieldName];
            if (!(((int)$emailFieldInstance->getPresence()) == 1)) {
                $activeEmailFields[] = $emailFieldName;
            }
        }

        //before calling vtws_query, need to check Active Email Fields are there or not
        if (php7_count($activeEmailFields) > 0) {
            $query = $this->buildSearchQuery($module, $email, 'EMAIL');
            $qresults = vtws_query($query, $currentUserModel);
            $describe = $this->ws_describe($module);
            $labelFields = explode(',', $describe['labelFields']);

            //overwrite labelfields with field names instead of column names
            $fieldColumnMapping = $meta->getFieldColumnMapping();
            $columnFieldMapping = array_flip($fieldColumnMapping);

            foreach ($labelFields as $i => $columnname) {
                $labelFields[$i] = $columnFieldMapping[$columnname];
            }

            foreach ($qresults as $qresult) {
                $recordId = vtws_getIdComponents($qresult['id'])[1];

                if (!empty($recordId) && isRecordExists($recordId)) {
                    $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
                    $results[] = [
                        'wsid' => $qresult['id'],
                        'id' => $recordId,
                        'icon' => $recordModel->getModule()->getModuleIcon(),
                        'url' => $recordModel->getDetailViewUrl(),
                        'label' => $recordModel->getName(),
                    ];
                }
            }
        }

        if (!empty($results)) {
            foreach ($results as $result) {
                $relResults = $this->lookupRelModuleRecords($result['wsid']);
                $results = array_merge($results, $relResults);
            }
        }

        return $results;
    }

    public function displayed($record): void
    {
        $this->displayedRecords[$record] = $record;
    }

    public function isDisplayed($record): bool
    {
        return !empty($this->displayedRecords[$record]);
    }

    public function getOtherRelations(): array
    {
        $relations = [];

        foreach ($this->getRelations() as $record) {
            if ($this->isDisplayed($record->getId())) {
                continue;
            }

            $relations[$record->getId()] = $record;
        }

        return $relations;
    }

    /**
     * @param int $record Record id from contact, account, leads, ...
     * @return array
     */
    public function getRelationsById($record): array
    {
        $relation = [];

        if (empty($record)) {
            return $relation;
        }

        $record = (int)$record;
        $moduleName = getSalesEntityType($record);

        foreach ($this->getRelations() as $recordModel) {
            $fieldName = self::RELATIONS_MAPPING[$recordModel->getModuleName()][$moduleName];

            if (empty($fieldName) || $recordModel->isEmpty($fieldName) || $record !== (int)$recordModel->get($fieldName)) {
                continue;
            }

            $relation[$recordModel->getId()] = $recordModel;
        }

        return $relation;
    }

    public function hasRelations(): bool
    {
        $adb = PearDatabase::getInstance();
        $sql = 'SELECT muid,muniqueid FROM vtiger_mailmanager_mailrecord
            LEFT JOIN vtiger_mailmanager_mailrel ON vtiger_mailmanager_mailrel.mailuid=muniqueid 
            LEFT JOIN its4you_emails ON its4you_emails.mail_manager_id=muid 
            LEFT JOIN vtiger_troubletickets ON vtiger_troubletickets.mail_manager_id=muid 
            LEFT JOIN vtiger_potential ON vtiger_potential.mail_manager_id=muid 
            WHERE muid=? AND (
                its4you_emails.mail_manager_id > 0 
                OR vtiger_troubletickets.mail_manager_id > 0 
                OR vtiger_potential.mail_manager_id > 0 
                OR vtiger_mailmanager_mailrel.emailid > 0
            )';
        $params = [$this->getUid()];
        $result = $adb->pquery($sql, $params);

        return $result && $adb->num_rows($result);
    }

    /**
     * @return bool
     * @throws AppException
     */
    public function hasAttachments(): bool
    {
        $data = $this->getAttachmentTable()->selectData(['muid'], ['muid' => $this->getUid()]);

        return !empty($data['muid']);
    }

    public function hasLookUps($folder)
    {
        $this->retrieveLookUps($this->getFrom()[0], $this->getTo()[0], $folder->isSentFolder());

        return !empty($this->getLookUps());
    }

    /**
     * Returns the available List of accessible modules for Mail Manager
     * @return Array
     */
    public function getCurrentUserMailManagerAllowedModules()
    {
        $moduleListForCreateRecordFromMail = ['Contacts', 'Accounts', 'Leads'];
        $modules = [];

        foreach ($moduleListForCreateRecordFromMail as $module) {
            if (MailManager::checkModuleWriteAccessForCurrentUser($module)) {
                $modules[] = $module;
            }
        }

        return $modules;
    }

    /**
     * Funtion used to build Web services query
     * @param String $module - Name of the module
     * @param String $text - Search String
     * @param String $type - Tyoe of fields Phone, Email etc
     * @return String
     */
    public function buildSearchQuery($module, $text, $type)
    {
        $describe = $this->ws_describe($module);
        // to check whether fields are accessible to current_user or not
        $labelFields = explode(',', $describe['labelFields']);

        //overwrite labelfields with field names instead of column names
        $currentUserModel = vglobal('current_user');
        $handler = vtws_getModuleHandlerFromName($module, $currentUserModel);
        $meta = $handler->getMeta();
        $fieldColumnMapping = $meta->getFieldColumnMapping();
        $columnFieldMapping = array_flip($fieldColumnMapping);

        foreach ($labelFields as $i => $columnname) {
            $labelFields[$i] = $columnFieldMapping[$columnname];
        }

        foreach ($labelFields as $fieldName) {
            foreach ($describe['fields'] as $describefield) {
                if ($describefield['name'] == $fieldName) {
                    $searchFields[] = $fieldName;
                    break;
                }
            }
        }

        $whereClause = '';
        foreach ($describe['fields'] as $field) {
            if (strcasecmp($type, $field['type']['name']) === 0) {
                $whereClause .= sprintf(" %s LIKE '%%%s%%' OR", $field['name'], $text);
            }
        }
        return sprintf("SELECT %s FROM %s WHERE %s;", implode(',', $searchFields), $module, rtrim($whereClause, 'OR'));
    }

    /**
     * Helper function to scan for relations
     */
    protected $wsDescribeCache = array();
    public function ws_describe($module) {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        if (!isset($this->wsDescribeCache[$module])) {
            $this->wsDescribeCache[$module] = vtws_describe( $module, $currentUserModel);
        }
        return $this->wsDescribeCache[$module];
    }

    /**
     * Function to lookup rel records(which supports emails only) of records
     * @param <string> $wsId
     * @return <array> $results
     */
    public function lookupRelModuleRecords($wsId) {
        $currentUser = vglobal('current_user');
        $results = array();
        /* Harcoded to fecth only project records. In future we should treat
         * below $relModules array as modules which support emails and related to
         * parent module.
         */

        /* [20180601 Softar TODO #1002] This causes and exception and total failure to fetch the related items
         if the Projects module is disabled or not in user permissions list
        $relModules = array('Project');
        */

        $relModules = [];
        $db = PearDatabase::getInstance();
        $wsObject = VtigerWebserviceObject::fromId($db, $wsId);
        $entityName = $wsObject->getEntityName();

        foreach ($relModules as $relModule) {
            $relation = Vtiger_Relation_Model::getInstanceByModuleName($entityName, $relModule);
            if(!$relation) {
                continue;
            }
            $relDescribe = $this->ws_describe($relModule);
            $labelFields = explode(',', $relDescribe['labelFields']);
            $relHandler = vtws_getModuleHandlerFromName($relModule, $currentUser);
            $relMeta = $relHandler->getMeta();
            //overwrite labelfields with field names instead of column names
            $fieldColumnMapping = $relMeta->getFieldColumnMapping();
            $columnFieldMapping = array_flip($fieldColumnMapping);

            foreach ($labelFields as $i => $columnname) {
                $labelFields[$i] = $columnFieldMapping[$columnname];
            }

            $sql = sprintf("SELECT %s FROM %s",  implode(',', $labelFields),$relModule);
            $relQResults = vtws_query_related($sql, $wsId, $relation->get('label'), $currentUser);

            foreach($relQResults as $qresult) {
                $labelValues = array();
                foreach($labelFields as $fieldname) {
                    if(isset($qresult[$fieldname])) $labelValues[] = $qresult[$fieldname];
                }
                $ids = vtws_getIdComponents($qresult['id']);
                $results[] = array( 'wsid' => $qresult['id'], 'id' => $ids[1], 'label' => implode(' ', $labelValues),'parent' => $wsId);
            }
        }
        return $results;
    }

    public function getImageUrl($fileName): string
    {
        return 'index.php?module=MailManager&view=Index&_operation=mail&_operationarg=attachment_dld&_muid=' . $this->getUid() . '&_atname=' . urlencode($fileName);
    }

    public function isAttachmentsAllowed()
    {
        if (null !== $this->attachmentsAllowed) {
            return $this->attachmentsAllowed;
        }

        $this->attachmentsAllowed = true;

        if (!empty($this->_attachments) || !empty($this->_inline_attachments)) {
            $data = $this->getAttachmentTable()->selectData(['muid'], ['muid' => $this->getUid()]);
            $this->attachmentsAllowed = !empty($data['muid']);
        }

        return $this->attachmentsAllowed;
    }
}