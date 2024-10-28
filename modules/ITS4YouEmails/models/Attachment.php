<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

