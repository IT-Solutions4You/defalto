<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class HelpDeskHandler extends VTEventHandler
{
    /**
     * @param string $eventName
     * @param object $entityData
     * @return void
     * @throws AppException
     */
    public function handleEvent($eventName, $entityData)
    {
        if ($eventName == 'vtiger.entity.aftersave.final') {
            $moduleName = $entityData->getModuleName();
            if ($moduleName == 'HelpDesk') {
                $this->updateTicketFromPortal($entityData->getId());
                $this->createEmailFromMailManager($entityData->getId());
            }
        }
    }

    /**
     * @param int $recordId
     * @return void
     * @throws AppException
     */
    public function createEmailFromMailManager(int $recordId): void
    {
        $requestData = $_REQUEST;

        if (empty(MailManager_Relation_View::getMailSession()) || empty($requestData['sourceModule']) || empty($requestData['sourceRecord']) || 'MailManager' !== $requestData['sourceModule']) {
            return;
        }

        $mailData = MailManager_Relation_View::getMailSession();

        if (empty($mailData['uniqueId']) || $mailData['uniqueId'] !== $requestData['sourceRecord']) {
            return;
        }

        $recipientId = $mailData['recipientId'];
        $folderName = $mailData['folderName'];
        $uniqueId = $mailData['uniqueId'];

        // This is to handle larger uploads
        ini_set('memory_limit', MailManager_Config_Model::get('MEMORY_LIMIT'));

        $mail = (new MailManager_Relation_View())->getMail($folderName, $uniqueId);
        $mail->setAttachmentRelationIds($recordId);

        MailManager_Relate_Action::associate($mail, $recordId, (int)$recipientId);
        MailManager_Relation_View::setMailSession([]);
    }

    /**
     * @param int $recordId
     * @return void
     */
    public function updateTicketFromPortal(int $recordId): void
    {
        $adb = PearDatabase::getInstance();
        $adb->pquery('UPDATE vtiger_ticketcf SET from_portal=0 WHERE ticketid=?', [$recordId]);
    }
}

function HelpDesk_nofifyOnPortalTicketCreation($entityData) {
	global $HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID;
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$ownerIdInfo = getRecordOwnerId($entityId);
	if(!empty($ownerIdInfo['Users'])) {
		$ownerId = $ownerIdInfo['Users'];
		$to_email = getUserEmail($ownerId);
	}
	if(!empty($ownerIdInfo['Groups'])) {
		$ownerId = $ownerIdInfo['Groups'];
		$to_email = implode(',', getDefaultAssigneeEmailIds($ownerId));
	}
	$wsParentId = $entityData->get('contact_id');
	$parentIdParts = explode('x', $wsParentId);
	$parentId = $parentIdParts[1];

	$subject = '[From Portal] ' .$entityData->get('ticket_no'). " [ Ticket Id : $entityId ] " .$entityData->get('ticket_title');
	$contents = ' Ticket No : '.$entityData->get('ticket_no'). '<br> Ticket ID : '.$entityId.'<br> Ticket Title : '.
							$entityData->get('ticket_title').'<br><br>'.$entityData->get('description');

	//get the contact email id who creates the ticket from portal and use this email as from email id in email
	$result = $adb->pquery("SELECT email, concat (firstname,' ',lastname) as name FROM vtiger_contactdetails WHERE contactid=?", array($parentId));
	$contact_email = $adb->query_result($result,0,'email');
	$name = $adb->query_result($result, 0, 'name');
	$from_email = $contact_email;

	//send mail to assigned to user
	EMAILMaker_Utils_Helper::sendMail($to_email, $name, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents);

	//send mail to the customer(contact who creates the ticket from portal)
	EMAILMaker_Utils_Helper::sendMail($contact_email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents);
}

function HelpDesk_notifyParentOnTicketChange($entityData) {
	global $HELPDESK_SUPPORT_NAME,$HELPDESK_SUPPORT_EMAIL_ID;
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$isNew = $entityData->isNew();

	if(!$isNew) {
		$reply = 'Re : ';
	} else {
		$reply = '';
	}

	$subject = $entityData->get('ticket_no') . " [ Ticket Id : $entityId ] " . $reply . $entityData->get('ticket_title');
	$emailoptout = 0;
	$wsContactId = $entityData->get('contact_id');
	$contactId = explode('x', $wsContactId);
	$wsAccountId = $entityData->get('parent_id');
	$accountId = explode('x', $wsAccountId);
	//To get the emailoptout vtiger_field value and then decide whether send mail about the tickets or not
	if(!empty($contactId[0])) {
		$result = $adb->pquery('SELECT email, emailoptout, lastname, firstname FROM vtiger_contactdetails WHERE contactid=?', array($contactId[1]));
		$emailoptout = $adb->query_result($result,0,'emailoptout');
		$parent_email = $contact_mailid = $adb->query_result($result,0,'email');
		$parentname = $adb->query_result($result,0,'firstname').' '.$adb->query_result($result,0,'firstname');

		//Get the status of the vtiger_portal user. if the customer is active then send the vtiger_portal link in the mail
		if($parent_email != '') {
			$sql = "SELECT * FROM vtiger_portalinfo WHERE user_name=?";
			$isPortalUser = $adb->query_result($adb->pquery($sql, array($contact_mailid)),0,'isactive');
		}
	} elseif(!empty($accountId[0])) {
		$result = $adb->pquery("SELECT accountname, emailoptout, email1 FROM vtiger_account WHERE accountid=?",
									array($accountId[1]));
		$emailoptout = $adb->query_result($result,0,'emailoptout');
		$parent_email = $adb->query_result($result,0,'email1');
		$parentname = $adb->query_result($result,0,'accountname');
	}
	//added condition to check the emailoptout(this is for contacts and vtiger_accounts.)
	if($emailoptout == 0) {
		if($isPortalUser == 1) {
			$email_body = HelpDesk::getTicketEmailContents($entityData);
		} else {
			$email_body = HelpDesk::getTicketEmailContents($entityData);
		}

		if($isNew) {
			EMAILMaker_Utils_Helper::sendMail($parent_email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $email_body);
		} else {
			$entityDelta = new VTEntityDelta();
			$statusHasChanged = $entityDelta->hasChanged($entityData->getModuleName(), $entityId, 'ticketstatus');
			$solutionHasChanged = $entityDelta->hasChanged($entityData->getModuleName(), $entityId, 'solution');
			$descriptionHasChanged = $entityDelta->hasChanged($entityData->getModuleName(), $entityId, 'description');

			if(($statusHasChanged && $entityData->get('ticketstatus') == "Closed") || $solutionHasChanged || $descriptionHasChanged) {
				EMAILMaker_Utils_Helper::sendMail($parent_email, $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $email_body);
			}
		}
	}
}
