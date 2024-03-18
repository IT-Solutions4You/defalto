<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class RemoveSharing extends VTTask
{
    public $executeImmediately = true;

    public $memberViewList = [];
    public $memberEditList = [];

    public function getFieldNames()
    {
        return ['memberViewList', 'memberEditList'];
    }

    public function getSharingRecord(): Vtiger_SharingRecord_Model
    {
        return new Vtiger_SharingRecord_Model();
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
        $sharing = Vtiger_SharingRecord_Model::getAllSharing($recordId);
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