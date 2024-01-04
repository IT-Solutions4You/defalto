<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

require_once 'modules/com_vtiger_workflow/VTEntityCache.inc';
require_once 'modules/com_vtiger_workflow/VTWorkflowUtils.php';
require_once 'modules/com_vtiger_workflow/VTEmailRecipientsTemplate.inc';
require_once 'libraries/ToAscii/ToAscii.php';
require_once 'include/utils/VtlibUtils.php';

class VTEmailTask extends VTTask
{
    // Sending email takes more time, this should be handled via queue all the time.
    public $executeImmediately = false;

    public function doTask($entity)
    {
        global $current_user;
        $util = new VTWorkflowUtils();
        $admin = $util->adminUser();
        $module = $entity->getModuleName();
        $taskContents = Zend_Json::decode($this->getContents($entity));
        $relatedInfo = Zend_Json::decode($this->getRelatedInfo());
        $from_email = $taskContents['fromEmail'];
        $from_name = $taskContents['fromName'];
        $cc = $taskContents['ccEmail'];
        $bcc = $taskContents['bccEmail'];
        $replyTo = $taskContents['replyTo'];
        $subject = $taskContents['subject'];
        $content = $taskContents['content'];
        $entityCache = new VTEntityCache($admin);
        $et = new VTEmailRecipientsTemplate($this->recepient);
        $to_email = $et->render($entityCache, $entity->getId());

        $nameEmailArray = Vtiger_Functions::extractNameEmail($from_email);

        if ($nameEmailArray) {
            $from_name = $nameEmailArray['name'];
            $from_email = $nameEmailArray['email'];
        }

        if (!empty($to_email)) {
            //Storing the details of emails
            $entityIdDetails = vtws_getIdComponents($entity->getId());
            $entityId = $entityIdDetails[1];
            $moduleName = 'ITS4YouEmails';
            $userId = $current_user->id;

            $emailRecordModel = ITS4YouEmails_Record_Model::getCleanInstance($moduleName);
            $emailRecordModel->set('email_flag', 'SAVED');
            $emailRecordModel->set('related_to', $entityId);
            $emailRecordModel->set('assigned_user_id', $userId);
            $emailRecordModel->set('subject', strip_tags(decode_html($subject)));
            $emailRecordModel->set('body', $content);

            $emailRecordModel->set('workflow_id', $this->workflowId);
            $emailRecordModel->set('source', 'WF');

            $emailRecordModel->set('from_email', $from_email);
            $emailRecordModel->set('from_email_ids', 'email|' . $from_email . '|');

            $emailRecordModel->set('reply_email', $replyTo);
            $emailRecordModel->set('reply_email_ids', 'email|' . $replyTo . '|');

            $addressInfo = $this->getAddresses($to_email);

            if ($addressInfo) {
                $emailRecordModel->set('to_email', implode(',', $addressInfo['emails']));
                $emailRecordModel->set('to_email_ids', implode(',', $addressInfo['ids']));
            }

            $addressInfo = $this->getAddresses($cc);

            if ($addressInfo) {
                $emailRecordModel->set('cc_email', implode(',', $addressInfo['emails']));
                $emailRecordModel->set('cc_email_ids', implode(',', $addressInfo['ids']));
            }

            $addressInfo = $this->getAddresses($bcc);

            if ($addressInfo) {
                $emailRecordModel->set('bcc_email', implode(',', $addressInfo['emails']));
                $emailRecordModel->set('bcc_email_ids', implode(',', $addressInfo['ids']));
            }

            $emailRecordModel->save();

            //Block to get file details if comment is having attachment
            if (!empty($relatedInfo) && $relatedInfo['module'] == 'ModComments') {
                $modCommentsRecordId = $relatedInfo['id'];
                $modCommentsRecordModel = ModComments_Record_Model::getInstanceById($modCommentsRecordId);
                $modCommentsRecordModel->set('id', $modCommentsRecordId);
                $fileDetails = $modCommentsRecordModel->getFileDetails();
                //There can be multiple attachments for a single comment
                foreach ($fileDetails as $fileDetail) {
                    if (!empty($fileDetail)) {
                        $emailRecordModel->saveAttachmentRelation($fileDetail['attachmentsid']);
                    }
                }
            }

            $emailRecordId = $emailRecordModel->getId();
            $emailRecordModel = ITS4YouEmails_Record_Model::getInstanceById($emailRecordId);
            $emailRecordModel->set('from_name', $from_name);
            $emailRecordModel->set('reply_name', $from_name);
            $emailRecordModel->send();
        }

        $util->revertUser();
    }

