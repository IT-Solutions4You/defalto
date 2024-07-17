<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'include/events/include.inc';

class Core_SharingRecord_Model extends Vtiger_Base_Model
{
    private $isShared;

    /**
     * Function to get the Id
     * @return <Number> record Id
     */
    public function getId()
    {
        return $this->get('recordid');
    }

    /**
     * Function to set the Id
     *
     * @param <Number> record Id
     *
     * @return <Core_SharingRecord_Model> instance
     */
    public function setId($id)
    {
        return $this->set('recordid', $id);
    }

    /**
     * Function to get the record Name
     * @return <String>
     */
    public function getName()
    {
        return $this->get('recordid');
    }

    /**
     * Function to get the Edit View Url for the Sharing record
     * @return <String>
     */
    public function getEditViewUrl($module, $record)
    {
        return '?module=' . $module . '&view=EditSharingRecord&record=' . $record;
    }

    /**
     * Function to get the Detail Url for the Sharing record
     * @return <String>
     */
    public function getDetailViewUrl($module, $record)
    {
        return '?module=' . $module . '&view=Detail&record=' . $record . '&mode=DetailSharingRecord&tab_label=LBL_SHARING_RECORD';
    }

    /**
     * @param $value
     * @param $id
     *
     * @return string|void
     */
    public function getRecordDetailViewUrl($value, $id)
    {
        if ('Users' === $value ) {
            return '?module=Users&parent=Settings&view=Detail&record=' . $id;
        }
        if ('Groups' === $value ) {
            return '?module=Groups&parent=Settings&view=Detail&record=' . $id;
        }
        if ('Roles' === $value ) {
            return '?module=Roles&parent=Settings&view=Edit&record=' . $id;
        }
        if ('RoleAndSubordinates' === $value ) {
            return '?module=Roles&parent=Settings&view=Edit&record=' . $id;
        }
        if ( 'MultiCompany4you' === $value) {
            return '?module=ITS4YouMultiCompany&view=Detail&companyid=' . $id;
        }
    }

    /**
     * Function to get all the members of the groups
     * @return <Array> Core_SharingRecord_Model instances
     */
    public function getMembers($record = false)
    {
        if (!$this->members) {
            $this->members = self::getAllSharing($record);
        }

        return $this->members;
    }

    /**
     * @param $record
     *
     * @return array
     */
    public static function getAllSharing($record)
    {
        $db = PearDatabase::getInstance();

        $members = [];
        $sql = 'SELECT its4you_sharing_users.type, vtiger_users.id, vtiger_users.last_name, vtiger_users.first_name FROM vtiger_users
                INNER JOIN its4you_sharing_users ON its4you_sharing_users.userid = vtiger_users.id
                WHERE its4you_sharing_users.crmid = ?';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            $members[$row['type']]['Users']['Users:' . $row['id']] = getFullNameFromArray('Users', $row);
        }

