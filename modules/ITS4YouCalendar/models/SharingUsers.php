<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_SharingUsers_Model extends Vtiger_Base_Model
{
    public string $table = 'its4you_sharing_users';
    public PearDatabase $adb;

    /**
     * @param int $recordId
     * @return self
     */
    public static function getInstance(int $recordId): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->set('record_id', $recordId);

        return $instance;
    }

    public function setUsers(array $value)
    {
        $this->set('users', $value);
    }

    public function saveUsers()
    {
        $userIds = $this->getUsers();

        foreach ($userIds as $userId) {
            $this->set('user_id', $userId);
            $this->saveUser();
        }
    }

    public function getUsers(): array
    {
        return (array)$this->get('users');
    }

    public function saveUser()
    {
        $params = [
            'crmid' => $this->get('record_id'),
            'userid' => $this->get('user_id'),
            'type' => !$this->isEmpty('type') ? $this->get('type') : 1,
        ];
        $query = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, implode(',', array_keys($params)), generateQuestionMarks($params));

        $this->adb->pquery($query, $params);
    }

    public function deleteUsers()
    {
        $query = sprintf('DELETE FROM %s WHERE crmid=?', $this->table);
        $this->adb->pquery($query, [
            $this->get('record_id')
        ]);
    }
}