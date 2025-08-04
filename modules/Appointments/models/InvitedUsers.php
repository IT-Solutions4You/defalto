<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

class Appointments_InvitedUsers_Model extends Vtiger_Base_Model
{
    /**
     * @var PearDatabase
     */
    public PearDatabase $adb;

    /**
     * @return void
     */
    public function deleteUsers()
    {
        $this->adb->pquery('DELETE FROM its4you_invited_users WHERE record_id=?', [$this->get('record_id')]);
    }

    /**
     * @return array
     */
    public static function getAccessibleUsers(): array
    {
        $currentUser = Users_Record_Model::getCurrentUserModel();

        return $currentUser->getAccessibleUsers() + $currentUser->getAccessibleGroups();
    }

    /**
     * @param int $recordId
     *
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

    /**
     * @return int
     */
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

    /**
     * @return array
     */
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

    /**
     * @return void
     */
    public function retrieveRecordModel()
    {
        $this->set('record_model', Vtiger_Record_Model::getInstanceById($this->getRecord()));
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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
        Appointments_InvitationEmail_Model::getInstance($this)->send();
    }

    /**
     * @param int $value
     *
     * @return void
     */
    public function setRecord(int $value)
    {
        $this->set('record_id', $value);
    }

    /**
     * @param array $value
     *
     * @return void
     */
    public function setUsers(array $value)
    {
        $this->set('invite_users', $value);
    }

    /**
     * @param array $value
     *
     * @return void
     */
    public function setUsersInfo(array $value)
    {
        $this->set('invite_users_info', $value);
    }

}