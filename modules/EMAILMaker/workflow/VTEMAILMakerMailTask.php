<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
require_once('modules/com_vtiger_workflow/VTTaskManager.inc');
require_once('modules/com_vtiger_workflow/VTEntityCache.inc');
require_once('modules/com_vtiger_workflow/VTWorkflowUtils.php');
require_once('modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc');
require_once('modules/Emails/mail.php');
require_once('modules/EMAILMaker/EMAILMaker.php');
require_once('modules/Emails/models/Mailer.php');

class VTEMAILMakerMailTask extends VTTask
{

    public $executeImmediately = false;
    public $template;
    public $template_language;
    public $contents;
    public $recepient;
    public $emailcc;
    public $emailbcc;
    public $fromEmail;
    public $template_field;
    public $parent;
    public $cache;
    public $signature;
    public $smtp;

    public function getFieldNames()
    {
        return array('recepient', 'emailcc', 'emailbcc', 'fromEmail', 'template', 'template_language', 'template_field', 'replyTo', 'signature', 'smtp');
    }

    /**
     * @param VTWorkflowEntity $entity
     * @return void
     * @throws Exception
     */
    public function doTask($entity)
    {
        $this->contents = null;

        $current_user = Users_Record_Model::getCurrentUserModel();
        $sendingId = ITS4YouEmails_Utils_Helper::getSendingId();

        $util = new VTWorkflowUtils();
        $admin = $util->adminUser();
        $module = $entity->getModuleName();

        $taskContents = Zend_Json::decode($this->getContents($entity));
        $from_email = $taskContents['fromEmail'];
		$from_name = $taskContents['fromName'];
        $cc_string = trim($taskContents['ccEmail'], ',');
        $bcc_string = trim($taskContents['bccEmail'], ',');
        $load_subject = $taskContents['subject'];
        $load_body = $taskContents['body'];
        $to_emails = $taskContents['toEmails'];
        $attachments = $taskContents['attachments'];
        $language = $taskContents['language'];
        $logged_user_id = $taskContents['luserid'];
        $modified_by_user_id = $taskContents['muserid'];
        $replyTo = $taskContents['replyTo'];
        $signature = $taskContents['signature'];
        $emailTemplateData = [
            'luserid' => $logged_user_id,
            'muserid' => $modified_by_user_id,
        ];

        list($entityTabId, $entityId) = vtws_getIdComponents($entity->getId());

        $moduleName = 'ITS4YouEmails';
        $userId = $current_user->id;

        foreach ($to_emails as $email_data) {

            $to_email = $email_data['email'];
            $recipientModule = $email_data['module'];

            list($recipientTabId, $recipientId) = explode('x', $email_data['id']);

            if (!empty($to_email)) {
                $subject = strip_tags(decode_html($load_subject));
                $body = decode_html($load_body);

                if ($signature && class_exists('ITS4YouEmails_Record_Model')) {
                    $body .= ITS4YouEmails_Record_Model::getSignature($userId);
                }

                if (empty($body) && empty($subject)) {
                    continue;
                }

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $emailRecord = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
                $emailRecord->set('sending_id', $sendingId);
	            $emailRecord->set('workflow_id', $this->workflowId);
				$emailRecord->set('source', 'WF');
                $emailRecord->set('assigned_user_id', $userId);
                $emailRecord->set('subject', $subject);
                $emailRecord->set('body', $body);
                $emailRecord->set('email_flag', 'SAVED');
                $emailRecord->set('related_to', $entityId);
                $emailRecord->set('email_template_ids', $this->template);
                $emailRecord->set('email_template_language', $this->template_language);
                $emailRecord->set('pdf_template_ids', '');
                $emailRecord->set('pdf_template_language', '');
                $emailRecord->set('is_merge_templates', '');
                $emailRecord->set('smtp', $this->smtp);

                if (!empty($from_email)) {
                    $emailRecord->set('from_email', $from_email);
                    $emailRecord->set('from_email_ids', $userId . '|' . $from_email . '|Users');
                }

                if(!empty($replyTo)) {
                    $emailRecord->set('reply_email', $replyTo);
                    $emailRecord->set('reply_email_ids', 'email|' . $replyTo . '|');
                } else {
                    $emailRecord->set('reply_email', $from_email);
                    $emailRecord->set('reply_email_ids', $userId . '|' . $from_email . '|Users');
                }

                $emailRecord->set('to_email', $to_email);
                $emailRecord->set('to_email_ids', implode('|', [$recipientId, $to_email, $recipientModule]));

                if(!empty($cc_string)) {
                    $ccEmails = array_filter(explode(',', $cc_string));

                    $emailRecord->set('cc_email', implode(',', $ccEmails));
                    $emailRecord->set('cc_email_ids', implode(',', $this->getAddressIds($ccEmails)));
                }

                if(!empty($bcc_string)) {
                    $bccEmails = array_filter(explode(',', $bcc_string));

                    $emailRecord->set('bcc_email', implode(',', $bccEmails));
                    $emailRecord->set('bcc_email_ids', implode(',', $this->getAddressIds($bccEmails)));
                }

                $emailRecord->save();

                if (count($attachments) > 0) {
                    foreach ($attachments as $attachment_id) {
                        $emailRecord->saveAttachmentRelation($attachment_id);
                    }
                }

                /** @var ITS4YouEmails_Record_Model $emailRecord */
                $emailRecord = ITS4YouEmails_Record_Model::getInstanceById($emailRecord->getId(), $moduleName);
                $emailRecord->set('email_template_data', $emailTemplateData);
	            $emailRecord->set('from_name', $from_name);
                $emailRecord->send();
            }
        }

        $util->revertUser();
    }

