<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once('data/CRMEntity.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/ModComments/ModComments.php');
require_once('modules/Users/Users.php');
require_once('modules/Documents/Documents.php');
require_once('modules/Leads/Leads.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Accounts/Accounts.php');

/**
 * Mail Scanner Action
 */
class Settings_MailConverter_MailScannerAction_Handler
{
    // actionid for this instance
    public $actionid = false;
    // scanner to which this action is associated
    public $scannerid = false;
    // type of mailscanner action
    public $actiontype = false;
    // text representation of action
    public $actiontext = false;
    // target module for action
    public $module = false;
    // lookup information while taking action
    public $lookup = false;

    // Storage folder to use
    public $STORAGE_FOLDER = 'storage/mailscanner/';

    public $recordSource = 'MAIL SCANNER';

    /** DEBUG functionality */
    public $debug = false;
    public $moduleName = 'MailConverter';

    public function log($message)
    {
        global $log;

        if ($log && $this->debug) {
            $log->debug($message);
        } elseif ($this->debug) {
            echo "$message\n";
        }
    }

    /**
     * Constructor.
     */
    function __construct($foractionid)
    {
        $this->initialize($foractionid);
    }

    /**
     * Initialize this instance.
     */
    function initialize($foractionid)
    {
        global $adb;
        $result = $adb->pquery("SELECT * FROM vtiger_mailscanner_actions WHERE actionid=? ORDER BY sequence", [$foractionid]);

        if ($adb->num_rows($result)) {
            $this->actionid = $adb->query_result($result, 0, 'actionid');
            $this->scannerid = $adb->query_result($result, 0, 'scannerid');
            $this->actiontype = $adb->query_result($result, 0, 'actiontype');
            $this->module = $adb->query_result($result, 0, 'module');
            $this->lookup = $adb->query_result($result, 0, 'lookup');
            $this->actiontext = "$this->actiontype,$this->module,$this->lookup";
        }
    }

    /**
     * Create/Update the information of Action into database.
     */
    public function update($ruleid, $actiontext)
    {
        global $adb;

        $inputparts = explode(',', $actiontext);
        $this->actiontype = $inputparts[0]; // LINK, CREATE
        $this->module = $inputparts[1]; // Module name
        $this->lookup = $inputparts[2]; // FROM, TO

        $this->actiontext = $actiontext;

        if ($this->actionid) {
            $adb->pquery(
                'UPDATE vtiger_mailscanner_actions SET scannerid=?, actiontype=?, module=?, lookup=? WHERE actionid=?',
                [$this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->actionid],
            );
        } else {
            $this->sequence = $this->__nextsequence();
            $adb->pquery(
                'INSERT INTO vtiger_mailscanner_actions(scannerid, actiontype, module, lookup, sequence) VALUES(?,?,?,?,?)',
                [$this->scannerid, $this->actiontype, $this->module, $this->lookup, $this->sequence],
            );
            $this->actionid = $adb->database->Insert_ID();
        }

        $checkMapping = $adb->pquery('SELECT COUNT(*) AS ruleaction_count FROM vtiger_mailscanner_ruleactions WHERE ruleid=? AND actionid=?', [$ruleid, $this->actionid]);

        if ($adb->num_rows($checkMapping) && !$adb->query_result($checkMapping, 0, 'ruleaction_count')) {
            $adb->pquery(
                'INSERT INTO vtiger_mailscanner_ruleactions(ruleid, actionid) VALUES(?,?)',
                [$ruleid, $this->actionid],
            );
        }
    }

    /**
     * Delete the actions from tables.
     */
    function delete()
    {
        global $adb;
        if ($this->actionid) {
            $adb->pquery("DELETE FROM vtiger_mailscanner_actions WHERE actionid=?", [$this->actionid]);
            $adb->pquery("DELETE FROM vtiger_mailscanner_ruleactions WHERE actionid=?", [$this->actionid]);
        }
    }

