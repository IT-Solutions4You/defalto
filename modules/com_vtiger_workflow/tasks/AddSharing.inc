<?php
/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AddSharing extends VTTask
{
    public $executeImmediately = true;

    public $memberViewList = [];
    public $memberEditList = [];
    public $entity;

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
     * @return string
     */
    public function updateShareType(string $share): string
    {
        [$shareType, $shareValue] = explode(':', $share);

        if ('Fields' === $shareType) {
            $entityUserId = $this->entity->get($shareValue);

            if (empty($userId)) {
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