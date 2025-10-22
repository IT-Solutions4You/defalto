<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class AddSharing extends VTTask
{
    public $executeImmediately = true;

    public $memberViewList = [];
    public $memberEditList = [];
    public $entity;
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

    /**
     * @param string $share
     *
     * @return string
     */
    public function updateShareType(string $share): string
    {
        [$shareType, $shareValue] = explode(':', $share);

        if ('Fields' === $shareType) {
            $entityUserId = $this->entity->get($shareValue);

            if (empty($entityUserId)) {
                return '';
            }

            [$tabId, $userId] = explode('x', $entityUserId);
            $share = 'Users:' . $userId;
        }

        return $share;
    }

    public function doTask($entity)
    {
        $this->entity = $entity;
        $entityId = $entity->getId();
        $recordId = vtws_getIdComponents($entityId)[1];
        $sharing = Core_SharingRecord_Model::getAllSharing($recordId);
        $setMemberViewList = [];
        $setMemberEditList = [];
        $counter = 0;

        foreach ($sharing[1] as $data) {
            foreach ($data as $share => $name) {
                $setMemberViewList[$share] = $counter;
                $counter++;
            }
        }

        if (is_array($this->memberViewList)) {
            foreach ($this->memberViewList as $share) {
                $share = $this->updateShareType($share);

                if (!isset($setMemberViewList[$share]) && !empty($share)) {
                    $setMemberViewList[$share] = $counter;
                    $counter++;
                }
            }
        }

        $counter = 0;

        foreach ($sharing[2] as $data) {
            foreach ($data as $share => $name) {
                $setMemberEditList[$share] = $counter;
                $counter++;
            }
        }

        if (is_array($this->memberEditList)) {
            foreach ($this->memberEditList as $share) {
                $share = $this->updateShareType($share);

                if (!isset($setMemberEditList[$share]) && !empty($share)) {
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