    /**
     * Get next sequence of Action to use.
     */
    function __nextsequence()
    {
        global $adb;
        $seqres = $adb->pquery("SELECT max(sequence) AS max_sequence FROM vtiger_mailscanner_actions", []);
        $maxsequence = 0;
        if ($adb->num_rows($seqres)) {
            $maxsequence = $adb->query_result($seqres, 0, 'max_sequence');
        }
        ++$maxsequence;

        return $maxsequence;
    }

    /**
     * Apply the action on the mail record.
     * @throws Exception
     */
    public function apply($mailScanner, $mailRecord, $mailScannerRule, $matchResult)
    {
        $returnId = false;

        if ($this->actiontype == 'CREATE') {
            if ($this->module == 'HelpDesk') {
                $returnId = $this->createTicket($mailScanner, $mailRecord, $mailScannerRule);
            } elseif ($this->module == 'Contacts') {
                $returnId = $this->createContact($mailScanner, $mailRecord, $mailScannerRule);
            } elseif ($this->module == 'Leads') {
                $returnId = $this->createLead($mailScanner, $mailRecord, $mailScannerRule);
            } elseif ($this->module == 'Accounts') {
                $returnId = $this->createAccount($mailScanner, $mailRecord, $mailScannerRule);
            }
        } elseif ($this->actiontype == 'LINK') {
            $returnId = $this->linkToRecord($mailScanner, $mailRecord);
        } elseif ($this->actiontype == 'UPDATE') {
            if ($this->module == 'HelpDesk') {
                $returnId = $this->updateTicket($mailScanner, $mailRecord, $mailScannerRule->hasRegexMatch($matchResult), $mailScannerRule);
            }
        } elseif (!empty($this->actiontype)) {
            $action = $this;

            $params = [$action, $mailScanner, $mailRecord, $mailScannerRule];
            $adb = PearDatabase::getInstance();
            $emm = new Settings_MailConverter_MailScannerEntityMethodManager_Handler($adb);

            $returnId = $emm->executeMethod($this->module, $this->actiontype, $params);
        }

        return $returnId;
    }

    /**
     * Update ticket action.
     * @throws Exception
     */
    public function updateTicket(Settings_MailConverter_MailScanner_Handler $mailScanner, Settings_MailConverter_MailRecord_Handler $mailRecord, $regexMatchInfo, $mailScannerRule)
    {
        global $adb;
        $returnId = false;
        $useSubject = false;

        if ($this->lookup == 'SUBJECT') {
            // If regex match was performed on subject use the matched group
            // to lookup the ticket record
            if ($regexMatchInfo) {
                $useSubject = $regexMatchInfo['matches'];
            } else {
                $useSubject = $mailRecord->_subject;
            }

            // Get the ticket record that was created by SENDER earlier
            $fromEmail = $mailRecord->getFrom()[0];
            $linkFocus = $mailScanner->getTicketRecord($useSubject, $fromEmail);
            $commentedBy = $mailScanner->getLookupContact($fromEmail);

            if (!$commentedBy) {
                $commentedBy = $mailScanner->getLookupAccount($fromEmail);
            }

            // If matching ticket is found, update comment, attach email
            if ($linkFocus) {
                $mailRecord->setDocumentRelationIds((int)$linkFocus->id);
                $relationIds = array_filter([$linkFocus->column_fields['parent_id'], $linkFocus->column_fields['contact_id']]);
                $returnId = $this->createNewEmail($mailRecord, $linkFocus, $relationIds);

                $commentFocus = new ModComments();
                $commentFocus->column_fields['commentcontent'] = Core_CKEditor_UIType::transformEditViewDisplayValue($mailRecord->getBody(false));
                $commentFocus->column_fields['related_to'] = $linkFocus->id;
                $commentFocus->column_fields['assigned_user_id'] = $mailScannerRule->assigned_to;
                $commentFocus->column_fields['source'] = $this->recordSource;

                if ($commentedBy) {
                    $commentFocus->column_fields['customer'] = $commentedBy;
                    $commentFocus->column_fields['from_mailconverter'] = 1;
                } else {
                    $commentFocus->column_fields['userid'] = $mailScannerRule->assigned_to;
                }

                $commentFocus->column_fields['mail_attachment_ids'] = $mailRecord->getAttachmentsIds();
                $commentFocus->save('ModComments');

                // Set the ticket status to Open if its Closed
                $adb->pquery("UPDATE vtiger_troubletickets set ticketstatus=? WHERE ticketid=? AND ticketstatus=?", ['Open', $linkFocus->id, 'Closed']);
            }
        }

        return $returnId;
    }