    public function getAddresses($address)
    {
        $addresses = explode(',', trim(decode_html($address), ','));

        if (empty($addresses)) {
            return false;
        }

        $emails = [];
        $ids = [];

        foreach ($addresses as $address) {
            $name = '';
            $email = $address;

            if (stripos($address, '<')) {
                $addressInfo = explode('<', $address);
                $name = $addressInfo[0];
                $email = trim($addressInfo[1], '>');
            }

            if (!empty($email)) {
                $emails[] = $email;
                $ids[] = 'email|' . $email . '|' . $name;
            }
        }

        return [
            'emails' => $emails,
            'ids' => $ids,
        ];
    }

    /**
     * Function to get contents of this task
     * @param <Object> $entity
     * @return <Array> contents
     */
    public function getContents($entity, $entityCache = false)
    {
        if (!$this->contents) {
            global $adb, $current_user;
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
            $userObj = CRMEntity::getInstance('Users');
            $userObj->retrieveCurrentUserInfoFromFile($fromUserId);
            if ($this->fromEmail && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->fromEmail, 'assigned_user_id : (Users) ') !== false)) {
                /**From email merge tag have combination of name<email> format, So VTSimpleTemplate only
                 * merge the name not email part because of anchor pair. So we need to explode them and then
                 * assign them to VTSimpleTemplate to merge properly
                 **/
                if (strpos($this->fromEmail, '&lt;') && strpos($this->fromEmail, '&gt;')) {
                    list($fromNameTag, $fromEmailTag) = explode('&lt;', $this->fromEmail);
                    list($fromEmailTag, $rest) = explode('&gt;', $fromEmailTag);
                } elseif (strpos($this->fromEmail, '<') && strpos($this->fromEmail, '>')) {
                    list($fromNameTag, $fromEmailTag) = explode('<', $this->fromEmail);
                    list($fromEmailTag, $rest) = explode('>', $fromEmailTag);
                } else {
                    /**In this case user entered only email or name and email without anchor tags or mergetag without anchor tags etc..
                     * So we need to check if user given only email and if it is valid, then we will set it as valid email string and
                     * from name as current user name else we will treat it as from name and set from email as active admin's primary email
                     */
                    if (filter_var($this->fromEmail, FILTER_VALIDATE_EMAIL)) {
                        $fromEmailTag = $this->fromEmail;
                        $fromNameTag = $this->fromEmail;
                    } else {
                        $fromNameTag = $this->fromEmail;
                        if ($userObj) {
                            $fromEmailTag = $userObj->email1;
                        } else {
                            $fromEmailTag = $this->getDefaultFromEmail($this->fromEmail);
                        }
                    }
                }
                $et = new VTEmailRecipientsTemplate($fromEmailTag);
                $fromEmail = $et->render($entityCache, $entityId);

                $nt = new VTEmailRecipientsTemplate($fromNameTag);
                $fromName = $nt->render($entityCache, $entityId);
            } else {
                if ($userObj) {
                    $fromEmail = $userObj->email1;
                    $fromName = $userObj->userlabel;
                } else {
                    $fromEmail = $this->getDefaultFromEmail();
                    $userObj = Users::getActiveAdminUser();
                    $fromName = $userObj->userlabel;
                }
            }

            if (!$fromEmail) {
                $utils->revertUser();
                return false;
            }

