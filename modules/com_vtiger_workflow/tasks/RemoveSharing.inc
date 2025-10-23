<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class RemoveSharing extends VTTask
{
    public $executeImmediately = true;

    public $memberViewList = [];
    public $memberEditList = [];
    public $trigger;
    public $field_value_mapping;

    public function getFieldNames()
    {
        return ['memberViewList', 'memberEditList'];
    }

    public function getSharingRecord(): Core_SharingRecord_Model
    {
        return new Core_SharingRecord_Model();
    }

    public function isSelectedList($id): bool
    {
        return in_array($id, (array)$this->memberViewList);
    }

    public function isSelectedEdit($id): bool
    {
        return in_array($id, (array)$this->memberEditList);
    }

    public function doTask($entity)
    {
        $entityId = $entity->getId();
        $recordId = vtws_getIdComponents($entityId)[1];
        $sharing = Core_SharingRecord_Model::getAllSharing($recordId);
        $setMemberViewList = [];
        $setMemberEditList = [];
        $counter = 0;

        foreach ($sharing[1] as $data) {
            foreach ($data as $share => $name) {
                if (!is_array($this->memberViewList) || !in_array($share, $this->memberViewList)) {
                    $setMemberViewList[$share] = $counter;
                    $counter++;
                }
            }
        }

        $counter = 0;

        foreach ($sharing[2] as $data) {
            foreach ($data as $share => $name) {
                if (!is_array($this->memberEditList) || !in_array($share, $this->memberEditList)) {
                    $setMemberEditList[$share] = $counter;
                    $counter++;
                }
            }
        }

        $recordModel = $this->getSharingRecord();

        if ($recordModel) {
            $recordModel->set('record', $recordId);
            $recordModel->set('memberViewList', array_flip($setMemberViewList));
            $recordModel->set('memberEditList', array_flip($setMemberEditList));
            $recordModel->save();
        }
    }
}