    /**
     * Create ticket action.
     */
    public function createContact($mailScanner, $mailRecord, $mailScannerRule)
    {
        if ($mailScanner->getLookupContact($mailRecord->_from[0])) {
            $this->lookup = 'FROM';

            return $this->linkToRecord($mailScanner, $mailRecord);
        }
        $name = $this->getName($mailRecord);
        $email = $mailRecord->_from[0];
        $description = $mailRecord->getBodyText();

        $contact = Vtiger_Record_Model::getCleanInstance('Contacts');
        $this->setDefaultValue('Contacts', $contact);
        $contact->set('firstname', $name[0]);
        $contact->set('lastname', $name[1]);
        $contact->set('email', $email);
        $contact->set('assigned_user_id', $mailScannerRule->assigned_to);
        $contact->set('description', $description);
        $contact->set('source', $this->recordSource);

        try {
            $contact->save();
            $mailRecord->setDocumentRelationIds($contact->getId());
            $this->saveAttachments($mailRecord, $contact);

            return $contact->getId();
        } catch (Exception $e) {
            //TODO - Review
            return false;
        }
    }

    /**
     * Create Lead action.
     * @throws Exception
     */
    public function createLead($mailScanner, $mailRecord, $mailScannerRule)
    {
        $fromEmail = $mailRecord->getFrom()[0];

        if ($mailScanner->LookupLead($fromEmail)) {
            $this->lookup = 'FROM';

            return $this->linkToRecord($mailScanner, $mailRecord);
        }

        $name = $this->getName($mailRecord);
        $email = $fromEmail;
        $description = $mailRecord->getBodyText();

        $lead = Vtiger_Record_Model::getCleanInstance('Leads');
        $this->setDefaultValue('Leads', $lead);
        $lead->set('firstname', $name[0]);
        $lead->set('lastname', $name[1]);
        $lead->set('email', $email);
        $lead->set('assigned_user_id', $mailScannerRule->assigned_to);
        $lead->set('description', $description);
        $lead->set('source', $this->recordSource);

        try {
            $lead->save();
            $mailRecord->setDocumentRelationIds($lead->getId());
            $this->saveAttachments($mailRecord, $lead);

            return $lead->getId();
        } catch (Exception $e) {
            //TODO - Review
            return false;
        }
    }

    /**
     * Create Account action.
     */
    public function createAccount($mailScanner, $mailRecord, $mailScannerRule)
    {
        if ($mailScanner->getLookupAccount($mailRecord->_from[0])) {
            $this->lookup = 'FROM';

            return $this->linkToRecord($mailScanner, $mailRecord);
        }

        $name = $this->getName($mailRecord);
        $email = $mailRecord->_from[0];
        $description = $mailRecord->getBodyText();
        $account = Vtiger_Record_Model::getCleanInstance('Accounts');
        $this->setDefaultValue('Accounts', $account);
        $account->set('accountname', $name[0] . ' ' . $name[1]);
        $account->set('email1', $email);
        $account->set('assigned_user_id', $mailScannerRule->assigned_to);
        $account->set('description', $description);
        $account->set('source', $this->recordSource);

        try {
            $account->save();
            $mailRecord->setDocumentRelationIds($account->getId());
            $this->saveAttachments($mailRecord, $account);

            return $account->getId();
        } catch (Exception $e) {
            //TODO - Review
            return false;
        }
    }

