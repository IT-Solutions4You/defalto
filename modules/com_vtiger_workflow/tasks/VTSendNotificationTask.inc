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

require_once('modules/com_vtiger_workflow/tasks/VTEmailTask.inc');

class VTSendNotificationTask extends VTEmailTask
{
    public function doTask($entity): void
    {
        global $current_user;
        $util = new VTWorkflowUtils();

        $taskContents = Zend_Json::decode($this->getContents($entity));
        $fromEmail = $taskContents['fromEmail'];
        $fromName = $taskContents['fromName'];
        $toEmail = $taskContents['toEmail'];
        $cc = $taskContents['ccEmail'];
        $bcc = $taskContents['bccEmail'];
        $subject = $taskContents['subject'];
        $content = $taskContents['content'];

        //Storing the details of emails
        $entityIdDetails = vtws_getIdComponents($entity->getId());
        $entityId = $entityIdDetails[1];
        $moduleName = 'ITS4YouEmails';
        $userId = $current_user->id;

        $emailFocus = CRMEntity::getInstance($moduleName);
        $emailFocus->column_fields['assigned_user_id'] = $userId;
        $emailFocus->column_fields['subject'] = $subject;
        $emailFocus->column_fields['body'] = $content;
        $emailFocus->column_fields['from_email'] = $fromEmail;
        $emailFocus->column_fields['to_email'] = $toEmail;
        $emailFocus->column_fields['cc_email'] = $cc;
        $emailFocus->column_fields['bcc_email'] = $bcc;
        $emailFocus->column_fields['related_to'] = $entityId;
        $emailFocus->column_fields['user_id'] = $userId;
        $emailFocus->column_fields['email_flag'] = 'SENT';
        $emailFocus->save($moduleName);

        $emailId = $emailFocus->id;
        $trackURL = Vtiger_Functions::generateTrackingURL(['record' => $entityId, 'mailid' => $emailId, 'method' => 'open']);
        $content = "<img src='$trackURL' alt='' width='1' height='1'>$content";

        $status = EMAILMaker_Utils_Helper::sendMail($toEmail, $fromName, $fromEmail, $subject, $content);

        if (!empty($emailId)) {
            $emailFocus->setEmailAccessCountValue($emailId);
        }

        if (!$status) {
            //If mail is not sent then removing the details about email
            $emailFocus->trash($moduleName, $emailId);
        }

        $util->revertUser();
    }

    /**
     * Function to get contents of this task
     *
     * @param <Object> $entity
     *
     * @return <Array> contents
     */
    public function getContents($entity, $entityCache = false)
    {
        if (!$this->contents) {
            $util = new VTWorkflowUtils();
            $admin = $util->adminUser();
            $entityCache = new VTEntityCache($admin);

            $entityOwnerId = $entity->get('assigned_user_id');
            $entityId = $entity->getId();
            $entity = $entityCache->forId($entityId);

            [$referenceEntityId, $ownerId] = vtws_getIdComponents($entityOwnerId);

            $referenceEntity = $entityCache->forId($entityOwnerId);
            if ($referenceEntity->getModuleName() === 'Groups') {
                require_once('include/utils/GetGroupUsers.php');

                $groupModel = new GetGroupUsers();
                $groupModel->getAllUsersInGroup($ownerId);

                //Clearing static cache for sub groups
                GetGroupUsers::$groupIdsList = [];
                $usersList = $groupModel->group_users;
                if ($usersList) {
                    $ownerId = $usersList[0];
                }
            }
            if ($ownerId) {
                $ownerFocus = CRMEntity::getInstance('Users');
                $ownerFocus->retrieve_entity_info($ownerId, 'Users');

                $dateTime = new DateTimeField($entity->get('date_start') . ' ' . $entity->get('time_start'));
                $entity->set('date_start', $dateTime->getDisplayDate($ownerFocus));
                $entity->set('time_start', $dateTime->getDisplayTime($ownerFocus));

                $dateTime = new DateTimeField($entity->get('due_date') . ' ' . $entity->get('time_end'));
                $entity->set('due_date', $dateTime->getDisplayDate($ownerFocus));
                $entity->set('time_end', $dateTime->getDisplayTime($ownerFocus));
            }
            $entityCache->cache[$entityId] = $entity;
            $this->contents = parent::getContents($entity, $entityCache);
            $util->revertUser();
        }

        return $this->contents;
    }
}