    public function getAddressIds($values)
    {
        $ids = [];

        foreach ($values as $value) {
            $ids[] = 'email|' . $value . '|';
        }

        return $ids;
    }

    public function getContents($entity, $entityCache = false)
    {

        if (!$this->contents) {
            global $adb;
            $taskContents = array();
            $entityId = $entity->getId();

            $utils = new VTWorkflowUtils();
            $adminUser = $utils->adminUser();
            if (!$entityCache) {
                $entityCache = new VTEntityCache($adminUser);
            }

            $fromUserId = Users::getActiveAdminId();
            $entityOwnerId = $entity->get('assigned_user_id');
            if ($entityOwnerId) {
                list ($moduleId, $fromUserId) = explode('x', $entityOwnerId);
            }

            $ownerEntity = $entityCache->forId($entityOwnerId);
            if ($ownerEntity->getModuleName() === 'Groups') {
                list($moduleId, $recordId) = vtws_getIdComponents($entityId);
                $fromUserId = Vtiger_Util_Helper::getCreator($recordId);
            }

            if ($this->fromEmail && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->fromEmail, 'assigned_user_id : (Users) ') !== false)) {
                $et = new VTSimpleTemplate($this->fromEmail);
                $fromEmailDetails = $et->render($entityCache, $entityId);
                $fromEmailDecoded = html_entity_decode($fromEmailDetails);

                if (strpos($fromEmailDecoded, '<') && strpos($fromEmailDecoded, '>')) {
                    list($fromName, $fromEmail) = explode('<', $fromEmailDecoded);
                    list($fromEmail, $rest) = explode('>', $fromEmail);
                } else {
                    $fromName = "";
                    $fromEmail = $fromEmailDetails;
                }

            } else {
                $userObj = CRMEntity::getInstance('Users');
                $userObj->retrieveCurrentUserInfoFromFile($fromUserId);
                if ($userObj) {
                    $fromEmail = $userObj->email1;
                    $fromName = $userObj->user_name;
                } else {
                    $result = $adb->pquery('SELECT user_name, email1 FROM vtiger_users WHERE id = ?', array($fromUserId));
                    $fromEmail = $adb->query_result($result, 0, 'email1');
                    $fromName = $adb->query_result($result, 0, 'user_name');
                }
            }

            if (!$fromEmail) {
                $utils->revertUser();
                return false;
            }

