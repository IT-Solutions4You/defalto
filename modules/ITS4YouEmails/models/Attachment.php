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