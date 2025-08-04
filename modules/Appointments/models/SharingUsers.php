<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_SharingUsers_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    public PearDatabase $adb;
    /**
     * @var string
     */
    public string $table = 'its4you_sharing_users';

    /**
     * @return void
     */
    public function deleteUsers()
    {
        $query = sprintf('DELETE FROM %s WHERE crmid=?', $this->table);
        $this->adb->pquery($query, [
            $this->get('record_id'),
        ]);
    }

    /**
     * @param int $recordId
     *
     * @return self
     */
    public static function getInstance(int $recordId): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->set('record_id', $recordId);

        return $instance;
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return (array)$this->get('users');
    }

    /**
     * @return void
     */
    public function saveUser()
    {
        $params = [
            'crmid'  => $this->get('record_id'),
            'userid' => $this->get('user_id'),
            'type'   => !$this->isEmpty('type') ? $this->get('type') : 1,
        ];
        $query = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, implode(',', array_keys($params)), generateQuestionMarks($params));

        $this->adb->pquery($query, $params);
    }

    /**
     * @return void
     */
    public function saveUsers()
    {
        $userIds = $this->getUsers();

        foreach ($userIds as $userId) {
            $this->set('user_id', $userId);
            $this->saveUser();
        }
    }

    /**
     * @param array $value
     *
     * @return void
     */
    public function setUsers(array $value)
    {
        $this->set('users', $value);
    }
}