            $taskContents['fromEmail'] = $fromEmail;
            $taskContents['fromName'] = $fromName;
            $defReplyTo = $this->getDefaultReplyToEmail();
            if ($this->replyTo && !($ownerEntity->getModuleName() === 'Groups' && strpos($this->replyTo, 'assigned_user_id : (Users) ') !== false)) {
                $et = new VTEmailRecipientsTemplate($this->replyTo);
                $replyToEmailDetails = $et->render($entityCache, $entityId);
                $replyToEmailDetails = trim($replyToEmailDetails, ',');
                //ReplyTo might be empty when record's email value is not set
                if (filter_var($replyToEmailDetails, FILTER_VALIDATE_EMAIL)) {
                    $replyToEmail = $replyToEmailDetails;
                } else {
                    $replyToEmail = $defReplyTo;
                }
            } else {
                $replyToEmail = $defReplyTo;
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

            $et = new VTEmailRecipientsTemplate($this->recepient);
            $toEmail = $et->render($entityCache, $entityId);

            $ecct = new VTEmailRecipientsTemplate($this->emailcc);
            $ccEmail = $ecct->render($entityCache, $entityId);

            $ebcct = new VTEmailRecipientsTemplate($this->emailbcc);
            $bccEmail = $ebcct->render($entityCache, $entityId);

            if (strlen(trim($toEmail, " \t\n,")) == 0 && strlen(trim($ccEmail, " \t\n,")) == 0 && strlen(trim($bccEmail, " \t\n,")) == 0) {
                $utils->revertUser();
                return false;
            }

            $taskContents['toEmail'] = $toEmail;
            $taskContents['ccEmail'] = $ccEmail;
            $taskContents['bccEmail'] = $bccEmail;

            $this->parseEmailTemplate($entity);
            //line item merge tags also should replace with proper values for subject
            $st = new VTSimpleTemplate($this->subject);
            $taskContents['subject'] = $st->render($entityCache, $entityId);

            $ct = new VTSimpleTemplate($this->content);
            $taskContents['content'] = $ct->render($entityCache, $entityId);
            //adding signatue to body
            // To handle existing workflows those having signature value as empty so assigning value
            // "Yes" for those workflows.
            if (empty($this->signature)) {
                $this->signature = 'Yes';
            }
            $content = $taskContents['content'];
            if ($this->signature == 'Yes') {
                $userObj = CRMEntity::getInstance('Users');
                $userObj->retrieveCurrentUserInfoFromFile($fromUserId);
                $content .= '<br><br>' . decode_html($userObj->signature);
            }
            $taskContents['content'] = $content;
            $this->contents = $taskContents;
            $utils->revertUser();
        }
        if (is_array($this->contents)) {
            $this->contents = Zend_Json::encode($this->contents);
        }
        return $this->contents;
    }

    public function getDefaultFromEmail($fromName = null)
    {
        $defaultFromEmail = null;
        $cachedOutgoingFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
        if (empty($cachedOutgoingFromEmail)) {
            global $adb;
            $sql = 'SELECT from_email_field FROM vtiger_systems WHERE server_type=?';
            $result = $adb->pquery($sql, array('email'));
            $outgoingFromEamil = $adb->query_result($result, 0, 'from_email_field');
            if (empty($outgoingFromEamil)) {
                if ($fromName) {
                    $userEmail = getUserEmailId('user_name', $fromName);
                    $defaultFromEmail = $userEmail;
                }
                if (!$defaultFromEmail) {
                    $activeAdminUser = Users::getActiveAdminUser();
                    $defaultFromEmail = $activeAdminUser->email1;
                }
            } else {
                $defaultFromEmail = $outgoingFromEamil;
                VTCacheUtils::setOutgoingMailFromEmailAddress($outgoingFromEamil);
            }
        } else {
            $defaultFromEmail = $cachedOutgoingFromEmail;
        }
        return $defaultFromEmail;
    }

    public function getDefaultReplyToEmail()
    {
        global $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
        $defaultReplyToEmail = null;
        if (!empty($HELPDESK_SUPPORT_EMAIL_REPLY_ID) && $HELPDESK_SUPPORT_EMAIL_REPLY_ID !== 'support@company-name.com') {
            $defaultReplyToEmail = $HELPDESK_SUPPORT_EMAIL_REPLY_ID;
        } else {
            $cachedOutgoingFromEmail = VTCacheUtils::getOutgoingMailFromEmailAddress();
            if (empty($cachedOutgoingFromEmail)) {
                global $adb;
                $sql = 'SELECT from_email_field FROM vtiger_systems WHERE server_type=?';
                $result = $adb->pquery($sql, array('email'));
                $outgoingFromEamil = $adb->query_result($result, 0, 'from_email_field');
                if (empty($outgoingFromEamil)) {
                    $activeAdmin = Users::getActiveAdminUser();
                    $defaultReplyToEmail = $activeAdmin->email1;
                } else {
                    $defaultReplyToEmail = $outgoingFromEamil;
                    VTCacheUtils::setOutgoingMailFromEmailAddress($outgoingFromEamil);
                }
            } else {
                $defaultReplyToEmail = $cachedOutgoingFromEmail;
            }
        }
        return $defaultReplyToEmail;
    }

    public function getFieldNames()
    {
        return array("subject", "content", "recepient", 'emailcc', 'emailbcc', 'fromEmail', 'pdf', 'pdfTemplateId', 'signature', 'replyTo');
    }

    /**
     * Function to parse merge tags related to email template selected
     * while creating email task
     * @param <object> $entity
     */
    public function parseEmailTemplate($entity)
    {
        $moduleName = $entity->getModuleName();
        list($wsId, $recordId) = explode('x', $entity->getId());
        $mergedHtml = getMergedDescription($this->content, $recordId, $moduleName);
        $this->content = $mergedHtml;
    }

}
