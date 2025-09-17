<?php
/************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc';
require_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/VtlibUtils.php';

class VTEmailTask extends VTTask
{
    public $cache;
    public $content;
    public $subject;
    public $contents;
    public $emailbcc;
    public $emailcc;
    public $executeImmediately = null;
    public $fromEmail;
    public $parent;
    public $recepient;
    public $replyTo;
    public $signature;
    public $smtp;
    public $template;
    public $template_field;
    public $template_language;

    /**
     * @param VTWorkflowEntity $entity
     *
     * @return void
     * @throws Exception
     */
    public function doTask($entity): void
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
        $entityId = vtws_getIdComponents($entity->getId())[1];
        $entityUserId = vtws_getIdComponents($entity->get('assigned_user_id'))[1];
        $moduleName = 'ITS4YouEmails';
        $userId = !empty($entityUserId) ? $entityUserId : $current_user->id;

        foreach ($to_emails as $email_data) {
            $to_email = $email_data['email'];
            $recipientModule = $email_data['module'];
            $recipientId = vtws_getIdComponents($email_data['id'])[1];

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

                if (!empty($replyTo)) {
                    $emailRecord->set('reply_email', $replyTo);
                    $emailRecord->set('reply_email_ids', 'email|' . $replyTo . '|');
                } else {
                    $emailRecord->set('reply_email', $from_email);
                    $emailRecord->set('reply_email_ids', $userId . '|' . $from_email . '|Users');
                }

                $emailRecord->set('to_email', $to_email);
                $emailRecord->set('to_email_ids', implode('|', [$recipientId, $to_email, $recipientModule]));

                if (!empty($cc_string)) {
                    $ccEmails = array_filter(explode(',', $cc_string));

                    $emailRecord->set('cc_email', implode(',', $ccEmails));
                    $emailRecord->set('cc_email_ids', implode(',', $this->getAddressIds($ccEmails)));
                }

                if (!empty($bcc_string)) {
                    $bccEmails = array_filter(explode(',', $bcc_string));

                    $emailRecord->set('bcc_email', implode(',', $bccEmails));
                    $emailRecord->set('bcc_email_ids', implode(',', $this->getAddressIds($bccEmails)));
                }

                $this->retrievePDFTemplate($emailRecord);

                $emailRecord->save();
                $emailRecord->savePDF();

                if (count($attachments) > 0) {
                    foreach ($attachments as $attachmentId) {
                        if ('Documents' === getSalesEntityType($attachmentId)) {
                            $emailRecord->saveDocumentRelation($attachmentId);
                        } else {
                            $emailRecord->saveAttachmentRelation($attachmentId);;
                        }
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

    public function getBCCEmailFields($values)
    {
        $selectedValues = $this->getRecipientsBCC();
        $values = array_merge($values, $this->getSpecialOptions());

        return $this->getEmailFields($values, $selectedValues);
    }

    public function getCCEmailFields($values)
    {
        $selectedValues = $this->getRecipientsCC();

        $values = array_merge($values, $this->getSpecialOptions());

        return $this->getEmailFields($values, $selectedValues);
    }

    public function isDynamicTemplateByField()
    {
        return true === vglobal('email_maker_dynamic_template_wf');
    }

    public function retrieveDynamicTemplate($entity): void
    {
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery('SELECT * FROM vtiger_emakertemplates WHERE templatename = ? AND deleted = 0 ', [
            $entity->data[$this->template_field],
        ]);
        $templateId = $adb->query_result($result, 0, 'templateid');

        if (!empty($templateId)) {
            $this->template = $templateId;
        }
    }

    /**
     * @throws Exception
     */
    public function getContents($entity, $entityCache = false)
    {
        if (!$this->contents) {
            $adb = PearDatabase::getInstance();
            $taskContents = [];
            $entityId = $entity->getId();

            $utils = new VTWorkflowUtils();
            $adminUser = $utils->adminUser();

            if (!$entityCache) {
                $entityCache = new VTEntityCache($adminUser);
            }

            $fromUserId = Users::getActiveAdminId();
            $entityOwnerId = $entity->get('assigned_user_id');

            if ($entityOwnerId) {
                $fromUserId = vtws_getIdComponents($entityOwnerId)[1];
            }

            $ownerEntity = $entityCache->forId($entityOwnerId);

            if ($ownerEntity->getModuleName() === 'Groups') {
                $recordId = vtws_getIdComponents($entityId)[1];
                $fromUserId = Vtiger_Util_Helper::getCreator($recordId);
            }

            if ($this->fromEmail && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->fromEmail, 'assigned_user_id : (Users) ') !== false)) {
                $et = new VTSimpleTemplate($this->fromEmail);
                $fromEmailDetails = $et->render($entityCache, $entityId);
                $fromEmailDecoded = html_entity_decode($fromEmailDetails);

                if (strpos($fromEmailDecoded, '<') && strpos($fromEmailDecoded, '>')) {
                    [$fromName, $fromEmail] = explode('<', $fromEmailDecoded);
                    [$fromEmail, $rest] = explode('>', $fromEmail);
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
                    $result = $adb->pquery('SELECT user_name, email1 FROM vtiger_users WHERE id = ?', [$fromUserId]);
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

            $toEmails = $this->getRecipientEmails($entityCache, $entityId, $this->getRecipients());

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

            if ($this->isDynamicTemplateByField() && !empty($this->template_field)) {
                $this->retrieveDynamicTemplate($entity);
            }

            $templateId = $this->template;
            $language = $this->template_language;

            $entityRecordId = vtws_getIdComponents($entityId)[1];
            $entityModuleName = getSalesEntityType($entityRecordId);

            if (!empty($templateId) && is_numeric($templateId)) {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstanceById(
                    $templateId,
                    $language,
                    $entityModuleName,
                    $entityRecordId
                );
            } else {
                $EMAILContentModel = EMAILMaker_EMAILContent_Model::getInstance(
                    $entityModuleName,
                    $entityRecordId,
                    vglobal('default_language')
                );
            }

            if ('custom_template' === $templateId) {
                $EMAILContentModel->setSubject($this->subject);
                $EMAILContentModel->setBody($this->content);
            }

            $EMAILContentModel->getContent(false);

            $taskContents['subject'] = (new VTSimpleTemplate($EMAILContentModel->getSubject()))->render($entityCache, $entityId);
            $taskContents['body'] = (new VTSimpleTemplate($EMAILContentModel->getBodyWithStyles()))->render($entityCache, $entityId);
            $taskContents['attachments'] = $EMAILContentModel->getAttachments();
            $taskContents['language'] = $language;
            $taskContents['luserid'] = isset($_SESSION['authenticated_user_id']) ? $_SESSION['authenticated_user_id'] : '';

            $modifiedById = $entity->get('modifiedby');
            $taskContents['muserid'] = vtws_getIdComponents($modifiedById)[1];

            $taskContents['signature'] = $this->signature;

            $this->contents = $taskContents;
            $utils->revertUser();
        }
        if (is_array($this->contents)) {
            $this->contents = Zend_Json::encode($this->contents);
        }

        return $this->contents;
    }

    public function getEmailFields(array $values, array $selectedValues): array
    {
        foreach (array_filter($selectedValues) as $value) {
            if (!empty($values[$value])) {
                continue;
            }

            $values[$value] = $value;
        }

        return $values;
    }

    public function getFieldNames()
    {
        return [
            'smtp',
            'fromEmail',
            'replyTo',
            'recepient',
            'emailcc',
            'emailbcc',
            'template',
            'template_language',
            'template_field',
            'signature',
            'subject',
            'content',
            'pdf_template',
            'pdf_template_language',
            'executeImmediately',
        ];
    }

    public function getFromEmailFields(array $values): array
    {
        $selectedValues = explode(',', $this->fromEmail);

        return $this->getEmailFields($values, $selectedValues);
    }

    public function getLanguages()
    {
        global $current_language;
        $langvalue = [];
        $currlang = [];

        $adb = PearDatabase::getInstance();
        $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?", ['1']);

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

    public function getDynamicTemplateFields(string $sourceModule): array
    {
        require_once 'vtlib/Vtiger/Field.php';

        $moduleModel = Vtiger_Module_Model::getInstance($sourceModule);
        $fields = Vtiger_Field::getAllForModule($moduleModel);
        $fieldsArray = [];

        foreach ($fields as $field) {
            if ($field->displaytype == 1) {
                $name = $field->name;
                $label = $field->label;
                $fieldsArray[$name] = $label;
            }
        }

        return $fieldsArray;
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
                'id'     => $parentRoleUserId,
                'module' => 'Users',
                'email'  => getUserEmail($parentRoleUserId),
            ];
        }

        return $parentUserEmails[$userId];
    }

    public function getRecipientEmails($entityCache, $entityId, $emails)
    {
        $this->cache = $entityCache;
        $this->parent = $this->cache->forId($entityId);

        $recipients = [];

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

    public function getRecipients()
    {
        return is_array($this->recepient) ? $this->recepient : explode(',', $this->recepient);
    }

    public function getRecipientsBCC()
    {
        return is_array($this->emailbcc) ? $this->emailbcc : explode(',', $this->emailbcc);
    }

    public function getRecipientsCC()
    {
        return is_array($this->emailcc) ? $this->emailcc : explode(',', $this->emailcc);
    }

    public function getReplyToEmailFields($values)
    {
        $selectedValues = explode(',', $this->replyTo);

        return $this->getEmailFields($values, $selectedValues);
    }

    public function getSMTPServers()
    {
        $records = [];

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
            '$parent_role_emails' => vtranslate('Parent Role Emails', 'EMAILMaker'),
        ];
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

    public function getToEmailFields($values)
    {
        $selectedValues = $this->getRecipients();

        $values = array_merge($values, $this->getSpecialOptions());

        return $this->getEmailFields($values, $selectedValues);
    }

    public function isSelectedFromEmailField($value)
    {
        $selectedValue = trim($this->fromEmail, ' ,');

        return $value === $selectedValue;
    }

    public function isSelectedReplyToEmailField($value)
    {
        $selectedValue = trim($this->replyTo, ' ,');

        return $value === $selectedValue;
    }

    public function isSelectedToEmailField($value)
    {
        $selectedValues = $this->getRecipients();

        return in_array($value, $selectedValues);
    }

    public function isSelectedCCEmailField($value)
    {
        $selectedValues = $this->getRecipientsCC();

        return in_array($value, $selectedValues);
    }

    public function isSelectedBCCEmailField($value)
    {
        $selectedValues = $this->getRecipientsBCC();

        return in_array($value, $selectedValues);
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
                } elseif ('$parent_role_emails' === $to_email) {
                    $userRecordId = vtws_getIdComponents($data['assigned_user_id'])[1];

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

            return [["id" => $to_email_id, "module" => $to_email_module, "email" => $to_email]];
        } else {
            [$full, $referenceField, $referenceModule, $fieldname] = $matches;

            $referenceId = $this->parent->get($referenceField);
            if ($referenceId == null) {
                return false;
            } else {
                if ($referenceField === 'contact_id') {
                    $referenceIdsList = explode(',', $referenceId);
                    $parts = [];
                    foreach ($referenceIdsList as $referenceId) {
                        $entity = $this->cache->forId($referenceId);
                        $to_email_module = $entity->getModuleName();
                        $data = $entity->getData();
                        if ($this->useValue($data, $fieldname)) {
                            $parts[] = ["id" => $referenceId, "module" => $to_email_module, "email" => $data[$fieldname]];
                        }
                    }

                    return $parts;
                }

                $entity = $this->cache->forId($referenceId);
                if ($referenceModule === "Users" && $entity->getModuleName() == "Groups") {
                    [$groupEntityId, $groupId] = vtws_getIdComponents($referenceId);

                    require_once('include/utils/GetGroupUsers.php');
                    $ggu = new GetGroupUsers();
                    $ggu->getAllUsersInGroup($groupId);

                    $users = $ggu->group_users;
                    $parts = [];
                    foreach ($users as $userId) {
                        $refId = vtws_getWebserviceEntityId("Users", $userId);
                        $entity = $this->cache->forId($refId);
                        $data = $entity->getData();
                        if ($this->useValue($data, $fieldname)) {
                            $parts[] = ["id" => $userId, "module" => "Users", "email" => $data[$fieldname]];
                        }
                    }

                    return $parts;
                } elseif ($entity->getModuleName() === $referenceModule) {
                    $data = $entity->getData();

                    if ($this->useValue($data, $fieldname)) {
                        return [["id" => $referenceId, "module" => $referenceModule, "email" => $data[$fieldname]]];
                    } else {
                        return false;
                    }
                }
            }
        }

        return false;
    }

    public function retrieveSpecialOptions($entity, $emails)
    {
        if (is_array($emails)) {
            $emails = implode(',', $emails);
        }
        if (strpos($emails, 'parent_role_emails')) {
            $userId = vtws_getIdComponents($entity->get('assigned_user_id'))[1];

            $parentEmails = $this->getParentEmails($userId);
            $parentEmailsAddresses = [];

            foreach ($parentEmails as $parentEmail) {
                $parentEmailsAddresses[] = $parentEmail['email'];
            }

            $emails = str_replace('$parent_role_emails', implode(',', $parentEmailsAddresses), $emails);
        }

        return $emails;
    }

    protected function useValue($data, $fieldname)
    {
        return !empty($data[$fieldname]);
    }

    public function retrievePDFTemplate($emailRecord)
    {
        $pdfTemplates = $this->getPDFTemplate();

        if (EMAILMaker_Module_Model::isPDFMakerInstalled() && !empty($pdfTemplates)) {
            $emailRecord->set('pdf_template_ids', $pdfTemplates);
            $emailRecord->set('pdf_template_language', $this->getPDFTemplateLanguage());
            $emailRecord->set('is_merge_templates', $this->getPDFTemplateMerge());
        } else {
            $emailRecord->set('pdf_template_ids', '');
            $emailRecord->set('pdf_template_language', '');
            $emailRecord->set('is_merge_templates', '');
        }
    }

    public function getPDFTemplateMerge()
    {
        return 'Yes' === $this->pdf_template_merge ? 1 : 0;
    }

    public function getPDFTemplateLanguage()
    {
        return $this->pdf_template_language;
    }

    public function getPDFMakerModel()
    {
        return Vtiger_Module_Model::getInstance('PDFMaker');
    }

    public function getPDFTemplate($returnArray = false)
    {
        if (!EMAILMaker_Module_Model::isPDFMakerInstalled()) {
            return $returnArray ? [] : '';
        }

        if (is_array($this->pdf_template)) {
            $templateIds = $this->pdf_template;
        } else {
            $templateIds = explode(';', $this->pdf_template);
        }

        $PDFMakerModel = $this->getPDFMakerModel();

        foreach ($templateIds as $templateId) {
            if ($PDFMakerModel->isTemplateDeleted($templateId)) {
                return $returnArray ? [] : '';
            }
        }

        if ($returnArray) {
            return $templateIds;
        }

        return implode(';', $templateIds);
    }

    /**
     * @param string $moduleName
     *
     * @return array
     */
    public function getPDFTemplates(string $moduleName): array
    {
        if (!EMAILMaker_Module_Model::isPDFMakerInstalled()) {
            return [];
        }

        $PDFMaker = PDFMaker_PDFMaker_Model::getInstance();
        $templates = $PDFMaker->GetAvailableTemplates($moduleName);
        $defaultTemplate = [];
        $fieldValue = [];

        if ($PDFMaker->CheckPermissions('DETAIL')) {
            foreach ($templates as $templateId => $templateInfo) {
                if (!$PDFMaker->isTemplateDeleted($templateId)) {
                    if (in_array($templateInfo['is_default'], ['1', '3'])) {
                        $defaultTemplate[$templateId] = $templateInfo['templatename'];
                    } else {
                        $fieldValue[$templateId] = $templateInfo['templatename'];
                    }
                }
            }

            if (php7_count($defaultTemplate) > 0) {
                $fieldValue = $defaultTemplate + $fieldValue;
            }
        }

        return $fieldValue;
    }
}