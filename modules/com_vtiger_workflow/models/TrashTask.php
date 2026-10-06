<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class VTTrashTask extends VTTask
{
    public $executeImmediately = false;
    public $deletionMode = 'trash';
    private static array $activeRecords = [];

    public function doTask($entity)
    {
        $id = $entity->getId();
        [, $recordId] = vtws_getIdComponents($id);

        if (isset(self::$activeRecords[$id]) || !isRecordExists($recordId)) {
            return;
        }

        self::$activeRecords[$id] = true;

        try {
            $record = Vtiger_Record_Model::getInstanceById($recordId, $entity->getModuleName());
            $record->delete();
            VTEntityCache::unsetCachedEntity($id);

            if ($this->isPermanentDelete()) {
                Vtiger_Module_Model::getInstance('RecycleBin')->deleteRecords([$recordId]);
            }
        } finally {
            unset(self::$activeRecords[$id]);
        }
    }

    public function getFieldNames()
    {
        return ['deletionMode'];
    }

    public function isPermanentDelete(): bool
    {
        return $this->deletionMode === 'permanent';
    }

    public function getContents($entity)
    {
        // The standard queue requires nonempty contents for non-immediate tasks.
        return 'trash';
    }
}