    /**
     * Create ticket action.
     */
    public function createTicket($mailScanner, $mailRecord, $mailScannerRule)
    {
        // Prepare data to create trouble ticket
        $useTitle = $mailRecord->getSubject();
        $description = $mailRecord->getBody(false);

        // There will be only on FROM address to email, so pick the first one
        $fromEmail = $mailRecord->getFrom()[0];
        $contactLinkToId = $mailScanner->getLookupContact($fromEmail);
        $linkToId = null;

        if (empty($contactLinkToId)) {
            $contactLinkToId = $this->createContact($mailScanner, $mailRecord, $mailScannerRule);
        }

        if (empty($contactLinkToId)) {
            $linkToId = $mailScanner->getAccountId($contactLinkToId);
        }

        if (empty($linkToId)) {
            $linkToId = $mailScanner->getLookupAccount($fromEmail);
        }

        // Create trouble ticket record
        $recordModel = Vtiger_Record_Model::getCleanInstance('HelpDesk');
        $this->setDefaultValue('HelpDesk', $recordModel);

        if ($recordModel->isEmpty('ticketstatus') || $recordModel->get('ticketstatus') == '?????') {
            $recordModel->set('ticketstatus', 'Open');
        }

        $recordModel->set('ticket_title', $useTitle);
        $recordModel->set('description', $description);
        $recordModel->set('assigned_user_id', $mailScannerRule->assigned_to);

        if ($contactLinkToId) {
            $recordModel->set('contact_id', $contactLinkToId);
        }

        if ($linkToId) {
            $recordModel->set('parent_id', $linkToId);
        }

        $recordModel->set('source', $this->recordSource);
        $recordModel->set('mail_message_key', $mailRecord->generateUniqueKeyFromEmail());

        try {
            $recordModel->save();
            $ticketId = $recordModel->getId();
            $mailRecord->setDocumentRelationIds($ticketId);
            $this->createNewEmail($mailRecord, $recordModel->getEntity(), array_filter([$contactLinkToId, $linkToId]));

            return $ticketId;
        } catch (Exception $e) {
            //TODO - Review
            return false;
        }
    }

    /**
     * Function to link email record to contact/account/lead
     * record if exists with same email id
     *
     * @param object $mailScanner
     * @param object $mailRecord
     *
     * @throws Exception
     */
    function linkMail(object $mailScanner, object $mailRecord, $relatedTo): void
    {
        $fromEmail = $mailRecord->_from[0];
        $linkFocus = $mailScanner->getContactRecord($fromEmail, $relatedTo);

        if (!$linkFocus) {
            $linkFocus = $mailScanner->getAccountRecord($fromEmail, $relatedTo);
        }

        if ($linkFocus) {
            $this->createNewEmail($mailRecord, $linkFocus, [$relatedTo]);
        }
    }

    /**
     * Add email to CRM record like Contacts/Accounts
     * @throws Exception
     */
    public function linkToRecord($mailScanner, $mailRecord)
    {
        $linkFocus = false;
        $useEmail = false;

        if ($this->lookup == 'FROM') {
            $useEmail = $mailRecord->_from;
        } elseif ($this->lookup == 'TO') {
            $useEmail = $mailRecord->_to;
        }

        if ($this->module == 'Contacts') {
            foreach ($useEmail as $email) {
                $linkFocus = $mailScanner->getContactRecord($email);

                if ($linkFocus) {
                    break;
                }
            }
        } elseif ($this->module == 'Accounts') {
            foreach ($useEmail as $email) {
                $linkFocus = $mailScanner->getAccountRecord($email);

                if ($linkFocus) {
                    break;
                }
            }
        } elseif ($this->module == 'Leads') {
            foreach ($useEmail as $email) {
                $linkFocus = $mailScanner->getLeadRecord($email);

                if ($linkFocus) {
                    break;
                }
            }
        }

        $returnId = false;

        if ($linkFocus) {
            $returnId = $this->createNewEmail($mailRecord, $linkFocus);
        }

        return $returnId;
    }