        $sql = 'SELECT its4you_sharing_groups.type, vtiger_groups.groupid, vtiger_groups.groupname FROM vtiger_groups
                INNER JOIN its4you_sharing_groups ON its4you_sharing_groups.groupid = vtiger_groups.groupid
                WHERE its4you_sharing_groups.crmid = ?';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            $members[$row['type']]['Groups']['Groups:' . $row['groupid']] = $row['groupname'];
        }

        $sql = 'SELECT its4you_sharing_roles.type, vtiger_role.roleid, vtiger_role.rolename FROM vtiger_role
                INNER JOIN its4you_sharing_roles ON its4you_sharing_roles.roleid = vtiger_role.roleid
                WHERE its4you_sharing_roles.crmid = ?';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            $members[$row['type']]['Roles']['Roles:' . $row['roleid']] = $row['rolename'];
        }

        $sql = 'SELECT its4you_sharing_rolessubroles.type, vtiger_role.roleid, vtiger_role.rolename FROM vtiger_role
                INNER JOIN its4you_sharing_rolessubroles ON its4you_sharing_rolessubroles.roleid = vtiger_role.roleid
                WHERE its4you_sharing_rolessubroles.crmid = ?';
        $result = $db->pquery($sql, [$record]);

        while ($row = $db->fetchByAssoc($result)) {
            $members[$row['type']]['RoleAndSubordinates']['RoleAndSubordinates:' . $row['roleid']] = $row['rolename'];
        }

        if (false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany') && false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany')->isActive()) {
            $sql = 'SELECT its4you_sharing_multicompany.type, its4you_sharing_multicompany.companyid, its4you_multicompany4you.companyname 
                FROM its4you_sharing_multicompany
                INNER JOIN its4you_multicompany4you ON its4you_multicompany4you.companyid=its4you_sharing_multicompany.companyid
                WHERE its4you_sharing_multicompany.crmid = ?';
            $result = $db->pquery($sql, [$record]);

            while ($row = $db->fetchByAssoc($result)) {
                $members[$row['type']]['MultiCompany4you']['MultiCompany4you:' . $row['companyid']] = $row['companyname'];
            }
        }

        return $members;
    }

    /**
     * Function to save the Sharing Record
     */
    public function save()
    {
        $this->isShared = 0;
        $record = $this->get('record');

        $membersView = $this->get('memberViewList');
        $this->memberSave($membersView, $record, 1);

        $membersEdit = $this->get('memberEditList');
        $this->memberSave($membersEdit, $record, 2);

        $this->setShared();
    }

    /**
     * @param $member
     * @param $record
     * @param $type
     */
    private function memberSave($member, $record, $type)
    {
        $db = PearDatabase::getInstance();

        $sql = 'DELETE FROM its4you_sharing_users WHERE crmid=? AND type = ?';
        $db->pquery($sql, [$record, $type]);
        $sql = 'DELETE FROM its4you_sharing_groups WHERE crmid=? AND type = ?';
        $db->pquery($sql, [$record, $type]);
        $sql = 'DELETE FROM  its4you_sharing_roles WHERE crmid=? AND type = ?';
        $db->pquery($sql, [$record, $type]);
        $sql = 'DELETE FROM  its4you_sharing_rolessubroles WHERE crmid=? AND type = ?';
        $db->pquery($sql, [$record, $type]);
        $sql = 'DELETE FROM  its4you_sharing_multicompany WHERE crmid=? AND type = ?';
        $db->pquery($sql, [$record, $type]);

        if (is_array($member)) {
            foreach ($member as $id) {
                $idComponents = explode(':', $id);
                $this->isShared = 1;
                $memberType = $idComponents[0];
                $memberId = $idComponents[1];

                if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_USERS) {
                    $db->pquery('INSERT INTO its4you_sharing_users (crmid, userid, type ) VALUES (?,?,?)', [$record, $memberId, $type]);
                }

                if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_GROUPS) {
                    $db->pquery('INSERT INTO its4you_sharing_groups (crmid, groupid, type ) VALUES (?,?,?)', [$record, $memberId, $type]);
                }

                if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLES) {
                    $db->pquery('INSERT INTO its4you_sharing_roles (crmid, roleid, type ) VALUES (?,?,?)', [$record, $memberId, $type]);
                }

                if ($memberType == Settings_Groups_Member_Model::MEMBER_TYPE_ROLE_AND_SUBORDINATES) {
                    $db->pquery('INSERT INTO its4you_sharing_rolessubroles (crmid, roleid, type ) VALUES (?,?,?)', [$record, $memberId, $type]);
                }

                if ('MultiCompany4you' === $memberType) {
                    $db->pquery('INSERT INTO its4you_sharing_multicompany (crmid, companyid, type ) VALUES (?,?,?)', [$record, $memberId, $type]);
                }
            }
        }
    }

    private function setShared()
    {
        $db = PearDatabase::getInstance();
        $isShared = 0;

        if ($this->isShared) {
            $isShared = 1;
        }

        $db->pquery('UPDATE vtiger_crmentity SET isshared=? WHERE crmid=?', [$isShared, $this->get('record')]);
    }

    /**
     * Function to get the instance of Group model, given group id or name
     *
     * @param <Object> $value
     *
     * @return Settings_Groups_Record_Model instance, if exists. Null otherwise
     */
    public static function getInstance($recordId)
    {
        $db = PearDatabase::getInstance();
        $role = new self();
        $users = [1 => [], 2 => []];
        $sql = 'SELECT its4you_sharing_users.type, vtiger_users.id, vtiger_users.last_name, vtiger_users.first_name FROM vtiger_users
                INNER JOIN its4you_sharing_users ON its4you_sharing_users.userid = vtiger_users.id
                WHERE its4you_sharing_users.crmid = ?';
        $result = $db->pquery($sql, [$recordId]);

        while ($row = $db->fetchByAssoc($result)) {
            $users[$row['type']]['Users'][$row['id']] = getFullNameFromArray('Users', $row);
        }

        $sql = 'SELECT its4you_sharing_groups.type, vtiger_groups.groupid, vtiger_groups.groupname FROM vtiger_groups
                INNER JOIN its4you_sharing_groups ON its4you_sharing_groups.groupid = vtiger_groups.groupid
                WHERE its4you_sharing_groups.crmid = ?';
        $result = $db->pquery($sql, [$recordId]);

        while ($row = $db->fetchByAssoc($result)) {
            $users[$row['type']]['Groups'][$row['groupid']] = $row['groupname'];
        }

        $sql = 'SELECT its4you_sharing_roles.type, vtiger_role.roleid, vtiger_role.rolename FROM vtiger_role
                INNER JOIN its4you_sharing_roles ON its4you_sharing_roles.roleid = vtiger_role.roleid
                WHERE its4you_sharing_roles.crmid = ?';
        $result = $db->pquery($sql, [$recordId]);

        while ($row = $db->fetchByAssoc($result)) {
            $users[$row['type']]['Roles'][$row['roleid']] = $row['rolename'];
        }

        $sql = 'SELECT its4you_sharing_rolessubroles.type, vtiger_role.roleid, vtiger_role.rolename FROM vtiger_role
                INNER JOIN its4you_sharing_rolessubroles ON its4you_sharing_rolessubroles.roleid = vtiger_role.roleid
                WHERE its4you_sharing_rolessubroles.crmid = ?';
        $result = $db->pquery($sql, [$recordId]);

        while ($row = $db->fetchByAssoc($result)) {
            $users[$row['type']]['RoleAndSubordinates'][$row['roleid']] = $row['rolename'];
        }

        if (false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany') && false !== Vtiger_Module_Model::getInstance('ITS4YouMultiCompany')->isActive()) {
            $sql = 'SELECT its4you_sharing_multicompany.type, its4you_sharing_multicompany.companyid, its4you_multicompany4you.companyname 
                FROM its4you_sharing_multicompany
                INNER JOIN its4you_multicompany4you ON its4you_multicompany4you.companyid=its4you_sharing_multicompany.companyid
                WHERE its4you_sharing_multicompany.crmid = ?';
            $result = $db->pquery($sql, [$recordId]);

            while ($row = $db->fetchByAssoc($result)) {
                $users[$row['type']]['MultiCompany4you'][$row['companyid']] = $row['companyname'];
            }
        }
        $role->setData($users);

        return $role;
    }

    /**
     * @param $recordId
     *
     * @return array|int|mixed|string|string[]|null
     * @throws Exception
     */
    public function getRecordName($recordId)
    {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT label FROM vtiger_crmentity WHERE crmid = ?', [$recordId]);

        return $db->query_result($result, 0, 'label');
    }
}