            $taskContents['fromEmail'] = $fromEmail;
            $taskContents['fromName'] = $fromName;

            if (!empty($this->replyTo)) {
                $et = new VTEmailRecipientsTemplate($this->replyTo);
                $replyToEmailDetails = $et->render($entityCache, $entityId);
                $replyToEmailDetails = trim($replyToEmailDetails, ',');

                if (filter_var($replyToEmailDetails, FILTER_VALIDATE_EMAIL)) {
                    $replyToEmail = $replyToEmailDetails;
                }
            }

            $taskContents['replyTo'] = $replyToEmail;

            if ($entity->getModuleName() === 'Events') {
                $contactId = $entity->get('contact_id');
                if ($contactId) {
                    $contactIds = '';
                    list($wsId, $recordId) = explode('x', $entityId);
                    $webserviceObject = VtigerWebserviceObject::fromName($adb, 'Contacts');

                    $result = $adb->pquery('SELECT contactid FROM vtiger_cntactivityrel WHERE activityid = ?', array($recordId));
                    $numOfRows = $adb->num_rows($result);
                    for ($i = 0; $i < $numOfRows; $i++) {
                        $contactIds .= vtws_getId($webserviceObject->getEntityId(), $adb->query_result($result, $i, 'contactid')) . ',';
                    }
                }
                $entity->set('contact_id', trim($contactIds, ','));
                $entityCache->cache[$entityId] = $entity;
            }

            $toEmails = $this->getRecipientEmails($entityCache, $entityId, $this->recepient);

            $toEmail = (new VTSimpleTemplate($this->recepient))->render($entityCache, $entityId);
            $toEmail = $this->retrieveSpecialOptions($entity, $toEmail);

            $ccEmail = (new VTSimpleTemplate($this->emailcc))->render($entityCache, $entityId);
            $ccEmail = $this->retrieveSpecialOptions($entity, $ccEmail);

            $bccEmail = (new VTSimpleTemplate($this->emailbcc))->render($entityCache, $entityId);
            $bccEmail = $this->retrieveSpecialOptions($entity, $bccEmail);

            if (strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
                $utils->revertUser();
                return false;
            }
            $taskContents['toEmail'] = $toEmail;
            $taskContents['toEmails'] = $toEmails;
            $taskContents['ccEmail'] = $ccEmail;
            $taskContents['bccEmail'] = $bccEmail;

            global $email_maker_dynamic_template_wf;
            if ($email_maker_dynamic_template_wf === true) {
                if (isset($this->template_field) && !empty($this->template_field)) {
                    $value = $entity->data[$this->template_field];
                    $resultEmailMaker = $adb->pquery('SELECT * FROM vtiger_emakertemplates WHERE templatename = ? AND deleted = 0 ', array($value));
                    $resultTemplateId = $adb->query_result($resultEmailMaker, 0, 'templateid');
                    $this->template = $resultTemplateId;
                }
            }

            $templateId = $this->template;
            $language = $this->template_language;

            $EMAILMaker = new EMAILMaker_EMAILMaker_Model();
            $emailTemplateResult = $EMAILMaker->GetDetailViewData($templateId, true);
            $emailTemplateBody = $emailTemplateResult['body'];

            if (vtlib_isModuleActive('ITS4YouStyles')) {
                $stylesModel = new ITS4YouStyles_Module_Model();
                $emailTemplateBody = $stylesModel->addStyles($emailTemplateBody, $templateId, "EMAILMaker");
            }

            $taskContents['subject'] = $emailTemplateResult['subject'];
            $taskContents['body'] = $emailTemplateBody;

            $attachments = $EMAILMaker->GetAttachmentsData($templateId);
            $taskContents['attachments'] = $attachments;
            $taskContents['language'] = $language;
            $taskContents['luserid'] = isset($_SESSION['authenticated_user_id']) ? $_SESSION['authenticated_user_id'] : '';

            $modifiedById = $entity->get('modifiedby');
            list ($modifiedByTabId, $modifiedByUserId) = explode('x', $modifiedById);
            $taskContents['muserid'] = $modifiedByUserId;