    public function setDefaultValue($module, $moduleObj): void
    {
        $moduleInstance = Vtiger_Module_Model::getInstance($module);
        $fieldInstances = Vtiger_Field_Model::getAllForModule($moduleInstance);

        foreach ($fieldInstances as $blockInstance) {
            foreach ($blockInstance as $fieldInstance) {
                $fieldName = $fieldInstance->getName();
                $defaultValue = $fieldInstance->getDefaultFieldValue();

                if ($defaultValue) {
                    $moduleObj->set($fieldName, decode_html($defaultValue));
                }

                if ($fieldInstance->isMandatory() && !$defaultValue) {
                    $moduleObj->set($fieldName, Vtiger_Util_Helper::getDefaultMandatoryValue($fieldInstance->getFieldDataType()));
                }
            }
        }
    }

    /**
     * Function to get Mail Sender's Name
     *
     * @param Settings_MailConverter_MailRecord_Handler|Object $mailRecord
     *
     * @return Array containing First Name and Last Name
     */
    public function getName($mailRecord)
    {
        $name = $mailRecord->getFromName();

        if (!empty($name)) {
            $nameParts = explode(' ', $name);

            if (php7_count($nameParts) > 1) {
                $firstName = $nameParts[0];
                unset($nameParts[0]);
                $lastName = implode(' ', $nameParts);
            } else {
                $firstName = '';
                $lastName = $nameParts[0];
            }
        } else {
            $firstName = '';
            $lastName = $mailRecord->_from[0];
        }

        return [$firstName, $lastName];
    }

    /**
     * Create new Email record (and link to given record) including attachments
     *
     * @param MailManager_Message_Model|Settings_MailConverter_MailRecord_Handler|object $mailRecord
     * @param CRMEntity                                                                  $linkFocus
     * @param array                                                                      $relationIds
     *
     * @return Integer
     * @throws Exception
     * @global PearDataBase                                                              $db
     * @global Users                                                                     $current_user
     */
    public function createNewEmail(object $mailRecord, CRMEntity $linkFocus, array $relationIds = [])
    {
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        $handler = vtws_getModuleHandlerFromName('ITS4YouEmails', $currentUserModel);
        $meta = $handler->getMeta();

        if (!$meta->hasWriteAccess()) {
            return false;
        }

        $recordModel = Vtiger_Record_Model::getCleanInstance('ITS4YouEmails');
        $recordModel->set('subject', $mailRecord->getSubject());

        if (!empty($linkFocus->id)) {
            $recordModel->set('related_to', $linkFocus->id);

            foreach ($relationIds as $relationId) {
                if (empty($relationId)) {
                    continue;
                }

                $relationModule = getSalesEntityType($relationId);
                $relationField = MailManager_Message_Model::RELATIONS_MAPPING['ITS4YouEmails'][$relationModule];

                if (!empty($relationField)) {
                    $recordModel->set($relationField, $relationId);
                }
            }
        }

        //To display inline image in body of email when we go to detail view of email from email related tab of related record.
        $mailRecord->replaceInlineAttachments();

        $recordModel->set('body', $mailRecord->getBody(false));
        $recordModel->set('assigned_user_id', $this->getAssignedToId($linkFocus));
        $recordModel->set('email_flag', $this->recordSource);

        $from = $mailRecord->getFrom()[0];
        $to = implode(',', $mailRecord->getTo());
        $cc = (!empty($mailRecord->getCC())) ? implode(',', $mailRecord->getCC()) : '';
        $bcc = (!empty($mailRecord->getBCC())) ? implode(',', $mailRecord->getBCC()) : '';

        //emails field were restructured and to,bcc and cc field are JSON arrays
        $recordModel->set('from_email', $from);
        $recordModel->set('to_email', $to);
        $recordModel->set('cc_email', $cc);
        $recordModel->set('bcc_email', $bcc);
        $recordModel->set('mail_message_key', $mailRecord->generateUniqueKeyFromEmail());
        $recordModel->save();

        $mailRecord->setDocumentRelationIds([$recordModel->getId(), $recordModel->get('related_to')]);
        $mailRecord->setAttachmentRelationIds([$recordModel->getId()]);

        // TODO: Handle attachments of the mail (inline/file)
        $this->saveAttachments($mailRecord, $recordModel);

        return $recordModel->getId();
    }

