<?php

/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ITS4YouCalendar_InvitedUsers_Model extends Vtiger_Base_Model
{
    public PearDatabase $adb;

    public function deleteUsers()
    {
        $this->adb->pquery('DELETE FROM its4you_invited_users WHERE record_id=?', [$this->get('record_id')]);
    }

    public static function getAccessibleUsers()
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $users = $currentUser->getAccessibleUsers();

        unset($users[$currentUser->getId()]);

        return $users;
    }

    /**
     * @param int $recordId
     * @return static
     */
    public static function getInstance(int $recordId): self
    {
        $instance = new self();
        $instance->adb = PearDatabase::getInstance();
        $instance->setRecord($recordId);
        $instance->retrieveRecordModel();

        return $instance;
    }

    public function getRecord(): int
    {
        return (int)$this->get('record_id');
    }

    /**
     * @return Vtiger_Record_Model
     */
    public function getRecordModel(): Vtiger_Record_Model
    {
        return $this->get('record_model');
    }

    public function getUsers(): array
    {
        return (array)$this->get('invite_users');
    }

    /**
     * @return array
     */
    public function getUsersInfo(): array
    {
        return (array)$this->get('invite_users_info');
    }

    public function retrieveRecordModel()
    {
        $this->set('record_model', Vtiger_Record_Model::getInstanceById($this->getRecord()));
    }

    public function retrieveUsers()
    {
        $recordId = $this->getRecord();
        $query = 'SELECT vtiger_users.email1 as email, its4you_invited_users.user_id 
            FROM its4you_invited_users 
            INNER JOIN vtiger_users ON vtiger_users.id=its4you_invited_users.user_id 
            WHERE its4you_invited_users.record_id =? AND vtiger_users.deleted=? AND vtiger_users.status=?';
        $user_result = $this->adb->pquery($query, [$recordId, 0, 'Active']);
        $invitedUsers = [];

        if ($this->adb->num_rows($user_result)) {
            while ($row = $this->adb->fetch_array($user_result)) {
                $invitedUsers[$row['user_id']] = $row['email'];
            }
        }

        $this->setUsers(array_keys($invitedUsers));
        $this->setUsersInfo($invitedUsers);
    }

    public function saveUsers()
    {
        foreach ($this->getUsers() as $userId) {
            if (empty($userId)) {
                continue;
            }

            $this->adb->pquery('INSERT INTO its4you_invited_users (record_id, user_id) VALUES (?,?)', [$this->get('record_id'), $userId]);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function sendInvitation()
    {
        ITS4YouCalendar_InvitationEmail_Model::getInstance($this)->send();
    }

    /**
     * @param int $value
     * @return void
     */
    public function setRecord(int $value)
    {
        $this->set('record_id', $value);
    }

    public function setUsers(array $value)
    {
        $this->set('invite_users', $value);
    }

    /**
     * @param array $value
     * @return void
     */
    public function setUsersInfo(array $value)
    {
        $this->set('invite_users_info', $value);
    }

}