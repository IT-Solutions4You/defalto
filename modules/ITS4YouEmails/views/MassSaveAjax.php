<?php
/*********************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

class ITS4YouEmails_MassSaveAjax_View extends Vtiger_Footer_View
{

    /**
     * @var PearDatabase
     */
    public $db;
    public $documentsIds = array();
    public $existingAttachments = array();
    public $moduleName;
    /**
     * @var Users_Record_Model
     */
    public $currentUser;
    public $description = '';
    public $subject = '';
    public $smtp = null;
    public $mailAddresses = array();
    private $from_name;
    private $from_email;
    private $from_user;

    public function __construct()
    {
        parent::__construct();
        $this->exposeMethod('massSave');
    }

    public function checkPermission(Vtiger_Request $request)
    {
    }

    /**
     * @throws Exception
     */
    public function process(Vtiger_Request $request)
    {
        $mode = $request->getMode();
        if (!empty($mode)) {
            echo $this->invokeExposedMethod($mode, $request);
            return;
        }
    }

    /**
     * @throws Exception
     */
    public function getParentAttachments($parentEmailId)
    {
        if (!empty($parentEmailId)) {
            /** @var ITS4YouEmails_Record_Model $parentEmailModel */
            $parentEmailModel = Vtiger_Record_Model::getInstanceById($parentEmailId);

            return $parentEmailModel->getAttachments();
        }

        return [];
    }

    /**
     * @param Vtiger_Request $request
     * @throws Exception
     */
    public function massSave(Vtiger_Request $request)
    {
        $this->db = PearDatabase::getInstance();
        $this->moduleName = $request->getModule();
        $this->currentUser = Users_Record_Model::getCurrentUserModel();
        $sendEmails = array();

        $this->retrieveFromEmail($request);
        $this->retrieveDocuments($request);
        $this->retrieveDescription($request);
        $this->retrieveSubject($request);
        $this->retrieveMailAddresses($request);

        $sendEmails[] = array(
            'subject' => $this->subject,
            'description' => $this->description,
            'attachment_ids' => $this->getAttachmentDocuments(),
            'pdf_template_ids' => $request->get('pdf_template_ids'),
            'pdf_template_language' => $request->get('pdf_template_language'),
            'email_template_ids' => $request->get('email_template_ids'),
            'email_template_language' => $request->get('email_template_language'),
            'is_merge_templates' => $request->get('is_merge_templates'),
        );

        $emailSentId = $this->saveEmails($sendEmails, $this->mailAddresses);

        $emailsResult = [];
        $success = false;

        if ($emailSentId) {
            $emailsResult = ITS4YouEmails_Utils_Helper::sendEmails($emailSentId);
            $success = true;
        }

        $viewer = $this->getViewer($request);
        $viewer->assign('MODULE', $this->moduleName);
        $viewer->assign('QUALIFIED_MODULE', $request->getModule(false));
        $viewer->assign('SUCCESS', $success);
        $viewer->assign('TITLE', vtranslate($this->moduleName, $this->moduleName));
        $viewer->assign('RESULT', $emailsResult);
        $viewer->assign('RELATED_LOAD', !$request->isEmpty('related_load'));
        $viewer->view('ModalSendEmailResult.tpl', $this->moduleName);
    }

    /**
     * @throws Exception
     */
    public function retrieveFromEmail(Vtiger_Request $request)
    {
        list($type, $emailValue) = explode("::", addslashes($request->get('from_email')));

        if (!empty($emailValue)) {
            if ('s' === $type && class_exists('ITS4YouSMTP_Record_Model')) {
                $this->smtp = $emailValue;
            } elseif ('a' === $type) {
                $this->from_name = $emailValue;
                $this->from_email = ITS4YouEmails_Record_Model::getVtigerFromEmailField();
            } else {
                $userData = ITS4YouEmails_Record_Model::getUserDataByType($emailValue, $type);

                $this->from_user = $emailValue;
                $this->from_name = trim($userData['first_name'] . ' ' . $userData['last_name']);
                $this->from_email = $userData['email'];
            }
        }

        if (empty($this->from_email)) {
            $this->from_email = $this->currentUser->get('email1');
            $this->from_name = trim($this->currentUser->get('first_name') . ' ' . $this->currentUser->get('last_name'));
        }

        if (empty($this->from_user)) {
            $this->from_user = $this->currentUser->getId();
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveDocuments(Vtiger_Request $request)
    {
        $this->documentsIds = $request->get('documentids');
        $this->existingAttachments = $request->get('attachments', []);

        if (empty($this->documentsIds)) {
            $this->documentsIds = [];
        }

        if ($request->isEmpty('record')) {
            $this->retrieveDocumentsFromExistingAttachments();
        }

        $this->retrieveDocumentsFromFiles();
        $this->retrieveDocumentsFromSavedAttachments();
    }

    public function retrieveDocumentsFromExistingAttachments()
    {
        if (is_array($this->existingAttachments)) {
            foreach ($this->existingAttachments as $index => $existingAttachInfo) {
                $existingAttachInfo['tmp_name'] = $existingAttachInfo['name'];
                $this->existingAttachments[$index] = $existingAttachInfo;

                if (array_key_exists('docid', $existingAttachInfo)) {
                    $this->documentsIds[] = $existingAttachInfo['docid'];
                    unset($this->existingAttachments[$index]);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function retrieveDocumentsFromFiles()
    {
        $result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
        $_FILES = $result['file'];

        if (count((array)$_FILES) > 0) {
            foreach ($_FILES as $fileIndex => $files) {
                if (!empty($files['name']) && !empty($files['size'])) {
                    $files['original_name'] = vtlib_purify($_REQUEST[$fileIndex . '_hidden']);
                    $fileId = $this->uploadAndSaveFile($files);

                    if ($fileId) {
                        $this->documentsIds[] = $fileId;
                    }
                }
            }

            unset($_FILES);
        }
    }

    /**
     * @param array $fileDetails
     * @return false|int
     * @throws Exception
     */
    public function uploadAndSaveFile($fileDetails)
    {
        global $log, $upload_badext;

        $log->debug("Entering into uploadAndSaveFile($fileDetails) method.");

        $binFileName = !empty($fileDetails['original_name']) ? $fileDetails['original_name'] : $fileDetails['name'];
        $fileType = $fileDetails['type'];
        $attachmentId = (int)$this->db->getUniqueID('vtiger_crmentity');
        $binFile = sanitizeUploadFileName($binFileName, $upload_badext);
        $fileName = ltrim(basename(' ' . $binFile));
        $uploadFilePath = decideFilePath();
        $uploadStatus = move_uploaded_file($fileDetails['tmp_name'], $uploadFilePath . $attachmentId . '_' . $binFile);

        if ($uploadStatus) {
            $module = 'ITS4YouEmails';
            $attachment = ITS4YouEmails_Attachment_Model::getInstance();
            $attachment->setData([
                'id' => $attachmentId,
                'creator_id' => $this->currentUser->getId(),
                'owner_id' => $this->currentUser->getId(),
                'module' => $module,
                'description' => '',
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_path' => $uploadFilePath,
                'stored_name' => $fileName,
            ]);
            $attachment->save();

            return $attachmentId;
        } else {
            $log->debug('Skip the save attachment process.');

            return false;
        }
    }

    public function retrieveDocumentsFromSavedAttachments()
    {
        global $upload_badext;

        if (is_array($this->existingAttachments)) {
            foreach ($this->existingAttachments as $existingAttachInfo) {
                $fileName = $existingAttachInfo['attachment'];
                $filePath = $existingAttachInfo['path'];
                $fileId = $existingAttachInfo['fileid'];
                $oldFileName = $fileName;

                if (!empty ($fileId)) {
                    $oldFileName = $existingAttachInfo['fileid'] . '_' . $fileName;
                }

                $oldFilePath = $filePath . '/' . $oldFileName;

                $attachmentId = $this->db->getUniqueID('vtiger_crmentity');

                $binFile = sanitizeUploadFileName($fileName, $upload_badext);
                $fileName = ltrim(basename(' ' . $binFile));
                $filetype = $existingAttachInfo['type'];
                $upload_file_path = decideFilePath();
                $newFilePath = $upload_file_path . $attachmentId . '_' . $binFile;
                copy($oldFilePath, $newFilePath);

                $attachment = ITS4YouEmails_Attachment_Model::getInstance();
                $attachment->setData([
                    'id' => $attachmentId,
                    'creator_id' => $this->currentUser->getId(),
                    'owner_id' => $this->currentUser->getId(),
                    'module' => $this->moduleName,
                    'description' => '',
                    'file_name' => $fileName,
                    'file_type' => $filetype,
                    'file_path' => $upload_file_path,
                    'stored_name' => $fileName,
                ]);
                $attachment->save();

                $this->documentsIds[] = $attachmentId;
            }
        }
    }

    public function retrieveDescription(Vtiger_Request $request)
    {
        require_once 'libraries/ToAscii/ToAscii.php';
        require_once 'include/utils/VtlibUtils.php';

        $this->description = $request->getAll()['description'];
        $this->description = purifyHtmlEventAttributes($this->description, true);

        if ('Yes' === $request->get('signature')) {
            $signature = $this->currentUser->get('signature');

            if (!empty($signature)) {
                $this->description .= '<br><br>' . decode_html($signature);
            }
        }
    }

    public function retrieveSubject(Vtiger_Request $request)
    {
        $this->subject = $request->get('subject');
    }

    public function retrieveMailAddresses(Vtiger_Request $request)
    {
        $recordIds = $this->getRecordsListFromRequest($request);
        $this->mailAddresses = array();
        $mailTypes = array('to', 'cc', 'bcc');

        if (!is_array($recordIds)) {
            $getEmails = array($recordIds);
        } else {
            $getEmails = $recordIds;
        }

        if ($request->has('selected_sourceid')) {
            $selectedSourceId = $request->get('selected_sourceid');

            if ('0' == $selectedSourceId) {
                $getEmails = array('0');
            }
        }

        foreach ($getEmails as $sid) {
            foreach ($mailTypes as $mailType) {
                $n = sprintf('%s%semailinfo', $sid, $mailType);

                if ($request->has($n) && !$request->isEmpty($n)) {
                    $toMailNamesList = $request->get($n);

                    foreach ($toMailNamesList as $pidemail => $e) {
                        $this->mailAddresses[$mailType][$sid][$pidemail] = $e;
                    }
                }
            }
        }
    }

    public function getRecordsListFromRequest(Vtiger_Request $request)
    {
        $cvId = $request->get('viewname');
        $selectedIds = $request->get('selected_ids');
        $excludedIds = $request->get('excluded_ids');
        $excludedIds = is_string($excludedIds) ? Zend_Json::decode($excludedIds) : $excludedIds;

        if ($selectedIds == 'all') {
            $sourceRecord = $request->get('sourceRecord');
            $sourceModule = $request->get('sourceModule');

            if ($sourceRecord && $sourceModule) {
                /** @var Campaigns_Record_Model $sourceRecordModel */
                $sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);

                return $sourceRecordModel->getSelectedIdsList($request->get('parentModule'), $excludedIds);
            }

            $customViewModel = CustomView_Record_Model::getInstanceById($cvId);

            if ($customViewModel) {
                $searchKey = $request->get('search_key');
                $searchValue = $request->get('search_value');
                $operator = $request->get('operator');

                if (!empty($operator)) {
                    $customViewModel->set('operator', $operator);
                    $customViewModel->set('search_key', $searchKey);
                    $customViewModel->set('search_value', $searchValue);
                }

                return $customViewModel->getRecordIds($excludedIds);
            }

            $recordIds = [];

            foreach ($request->getAll() as $requestKey => $requestValue) {
                if (false !== strpos($requestKey, 'emailinfo')) {
                    foreach (json_decode($requestValue, true) as $emailKey => $email) {
                        $recordId = (int)explode('|', $emailKey)[0];

                        if ($recordId) {
                            array_push($recordIds, $recordId);
                        }
                    }
                }
            }
            if (!empty($recordIds)) {
                return array_diff($recordIds, (array)$excludedIds);
            }
        }

        if (!empty($selectedIds) && $selectedIds != 'all') {
            if (!empty($selectedIds) && count((array)$selectedIds) > 0) {
                if (!is_array($selectedIds)) {
                    $selectedIds = trim($selectedIds, '"');
                }
                return $selectedIds;
            }
        }

        return array();
    }

    public function getAttachmentDocuments()
    {
        $attachmentDocuments = '';

        if (count((array)$this->documentsIds)) {
            $attachmentDocuments = implode(',', $this->documentsIds);
        }

        return $attachmentDocuments;
    }

    public function saveEmails($sendEmails, $mailAddresses)
    {
        $sendingId = false;
        $emailTypes = ['cc', 'bcc'];
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $current_user_id = $currentUserModel->getId();

        if (!empty($sendEmails) && count((array)$sendEmails)) {
            $sendingId = ITS4YouEmails_Utils_Helper::getSendingId();

            foreach ($sendEmails as $sendEmail) {
                /** @var ITS4YouEmails_Record_Model $recordModelEmails */
                $recordModelEmails = ITS4YouEmails_Record_Model::getCleanInstance('ITS4YouEmails');
                $recordModelEmails->set('sending_id', $sendingId);
                $recordModelEmails->set('assigned_user_id', $current_user_id);
                $recordModelEmails->set('from_email', $this->from_email);
                $recordModelEmails->set('from_email_ids', $this->from_user . '|' . $this->from_email . '|Users');
                $recordModelEmails->set('from_name', $this->from_name);
                $recordModelEmails->set('subject', $sendEmail['subject']);
                $recordModelEmails->set('body', $sendEmail['description']);
                $recordModelEmails->set('email_flag', 'SAVED');
                $recordModelEmails->set('related_to', '');
                $recordModelEmails->set('email_template_ids', $sendEmail['email_template_ids']);
                $recordModelEmails->set('email_template_language', $sendEmail['email_template_language']);
                $recordModelEmails->set('pdf_template_ids', $sendEmail['pdf_template_ids']);
                $recordModelEmails->set('pdf_template_language', $sendEmail['pdf_template_language']);
                $recordModelEmails->set('is_merge_templates', $sendEmail['is_merge_templates']);

                if($this->smtp) {
                    $recordModelEmails->set('smtp', $this->smtp);
                } else {
                    $recordModelEmails->set('reply_email_ids', $this->from_user . '|' . $this->from_email . '|Users');
                    $recordModelEmails->set('reply_email', $this->from_email);
                }

                $mailCopies = array();
                $mailAddressIds = array();

                foreach ((array)$mailAddresses['to'] as $mailAddressId => $mailAddress) {
                    foreach ($emailTypes as $emailType) {
                        if (count((array)$mailAddresses[$emailType][$mailAddressId]) > 0) {
                            foreach ($mailAddresses[$emailType][$mailAddressId] as $mailCopyId => $mailCopy) {
                                if (is_array($mailCopy)) {
                                    foreach ($mailCopy as $ce) {
                                        if (!in_array($ce, (array)$mailCopies[$mailAddressId][$emailType])) {
                                            $mailCopies[$mailAddressId][$emailType][] = $ce;
                                        }
                                    }
                                } else {
                                    $mailCopies[$mailAddressId][$emailType][] = $mailCopy;
                                }

                                $mailAddressIds[$mailAddressId][$emailType][] = $mailCopyId;
                            }
                        }
                    }

                    if (count((array)$mailAddressIds[$mailAddressId]['cc']) > 0) {
                        $recordModelEmails->set('cc_email', implode(',', $recordModelEmails->getAddressesFromEmailIds($mailAddressIds[$mailAddressId]['cc'])));
                        $recordModelEmails->set('cc_email_ids', implode(',', $mailAddressIds[$mailAddressId]['cc']));
                    }

                    if (count((array)$mailAddressIds[$mailAddressId]['bcc']) > 0) {
                        $recordModelEmails->set('bcc_email', implode(',', $recordModelEmails->getAddressesFromEmailIds($mailAddressIds[$mailAddressId]['bcc'])));
                        $recordModelEmails->set('bcc_email_ids', implode(',', $mailAddressIds[$mailAddressId]['bcc']));
                    }

                    foreach ($mailAddress as $toEmailId => $toEmailAddress) {
                        $insertedEmails = array();

                        list($toRecord, $toEmail, $toModule) = explode('|', $toEmailId);

                        $cloneRecordModelEmails = clone $recordModelEmails;
                        $cloneRecordModelEmails->set('assigned_user_id', $current_user_id);
                        $cloneRecordModelEmails->set('attachment_ids', $sendEmail['attachment_ids']);
                        $cloneRecordModelEmails->set('related_to', $mailAddressId);
                        $cloneRecordModelEmails->set('to_email', $toEmail);
                        $cloneRecordModelEmails->set('to_email_ids', $toEmailId);
                        $cloneRecordModelEmails->clearRelatedToInfo();
                        $cloneRecordModelEmails->save();
                        $cloneRecordModelEmails->savePDF();

                        $attachmentIds = array_unique(array_filter(explode(',', $sendEmail['attachment_ids'])));

                        foreach ($attachmentIds as $attachmentId) {
                            if('Documents' === getSalesEntityType($attachmentId)) {
                                $cloneRecordModelEmails->saveDocumentRelation($attachmentId);
                            } else {
                                $cloneRecordModelEmails->saveAttachmentRelation($attachmentId);
                            }
                        }

                        if(!empty($mailAddressId)) {
                            $cloneRecordModelEmails->setEmailRelation($mailAddressId);
                        }

                        if (!empty($toRecord) && 'Users' !== $toModule) {
                            $insertedEmails[] = $toRecord;

                            if (is_numeric($toRecord)) {
                                $cloneRecordModelEmails->setEmailRelation($toRecord);
                            }
                        }

                        if (count((array)$mailAddressIds[$toRecord]['cc']) > 0) {
                            foreach ($mailAddressIds[$toRecord]['cc'] as $ccMailAddressId) {
                                if (!in_array($ccMailAddressId, $insertedEmails)) {
                                    $insertedEmails[] = $ccMailAddressId;

                                    if (is_numeric($ccMailAddressId)) {
                                        $cloneRecordModelEmails->setEmailRelation($ccMailAddressId);
                                    }
                                }
                            }
                        }

                        if (count((array)$mailAddressIds[$toRecord]['bcc']) > 0) {
                            foreach ($mailAddressIds[$toRecord]['bcc'] as $bccMailAddressId) {
                                if (!in_array($bccMailAddressId, $insertedEmails)) {
                                    $insertedEmails[] = $bccMailAddressId;

                                    if (is_numeric($bccMailAddressId)) {
                                        $cloneRecordModelEmails->setEmailRelation($bccMailAddressId);
                                    }
                                }
                            }
                        }

                        unset($cloneRecordModelEmails);
                        unset($insertedEmails);
                    }
                }
            }
        }

        return $sendingId;
    }

    public function checkUploadSize($documentIds = false)
    {
        $totalFileSize = 0;
        if (!empty ($_FILES)) {
            foreach ($_FILES as $fileDetails) {
                $totalFileSize = $totalFileSize + (int)$fileDetails['size'];
            }
        }
        if (!empty ($documentIds)) {
            $count = count((array)$documentIds);

            for ($i = 0; $i < $count; $i++) {
                $documentRecordModel = Vtiger_Record_Model::getInstanceById($documentIds[$i], 'Documents');
                $totalFileSize = $totalFileSize + (int)$documentRecordModel->get('filesize');
            }
        }

        global $upload_maxsize;

        if ($totalFileSize > $upload_maxsize) {
            return false;
        }

        return true;
    }

    public function getHeaderScripts(Vtiger_Request $request)
    {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $jsFileNames = array(
            "modules.EMAILMaker.resources.ckeditor.ckeditor",
            "libraries.jquery.ckeditor.adapters.jquery",
            'modules.Vtiger.resources.validator.BaseValidator',
            'modules.Vtiger.resources.validator.FieldValidator',
            "modules.EMAILMaker.resources.SendEmail",
            "modules.Emails.resources.EmailPreview",
            'modules.Vtiger.resources.Popup',
            'modules.Vtiger.resources.Vtiger',
            'libraries.jquery.jquery_windowmsg',
            'libraries.jquery.multiplefileupload.jquery_MultiFile'
        );

        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }
}