    /**
     * @param object $linkFocus
     *
     * @return int
     */
    public function getAssignedToId(object $linkFocus): int
    {
        if ('MAIL MANAGER' === $this->recordSource) {
            return (int)Users_Record_Model::getCurrentUserModel()->get('id');
        }

        $assignedToId = $linkFocus->column_fields['assigned_user_id'];

        if (vtws_getOwnerType($assignedToId) == 'Groups') {
            $assignedToId = Users::getActiveAdminId();
        }

        return (int)$assignedToId;
    }

    /**
     * Save attachments from the email and add it to the module record.
     *
     * @param MailManager_Message_Model|Settings_MailConverter_MailRecord_Handler|object $mailRecord
     * @param Vtiger_Record_Model                                                        $recordModel
     *
     * @throws Exception
     * @global String                                                                    $root_directory
     * @global PearDataBase                                                              $db
     */
    public function saveAttachments(object $mailRecord, Vtiger_Record_Model $recordModel): void
    {
        $recordId = $recordModel->getId();
        $currentUser = Users_Record_Model::getCurrentUserModel();

        foreach ($mailRecord->getAttachments() as $attachmentIndex => $attachmentInfo) {
            $attachmentId = $attachmentInfo['attachment_id'];

            if (empty($attachmentId)) {
                $attachInfo = $mailRecord->saveAttachmentFile($attachmentInfo['filename'], $attachmentInfo['data'], $attachmentInfo['type']);
                $attachmentId = $attachInfo['attachmentsid'];
                $mailRecord->_attachments[$attachmentIndex]['attachment_id'] = $attachmentId;
            }

            if (empty($attachmentId)) {
                continue;
            }

            $documentRecord = $mailRecord->saveDocumentFile($attachmentInfo['filename'], $attachmentInfo['size'], $currentUser->getId(), $this->moduleName);

            if (empty($documentRecord->getId())) {
                continue;
            }

            $mailRecord->setAttachmentRelationIds($documentRecord->getId());

            if (!empty($mailRecord->getAttachmentRelationIds())) {
                foreach ($mailRecord->getAttachmentRelationIds() as $relationId) {
                    $documentRecord->saveAttachmentsRelation($relationId, $attachmentId);
                }
            }

            if (!empty($mailRecord->getDocumentRelationIds())) {
                foreach ($mailRecord->getDocumentRelationIds() as $relationId) {
                    $documentRecord->saveDocumentsRelation($relationId, $documentRecord->getId());
                }
            }
        }

        foreach ($mailRecord->getInlineAttachments() as $attachmentIndex => $attachmentInfo) {
            $attachmentId = $attachmentInfo['attachment_id'];

            if (empty($attachmentId)) {
                $attachInfo = $mailRecord->saveAttachmentFile($attachmentInfo['filename'], $attachmentInfo['data'], $attachmentInfo['type']);
                $attachmentId = $attachInfo['attachmentsid'];
                $mailRecord->_inline_attachments[$attachmentIndex]['attachment_id'] = $attachmentId;
            }

            if ($attachmentId) {
                /** @var Documents_Record_Model $documentRecord */
                $documentRecord = Vtiger_Record_Model::getCleanInstance('Documents');
                $documentRecord->saveAttachmentsRelation($recordId, $attachmentId);
            }
        }
    }
}