            $taskContents['signature'] = $this->signature;

            $this->contents = $taskContents;
            $utils->revertUser();
        }
        if (is_array($this->contents)) {
            $this->contents = Zend_Json::encode($this->contents);
        }
        return $this->contents;
    }

    public function getRecipientEmails($entityCache, $entityId, $to_emails)
    {
        $this->cache = $entityCache;
        $this->parent = $this->cache->forId($entityId);

        $recipients = array();
        $emails = explode(',', $to_emails);

        foreach ($emails as $email) {
            if (!empty($email)) {
                $recipientsData = $this->parseEmail($email, $entityCache, $entityId);

                if ($recipientsData) {
                    $recipients = array_merge($recipientsData, $recipients);
                }
            }
        }

        return $recipients;
    }

    private function parseEmail($to_email, $entityCache, $entityId)
    {
        preg_match('/\((\w+) : \(([_\w]+)\) (\w+)\)/', $to_email, $matches);

        if (count($matches) == 0) {
            $to_email_module = "";
            $to_email_id = "";
            $data = $this->parent->getData();

            if (substr($to_email, 0, 1) == '$') {

                $filename = substr($to_email, 1);

                if (isset($data[$filename])) {

                    if ($this->useValue($data, $filename)) {
                        $to_email_id = $this->parent->getId();
                        $to_email_module = $this->parent->getModuleName();
                        $to_email = $data[$filename];
                    }
                } elseif('$parent_role_emails' === $to_email) {
                    list($userModuleId, $userRecordId) = explode('x', $data['assigned_user_id']);

                    return $this->getParentEmails($userRecordId);
                } else {
                    $et = new VTSimpleTemplate($to_email);

                    if (method_exists($et, 'renderArray')) {
                        return $et->renderArray($entityCache, $entityId);
                    } else {
                        $to_email = $et->render($entityCache, $entityId);
                    }
                }
            }

            return array(array("id" => $to_email_id, "module" => $to_email_module, "email" => $to_email));
        } else {
            list($full, $referenceField, $referenceModule, $fieldname) = $matches;

            $referenceId = $this->parent->get($referenceField);
            if ($referenceId == null) {
                return false;
            } else {
                if ($referenceField === 'contact_id') {
                    $referenceIdsList = explode(',', $referenceId);
                    $parts = array();
                    foreach ($referenceIdsList as $referenceId) {
                        $entity = $this->cache->forId($referenceId);
                        $to_email_module = $entity->getModuleName();
                        $data = $entity->getData();
                        if ($this->useValue($data, $fieldname)) {

                            $parts[] = array("id" => $referenceId, "module" => $to_email_module, "email" => $data[$fieldname]);
                        }
                    }
                    return $parts;
                }

                $entity = $this->cache->forId($referenceId);
                if ($referenceModule === "Users" && $entity->getModuleName() == "Groups") {
                    list($groupEntityId, $groupId) = vtws_getIdComponents($referenceId);

                    require_once('include/utils/GetGroupUsers.php');
                    $ggu = new GetGroupUsers();
                    $ggu->getAllUsersInGroup($groupId);

                    $users = $ggu->group_users;
                    $parts = array();
                    foreach ($users as $userId) {
                        $refId = vtws_getWebserviceEntityId("Users", $userId);
                        $entity = $this->cache->forId($refId);
                        $data = $entity->getData();
                        if ($this->useValue($data, $fieldname)) {
                            $parts[] = array("id" => $userId, "module" => "Users", "email" => $data[$fieldname]);
                        }
                    }
                    return $parts;

                } elseif ($entity->getModuleName() === $referenceModule) {
                    $data = $entity->getData();

                    if ($this->useValue($data, $fieldname)) {
                        return array(array("id" => $referenceId, "module" => $referenceModule, "email" => $data[$fieldname]));
                    } else {
                        return false;
                    }
                }
            }
        }
        return false;
    }

    protected function useValue($data, $fieldname)
    {
        return !empty($data[$fieldname]);
    }

    public function getTemplates($selected_module)
    {
        $orderby = "templateid";
        $dir = "asc";
        $c = "<div class='row-fluid'>";

        $EMAILMaker = new EMAILMaker_EMAILMaker_Model();

        $request = new Vtiger_Request($_REQUEST, $_REQUEST);
        $templates_data = $EMAILMaker->GetListviewData($orderby, $dir, $selected_module, false, $request);

        foreach ($templates_data as $tdata) {

            $templateid = $tdata["templateid"];

            if (!empty($tdata["category"]) || isset($fieldvalue[$templateid])) {

                $fieldvalue[$tdata["category"]][$templateid] = $tdata["name"];
            } else {
                $fieldvalue[$templateid] = $tdata["name"];
            }
        }

        return $fieldvalue;
    }

    public function getLanguages()
    {
        global $current_language;
        $langvalue = array();
        $currlang = array();

        $adb = PearDatabase::getInstance();
        $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?", array('1'));

        while ($temp_row = $adb->fetchByAssoc($temp_res)) {
            $template_languages[$temp_row["prefix"]] = $temp_row["label"];

            if ($temp_row["prefix"] == $current_language) {
                $currlang[$temp_row["prefix"]] = $temp_row["label"];
            } else {
                $langvalue[$temp_row["prefix"]] = $temp_row["label"];
            }
        }
        $langvalue = (array)$currlang + (array)$langvalue;

        return $langvalue;
    }

    public function getModuleFields($sourceModule)
    {
        global $email_maker_dynamic_template_wf;

        if ($email_maker_dynamic_template_wf !== true) {
            $return = false;
        } else {
            require_once 'vtlib/Vtiger/Field.php';
            $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
            $fields = Vtiger_Field::getAllForModule($moduleModel);
            $fieldsArray = array();

            foreach ($fields as $field) {
                if ($field->displaytype == 1) {
                    $name = $field->name;
                    $label = $field->label;
                    $fieldsArray[$name] = $label;
                }
            }

            $return = $fieldsArray;
        }

        return $return;
    }

    public function getSMTPServers()
    {
        $records = array();

        if (vtlib_isModuleActive('ITS4YouSMTP')) {
            /** @var ITS4YouSMTP_Module_Model $moduleModel */
            $moduleModel = Vtiger_Module_Model::getInstance('ITS4YouSMTP');
            $records = $moduleModel->getRecords();
        }

        return $records;
    }

    public function getSpecialOptions()
    {
        return [
            ',$parent_role_emails' => vtranslate('Parent Role Emails', 'EMAILMaker'),
        ];
    }

    public function retrieveSpecialOptions($entity, $emails)
    {
        if (strpos($emails, 'parent_role_emails')) {
            list($moduleId, $userId) = explode('x', $entity->get('assigned_user_id'));

            $parentEmails = $this->getParentEmails($userId);
            $parentEmailsAddresses = [];

            foreach ($parentEmails as $parentEmail) {
                $parentEmailsAddresses[] = $parentEmail['email'];
            }

            $emails = str_replace('$parent_role_emails', implode(',', $parentEmailsAddresses), $emails);
        }

        return $emails;
    }

    public function getParentEmails($userId)
    {
        global $parentUserEmails;

        if (!empty($parentUserEmails[$userId])) {
            return $parentUserEmails[$userId];
        }

        $userRecordModel = Users_Record_Model::getInstanceById($userId, 'Users');
        $roleId = $userRecordModel->get('roleid');
        $parentRoles = getParentRole($roleId);
        $parentRoleId = $parentRoles[max(array_keys($parentRoles))];
        $parentRoleUsers = getRoleUsers($parentRoleId);
        $parentUserEmails[$userId] = [];

        foreach ($parentRoleUsers as $parentRoleUserId => $parentRoleUserName) {
            $parentUserEmails[$userId][] = [
                'id' => $parentRoleUserId,
                'module' => 'Users',
                'email' => getUserEmail($parentRoleUserId),
            ];
        }

        return $parentUserEmails[$userId];
    }
}