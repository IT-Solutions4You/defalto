<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class ITS4YouEmails_Attachment_Model extends Core_Attachment_Model
{
    /**
     * @throws Exception
     */
    public function retrieveAccessibleAttachment(int $attachmentId): array
    {
        $attachment = $this->getAttachmentTable()->selectData([], ['attachmentsid' => $attachmentId]);

        if (empty($attachment) || !$this->hasAttachmentAccess($attachmentId)) {
            throw new Exception('Attachment is not accessible');
        }

        return $attachment;
    }

    /**
     * @throws Exception
     */
    public function retrieveAccessibleAttachments(array $attachments): array
    {
        $accessibleAttachments = [];

        foreach ($attachments as $attachment) {
            if (!is_array($attachment)) {
                throw new Exception('Attachment is not accessible');
            }

            $attachmentId = $attachment['fileid'] ?? null;

            if (!is_int($attachmentId) && (!is_string($attachmentId) || !ctype_digit($attachmentId))) {
                throw new Exception('Attachment is not accessible');
            }

            $accessibleAttachments[] = $this->retrieveAccessibleAttachment((int)$attachmentId);
        }

        return $accessibleAttachments;
    }

    /**
     * @throws Exception
     */
    public function validateDocumentIds(array $documentIds): array
    {
        $validatedIds = [];

        foreach ($documentIds as $documentId) {
            if (!is_int($documentId) && (!is_string($documentId) || !ctype_digit($documentId))) {
                throw new Exception('Document is not accessible');
            }

            $documentId = (int)$documentId;

            if ($documentId < 1
                || !isRecordExists($documentId)
                || 'Documents' !== getSalesEntityType($documentId)
                || !Users_Privileges_Model::isPermitted('Documents', 'DetailView', $documentId)
            ) {
                throw new Exception('Document is not accessible');
            }

            $validatedIds[] = $documentId;
        }

        return array_values(array_unique($validatedIds));
    }

    public function hasAttachmentAccess(int $attachmentId): bool
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $mailResult = $this->getDB()->pquery(
            'SELECT attachid FROM vtiger_mailmanager_mailattachments WHERE attachid = ? AND userid = ? AND cid IS NULL',
            [$attachmentId, $currentUser->getId()]
        );

        if ($this->getDB()->num_rows($mailResult)) {
            return true;
        }

        $relationResult = $this->getDB()->pquery(
            'SELECT vtiger_crmentity.crmid, vtiger_crmentity.setype
            FROM vtiger_seattachmentsrel
            INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_seattachmentsrel.crmid
            WHERE vtiger_seattachmentsrel.attachmentsid = ? AND vtiger_crmentity.deleted = 0',
            [$attachmentId]
        );

        while ($row = $this->getDB()->fetchByAssoc($relationResult)) {
            $moduleName = $row['setype'];
            $recordId = (int)$row['crmid'];

            if (Vtiger_Module_Model::getInstance($moduleName)
                && Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)
            ) {
                return true;
            }
        }

        return false;
    }

    public static function getParentRecords($recordId)
    {
        $parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
        $parentModuleModel = $parentRecordModel->getModule();

        $relatedModuleModel = Vtiger_Module_Model::getInstance('Documents');
        $relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relatedModuleModel);

        $sql = $relationModel->getQuery($parentRecordModel);
        $adb = PearDatabase::getInstance();
        $result = $adb->pquery($sql);
        $records = [];

        while ($row = $adb->fetchByAssoc($result)) {
            $documentRecordId = $row['crmid'];

            if (isPermitted('Documents', 'DetailView', $documentRecordId)) {
                $folder = Documents_Folder_Model::getInstanceById($row['folderid']);

                if ($folder) {
                    $row['foldername'] = $folder->getName();
                }

                $records[$documentRecordId] = $row;
            }
        }

        return $records;
    }
}
